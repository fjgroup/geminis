<?php

namespace App\Domains\Invoices\Application\Services;

use App\Domains\Invoices\Infrastructure\Persistence\Models\Invoice;
use App\Domains\Users\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de validación de facturas
 * 
 * Aplica Single Responsibility Principle - solo valida datos de facturas
 * Ubicado en Application layer según arquitectura hexagonal
 */
class InvoiceValidationService
{
    /**
     * Validar datos de creación de factura
     */
    public function validateInvoiceCreation(array $invoiceData): array
    {
        $errors = [];

        // Validar cliente requerido
        if (empty($invoiceData['client_id'])) {
            $errors[] = 'El cliente es requerido';
        } else {
            $client = User::find($invoiceData['client_id']);
            if (!$client) {
                $errors[] = 'Cliente no encontrado';
            }
        }

        // Validar fechas
        if (isset($invoiceData['issue_date'])) {
            if (!$this->isValidDate($invoiceData['issue_date'])) {
                $errors[] = 'Fecha de emisión no válida';
            }
        }

        if (isset($invoiceData['due_date'])) {
            if (!$this->isValidDate($invoiceData['due_date'])) {
                $errors[] = 'Fecha de vencimiento no válida';
            }
        }

        // Validar que la fecha de vencimiento sea posterior a la de emisión
        if (isset($invoiceData['issue_date']) && isset($invoiceData['due_date'])) {
            if ($invoiceData['due_date'] < $invoiceData['issue_date']) {
                $errors[] = 'La fecha de vencimiento debe ser posterior a la fecha de emisión';
            }
        }

        // Validar moneda
        if (isset($invoiceData['currency_code'])) {
            if (!$this->isValidCurrency($invoiceData['currency_code'])) {
                $errors[] = 'Código de moneda no válido';
            }
        }

        // Validar items si se proporcionan
        if (isset($invoiceData['items']) && is_array($invoiceData['items'])) {
            $itemErrors = $this->validateInvoiceItems($invoiceData['items']);
            $errors = array_merge($errors, $itemErrors);
        }

        // Validar tasas de impuestos
        if (isset($invoiceData['tax1_rate']) && ($invoiceData['tax1_rate'] < 0 || $invoiceData['tax1_rate'] > 100)) {
            $errors[] = 'La tasa de impuesto 1 debe estar entre 0 y 100';
        }

        if (isset($invoiceData['tax2_rate']) && ($invoiceData['tax2_rate'] < 0 || $invoiceData['tax2_rate'] > 100)) {
            $errors[] = 'La tasa de impuesto 2 debe estar entre 0 y 100';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validar items de factura
     */
    public function validateInvoiceItems(array $items): array
    {
        $errors = [];

        if (empty($items)) {
            $errors[] = 'La factura debe tener al menos un item';
            return $errors;
        }

        foreach ($items as $index => $item) {
            $itemNumber = $index + 1;

            // Validar descripción requerida
            if (empty($item['description'])) {
                $errors[] = "Item #{$itemNumber}: La descripción es requerida";
            }

            // Validar cantidad
            if (!isset($item['quantity']) || $item['quantity'] <= 0) {
                $errors[] = "Item #{$itemNumber}: La cantidad debe ser mayor a 0";
            }

            // Validar precio unitario
            if (!isset($item['unit_price']) || $item['unit_price'] < 0) {
                $errors[] = "Item #{$itemNumber}: El precio unitario debe ser mayor o igual a 0";
            }

            // Validar precio total si se proporciona
            if (isset($item['total_price'])) {
                $expectedTotal = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                if (abs($item['total_price'] - $expectedTotal) > 0.01) {
                    $errors[] = "Item #{$itemNumber}: El precio total no coincide con cantidad × precio unitario";
                }
            }
        }

        return $errors;
    }

    /**
     * Validar datos de actualización de factura
     */
    public function validateInvoiceUpdate(Invoice $invoice, array $updateData): array
    {
        $errors = [];

        // Verificar si la factura puede ser modificada
        if (!$this->canModifyInvoice($invoice)) {
            $errors[] = 'Esta factura no puede ser modificada';
            return [
                'valid' => false,
                'errors' => $errors
            ];
        }

        // Validar fechas si se proporcionan
        if (isset($updateData['issue_date'])) {
            if (!$this->isValidDate($updateData['issue_date'])) {
                $errors[] = 'Fecha de emisión no válida';
            }
        }

        if (isset($updateData['due_date'])) {
            if (!$this->isValidDate($updateData['due_date'])) {
                $errors[] = 'Fecha de vencimiento no válida';
            }
        }

        // Validar estado si se proporciona
        if (isset($updateData['status'])) {
            if (!$this->isValidInvoiceStatus($updateData['status'])) {
                $errors[] = 'Estado de factura no válido';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validar pago de factura
     */
    public function validateInvoicePayment(Invoice $invoice, array $paymentData): array
    {
        $errors = [];

        // Verificar que la factura pueda ser pagada
        if ($invoice->status !== 'unpaid') {
            $errors[] = 'Solo se pueden pagar facturas con estado "unpaid"';
        }

        // Validar monto del pago
        if (isset($paymentData['amount'])) {
            if ($paymentData['amount'] <= 0) {
                $errors[] = 'El monto del pago debe ser mayor a 0';
            }

            if ($paymentData['amount'] > $invoice->total_amount) {
                $errors[] = 'El monto del pago no puede ser mayor al total de la factura';
            }
        }

        // Validar método de pago si se proporciona
        if (isset($paymentData['payment_method_id'])) {
            // Aquí se podría validar que el método de pago existe y está activo
            // Por ahora solo verificamos que no esté vacío
            if (empty($paymentData['payment_method_id'])) {
                $errors[] = 'Método de pago no válido';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validar número de factura único
     */
    public function validateUniqueInvoiceNumber(string $invoiceNumber, int $excludeInvoiceId = null): bool
    {
        $query = Invoice::where('invoice_number', $invoiceNumber);
        
        if ($excludeInvoiceId) {
            $query->where('id', '!=', $excludeInvoiceId);
        }

        return !$query->exists();
    }

    /**
     * Validar si una fecha es válida
     */
    private function isValidDate(string $date): bool
    {
        try {
            $parsedDate = \Carbon\Carbon::parse($date);
            return $parsedDate->format('Y-m-d') === $date;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validar código de moneda
     */
    private function isValidCurrency(string $currencyCode): bool
    {
        $validCurrencies = ['USD', 'EUR', 'GBP', 'CAD', 'AUD', 'MXN', 'COP'];
        return in_array(strtoupper($currencyCode), $validCurrencies);
    }

    /**
     * Validar estado de factura
     */
    private function isValidInvoiceStatus(string $status): bool
    {
        $validStatuses = ['unpaid', 'paid', 'overdue', 'cancelled', 'refunded', 'collections', 'pending_confirmation'];
        return in_array($status, $validStatuses);
    }

    /**
     * Verificar si una factura puede ser modificada
     */
    private function canModifyInvoice(Invoice $invoice): bool
    {
        // No se pueden modificar facturas pagadas, canceladas o reembolsadas
        return !in_array($invoice->status, ['paid', 'cancelled', 'refunded']);
    }

    /**
     * Validar permisos de usuario para operaciones de factura
     */
    public function validateUserPermissions(User $user, Invoice $invoice, string $operation): array
    {
        $errors = [];

        switch ($operation) {
            case 'view':
                if (!$this->canViewInvoice($user, $invoice)) {
                    $errors[] = 'No tienes permisos para ver esta factura';
                }
                break;

            case 'edit':
                if (!$this->canEditInvoice($user, $invoice)) {
                    $errors[] = 'No tienes permisos para editar esta factura';
                }
                break;

            case 'delete':
                if (!$this->canDeleteInvoice($user, $invoice)) {
                    $errors[] = 'No tienes permisos para eliminar esta factura';
                }
                break;

            case 'pay':
                if (!$this->canPayInvoice($user, $invoice)) {
                    $errors[] = 'No tienes permisos para pagar esta factura';
                }
                break;
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Verificar si el usuario puede ver la factura
     */
    private function canViewInvoice(User $user, Invoice $invoice): bool
    {
        // Admins pueden ver cualquier factura
        if ($user->role === 'admin') {
            return true;
        }

        // Clientes pueden ver sus propias facturas
        if ($user->role === 'client' && $invoice->client_id === $user->id) {
            return true;
        }

        // Resellers pueden ver facturas de sus clientes
        if ($user->role === 'reseller' && $invoice->reseller_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Verificar si el usuario puede editar la factura
     */
    private function canEditInvoice(User $user, Invoice $invoice): bool
    {
        // Solo admins pueden editar facturas
        if ($user->role === 'admin') {
            return true;
        }

        // Resellers pueden editar facturas de sus clientes si no están pagadas
        if ($user->role === 'reseller' && $invoice->reseller_id === $user->id && $this->canModifyInvoice($invoice)) {
            return true;
        }

        return false;
    }

    /**
     * Verificar si el usuario puede eliminar la factura
     */
    private function canDeleteInvoice(User $user, Invoice $invoice): bool
    {
        // Solo admins pueden eliminar facturas
        return $user->role === 'admin' && $this->canModifyInvoice($invoice);
    }

    /**
     * Verificar si el usuario puede pagar la factura
     */
    private function canPayInvoice(User $user, Invoice $invoice): bool
    {
        // El cliente puede pagar sus propias facturas
        if ($user->role === 'client' && $invoice->client_id === $user->id && $invoice->status === 'unpaid') {
            return true;
        }

        // Admins pueden marcar facturas como pagadas
        if ($user->role === 'admin') {
            return true;
        }

        return false;
    }
}
