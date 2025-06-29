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
        // Asumiendo que domain_info, primary_service, additional_services tienen una propiedad 'price'
        // si son ítems facturables.
        if (account.domain_info && account.domain_info.price && account.domain_info.product_id) {
            total += parseFloat(account.domain_info.price);
        }
        if (account.primary_service && account.primary_service.price) {
            total += parseFloat(account.primary_service.price);
        }
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
    <div class="p-4 bg-white border rounded-lg shadow-sm">
        <h2 class="mb-3 text-xl font-semibold text-gray-700">Resumen del Pedido</h2>

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
                    class="p-2 mb-2 ml-2 rounded bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-700">Dominio: {{ account.domain_info.domain_name }}</p>
                            <div v-if="account.domain_info.product_id && account.domain_info.product_name"
                                class="ml-3 text-sm text-gray-600">
                                <span>{{ account.domain_info.product_name }}</span>
                                <span v-if="typeof account.domain_info.price === 'number'"
                                    class="float-right font-medium">
                                    {{ formatCurrency(account.domain_info.price, account.domain_info.currency_code ||
                                        cartCurrency) }}
                                </span>
                            </div>
                            <div v-else-if="!account.domain_info.product_id" class="ml-3 text-sm text-gray-500">
                                (Solo registro de nombre de dominio)
                            </div>
                        </div>
                        <button @click="removeDomain(account.account_id)"
                            class="ml-2 text-red-600 hover:text-red-800 text-sm font-medium">
                            ✕
                        </button>
                    </div>
                </div>

                <div v-if="account.primary_service && account.primary_service.product_name"
                    class="p-2 mb-2 ml-2 rounded bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-700">Servicio Principal:</p>
                            <div class="ml-3 text-sm text-gray-600">
                                <span>{{ account.primary_service.product_name }}</span>
                                <span v-if="typeof account.primary_service.price === 'number'"
                                    class="float-right font-medium">
                                    {{ formatCurrency(account.primary_service.price,
                                        account.primary_service.currency_code ||
                                        cartCurrency) }}
                                </span>
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
                    <!-- Mostrar Opciones Configurables Seleccionadas (Básico) -->
                    <div v-if="account.primary_service.configurable_options && Object.keys(account.primary_service.configurable_options).length > 0"
                        class="mt-1 ml-6 text-xs text-gray-500">
                        <p class="font-medium">Opciones Config.:</p>
                        <ul class="list-disc list-inside">
                            <li v-if="account.primary_service.configurable_options_details"
                                v-for="detail in account.primary_service.configurable_options_details"
                                :key="detail.group_id + '_' + detail.option_id">
                                {{ detail.group_name }}: {{ detail.option_name }}
                            </li>
                            <!-- Fallback si solo están los IDs crudos (configurable_options pero no details) -->
                            <li v-else v-for="(optionValue, groupKey) in account.primary_service.configurable_options"
                                :key="groupKey">
                                Grupo ID {{ groupKey }}: Opción ID {{ optionValue }}
                            </li>
                        </ul>
                    </div>
                </div>

                <div v-if="account.additional_services && account.additional_services.length > 0"
                    class="p-2 mb-1 ml-2 rounded bg-gray-50">
                    <p class="mb-1 font-semibold text-gray-700">Servicios Adicionales:</p>
                    <ul class="pl-5 text-sm text-gray-600 list-disc">
                        <li v-for="service in account.additional_services" :key="service.cart_item_id" class="mb-1">
                            <span>{{ service.product_name }}</span>
                            <span v-if="typeof service.price === 'number'" class="float-right pr-2 font-medium">
                                {{ formatCurrency(service.price, service.currency_code || cartCurrency) }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="pt-4 mt-6 border-t">
                <p class="text-xl font-bold text-right text-gray-800">
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
