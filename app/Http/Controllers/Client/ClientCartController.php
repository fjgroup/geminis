<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\ConfigurableOptionGroup;
use App\Models\ConfigurableOption;
use Illuminate\Support\Facades\Log;

class ClientCartController extends Controller
{
    private function initializeCart()
    {
        return [
            'accounts' => [],
            'active_account_id' => null,
        ];
    }

    public function getCart(Request $request)
    {
        $cart = $request->session()->get('cart', $this->initializeCart());
        if (!isset($cart['accounts']) || !isset($cart['active_account_id'])) {
            $cart = $this->initializeCart();
        }

        foreach ($cart['accounts'] as &$account) {
            if (isset($account['domain_info']['product_id'], $account['domain_info']['pricing_id'])) {
                $product = Product::find($account['domain_info']['product_id']);
                $pricing = ProductPricing::find($account['domain_info']['pricing_id']);
                if ($product && $pricing && $pricing->product_id == $product->id) {
                    $account['domain_info']['product_name'] = $product->name;
                    $account['domain_info']['price'] = (float) $pricing->price;
                    $account['domain_info']['currency_code'] = $pricing->currency_code;
                } else {
                    $account['domain_info']['product_name'] = 'Información no disponible';
                    $account['domain_info']['price'] = 0.00;
                    $account['domain_info']['currency_code'] = config('app.currency_code', 'USD');
                }
            } elseif (isset($account['domain_info']['domain_name']) && !isset($account['domain_info']['product_id'])) {
                $account['domain_info']['product_name'] = 'Registro de Dominio';
                $account['domain_info']['price'] = 0.00;
                $account['domain_info']['currency_code'] = config('app.currency_code', 'USD');
            }

            if (isset($account['primary_service']['product_id'], $account['primary_service']['pricing_id'])) {
                $product = Product::find($account['primary_service']['product_id']);
                $pricing = ProductPricing::find($account['primary_service']['pricing_id']);
                if ($product && $pricing && $pricing->product_id == $product->id) {
                    $account['primary_service']['product_name'] = $product->name;
                    $account['primary_service']['price'] = (float) $pricing->price;
                    $account['primary_service']['currency_code'] = $pricing->currency_code;

                    if (isset($account['primary_service']['configurable_options']) && is_array($account['primary_service']['configurable_options'])) {
                        $enrichedOptions = [];
                        foreach ($account['primary_service']['configurable_options'] as $groupId => $optionId) {
                            $group = ConfigurableOptionGroup::find($groupId);
                            $option = ConfigurableOption::find($optionId);
                            if ($group && $option && $option->group_id == $group->id) {
                                $enrichedOptions[] = [
                                    'group_id' => $group->id, 'group_name' => $group->name,
                                    'option_id' => $option->id, 'option_name' => $option->name,
                                ];
                            } else {
                                $enrichedOptions[] = [
                                    'group_id' => $groupId, 'group_name' => "ID Grupo: {$groupId}",
                                    'option_id' => $optionId, 'option_name' => "ID Opción: {$optionId}",
                                ];
                            }
                        }
                        $account['primary_service']['configurable_options_details'] = $enrichedOptions;
                    }
                } else {
                    $account['primary_service']['product_name'] = 'Servicio primario no disponible';
                    $account['primary_service']['price'] = 0.00;
                    $account['primary_service']['currency_code'] = config('app.currency_code', 'USD');
                }
            }

            if (isset($account['additional_services']) && is_array($account['additional_services'])) {
                foreach ($account['additional_services'] as &$item) {
                    if (isset($item['product_id'], $item['pricing_id'])) {
                        $product = Product::find($item['product_id']);
                        $pricing = ProductPricing::find($item['pricing_id']);
                        if ($product && $pricing && $pricing->product_id == $product->id) {
                            $item['product_name'] = $product->name;
                            $item['price'] = (float) $pricing->price;
                            $item['currency_code'] = $pricing->currency_code;
                        } else {
                            $item['product_name'] = 'Servicio adicional no disponible';
                            $item['price'] = 0.00;
                            $item['currency_code'] = config('app.currency_code', 'USD');
                        }
                    }
                }
                unset($item);
            }
        }
        unset($account);
        return response()->json(['status' => 'success', 'cart' => $cart]);
    }

    private function findAccount(Request $request, $accountId)
    {
        $cart = $request->session()->get('cart', $this->initializeCart());
        foreach ($cart['accounts'] as $index => $account) {
            if ($account['account_id'] === $accountId) {
                return $index;
            }
        }
        return null;
    }

    private function getActiveAccountIndex(Request $request)
    {
        $cart = $request->session()->get('cart', $this->initializeCart());
        if (!$cart['active_account_id']) {
            return null;
        }
        return $this->findAccount($request, $cart['active_account_id']);
    }

    private function findItemInAccount(&$account, $cartItemId)
    {
        if (isset($account['domain_info']['cart_item_id']) && $account['domain_info']['cart_item_id'] === $cartItemId) {
            return ['type' => 'domain_info', 'item' => &$account['domain_info']];
        }
        if (isset($account['primary_service']['cart_item_id']) && $account['primary_service']['cart_item_id'] === $cartItemId) {
            return ['type' => 'primary_service', 'item' => &$account['primary_service']];
        }
        if (isset($account['additional_services'])) {
            foreach ($account['additional_services'] as $key => &$service) {
                if (isset($service['cart_item_id']) && $service['cart_item_id'] === $cartItemId) {
                    return ['type' => 'additional_service', 'index' => $key, 'item' => &$service];
                }
            }
        }
        return null;
    }

    public function setDomainForAccount(Request $request)
    {
        $validated = $request->validate([
            'domain_name' => 'required|string|max:255',
            'product_id' => 'nullable|integer|exists:products,id',
            'pricing_id' => 'nullable|integer|exists:product_pricings,id',
        ]);

        $cart = $request->session()->get('cart', $this->initializeCart());
        $activeIndex = $this->getActiveAccountIndex($request);

        $domainName = $validated['domain_name'];
        $productId = $validated['product_id'] ?? null;
        $pricingId = $validated['pricing_id'] ?? null;

        if ($productId) {
            $product = Product::find($productId);
            if ($product->product_type_id != 3) {
                return response()->json(['status' => 'error', 'message' => 'El producto seleccionado no es un tipo de registro de dominio válido.'], 422);
            }
            if ($pricingId) {
                $pricing = ProductPricing::find($pricingId);
                if ($pricing->product_id != $product->id) {
                    return response()->json(['status' => 'error', 'message' => 'La configuración de precio no corresponde al producto de dominio seleccionado.'], 422);
                }
            }
        } elseif ($pricingId && !$productId) {
            return response()->json(['status' => 'error', 'message' => 'Se especificó un precio sin un producto de dominio.'], 422);
        }

        $domainInfo = [
            'domain_name' => $domainName, 'product_id' => $productId, 'pricing_id' => $pricingId,
            'cart_item_id' => (string) Str::uuid(),
        ];

        $newAccountId = null;
        if ($activeIndex === null) {
            $newAccountId = (string) Str::uuid();
            $newAccount = ['account_id' => $newAccountId, 'domain_info' => $domainInfo, 'primary_service' => null, 'additional_services' => []];
            $cart['accounts'][] = $newAccount;
            $cart['active_account_id'] = $newAccountId;
        } else {
            if (!empty($cart['accounts'][$activeIndex]['domain_info'])) {
                return response()->json(['status' => 'error', 'message' => 'La cuenta activa ya tiene información de dominio. Para cambiarla, primero elimine la existente o cree una nueva cuenta.'], 409);
            }
            $cart['accounts'][$activeIndex]['domain_info'] = $domainInfo;
            $newAccountId = $cart['accounts'][$activeIndex]['account_id'];
        }
        $request->session()->put('cart', $cart);
        return response()->json(['status' => 'success', 'message' => 'Dominio configurado para la cuenta.', 'cart' => $this->getCart($request)->getData(true)['cart'], 'account_id' => $newAccountId]);
    }

    public function setPrimaryServiceForAccount(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'pricing_id' => 'required|integer|exists:product_pricings,id',
            'configurable_options' => 'nullable|array',
        ]);

        $cart = $request->session()->get('cart', $this->initializeCart());
        $activeIndex = $this->getActiveAccountIndex($request);

        if ($activeIndex === null) {
            return response()->json(['status' => 'error', 'message' => 'No hay una cuenta activa para añadir el servicio.'], 400);
        }
        $account = &$cart['accounts'][$activeIndex];

        if (empty($account['domain_info'])) {
            return response()->json(['status' => 'error', 'message' => 'La cuenta activa debe tener información de dominio configurada.'], 400);
        }
        if (!empty($account['primary_service'])) {
            return response()->json(['status' => 'error', 'message' => 'La cuenta activa ya tiene un servicio principal.'], 409);
        }

        $product = Product::with('configurableOptionGroups.options')->find($validated['product_id']);
        $pricing = ProductPricing::find($validated['pricing_id']);

        if ($pricing->product_id != $product->id) {
            return response()->json(['status' => 'error', 'message' => 'La configuración de precio no corresponde al producto seleccionado.'], 422);
        }

        $allowedPrimaryServiceTypes = [1, 2, 7];
        if (!in_array($product->product_type_id, $allowedPrimaryServiceTypes)) {
            return response()->json(['status' => 'error', 'message' => 'Este tipo de producto no puede ser un servicio principal.'], 422);
        }

        $primaryServiceData = [
            'cart_item_id' => (string) Str::uuid(),
            'product_id' => $product->id, 'pricing_id' => $pricing->id,
        ];

        if (!empty($validated['configurable_options'])) {
            $validConfigOptions = [];
            $productConfigGroups = $product->configurableOptionGroups->keyBy('id');
            foreach ($validated['configurable_options'] as $groupId => $optionId) {
                if (!is_numeric($groupId) || !is_numeric($optionId)) {
                     return response()->json(['status' => 'error', 'message' => "ID de grupo u opción inválido: {$groupId} -> {$optionId}."], 422);
                }
                if (!isset($productConfigGroups[$groupId])) {
                    return response()->json(['status' => 'error', 'message' => "Grupo de opción configurable inválido: ID {$groupId}."], 422);
                }
                $group = $productConfigGroups[$groupId];
                if (!$group->options->contains('id', $optionId)) {
                    return response()->json(['status' => 'error', 'message' => "Opción configurable inválida: ID {$optionId} para el grupo '{$group->name}'."], 422);
                }
                $validConfigOptions[(int)$groupId] = (int)$optionId;
            }
            $primaryServiceData['configurable_options'] = $validConfigOptions;
        } else {
            $primaryServiceData['configurable_options'] = null;
        }

        $account['primary_service'] = $primaryServiceData;
        $request->session()->put('cart', $cart);
        return response()->json(['status' => 'success', 'message' => 'Servicio principal añadido.', 'cart' => $this->getCart($request)->getData(true)['cart']]);
    }

    public function addItem(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'pricing_id' => 'required|integer|exists:product_pricings,id',
        ]);

        $cart = $request->session()->get('cart', $this->initializeCart());
        $activeIndex = $this->getActiveAccountIndex($request);

        if ($activeIndex === null) {
            return response()->json(['status' => 'error', 'message' => 'No hay una cuenta activa para añadir servicios adicionales.'], 400);
        }
        $account = &$cart['accounts'][$activeIndex];

        if (empty($account['domain_info'])) {
            return response()->json(['status' => 'error', 'message' => 'La cuenta activa debe tener información de dominio configurada antes de añadir servicios adicionales.'], 400);
        }

        $product = Product::find($validated['product_id']);
        $pricing = ProductPricing::find($validated['pricing_id']);

        if ($pricing->product_id != $product->id) {
            return response()->json(['status' => 'error', 'message' => 'La configuración de precio no corresponde al producto seleccionado.'], 422);
        }

        $allowedAdditionalServiceTypes = [4, 6];
        if (!in_array($product->product_type_id, $allowedAdditionalServiceTypes)) {
            return response()->json(['status' => 'error', 'message' => 'Este tipo de producto no puede ser añadido como servicio adicional de esta manera.'], 422);
        }

        $additionalServiceData = [
            'cart_item_id' => (string) Str::uuid(),
            'product_id' => $product->id, 'pricing_id' => $pricing->id,
        ];

        $account['additional_services'][] = $additionalServiceData;
        $request->session()->put('cart', $cart);
        return response()->json(['status' => 'success', 'message' => 'Servicio adicional añadido.', 'cart' => $this->getCart($request)->getData(true)['cart']]);
    }

    public function updateItem(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|string',
            'pricing_id' => 'sometimes|integer|exists:product_pricings,id',
        ]);

        $cart = $request->session()->get('cart', $this->initializeCart());
        $activeIndex = $this->getActiveAccountIndex($request);

        if ($activeIndex === null) {
            return response()->json(['status' => 'error', 'message' => 'No se encontró una cuenta activa para actualizar el ítem.'], 400);
        }

        $account = &$cart['accounts'][$activeIndex];
        $cartItemId = $request->input('cart_item_id');
        $itemLocation = $this->findItemInAccount($account, $cartItemId);

        if (!$itemLocation) {
            return response()->json(['status' => 'error', 'message' => 'Ítem no encontrado en la cuenta activa.'], 404);
        }

        $itemData = &$itemLocation['item'];

        if ($request->has('pricing_id')) {
            if (!isset($itemData['product_id']) || $itemData['product_id'] === null) {
                return response()->json(['status' => 'error', 'message' => 'No se puede actualizar el precio de un ítem sin ID de producto.'], 400);
            }
            $newPricing = ProductPricing::find($request->input('pricing_id'));
            if (!$newPricing || $newPricing->product_id != $itemData['product_id']) {
                 return response()->json(['status' => 'error', 'message' => 'Nueva configuración de precio inválida para el ítem.'], 422);
            }
            $itemData['pricing_id'] = $newPricing->id;
        }

        $request->session()->put('cart', $cart);
        return response()->json(['status' => 'success', 'message' => 'Ítem actualizado correctamente.', 'cart' => $this->getCart($request)->getData(true)['cart']]);
    }

    public function removeItem(Request $request)
    {
        $request->validate(['cart_item_id' => 'required|string']);
        $cart = $request->session()->get('cart', $this->initializeCart());
        $activeIndex = $this->getActiveAccountIndex($request);

        if ($activeIndex === null) {
            return response()->json(['status' => 'error', 'message' => 'No se encontró cuenta activa.'], 400);
        }

        $account = &$cart['accounts'][$activeIndex];
        $cartItemId = $request->input('cart_item_id');
        $itemFoundAndRemoved = false;

        if (isset($account['domain_info']['cart_item_id']) && $account['domain_info']['cart_item_id'] === $cartItemId) {
            $deletedAccountId = $account['account_id'];
            array_splice($cart['accounts'], $activeIndex, 1);
            if ($cart['active_account_id'] === $deletedAccountId) {
                $cart['active_account_id'] = count($cart['accounts']) > 0 ? $cart['accounts'][0]['account_id'] : null;
            }
            $itemFoundAndRemoved = true;
        } elseif (isset($account['primary_service']['cart_item_id']) && $account['primary_service']['cart_item_id'] === $cartItemId) {
            $account['primary_service'] = null;
            $itemFoundAndRemoved = true;
        } elseif (isset($account['additional_services'])) {
            foreach ($account['additional_services'] as $key => $service) {
                if (isset($service['cart_item_id']) && $service['cart_item_id'] === $cartItemId) {
                    array_splice($account['additional_services'], $key, 1);
                    $itemFoundAndRemoved = true;
                    break;
                }
            }
        }

        if (!$itemFoundAndRemoved) {
            return response()->json(['status' => 'error', 'message' => 'Ítem no encontrado en la cuenta activa.'], 404);
        }
        $request->session()->put('cart', $cart);
        return response()->json(['status' => 'success', 'message' => 'Ítem eliminado correctamente.', 'cart' => $this->getCart($request)->getData(true)['cart']]);
    }

    public function clearCart(Request $request)
    {
        $request->session()->put('cart', $this->initializeCart());
        return response()->json(['status' => 'success', 'message' => 'Carrito vaciado correctamente.', 'cart' => $this->getCart($request)->getData(true)['cart']]);
    }

    public function setActiveAccount(Request $request)
    {
        $validated = $request->validate(['account_id' => 'nullable|string']);
        $cart = $request->session()->get('cart', $this->initializeCart());
        $accountIdToActivate = $validated['account_id'] ?? null;

        if ($accountIdToActivate === null) {
            $cart['active_account_id'] = null;
        } else {
            $accountIndex = $this->findAccount($request, $accountIdToActivate);
            if ($accountIndex === null) {
                return response()->json(['status' => 'error', 'message' => 'Cuenta no encontrada.'], 404);
            }
            $cart['active_account_id'] = $cart['accounts'][$accountIndex]['account_id'];
        }

        $request->session()->put('cart', $cart);
        return response()->json(['status' => 'success', 'message' => 'Cuenta activa establecida.', 'cart' => $this->getCart($request)->getData(true)['cart']]);
    }
}
