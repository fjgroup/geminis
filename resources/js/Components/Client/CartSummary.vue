<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'; // Añadir onUnmounted
import axios from 'axios';
import { router } from '@inertiajs/vue3';

const cartData = ref(null);
const isLoading = ref(true);
const error = ref(null);

// Helper para formatear moneda
const formatCurrency = (value, currencyCode = 'USD') => {
    if (typeof value !== 'number') {
        return value; // o 'N/A' o algún placeholder
    }
    return new Intl.NumberFormat('es-ES', { // Ajustar 'es-ES' según la localización deseada
        style: 'currency',
        currency: currencyCode,
    }).format(value);
};

const fetchCart = async () => {
    isLoading.value = true;
    error.value = null;
    try {
        // Asegúrate de que esta ruta está definida en tus rutas de Laravel (web.php o api.php)
        // y que Ziggy la está generando si usas `route()` en JS.
        // Si no usas Ziggy, hardcodea la URL: '/client/cart'
        const response = await axios.get(route('client.cart.get'));
        if (response.data && response.data.status === 'success') {
            cartData.value = response.data.cart;
        } else {
            throw new Error(response.data.message || 'Failed to load cart data.');
        }
    } catch (err) {
        console.error("Error fetching cart:", err);
        error.value = err.response?.data?.message || err.message || 'An unknown error occurred while fetching the cart.';
    } finally {
        isLoading.value = false;
    }
};

// Manejador para el evento, simplemente llama a fetchCart.
// Se define así para que la referencia sea la misma para add y remove.
const handleCartUpdate = () => {
    // console.log('CartSummary: cart-updated event received');
    fetchCart();
};

onMounted(() => {
    fetchCart();
    window.addEventListener('cart-updated', handleCartUpdate);
});

onUnmounted(() => {
    window.removeEventListener('cart-updated', handleCartUpdate);
});

const removeDomain = (accountId) => {
    if (!confirm('¿Estás seguro de que deseas eliminar este dominio del carrito?')) {
        return;
    }

    router.post(route('client.cart.account.removeDomain'), {
        account_id: accountId
    }, {
        preserveScroll: true,
        onSuccess: () => {
            window.dispatchEvent(new CustomEvent('cart-updated'));
        },
        onError: (errors) => {
            console.error('Error al eliminar dominio:', errors);
            alert('Error al eliminar el dominio del carrito.');
        }
    });
};

const removePrimaryService = (accountId) => {
    if (!confirm('¿Estás seguro de que deseas eliminar este servicio del carrito?')) {
        return;
    }

    router.post(route('client.cart.account.removePrimaryService'), {
        account_id: accountId
    }, {
        preserveScroll: true,
        onSuccess: () => {
            window.dispatchEvent(new CustomEvent('cart-updated'));
        },
        onError: (errors) => {
            console.error('Error al eliminar servicio:', errors);
            alert('Error al eliminar el servicio del carrito.');
        }
    });
};

const changeBillingCycle = (accountId, currentService) => {
    // Redirigir a la página de selección de servicios con el contexto de cambio
    router.visit(route('client.checkout.selectServices'), {
        data: {
            change_service: true,
            account_id: accountId,
            current_product_id: currentService.product_id
        }
    });
};

const accounts = computed(() => cartData.value?.accounts || []);
const activeAccountId = computed(() => cartData.value?.active_account_id);

const totalGeneral = computed(() => {
    if (!accounts.value.length) {
        return 0;
    }
    let total = 0;
    accounts.value.forEach(account => {
        // Calcular precio del dominio
        if (account.domain_info && account.domain_info.product_id) {
            // Usar override_price si existe, sino usar price
            const domainPrice = account.domain_info.override_price || account.domain_info.price || 0;
            total += parseFloat(domainPrice);
        }

        // Calcular precio del servicio principal
        if (account.primary_service && account.primary_service.price) {
            total += parseFloat(account.primary_service.price);
        }

        // Calcular precio de servicios adicionales
        if (account.additional_services && account.additional_services.length) {
            account.additional_services.forEach(service => {
                if (service.price) {
                    total += parseFloat(service.price);
                }
            });
        }
    });
    return total;
});

// Podríamos necesitar una función para obtener la moneda del carrito si varía,
// por ahora `formatCurrency` usa USD por defecto o lo que se le pase.
// Si el carrito tiene una moneda global, podríamos usarla.
const cartCurrency = computed(() => {
    // Intenta obtener la moneda del primer ítem que la tenga.
    // Esto es una simplificación. Idealmente, la moneda debería ser consistente en el carrito
    // o el backend debería proveer una moneda global para el carrito.
    if (accounts.value.length > 0) {
        const firstAccount = accounts.value[0];
        if (firstAccount.domain_info && firstAccount.domain_info.currency_code && firstAccount.domain_info.product_id) {
            return firstAccount.domain_info.currency_code;
        }
        if (firstAccount.primary_service && firstAccount.primary_service.currency_code) {
            return firstAccount.primary_service.currency_code;
        }
        if (firstAccount.additional_services && firstAccount.additional_services.length && firstAccount.additional_services[0].currency_code) {
            return firstAccount.additional_services[0].currency_code;
        }
    }
    return 'USD'; // Fallback
});

</script>

<template>
    <div class="p-6 bg-white border rounded-lg shadow-sm min-h-[400px]">
        <h2 class="mb-4 text-xl font-semibold text-gray-700">Resumen del Pedido</h2>

        <div v-if="isLoading" class="text-center text-gray-500">
            <p>Cargando resumen del pedido...</p>
            <!-- Puedes añadir un spinner aquí -->
        </div>

        <div v-else-if="error" class="p-3 text-center text-red-500 border border-red-300 rounded bg-red-50">
            <p><strong>Error:</strong> {{ error }}</p>
        </div>

        <div v-else-if="cartData && accounts.length > 0">
            <div v-for="(account, index) in accounts" :key="account.account_id || index"
                class="pb-4 mb-6 border-b last:border-b-0 last:pb-0 last:mb-0">
                <h3 class="mb-2 text-lg font-medium text-gray-800">
                    Cuenta #{{ index + 1 }}
                    <span v-if="account.account_id === activeAccountId"
                        class="text-sm font-normal text-blue-500">(Activa)</span>
                </h3>

                <div v-if="account.domain_info && account.domain_info.domain_name"
                    class="p-3 mb-3 ml-2 rounded bg-gray-50 border border-gray-200">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-700">Dominio: {{ account.domain_info.domain_name }}</p>
                            <div v-if="account.domain_info.product_id && account.domain_info.product_name"
                                class="ml-3 text-sm text-gray-600">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="font-medium">{{ account.domain_info.product_name }}</span>
                                        <span v-if="account.domain_info.billing_cycle_name"
                                            class="ml-2 px-2 py-1 text-xs font-semibold text-purple-800 bg-purple-100 rounded-full">
                                            {{ account.domain_info.billing_cycle_name.toUpperCase() }}
                                        </span>
                                    </div>
                                    <span class="font-medium text-lg">
                                        {{ formatCurrency(account.domain_info.override_price ||
                                            account.domain_info.price ||
                                            0, account.domain_info.currency_code || cartCurrency) }}
                                    </span>
                                </div>
                            </div>
                            <div v-else-if="!account.domain_info.product_id" class="ml-3 text-sm text-gray-500">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span>(Solo registro de nombre de dominio)</span>
                                        <span v-if="account.domain_info.billing_cycle_name"
                                            class="ml-2 px-2 py-1 text-xs font-semibold text-purple-800 bg-purple-100 rounded-full">
                                            {{ account.domain_info.billing_cycle_name.toUpperCase() }}
                                        </span>
                                    </div>
                                    <span v-if="account.domain_info.price" class="font-medium text-lg">
                                        {{ formatCurrency(account.domain_info.override_price ||
                                            account.domain_info.price ||
                                            0, account.domain_info.currency_code || cartCurrency) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <button @click="removeDomain(account.account_id)"
                            class="ml-2 text-red-600 hover:text-red-800 text-sm font-medium">
                            ✕
                        </button>
                    </div>
                </div>

                <div v-if="account.primary_service && account.primary_service.product_name"
                    class="p-3 mb-3 ml-2 rounded bg-gray-50 border border-gray-200">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-700">Servicio Principal:</p>
                            <div class="ml-3 text-sm text-gray-600">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="font-medium">{{ account.primary_service.product_name }}</span>
                                        <span v-if="account.primary_service.billing_cycle_name"
                                            class="ml-2 px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full">
                                            {{ account.primary_service.billing_cycle_name.toUpperCase() }}
                                        </span>
                                    </div>
                                    <span v-if="typeof account.primary_service.price === 'number'"
                                        class="font-medium text-lg">
                                        {{ formatCurrency(account.primary_service.price,
                                            account.primary_service.currency_code ||
                                            cartCurrency) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="ml-2 flex space-x-1">
                            <button @click="changeBillingCycle(account.account_id, account.primary_service)"
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                title="Cambiar ciclo de facturación">
                                🔄
                            </button>
                            <button @click="removePrimaryService(account.account_id)"
                                class="text-red-600 hover:text-red-800 text-sm font-medium" title="Eliminar servicio">
                                ✕
                            </button>
                        </div>
                    </div>
                    <!-- Mostrar Opciones Configurables como productos individuales -->
                    <div v-if="account.primary_service.configurable_options_details && account.primary_service.configurable_options_details.length > 0"
                        class="mt-3 ml-3 space-y-2">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">📦 Recursos Adicionales:
                        </p>
                        <!-- Debug: mostrar datos recibidos -->
                        <!-- <pre class="text-xs bg-gray-100 p-2 rounded mb-2">{{ JSON.stringify(account.primary_service.configurable_options_details, null, 2) }}</pre> -->
                        <div v-for="detail in account.primary_service.configurable_options_details"
                            :key="detail.group_id + '_' + detail.option_id"
                            class="flex justify-between items-center p-2 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-700">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                    {{ detail.group_name }}
                                </div>
                                <div class="text-xs text-blue-600 dark:text-blue-300">
                                    {{ detail.option_name }}
                                    <span v-if="detail.quantity && detail.quantity > 1"
                                        class="ml-1 px-1 py-0.5 bg-blue-200 dark:bg-blue-800 rounded text-xs">
                                        Cantidad: {{ detail.quantity }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div v-if="detail.unit_price"
                                    class="text-sm font-semibold text-blue-700 dark:text-blue-300">
                                    {{ formatCurrency(detail.total_price || detail.unit_price,
                                        account.primary_service.currency_code || cartCurrency) }}
                                </div>
                                <div v-if="detail.unit_price && detail.quantity > 1"
                                    class="text-xs text-blue-500 dark:text-blue-400">
                                    {{ formatCurrency(detail.unit_price, account.primary_service.currency_code ||
                                        cartCurrency) }} × {{ detail.quantity }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fallback: Mostrar opciones configurables básicas si no hay detalles enriquecidos -->
                    <div v-else-if="account.primary_service.configurable_options && Object.keys(account.primary_service.configurable_options).length > 0"
                        class="mt-3 ml-3 space-y-1">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">⚙️ Configuraciones:</p>
                        <!-- Debug: mostrar opciones básicas -->
                        <!-- <pre class="text-xs bg-yellow-100 p-2 rounded mb-2">{{ JSON.stringify(account.primary_service.configurable_options, null, 2) }}</pre> -->
                        <div v-for="(optionValue, optionKey) in account.primary_service.configurable_options"
                            :key="optionKey"
                            class="text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 p-2 rounded">
                            <span class="font-medium">{{ optionKey }}:</span> {{ optionValue }}
                        </div>
                    </div>
                </div>

                <div v-if="account.additional_services && account.additional_services.length > 0"
                    class="p-3 mb-3 ml-2 rounded bg-gray-50 border border-gray-200">
                    <p class="mb-1 font-semibold text-gray-700">Servicios Adicionales:</p>
                    <ul class="pl-5 text-sm text-gray-600 list-none space-y-2">
                        <li v-for="service in account.additional_services" :key="service.cart_item_id" class="mb-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="font-medium">{{ service.product_name }}</span>
                                    <span v-if="service.billing_cycle_name"
                                        class="ml-2 px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">
                                        {{ service.billing_cycle_name.toUpperCase() }}
                                    </span>
                                </div>
                                <span v-if="typeof service.price === 'number'" class="font-medium text-lg">
                                    {{ formatCurrency(service.price, service.currency_code || cartCurrency) }}
                                </span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="pt-6 mt-6 border-t-2 border-gray-300">
                <p class="text-2xl font-bold text-right text-gray-800 bg-gray-100 p-4 rounded-lg">
                    Total General: {{ formatCurrency(totalGeneral, cartCurrency) }}
                </p>
            </div>
        </div>

        <div v-else class="py-5 text-center text-gray-500">
            <p>Tu carrito está vacío.</p>
        </div>
    </div>
</template>

<style scoped>
/* Estilos específicos del componente si son necesarios, más allá de Tailwind */
.float-right {
    float: right;
}
</style>
