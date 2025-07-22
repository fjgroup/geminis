<?php

namespace App\Domains\Users\Services;

use App\Contracts\User\UserFormattingServiceInterface;
use App\Domains\Users\Models\User;
use NumberFormatter;

/**
 * Class UserFormattingService
 *
 * Servicio responsable del formateo de datos del usuario
 * Cumple con el Principio de Responsabilidad Única (SRP)
 * Implementa UserFormattingServiceInterface (DIP)
 */
class UserFormattingService implements UserFormattingServiceInterface
{
    /**
     * Formatear el balance del usuario como moneda
     *
     * @param User $user
     * @return string
     */
    public function formatBalance(User $user): string
    {
        $balance = $user->balance ?? 0;
        $currencyCode = $user->currency_code ?? 'USD';

        if (class_exists('NumberFormatter')) {
            $locale = config('app.locale', 'en_US');
            $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
            return $formatter->formatCurrency($balance, $currencyCode);
        }

        // Fallback basic formatting if NumberFormatter is not available
        return $currencyCode . ' ' . number_format($balance, 2);
    }

    /**
     * Formatear el nombre completo del usuario
     *
     * @param User $user
     * @return string
     */
    public function formatFullName(User $user): string
    {
        $name = trim($user->name ?? '');
        $companyName = trim($user->company_name ?? '');

        if (empty($name) && empty($companyName)) {
            return 'Usuario sin nombre';
        }

        if (!empty($companyName)) {
            return !empty($name) ? "{$name} ({$companyName})" : $companyName;
        }

        return $name;
    }

    /**
     * Formatear la dirección completa del usuario
     *
     * @param User $user
     * @return string
     */
    public function formatAddress(User $user): string
    {
        $addressParts = array_filter([
            $user->address,
            $user->city,
            $user->state,
            $user->postal_code,
            $user->country
        ]);

        return implode(', ', $addressParts);
    }

    /**
     * Formatear el estado del usuario para mostrar
     *
     * @param User $user
     * @return array
     */
    public function formatStatus(User $user): array
    {
        $statusMap = [
            'active' => ['label' => 'Activo', 'color' => 'green'],
            'inactive' => ['label' => 'Inactivo', 'color' => 'red'],
            'pending' => ['label' => 'Pendiente', 'color' => 'yellow'],
            'suspended' => ['label' => 'Suspendido', 'color' => 'orange'],
        ];

        $status = $user->status ?? 'inactive';

        return $statusMap[$status] ?? ['label' => 'Desconocido', 'color' => 'gray'];
    }

    /**
     * Formatear el rol del usuario para mostrar
     *
     * @param User $user
     * @return string
     */
    public function formatRole(User $user): string
    {
        $roleMap = [
            'admin' => 'Administrador',
            'reseller' => 'Revendedor',
            'client' => 'Cliente',
        ];

        return $roleMap[$user->role] ?? 'Rol desconocido';
    }
}
