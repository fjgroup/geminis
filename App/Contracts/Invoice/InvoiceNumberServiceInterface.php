<?php

namespace App\Contracts\Invoice;

/**
 * Interface InvoiceNumberServiceInterface
 * 
 * Contrato para servicios de generación de números de factura
 * Cumple con Interface Segregation Principle (ISP)
 */
interface InvoiceNumberServiceInterface
{
    /**
     * Generar el siguiente número de factura
     *
     * @param string $prefix
     * @return string
     */
    public function generateNextInvoiceNumber(string $prefix = 'INV-'): string;

    /**
     * Validar formato de número de factura
     *
     * @param string $invoiceNumber
     * @return bool
     */
    public function isValidInvoiceNumber(string $invoiceNumber): bool;

    /**
     * Obtener el último número de factura generado
     *
     * @return string|null
     */
    public function getLastInvoiceNumber(): ?string;
}
