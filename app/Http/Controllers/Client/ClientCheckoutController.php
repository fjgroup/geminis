<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ClientCheckoutController extends Controller
{
    /**
     * Show the form for creating a new order/invoice for a specific product.
     *
     * @param  Product  $product
     * @return InertiaResponse
     */
    public function showProductCheckoutPage(Product $product): InertiaResponse
    {
        // Autorización similar a la que tenía ClientOrderController@showOrderForm
        // $this->authorize('view', $product); // Asumiendo ProductPolicy@view
        // For now, policies are not strictly enforced in this step for this controller.
        // This can be added later if ProductPolicy or a general CheckoutPolicy is established.

        $product->load(['pricings.billingCycle', 'configurableOptionGroups.options', 'productType']);

        return Inertia::render('Client/Checkout/ProductCheckoutPage', [ // Actualizar la ruta de Inertia
            'product' => $product,
        ]);
    }
}
