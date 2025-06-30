<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

trait AuditLogging
{
    /**
     * Log an admin action for audit purposes
     */
    protected function logAdminAction(string $action, $model = null, array $additionalData = []): void
    {
        $user = Auth::user();
        $request = request();

        $logData = [
            'action' => $action,
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'user_role' => $user?->role,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'timestamp' => now()->toISOString(),
        ];

        // Agregar información del modelo si se proporciona
        if ($model) {
            $logData['model_type'] = get_class($model);
            $logData['model_id'] = $model->id ?? null;
            
            // Agregar campos específicos del modelo si existen
            if (method_exists($model, 'getAuditableAttributes')) {
                $logData['model_data'] = $model->getAuditableAttributes();
            } else {
                // Campos comunes que suelen ser importantes para auditoría
                $auditableFields = ['name', 'email', 'status', 'role', 'title'];
                $modelData = [];
                
                foreach ($auditableFields as $field) {
                    if (isset($model->$field)) {
                        $modelData[$field] = $model->$field;
                    }
                }
                
                if (!empty($modelData)) {
                    $logData['model_data'] = $modelData;
                }
            }
        }

        // Agregar datos adicionales
        if (!empty($additionalData)) {
            $logData['additional_data'] = $additionalData;
        }

        // Log con nivel apropiado según la acción
        $level = $this->getLogLevel($action);
        Log::log($level, "Admin audit: {$action}", $logData);
    }

    /**
     * Log a security event
     */
    protected function logSecurityEvent(string $event, array $additionalData = []): void
    {
        $user = Auth::user();
        $request = request();

        $logData = [
            'security_event' => $event,
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'user_role' => $user?->role,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'timestamp' => now()->toISOString(),
        ];

        if (!empty($additionalData)) {
            $logData['additional_data'] = $additionalData;
        }

        Log::warning("Security event: {$event}", $logData);
    }

    /**
     * Log a data change for audit trail
     */
    protected function logDataChange(string $action, $model, array $oldData = [], array $newData = []): void
    {
        $changes = [];
        
        // Detectar cambios específicos
        foreach ($newData as $key => $newValue) {
            $oldValue = $oldData[$key] ?? null;
            
            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        $this->logAdminAction($action, $model, [
            'changes' => $changes,
            'changed_fields' => array_keys($changes),
        ]);
    }

    /**
     * Determinar el nivel de log según la acción
     */
    private function getLogLevel(string $action): string
    {
        $criticalActions = [
            'user_deleted',
            'admin_created',
            'permissions_changed',
            'system_settings_changed',
        ];

        $warningActions = [
            'user_created',
            'user_updated',
            'product_deleted',
            'payment_method_deleted',
        ];

        if (in_array($action, $criticalActions)) {
            return 'critical';
        }

        if (in_array($action, $warningActions)) {
            return 'warning';
        }

        return 'info';
    }

    /**
     * Log bulk operations
     */
    protected function logBulkOperation(string $operation, array $items, array $additionalData = []): void
    {
        $this->logAdminAction("bulk_{$operation}", null, [
            'operation' => $operation,
            'item_count' => count($items),
            'item_ids' => array_column($items, 'id'),
            'additional_data' => $additionalData,
        ]);
    }

    /**
     * Log failed operations
     */
    protected function logFailedOperation(string $operation, \Exception $exception, $model = null): void
    {
        $this->logAdminAction("failed_{$operation}", $model, [
            'error_message' => $exception->getMessage(),
            'error_code' => $exception->getCode(),
            'error_file' => $exception->getFile(),
            'error_line' => $exception->getLine(),
        ]);
    }
}
