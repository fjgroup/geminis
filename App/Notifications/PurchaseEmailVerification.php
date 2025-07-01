<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class PurchaseEmailVerification extends VerifyEmail
{
    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'verification.verify.purchase',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return $this->buildMailMessage($verificationUrl);
    }

    /**
     * Get the verify email notification mail message for the given URL.
     *
     * @param  string  $url
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    protected function buildMailMessage($url)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Verifica tu email para continuar con tu compra')
            ->greeting('¡Hola!')
            ->line('Estás a un paso de completar tu compra.')
            ->line('Por favor, haz clic en el botón de abajo para verificar tu dirección de email y continuar con tu pedido.')
            ->action('Verificar Email y Continuar Compra', $url)
            ->line('Si no creaste esta cuenta, no es necesario realizar ninguna acción.')
            ->line('¡Gracias por elegirnos!')
            ->salutation('Saludos, El equipo de ' . config('app.name'));
    }
}
