<?php

namespace App\Contracts\Invoice;

use App\Models\Invoice;

/**
 * Interface InvoiceValidationServiceInterface
 * 
 * Contrato para servicios de validación de facturas
 * Cumple con Interface Segregation Principle (ISP)
 */
interface InvoiceValidationServiceInterface
{
    /**
     * Verificar si una factura puede ser cancelada como nuevo servicio
     *
     * @param Invoice $invoice
     * @return array
     */
    public function canInvoiceBeCancelledAsNewService(Invoice $invoice): array;

    /**
     * Validar si una factura puede ser pagada
     *
     * @param Invoice $invoice
     * @return array
     */
    public function canInvoiceBePaid(Invoice $invoice): array;

    /**
     * Validar datos de factura antes de crear
     *
     * @param array $invoiceData
     * @return array
     */
    public function validateInvoiceData(array $invoiceData): array;

    /**
     * Verificar si una factura está vencida
     *
     * @param Invoice $invoice
     * @return bool
     */
    public function isInvoiceOverdue(Invoice $invoice): bool;
}
