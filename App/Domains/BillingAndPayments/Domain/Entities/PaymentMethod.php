<?php

namespace App\Domains\BillingAndPayments\Domain\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug', // Nuevo campo añadido
        'type', // Added
        'account_holder_name',
        'identification_number', // Added
        'platform_name', // Added
        'email_address', // Added
        'payment_link', // Added
        'account_number',
        'bank_name',
        'branch_name',
        'swift_code',
        'iban',
        'instructions',
        'is_active',
        'is_automatic', // Nuevo campo añadido
        'logo_url',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_automatic' => 'boolean', // Cast for the new field
        // 'type' => PaymentMethodTypeEnum::class, // Example if using Enums in future
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
            'type_label' => ucfirst(str_replace('_', ' ', $this->type ?? 'bank')), // e.g. 'Bank', 'Paypal Manual'
            'type' => $this->type ?? 'bank',
            'name' => $this->name, // Admin-defined descriptive name
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
            // Grouping wallet-like types
            $details += [
                'platform_name' => $this->platform_name,
                'email_address' => $this->email_address,
                'account_holder_name' => $this->account_holder_name, // Can be used for wallet holder name/ID
                'payment_link' => $this->payment_link,
                // For crypto, 'account_number' could store the wallet address
                'wallet_address' => $this->type === 'crypto_wallet' ? $this->account_number : null,

            ];
             // If it's a crypto_wallet, and account_number holds the address, don't show it as 'account_number'
            if ($this->type === 'crypto_wallet') {
                unset($details['account_number']);
            }

        }
        // other types can be added here, e.g. 'cash', 'check'

        // Common details always included if present
        $details['instructions'] = $this->instructions;

        // Filter out null or empty string values from the specific fields before returning
        return array_filter($details, function($value) {
            return !is_null($value) && $value !== '';
        });
    }
}
