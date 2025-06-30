<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class InputSanitizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Sanitizar entrada para todas las requests
        $this->sanitizeInput($request);

        // Validar tamaño de payload
        $this->validatePayloadSize($request);

        // Detectar patrones maliciosos
        $this->detectMaliciousPatterns($request);

        return $next($request);
    }

    /**
     * Sanitizar entrada de datos
     */
    private function sanitizeInput(Request $request): void
    {
        $input = $request->all();
        
        $sanitized = $this->recursiveSanitize($input);
        
        $request->replace($sanitized);
    }

    /**
     * Sanitización recursiva de arrays
     */
    private function recursiveSanitize($data): array
    {
        if (!is_array($data)) {
            return $data;
        }

        $sanitized = [];
        
        foreach ($data as $key => $value) {
            // Sanitizar la clave
            $cleanKey = $this->sanitizeString($key);
            
            if (is_array($value)) {
                $sanitized[$cleanKey] = $this->recursiveSanitize($value);
            } elseif (is_string($value)) {
                $sanitized[$cleanKey] = $this->sanitizeString($value);
            } else {
                $sanitized[$cleanKey] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitizar strings individuales
     */
    private function sanitizeString(string $value): string
    {
        // Remover caracteres de control
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
        
        // Normalizar espacios en blanco
        $value = preg_replace('/\s+/', ' ', $value);
        
        // Trim espacios
        $value = trim($value);
        
        // Limitar longitud máxima
        if (strlen($value) > 10000) {
            $value = substr($value, 0, 10000);
            Log::warning('Input truncated due to excessive length', [
                'original_length' => strlen($value),
                'truncated_length' => 10000,
            ]);
        }

        return $value;
    }

    /**
     * Validar tamaño del payload
     */
    private function validatePayloadSize(Request $request): void
    {
        $maxSize = 1024 * 1024; // 1MB
        $contentLength = $request->header('Content-Length', 0);
        
        if ($contentLength > $maxSize) {
            Log::warning('Large payload detected', [
                'content_length' => $contentLength,
                'max_allowed' => $maxSize,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);
            
            abort(413, 'Payload too large');
        }
    }

    /**
     * Detectar patrones maliciosos en la entrada
     */
    private function detectMaliciousPatterns(Request $request): void
    {
        $maliciousPatterns = [
            // SQL Injection
            '/(\bUNION\b.*\bSELECT\b|\bSELECT\b.*\bFROM\b|\bINSERT\b.*\bINTO\b|\bDELETE\b.*\bFROM\b|\bDROP\b.*\bTABLE\b)/i',
            
            // XSS
            '/<script[^>]*>.*?<\/script>/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            
            // Path Traversal
            '/\.\.\/|\.\.\\\\/',
            
            // Command Injection
            '/(\b(exec|system|shell_exec|passthru|eval|file_get_contents|file_put_contents|fopen|fwrite)\b)/i',
            
            // LDAP Injection
            '/(\*|\(|\)|\||&)/i',
            
            // NoSQL Injection
            '/(\$where|\$ne|\$gt|\$lt|\$regex)/i',
        ];

        $input = json_encode($request->all());
        
        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                Log::critical('Malicious pattern detected in input', [
                    'pattern' => $pattern,
                    'input' => substr($input, 0, 500), // Solo los primeros 500 caracteres
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->fullUrl(),
                    'user_id' => auth()->id(),
                ]);
                
                // Opcional: bloquear la request
                abort(400, 'Malicious input detected');
            }
        }
    }
}
