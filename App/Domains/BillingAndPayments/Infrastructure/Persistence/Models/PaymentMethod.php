<?php

namespace App\Domains\BillingAndPayments\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Eloquent para PaymentMethod en arquitectura hexagonal
 *
 * Este es el modelo REAL que se conecta a la base de datos
 * Ubicado en Infrastructure layer como adaptador de persistencia
 */
class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'account_holder_name',
        'identification_number',
        'platform_name',
        'email_address',
        'payment_link',
        'account_number',
        'bank_name',
        'branch_name',
        'swift_code',
        'iban',
        'instructions',
        'is_active',
        'is_automatic',
        'logo_url',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_automatic' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['formatted_details'];

    /**
     * Get the formatted details for the payment method based on its type.
     *
     * @return array
     */
    public function getFormattedDetailsAttribute(): array
    {
        $details = [
            'type_label' => ucfirst(str_replace('_', ' ', $this->type ?? 'bank')),
            'type' => $this->type ?? 'bank',
            'name' => $this->name,
            'logo_url' => $this->logo_url,
        ];

        if ($this->type === 'bank') {
            $details += [
                'bank_name' => $this->bank_name,
                'account_number' => $this->account_number,
                'account_holder_name' => $this->account_holder_name,
                'identification_number' => $this->identification_number,
                'swift_code' => $this->swift_code,
                'iban' => $this->iban,
                'branch_name' => $this->branch_name,
            ];
        } elseif ($this->type === 'wallet' || $this->type === 'paypal_manual' || $this->type === 'crypto_wallet') {
            $details += [
                'platform_name' => $this->platform_name,
                'email_address' => $this->email_address,
                'account_holder_name' => $this->account_holder_name,
                'payment_link' => $this->payment_link,
                'wallet_address' => $this->type === 'crypto_wallet' ? $this->account_number : null,
            ];

            if ($this->type === 'crypto_wallet') {
                unset($details['account_number']);
            }
        }

        $details['instructions'] = $this->instructions;

        return array_filter($details, function($value) {
            return !is_null($value) && $value !== '';
        });
    }
}
