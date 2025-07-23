<?php

namespace App\Domains\Shared\Application\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Servicio centralizado para manejo de archivos subidos
 * 
 * Aplica Single Responsibility Principle - solo se encarga de la gestión de archivos
 * Ubicado en Application layer según arquitectura hexagonal
 */
class FileUploadService
{
    private array $allowedExtensions;
    private int $maxFileSize;
    private bool $scanForMalware;

    public function __construct()
    {
        $this->allowedExtensions = config('security.file_upload.allowed_extensions', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);
        $this->maxFileSize = config('security.file_upload.max_file_size', 2048) * 1024; // Convert KB to bytes
        $this->scanForMalware = config('security.file_upload.scan_for_malware', false);
    }

    /**
     * Subir archivo con validaciones de seguridad
     */
    public function uploadFile(UploadedFile $file, string $directory = 'uploads', array $options = []): array
    {
        try {
            // Validar archivo
            $validation = $this->validateFile($file, $options);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message'],
                    'file_path' => null
                ];
            }

            // Generar nombre único para el archivo
            $fileName = $this->generateUniqueFileName($file, $options);
            
            // Determinar ruta de almacenamiento
            $storagePath = $directory . '/' . $fileName;

            // Subir archivo
            $path = $file->storeAs($directory, $fileName, 'public');

            if (!$path) {
                return [
                    'success' => false,
                    'message' => 'Error almacenando el archivo',
                    'file_path' => null
                ];
            }

            // Escanear por malware si está habilitado
            if ($this->scanForMalware) {
                $scanResult = $this->scanFileForMalware(Storage::disk('public')->path($path));
                if (!$scanResult['safe']) {
                    // Eliminar archivo infectado
                    Storage::disk('public')->delete($path);
                    
                    Log::warning('Archivo infectado eliminado', [
                        'file_name' => $fileName,
                        'scan_result' => $scanResult
                    ]);

                    return [
                        'success' => false,
                        'message' => 'Archivo rechazado por razones de seguridad',
                        'file_path' => null
                    ];
                }
            }

            Log::info('Archivo subido exitosamente', [
                'file_name' => $fileName,
                'file_size' => $file->getSize(),
                'file_type' => $file->getMimeType(),
                'storage_path' => $path
            ]);

            return [
                'success' => true,
                'message' => 'Archivo subido exitosamente',
                'file_path' => $path,
                'file_name' => $fileName,
                'file_size' => $file->getSize(),
                'file_type' => $file->getMimeType(),
                'url' => Storage::disk('public')->url($path)
            ];

        } catch (\Exception $e) {
            Log::error('Error subiendo archivo', [
                'error' => $e->getMessage(),
                'file_name' => $file->getClientOriginalName()
            ]);

            return [
                'success' => false,
                'message' => 'Error interno subiendo archivo',
                'file_path' => null
            ];
        }
    }

    /**
     * Subir múltiples archivos
     */
    public function uploadMultipleFiles(array $files, string $directory = 'uploads', array $options = []): array
    {
        $results = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($files as $index => $file) {
            if ($file instanceof UploadedFile) {
                $result = $this->uploadFile($file, $directory, $options);
                $results[] = $result;
                
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failureCount++;
                }
            } else {
                $results[] = [
                    'success' => false,
                    'message' => 'Archivo inválido en posición ' . $index,
                    'file_path' => null
                ];
                $failureCount++;
            }
        }

        return [
            'success' => $successCount > 0,
            'message' => "Subidos: {$successCount}, Fallidos: {$failureCount}",
            'results' => $results,
            'success_count' => $successCount,
            'failure_count' => $failureCount
        ];
    }

    /**
     * Eliminar archivo
     */
    public function deleteFile(string $filePath): array
    {
        try {
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
                
                Log::info('Archivo eliminado exitosamente', [
                    'file_path' => $filePath
                ]);

                return [
                    'success' => true,
                    'message' => 'Archivo eliminado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Archivo no encontrado'
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error eliminando archivo', [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error eliminando archivo'
            ];
        }
    }

    /**
     * Validar archivo subido
     */
    private function validateFile(UploadedFile $file, array $options = []): array
    {
        // Validar tamaño
        if ($file->getSize() > $this->maxFileSize) {
            return [
                'valid' => false,
                'message' => 'El archivo excede el tamaño máximo permitido (' . ($this->maxFileSize / 1024) . ' KB)'
            ];
        }

        // Validar extensión
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = $options['allowed_extensions'] ?? $this->allowedExtensions;
        
        if (!in_array($extension, $allowedExtensions)) {
            return [
                'valid' => false,
                'message' => 'Tipo de archivo no permitido. Extensiones permitidas: ' . implode(', ', $allowedExtensions)
            ];
        }

        // Validar MIME type
        $mimeType = $file->getMimeType();
        if (!$this->isValidMimeType($mimeType, $extension)) {
            return [
                'valid' => false,
                'message' => 'Tipo de archivo no válido'
            ];
        }

        // Validar que el archivo no esté corrupto
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return [
                'valid' => false,
                'message' => 'Error en la subida del archivo'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Archivo válido'
        ];
    }

    /**
     * Generar nombre único para el archivo
     */
    private function generateUniqueFileName(UploadedFile $file, array $options = []): string
    {
        $extension = $file->getClientOriginalExtension();
        $prefix = $options['prefix'] ?? '';
        $timestamp = $options['include_timestamp'] ?? true;

        if ($timestamp) {
            $name = $prefix . time() . '_' . Str::random(8) . '.' . $extension;
        } else {
            $name = $prefix . Str::random(16) . '.' . $extension;
        }

        return $name;
    }

    /**
     * Validar MIME type
     */
    private function isValidMimeType(string $mimeType, string $extension): bool
    {
        $validMimeTypes = [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'txt' => ['text/plain'],
            'zip' => ['application/zip'],
        ];

        $allowedMimeTypes = $validMimeTypes[$extension] ?? [];
        
        return in_array($mimeType, $allowedMimeTypes);
    }

    /**
     * Escanear archivo por malware (simulado)
     */
    private function scanFileForMalware(string $filePath): array
    {
        // Simulación de escaneo de malware
        // En implementación real, aquí se integraría con un servicio de escaneo
        
        try {
            // Simular escaneo
            usleep(100000); // 0.1 segundos de delay

            // Simular resultado (99% de archivos son seguros)
            $isSafe = rand(1, 100) <= 99;

            return [
                'safe' => $isSafe,
                'scan_time' => 0.1,
                'threats_found' => $isSafe ? 0 : 1,
                'scanner' => 'SimulatedScanner'
            ];

        } catch (\Exception $e) {
            Log::error('Error escaneando archivo', [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);

            // En caso de error, asumir que no es seguro
            return [
                'safe' => false,
                'scan_time' => 0,
                'threats_found' => 1,
                'scanner' => 'SimulatedScanner',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener información de un archivo
     */
    public function getFileInfo(string $filePath): array
    {
        try {
            if (!Storage::disk('public')->exists($filePath)) {
                return [
                    'exists' => false,
                    'message' => 'Archivo no encontrado'
                ];
            }

            $fullPath = Storage::disk('public')->path($filePath);
            $size = Storage::disk('public')->size($filePath);
            $lastModified = Storage::disk('public')->lastModified($filePath);

            return [
                'exists' => true,
                'file_path' => $filePath,
                'full_path' => $fullPath,
                'size' => $size,
                'size_human' => $this->formatBytes($size),
                'last_modified' => date('Y-m-d H:i:s', $lastModified),
                'url' => Storage::disk('public')->url($filePath),
                'mime_type' => Storage::disk('public')->mimeType($filePath)
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo información del archivo', [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);

            return [
                'exists' => false,
                'message' => 'Error obteniendo información del archivo'
            ];
        }
    }

    /**
     * Formatear bytes a formato legible
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Limpiar archivos antiguos
     */
    public function cleanupOldFiles(string $directory, int $daysOld = 30): array
    {
        try {
            $files = Storage::disk('public')->files($directory);
            $deletedCount = 0;
            $cutoffTime = now()->subDays($daysOld)->timestamp;

            foreach ($files as $file) {
                $lastModified = Storage::disk('public')->lastModified($file);
                
                if ($lastModified < $cutoffTime) {
                    Storage::disk('public')->delete($file);
                    $deletedCount++;
                }
            }

            Log::info('Limpieza de archivos antiguos completada', [
                'directory' => $directory,
                'days_old' => $daysOld,
                'deleted_count' => $deletedCount
            ]);

            return [
                'success' => true,
                'message' => "Se eliminaron {$deletedCount} archivos antiguos",
                'deleted_count' => $deletedCount
            ];

        } catch (\Exception $e) {
            Log::error('Error en limpieza de archivos antiguos', [
                'directory' => $directory,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error en la limpieza de archivos',
                'deleted_count' => 0
            ];
        }
    }
}
