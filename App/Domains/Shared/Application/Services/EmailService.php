<?php

namespace App\Domains\Shared\Application\Services;

use App\Domains\Users\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Mailable;

/**
 * Servicio centralizado para envío de correos electrónicos
 * 
 * Aplica Single Responsibility Principle - solo se encarga del envío de emails
 * Ubicado en Application layer según arquitectura hexagonal
 */
class EmailService
{
    /**
     * Enviar email usando plantilla
     */
    public function sendEmail(User $recipient, string $templateSlug, array $data = [], ?User $resellerContext = null): array
    {
        try {
            // Buscar plantilla de correo
            $template = $this->findEmailTemplate($templateSlug, $resellerContext);
            
            if (!$template) {
                Log::error('Plantilla de email no encontrada', [
                    'template_slug' => $templateSlug,
                    'recipient_id' => $recipient->id,
                    'reseller_context' => $resellerContext?->id
                ]);

                return [
                    'success' => false,
                    'message' => 'Plantilla de email no encontrada'
                ];
            }

            // Parsear subject y body con datos
            $subject = $this->parseTemplate($template['subject'], $data);
            $bodyHtml = $this->parseTemplate($template['body_html'], $data);

            // Crear mailable
            $mailable = new class($subject, $bodyHtml) extends Mailable {
                private string $emailSubject;
                private string $emailBody;

                public function __construct(string $subject, string $body)
                {
                    $this->emailSubject = $subject;
                    $this->emailBody = $body;
                }

                public function build()
                {
                    return $this->subject($this->emailSubject)
                        ->html($this->emailBody);
                }
            };

            // Enviar email
            Mail::to($recipient->email)->send($mailable);

            Log::info('Email enviado exitosamente', [
                'template_slug' => $templateSlug,
                'recipient_id' => $recipient->id,
                'recipient_email' => $recipient->email,
                'subject' => $subject
            ]);

            return [
                'success' => true,
                'message' => 'Email enviado exitosamente'
            ];

        } catch (\Exception $e) {
            Log::error('Error enviando email', [
                'template_slug' => $templateSlug,
                'recipient_id' => $recipient->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error enviando email: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Enviar email simple sin plantilla
     */
    public function sendSimpleEmail(string $to, string $subject, string $body, array $options = []): array
    {
        try {
            $mailable = new class($subject, $body) extends Mailable {
                private string $emailSubject;
                private string $emailBody;

                public function __construct(string $subject, string $body)
                {
                    $this->emailSubject = $subject;
                    $this->emailBody = $body;
                }

                public function build()
                {
                    return $this->subject($this->emailSubject)
                        ->html($this->emailBody);
                }
            };

            Mail::to($to)->send($mailable);

            Log::info('Email simple enviado exitosamente', [
                'to' => $to,
                'subject' => $subject
            ]);

            return [
                'success' => true,
                'message' => 'Email enviado exitosamente'
            ];

        } catch (\Exception $e) {
            Log::error('Error enviando email simple', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error enviando email: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Enviar email de bienvenida
     */
    public function sendWelcomeEmail(User $user): array
    {
        $data = [
            'client_name' => $user->name,
            'client_email' => $user->email,
            'login_url' => route('login'),
            'company_name' => config('app.name', 'FJ Group CA')
        ];

        return $this->sendEmail($user, 'welcome', $data);
    }

    /**
     * Enviar email de factura generada
     */
    public function sendInvoiceEmail(User $user, $invoice): array
    {
        $data = [
            'client_name' => $user->name,
            'invoice_number' => $invoice->invoice_number,
            'invoice_amount' => '$' . number_format($invoice->total_amount, 2),
            'due_date' => $invoice->due_date->format('d/m/Y'),
            'invoice_url' => route('client.invoices.show', $invoice->id),
            'company_name' => config('app.name', 'FJ Group CA')
        ];

        return $this->sendEmail($user, 'invoice_generated', $data);
    }

    /**
     * Enviar email de servicio activado
     */
    public function sendServiceActivatedEmail(User $user, $service): array
    {
        $data = [
            'client_name' => $user->name,
            'service_name' => $service->product->name,
            'service_domain' => $service->domain_name,
            'service_url' => route('client.services.show', $service->id),
            'company_name' => config('app.name', 'FJ Group CA')
        ];

        return $this->sendEmail($user, 'service_activated', $data);
    }

    /**
     * Buscar plantilla de email
     */
    private function findEmailTemplate(string $templateSlug, ?User $resellerContext = null): ?array
    {
        // Simulación de búsqueda de plantilla
        // En implementación real, esto buscaría en la base de datos
        
        $templates = [
            'welcome' => [
                'subject' => 'Bienvenido a {{ company_name }}',
                'body_html' => '<h1>¡Hola {{ client_name }}!</h1><p>Bienvenido a {{ company_name }}. Tu cuenta ha sido creada exitosamente.</p><p>Puedes acceder a tu panel en: <a href="{{ login_url }}">{{ login_url }}</a></p>'
            ],
            'invoice_generated' => [
                'subject' => 'Nueva factura #{{ invoice_number }} - {{ company_name }}',
                'body_html' => '<h1>Nueva Factura</h1><p>Hola {{ client_name }},</p><p>Se ha generado una nueva factura:</p><ul><li>Número: {{ invoice_number }}</li><li>Monto: {{ invoice_amount }}</li><li>Fecha de vencimiento: {{ due_date }}</li></ul><p><a href="{{ invoice_url }}">Ver factura</a></p>'
            ],
            'service_activated' => [
                'subject' => 'Servicio activado: {{ service_name }}',
                'body_html' => '<h1>Servicio Activado</h1><p>Hola {{ client_name }},</p><p>Tu servicio {{ service_name }} ha sido activado exitosamente.</p><p>Dominio: {{ service_domain }}</p><p><a href="{{ service_url }}">Ver detalles del servicio</a></p>'
            ]
        ];

        return $templates[$templateSlug] ?? null;
    }

    /**
     * Parsear plantilla reemplazando placeholders
     */
    private function parseTemplate(string $template, array $data): string
    {
        $parsed = $template;

        foreach ($data as $key => $value) {
            $placeholder = '{{ ' . $key . ' }}';
            $parsed = str_replace($placeholder, $value, $parsed);
        }

        return $parsed;
    }

    /**
     * Validar dirección de email
     */
    public function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Obtener configuración de email
     */
    public function getEmailConfiguration(): array
    {
        return [
            'driver' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
        ];
    }

    /**
     * Verificar si el servicio de email está configurado
     */
    public function isConfigured(): bool
    {
        $config = $this->getEmailConfiguration();
        
        return !empty($config['host']) && 
               !empty($config['from_address']) && 
               !empty($config['from_name']);
    }

    /**
     * Enviar email de prueba
     */
    public function sendTestEmail(string $to): array
    {
        $subject = 'Email de prueba - ' . config('app.name');
        $body = '<h1>Email de Prueba</h1><p>Este es un email de prueba enviado desde ' . config('app.name') . '</p><p>Fecha: ' . now()->format('d/m/Y H:i:s') . '</p>';

        return $this->sendSimpleEmail($to, $subject, $body);
    }

    /**
     * Obtener estadísticas de emails enviados
     */
    public function getEmailStats(): array
    {
        // En implementación real, esto consultaría logs o base de datos
        return [
            'emails_sent_today' => 0,
            'emails_sent_this_week' => 0,
            'emails_sent_this_month' => 0,
            'failed_emails_today' => 0,
            'last_email_sent' => null,
        ];
    }
}
