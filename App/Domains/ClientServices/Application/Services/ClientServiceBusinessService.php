<?php

namespace App\Domains\ClientServices\Application\Services;

use App\Domains\ClientServices\Infrastructure\Persistence\Models\ClientService;
use App\Domains\Products\Infrastructure\Persistence\Models\BillingCycle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de aplicación para lógica de negocio de ClientService
 * 
 * Cumple con Single Responsibility Principle - maneja operaciones de negocio complejas
 * Ubicado en Application layer según arquitectura hexagonal
 */
class ClientServiceBusinessService
{
    /**
     * Get the possible enum values for a given column.
     *
     * @param string $column
     * @return array
     */
    public function getEnumValues(string $column): array
    {
        try {
            $tableName = (new ClientService())->getTable();
            $query = "SHOW COLUMNS FROM {$tableName} LIKE '{$column}'";
            $result = DB::select($query);
            
            if (empty($result)) {
                return [];
            }
            
            $type = $result[0]->Type;
            
            // Extract enum values from the type definition
            if (preg_match('/^enum\((.*)\)$/', $type, $matches)) {
                $enumString = $matches[1];
                $enumValues = [];
                
                // Parse the enum values
                if (preg_match_all("/'([^']*)'/", $enumString, $valueMatches)) {
                    $enumValues = $valueMatches[1];
                }
                
                return $enumValues;
            }
            
            return [];
        } catch (\Exception $e) {
            Log::error("Error getting enum values for column {$column}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Extends the service's next due date based on the provided billing cycle.
     *
     * @param ClientService $clientService The client service to extend
     * @param BillingCycle $billingCycle The billing cycle used for renewal
     * @return array Result with success status and message
     */
    public function extendServiceRenewal(ClientService $clientService, BillingCycle $billingCycle): array
    {
        try {
            DB::beginTransaction();

            // Calculate new next due date
            $currentDueDate = Carbon::parse($clientService->next_due_date);
            $newDueDate = $this->calculateNewDueDate($currentDueDate, $billingCycle);

            // Update the client service
            $clientService->update([
                'next_due_date' => $newDueDate,
                'billing_cycle_id' => $billingCycle->id,
                'updated_at' => now(),
            ]);

            DB::commit();

            Log::info("Client service {$clientService->id} renewal extended", [
                'previous_due_date' => $currentDueDate->toDateString(),
                'new_due_date' => $newDueDate->toDateString(),
                'billing_cycle' => $billingCycle->name,
            ]);

            return [
                'success' => true,
                'message' => 'Service renewal extended successfully',
                'new_due_date' => $newDueDate,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Error extending service renewal for client service {$clientService->id}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to extend service renewal: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Calculate new due date based on billing cycle
     *
     * @param Carbon $currentDueDate
     * @param BillingCycle $billingCycle
     * @return Carbon
     */
    private function calculateNewDueDate(Carbon $currentDueDate, BillingCycle $billingCycle): Carbon
    {
        return $currentDueDate->addDays($billingCycle->days);
    }

    /**
     * Check if a service can be renewed
     *
     * @param ClientService $clientService
     * @return bool
     */
    public function canBeRenewed(ClientService $clientService): bool
    {
        return in_array($clientService->status, ['active', 'suspended']);
    }

    /**
     * Get renewal price for a service
     *
     * @param ClientService $clientService
     * @param BillingCycle $billingCycle
     * @return float
     */
    public function getRenewalPrice(ClientService $clientService, BillingCycle $billingCycle): float
    {
        // Get the current pricing for the product and billing cycle
        $pricing = $clientService->product->pricings()
            ->where('billing_cycle_id', $billingCycle->id)
            ->where('is_active', true)
            ->first();

        if ($pricing) {
            return $pricing->price;
        }

        // Fallback to current billing amount
        return $clientService->billing_amount;
    }

    /**
     * Validate service for renewal
     *
     * @param ClientService $clientService
     * @param BillingCycle $billingCycle
     * @return array
     */
    public function validateForRenewal(ClientService $clientService, BillingCycle $billingCycle): array
    {
        $errors = [];

        if (!$this->canBeRenewed($clientService)) {
            $errors[] = "Service with status '{$clientService->status}' cannot be renewed";
        }

        if (!$clientService->product) {
            $errors[] = "Service product not found";
        }

        if (!$billingCycle) {
            $errors[] = "Invalid billing cycle";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
