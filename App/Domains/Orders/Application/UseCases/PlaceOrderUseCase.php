<?php

namespace App\Domains\Orders\Application\UseCases;

use App\Domains\Orders\Application\Services\CartValidationService;
use App\Domains\Orders\Application\Services\InvoiceCreationService;
use App\Domains\Orders\Application\Services\ClientServiceCreationService;
use App\Domains\Users\Infrastructure\Persistence\Models\User;
use App\Domains\Invoices\Infrastructure\Persistence\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Use Case para procesar una orden completa
 * 
 * Aplica Single Responsibility Principle - solo coordina el proceso de orden
 * Delega responsabilidades específicas a servicios especializados
 * Ubicado en Application layer según arquitectura hexagonal
 */
class PlaceOrderUseCase
{
    public function __construct(
        private CartValidationService $cartValidationService,
        private InvoiceCreationService $invoiceCreationService,
        private ClientServiceCreationService $clientServiceCreationService
    ) {}

    /**
     * Ejecutar el proceso completo de colocación de orden
     *
     * @param User|null $client
     * @param array $additionalData
     * @return Invoice
     * @throws Exception
     */
    public function execute(?User $client = null, array $additionalData = []): Invoice
    {
        // Validar cliente
        $client = $this->validateClient($client);
        
        // Obtener y validar carrito
        $cart = $this->getAndValidateCart();
        
        // Iniciar transacción
        DB::beginTransaction();
        
        try {
            // Crear factura con items
            $invoice = $this->invoiceCreationService->createInvoiceFromCart(
                $client,
                $cart,
                $additionalData
            );
            
            // Crear servicios de cliente
            $this->clientServiceCreationService->createClientServicesFromCart(
                $client,
                $cart,
                $invoice
            );
            
            // Confirmar transacción
            DB::commit();
            
            // Limpiar carrito
            session()->forget('cart');
            
            Log::info('Order placed successfully', [
                'client_id' => $client->id,
                'invoice_id' => $invoice->id,
                'total_amount' => $invoice->total_amount
            ]);
            
            return $invoice;
            
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error("Error placing order for client ID {$client->id}: " . $e->getMessage(), [
                'cart' => $cart,
                'additional_data' => $additionalData,
                'exception_trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Validar que el cliente esté disponible
     */
    private function validateClient(?User $client): User
    {
        $client = $client ?? auth()->user();
        
        if (!$client) {
            throw new Exception("Cliente no proporcionado o no autenticado.");
        }
        
        return $client;
    }

    /**
     * Obtener y validar el carrito de la sesión
     */
    private function getAndValidateCart(): array
    {
        $cart = session()->get('cart');
        
        if (!$cart || empty($cart['accounts'])) {
            throw new Exception("El carrito está vacío o es inválido.");
        }
        
        // Validar disponibilidad de items del carrito
        $this->cartValidationService->validateCartItemsAvailability($cart);
        
        return $cart;
    }
}
