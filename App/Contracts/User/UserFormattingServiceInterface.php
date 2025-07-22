<?php

namespace App\Contracts\User;

use App\Models\User;

/**
 * Interface UserFormattingServiceInterface
 * 
 * Contrato para servicios de formateo de datos de usuario
 * Cumple con Interface Segregation Principle (ISP)
 */
interface UserFormattingServiceInterface
{
    /**
     * Formatear el balance del usuario como moneda
     *
     * @param User $user
     * @return string
     */
    public function formatBalance(User $user): string;

    /**
     * Formatear el nombre completo del usuario
     *
     * @param User $user
     * @return string
     */
    public function formatFullName(User $user): string;

    /**
     * Formatear la dirección completa del usuario
     *
     * @param User $user
     * @return string
     */
    public function formatAddress(User $user): string;

    /**
     * Formatear el estado del usuario para mostrar
     *
     * @param User $user
     * @return array
     */
    public function formatStatus(User $user): array;

    /**
     * Formatear el rol del usuario para mostrar
     *
     * @param User $user
     * @return string
     */
    public function formatRole(User $user): string;
}
