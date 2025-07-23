<?php

namespace App\Domains\Shared\Application\Services;

use App\Domains\Users\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Servicio centralizado para notificaciones
 * 
 * Aplica Single Responsibility Principle - solo se encarga de las notificaciones
 * Ubicado en Application layer según arquitectura hexagonal
 */
class NotificationService
{
    private EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Enviar notificación de bienvenida
     */
    public function sendWelcomeNotification(User $user): array
    {
        try {
            // Enviar email de bienvenida
            $emailResult = $this->emailService->sendWelcomeEmail($user);

            // Crear notificación en base de datos (simulado)
            $this->createDatabaseNotification($user, [
                'type' => 'welcome',
                'title' => 'Bienvenido a ' . config('app.name'),
                'message' => 'Tu cuenta ha sido creada exitosamente.',
                'data' => [
                    'user_id' => $user->id,
                    'created_at' => now()->toISOString()
                ]
            ]);

            Log::info('Notificación de bienvenida enviada', [
                'user_id' => $user->id,
                'email_sent' => $emailResult['success']
            ]);

            return [
                'success' => true,
                'message' => 'Notificación de bienvenida enviada',
                'email_result' => $emailResult
            ];

        } catch (\Exception $e) {
            Log::error('Error enviando notificación de bienvenida', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error enviando notificación de bienvenida'
            ];
        }
    }

    /**
     * Enviar notificación de factura generada
     */
    public function sendInvoiceNotification(User $user, $invoice): array
    {
        try {
            // Enviar email de factura
            $emailResult = $this->emailService->sendInvoiceEmail($user, $invoice);

            // Crear notificación en base de datos
            $this->createDatabaseNotification($user, [
                'type' => 'invoice_generated',
                'title' => 'Nueva factura generada',
                'message' => "Se ha generado la factura #{$invoice->invoice_number} por $" . number_format($invoice->total_amount, 2),
                'data' => [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'amount' => $invoice->total_amount,
                    'due_date' => $invoice->due_date->toISOString()
                ]
            ]);

            Log::info('Notificación de factura enviada', [
                'user_id' => $user->id,
                'invoice_id' => $invoice->id,
                'email_sent' => $emailResult['success']
            ]);

            return [
                'success' => true,
                'message' => 'Notificación de factura enviada',
                'email_result' => $emailResult
            ];

        } catch (\Exception $e) {
            Log::error('Error enviando notificación de factura', [
                'user_id' => $user->id,
                'invoice_id' => $invoice->id ?? null,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error enviando notificación de factura'
            ];
        }
    }

    /**
     * Enviar notificación de servicio activado
     */
    public function sendServiceActivatedNotification(User $user, $service): array
    {
        try {
            // Enviar email de servicio activado
            $emailResult = $this->emailService->sendServiceActivatedEmail($user, $service);

            // Crear notificación en base de datos
            $this->createDatabaseNotification($user, [
                'type' => 'service_activated',
                'title' => 'Servicio activado',
                'message' => "Tu servicio {$service->product->name} ha sido activado exitosamente.",
                'data' => [
                    'service_id' => $service->id,
                    'product_name' => $service->product->name,
                    'domain_name' => $service->domain_name,
                    'activated_at' => now()->toISOString()
                ]
            ]);

            Log::info('Notificación de servicio activado enviada', [
                'user_id' => $user->id,
                'service_id' => $service->id,
                'email_sent' => $emailResult['success']
            ]);

            return [
                'success' => true,
                'message' => 'Notificación de servicio activado enviada',
                'email_result' => $emailResult
            ];

        } catch (\Exception $e) {
            Log::error('Error enviando notificación de servicio activado', [
                'user_id' => $user->id,
                'service_id' => $service->id ?? null,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error enviando notificación de servicio activado'
            ];
        }
    }

    /**
     * Enviar notificación de pago recibido
     */
    public function sendPaymentReceivedNotification(User $user, $transaction): array
    {
        try {
            $data = [
                'client_name' => $user->name,
                'transaction_amount' => '$' . number_format($transaction->amount, 2),
                'transaction_date' => $transaction->transaction_date->format('d/m/Y'),
                'company_name' => config('app.name', 'FJ Group CA')
            ];

            // Enviar email simple
            $subject = 'Pago recibido - ' . config('app.name');
            $body = "<h1>Pago Recibido</h1><p>Hola {$data['client_name']},</p><p>Hemos recibido tu pago de {$data['transaction_amount']} el {$data['transaction_date']}.</p><p>Gracias por tu pago.</p>";
            
            $emailResult = $this->emailService->sendSimpleEmail($user->email, $subject, $body);

            // Crear notificación en base de datos
            $this->createDatabaseNotification($user, [
                'type' => 'payment_received',
                'title' => 'Pago recibido',
                'message' => "Hemos recibido tu pago de $" . number_format($transaction->amount, 2),
                'data' => [
                    'transaction_id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'transaction_date' => $transaction->transaction_date->toISOString()
                ]
            ]);

            Log::info('Notificación de pago recibido enviada', [
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'email_sent' => $emailResult['success']
            ]);

            return [
                'success' => true,
                'message' => 'Notificación de pago recibido enviada',
                'email_result' => $emailResult
            ];

        } catch (\Exception $e) {
            Log::error('Error enviando notificación de pago recibido', [
                'user_id' => $user->id,
                'transaction_id' => $transaction->id ?? null,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error enviando notificación de pago recibido'
            ];
        }
    }

    /**
     * Enviar notificación personalizada
     */
    public function sendCustomNotification(User $user, array $notificationData): array
    {
        try {
            // Validar datos requeridos
            if (empty($notificationData['title']) || empty($notificationData['message'])) {
                return [
                    'success' => false,
                    'message' => 'Título y mensaje son requeridos'
                ];
            }

            // Enviar email si se especifica
            if (!empty($notificationData['send_email']) && $notificationData['send_email']) {
                $subject = $notificationData['title'];
                $body = $notificationData['message'];
                
                $emailResult = $this->emailService->sendSimpleEmail($user->email, $subject, $body);
            } else {
                $emailResult = ['success' => true, 'message' => 'Email no solicitado'];
            }

            // Crear notificación en base de datos
            $this->createDatabaseNotification($user, [
                'type' => $notificationData['type'] ?? 'custom',
                'title' => $notificationData['title'],
                'message' => $notificationData['message'],
                'data' => $notificationData['data'] ?? []
            ]);

            Log::info('Notificación personalizada enviada', [
                'user_id' => $user->id,
                'type' => $notificationData['type'] ?? 'custom',
                'email_sent' => $emailResult['success']
            ]);

            return [
                'success' => true,
                'message' => 'Notificación personalizada enviada',
                'email_result' => $emailResult
            ];

        } catch (\Exception $e) {
            Log::error('Error enviando notificación personalizada', [
                'user_id' => $user->id,
                'notification_data' => $notificationData,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error enviando notificación personalizada'
            ];
        }
    }

    /**
     * Crear notificación en base de datos (simulado)
     */
    private function createDatabaseNotification(User $user, array $notificationData): void
    {
        // Simulación de creación de notificación en base de datos
        // En implementación real, esto crearía un registro en la tabla notifications
        
        Log::info('Notificación creada en base de datos (simulado)', [
            'user_id' => $user->id,
            'type' => $notificationData['type'],
            'title' => $notificationData['title'],
            'created_at' => now()->toISOString()
        ]);
    }

    /**
     * Obtener notificaciones de un usuario (simulado)
     */
    public function getUserNotifications(User $user, int $limit = 10): array
    {
        // Simulación de obtención de notificaciones
        // En implementación real, esto consultaría la base de datos
        
        $notifications = [
            [
                'id' => 1,
                'type' => 'welcome',
                'title' => 'Bienvenido a ' . config('app.name'),
                'message' => 'Tu cuenta ha sido creada exitosamente.',
                'read_at' => null,
                'created_at' => now()->subDays(1)->toISOString()
            ]
        ];

        return [
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => 1
        ];
    }

    /**
     * Marcar notificación como leída (simulado)
     */
    public function markAsRead(User $user, int $notificationId): array
    {
        // Simulación de marcar como leída
        Log::info('Notificación marcada como leída (simulado)', [
            'user_id' => $user->id,
            'notification_id' => $notificationId
        ]);

        return [
            'success' => true,
            'message' => 'Notificación marcada como leída'
        ];
    }

    /**
     * Marcar todas las notificaciones como leídas (simulado)
     */
    public function markAllAsRead(User $user): array
    {
        // Simulación de marcar todas como leídas
        Log::info('Todas las notificaciones marcadas como leídas (simulado)', [
            'user_id' => $user->id
        ]);

        return [
            'success' => true,
            'message' => 'Todas las notificaciones marcadas como leídas'
        ];
    }
}
