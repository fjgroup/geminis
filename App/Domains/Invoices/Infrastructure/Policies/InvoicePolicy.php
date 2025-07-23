<?php

namespace App\Domains\Invoices\Infrastructure\Policies;

use App\Domains\Users\Infrastructure\Persistence\Models\User;
use App\Domains\Invoices\Infrastructure\Persistence\Models\Invoice;

/**
 * Policy para autorización de facturas en arquitectura hexagonal
 * 
 * Ubicado en Infrastructure layer como adaptador de autorización
 * Cumple con Single Responsibility Principle - solo maneja autorización de facturas
 */
class InvoicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'reseller', 'client']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        // Admin puede ver cualquier factura
        if ($user->role === 'admin') {
            return true;
        }

        // Reseller puede ver facturas de sus clientes
        if ($user->role === 'reseller') {
            return $invoice->client->reseller_id === $user->id;
        }

        // Cliente puede ver solo sus propias facturas
        return $user->role === 'client' && $invoice->client_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'reseller']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        // Solo admin puede actualizar facturas
        if ($user->role !== 'admin') {
            return false;
        }

        // No se pueden actualizar facturas pagadas
        return $invoice->status !== 'paid';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        // Solo admin puede eliminar facturas
        if ($user->role !== 'admin') {
            return false;
        }

        // No se pueden eliminar facturas pagadas
        return $invoice->status !== 'paid';
    }

    /**
     * Determine whether the user can pay the invoice.
     */
    public function pay(User $user, Invoice $invoice): bool
    {
        // Admin puede marcar como pagada cualquier factura
        if ($user->role === 'admin') {
            return $invoice->status === 'pending';
        }

        // Cliente puede pagar solo sus propias facturas pendientes
        return $user->role === 'client' 
            && $invoice->client_id === $user->id 
            && $invoice->status === 'pending';
    }

    /**
     * Determine whether the user can cancel the invoice.
     */
    public function cancel(User $user, Invoice $invoice): bool
    {
        // Solo admin puede cancelar facturas
        if ($user->role !== 'admin') {
            return false;
        }

        // Solo se pueden cancelar facturas pendientes
        return $invoice->status === 'pending';
    }

    /**
     * Determine whether the user can download the invoice.
     */
    public function download(User $user, Invoice $invoice): bool
    {
        return $this->view($user, $invoice);
    }

    /**
     * Determine whether the user can send the invoice by email.
     */
    public function sendByEmail(User $user, Invoice $invoice): bool
    {
        // Admin puede enviar cualquier factura
        if ($user->role === 'admin') {
            return true;
        }

        // Reseller puede enviar facturas de sus clientes
        if ($user->role === 'reseller') {
            return $invoice->client->reseller_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can refund the invoice.
     */
    public function refund(User $user, Invoice $invoice): bool
    {
        // Solo admin puede hacer reembolsos
        if ($user->role !== 'admin') {
            return false;
        }

        // Solo se pueden reembolsar facturas pagadas
        return $invoice->status === 'paid';
    }

    /**
     * Determine whether the user can view invoice items.
     */
    public function viewItems(User $user, Invoice $invoice): bool
    {
        return $this->view($user, $invoice);
    }

    /**
     * Determine whether the user can add items to the invoice.
     */
    public function addItems(User $user, Invoice $invoice): bool
    {
        // Solo admin puede agregar items
        if ($user->role !== 'admin') {
            return false;
        }

        // Solo a facturas pendientes
        return $invoice->status === 'pending';
    }

    /**
     * Determine whether the user can remove items from the invoice.
     */
    public function removeItems(User $user, Invoice $invoice): bool
    {
        return $this->addItems($user, $invoice);
    }
}
