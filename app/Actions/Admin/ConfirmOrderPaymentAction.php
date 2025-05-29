<?php

namespace App\Actions\Admin;

use App\Models\Order;
use App\Models\OrderActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception; // Import base Exception for re-throwing if necessary

class ConfirmOrderPaymentAction
{
    /**
     * Execute the action to confirm payment for an order.
     *
     * @param  Order  $order
     * @return void
     * @throws Exception If any error occurs during the process.
     */
    public function execute(Order $order): void
    {
        DB::beginTransaction();

        try {
            $previousStatus = $order->status;

            // This check is also in the controller, but can be an assertion here too.
            // If the action is only ever called after this check, it might be redundant.
            // For robustness, keeping it can be good.
            if ($previousStatus !== 'pending_payment') {
                // Or throw a specific domain exception
                throw new Exception("Order is not in 'pending_payment' status. Current status: {$previousStatus}");
            }

            $order->status = 'paid_pending_execution';
            $order->save();

            OrderActivity::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(), // Assumes admin is authenticated
                'type' => 'admin_confirmed_payment',
                'details' => [
                    'previous_status' => $previousStatus,
                    'new_status' => $order->status,
                    'confirmed_by_admin_id' => Auth::id(),
                ]
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            // Re-throw the exception to be caught by the controller or a global handler.
            // This allows the controller to handle the redirect and user feedback.
            throw $e;
        }
    }
}
