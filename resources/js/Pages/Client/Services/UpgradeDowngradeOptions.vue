<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/Forms/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Forms/InputLabel.vue';
import { ref, computed, watch } from 'vue';
import axios from 'axios'; // Added axios

const props = defineProps({
    service: {
        type: Object,
        required: true,
    },
    availableOptions: { // This is an array of ProductPricing objects
        type: Array,
        default: () => [],
    },
    // discountInfo prop might not be used in this new design directly, unless adapted
});

// Reactive state
const selectedProductId = ref(null);
const selectedProductPricingId = ref(null);

const proratedResult = ref(null);
const isCalculatingProration = ref(false);
const prorationError = ref(null);

// Helper function for formatting currency
const formatCurrency = (amount, currencyCode = 'USD') => {
    const number = parseFloat(amount);
    if (isNaN(number)) {
        return 'N/A';
    }
    try {
        return new Intl.NumberFormat(undefined, { style: 'currency', currency: currencyCode }).format(number);
    } catch (e) {
        return `${currencyCode} ${number.toFixed(2)}`;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
};

// Computed property for unique products in the dropdown
const uniqueProducts = computed(() => {
    const productsMap = new Map();
    props.availableOptions.forEach(option => {
        if (option.product && !productsMap.has(option.product.id)) {
            productsMap.set(option.product.id, {
                id: option.product.id,
                name: option.product.name,
            });
        }
    });
    return Array.from(productsMap.values());
});

// Computed property for available billing cycles for the selected product
const availableCyclesForSelectedProduct = computed(() => {
    if (!selectedProductId.value) {
        return [];
    }
    return props.availableOptions.filter(option => option.product_id === selectedProductId.value);
});

// Computed property for the details of the selected plan (for summary)
const selectedPlanDetails = computed(() => {
    if (!selectedProductPricingId.value) {
        return null;
    }
    return props.availableOptions.find(option => option.id === selectedProductPricingId.value);
});

// Watch for product selection changes to reset cycle selection
watch(selectedProductId, (newVal, oldVal) => {
    if (newVal !== oldVal) {
        selectedProductPricingId.value = null;
        proratedResult.value = null; // Clear proration when product changes
        prorationError.value = null;
    }
});

// Watch for pricing selection changes to fetch proration
watch(selectedProductPricingId, (newValue) => {
    if (newValue) {
        fetchProrationDetails();
    } else {
        proratedResult.value = null;
        prorationError.value = null;
    }
});

async function fetchProrationDetails() {
    if (!selectedProductPricingId.value || !props.service || !props.service.id) {
        proratedResult.value = null;
        prorationError.value = null;
        return;
    }

    isCalculatingProration.value = true;
    prorationError.value = null;
    proratedResult.value = null;

    try {
        const response = await axios.post(
            route('client.services.calculateProration', { service: props.service.id }),
            { new_product_pricing_id: selectedProductPricingId.value }
        );
        proratedResult.value = response.data;
    } catch (error) {
        console.error('Error calculating proration:', error.response?.data || error.message);
        if (error.response?.status === 422 && error.response?.data?.message) {
            prorationError.value = error.response.data.message; // Use validation message from backend
        } else if (error.response?.data?.error) {
            prorationError.value = error.response.data.error;
        } else {
            prorationError.value = 'No se pudo calcular el monto de prorrateo.';
        }
    } finally {
        isCalculatingProration.value = false;
    }
}

const handlePlanChange = () => {
    if (!selectedPlanDetails.value) {
        // This alert should ideally not be needed if button is correctly disabled
        alert('Por favor, selecciona un plan y ciclo válidos.');
        return;
    }
    if (prorationError.value && selectedPlanDetails.value?.id === props.service.product_pricing_id) {
        // If there was an error trying to calculate proration for the *current plan* (which is an invalid selection for change),
        // still allow "confirming" it, which will just be caught by backend validation or do nothing.
        // Or, more strictly, the button should be disabled. Let's assume button is disabled for invalid states.
    } else if (prorationError.value) {
         alert(`Error de prorrateo: ${prorationError.value} No se puede continuar.`);
        return;
    }


    const { product, billing_cycle, price, currency_code } = selectedPlanDetails.value;
    const newPlanName = `${product.name} - ${billing_cycle.name}`;
    const newPlanPriceFormatted = formatCurrency(price, currency_code);

    if (confirm(`¿Estás seguro de que quieres cambiar tu plan a "${newPlanName}" por ${newPlanPriceFormatted}? Se aplicarán cargos o créditos prorrateados por el período restante. El cambio de plan es inmediato.`)) {
        router.post(route('client.services.processUpgradeDowngrade', { service: props.service.id }), {
            new_product_pricing_id: selectedProductPricingId.value,
        }, {
            preserveScroll: true,
        });
    }
};

</script>

<template>
    <Head :title="`Cambiar Plan para ${service.product?.name || 'Servicio'}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Cambiar Plan: {{ service.product?.name }}
                <span v-if="service.domain_name" class="text-sm text-gray-500 dark:text-gray-400">({{ service.domain_name }})</span>
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                    <!-- Card Izquierda (Plan Actual) -->
                    <div class="p-6 bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Tu Plan Actual</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Producto:</p>
                                <p class="text-gray-900 dark:text-gray-100">{{ service.product?.name || 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Ciclo de Facturación:</p>
                                <p class="text-gray-900 dark:text-gray-100">{{ service.product_pricing?.billing_cycle?.name || 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Precio:</p>
                                <p class="text-gray-900 dark:text-gray-100">{{ formatCurrency(service.billing_amount, service.product_pricing?.currency_code) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Próximo Vencimiento:</p>
                                <p class="text-gray-900 dark:text-gray-100">{{ formatDate(service.next_due_date) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Card Derecha (Seleccionar Nuevo Plan) -->
                    <div class="p-6 bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                        <h3 class="mb-6 text-lg font-semibold text-gray-900 dark:text-gray-100">Cambiar Plan</h3>
                        
                        <div class="space-y-6">
                            <div>
                                <InputLabel for="product_select" value="Selecciona un Producto:" />
                                <select id="product_select" v-model="selectedProductId"
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option :value="null" disabled>Selecciona un producto...</option>
                                    <option v-for="product in uniqueProducts" :key="product.id" :value="product.id">
                                        {{ product.name }}
                                    </option>
                                </select>
                            </div>

                            <div v-if="selectedProductId">
                                <InputLabel for="cycle_select" value="Selecciona un Ciclo de Facturación:" />
                                <select id="cycle_select" v-model="selectedProductPricingId"
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                        :disabled="availableCyclesForSelectedProduct.length === 0">
                                    <option :value="null" disabled>
                                        {{ availableCyclesForSelectedProduct.length === 0 ? 'No hay ciclos para este producto' : 'Selecciona un ciclo...' }}
                                    </option>
                                    <option v-for="option in availableCyclesForSelectedProduct" :key="option.id" :value="option.id">
                                        {{ option.billing_cycle.name }} - {{ formatCurrency(option.price, option.currency_code) }}
                                    </option>
                                </select>
                            </div>

                            <div v-if="selectedPlanDetails" class="p-4 mt-4 border-t border-gray-200 dark:border-gray-700">
                                <h4 class="font-semibold text-gray-800 dark:text-gray-200">Resumen de Selección:</h4>
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    Nuevo Producto: {{ selectedPlanDetails.product.name }}
                                </p>
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    Nuevo Ciclo: {{ selectedPlanDetails.billing_cycle.name }}
                                </p>
                                <p class="text-sm font-bold text-gray-800 dark:text-gray-100">
                                    Nuevo Precio: {{ formatCurrency(selectedPlanDetails.price, selectedPlanDetails.currency_code) }}
                                </p>
                            </div>

                            <!-- Proration Calculation Display -->
                            <div v-if="selectedProductPricingId">
                                <div v-if="isCalculatingProration" class="mt-4 text-sm text-gray-600 dark:text-gray-400 animate-pulse">
                                    Calculando monto de prorrateo...
                                </div>

                                <div v-if="prorationError && !isCalculatingProration" class="mt-4 p-3 bg-red-100 dark:bg-red-800 text-red-700 dark:text-red-200 rounded-md text-sm">
                                    <p class="font-semibold">Error de Prorrateo:</p>
                                    <p>{{ prorationError }}</p>
                                </div>

                                <div v-if="proratedResult && !isCalculatingProration && !prorationError" class="mt-4 p-3 rounded-md text-sm"
                                     :class="{
                                        'bg-green-100 dark:bg-green-800 text-green-700 dark:text-green-100': proratedResult.prorated_amount < 0,
                                        'bg-yellow-100 dark:bg-yellow-700 text-yellow-700 dark:text-yellow-100': proratedResult.prorated_amount > 0,
                                        'bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-100': proratedResult.prorated_amount === 0
                                     }">
                                    <h4 class="font-semibold">Resultado del Prorrateo:</h4>
                                    <p v-if="proratedResult.prorated_amount > 0">
                                        Monto adicional a pagar: {{ formatCurrency(proratedResult.prorated_amount, proratedResult.currency_code) }}
                                    </p>
                                    <p v-else-if="proratedResult.prorated_amount < 0">
                                        Crédito a tu balance: {{ formatCurrency(Math.abs(proratedResult.prorated_amount), proratedResult.currency_code) }}
                                    </p>
                                    <p v-else>
                                        No hay cargos adicionales ni créditos por el período actual.
                                    </p>
                                    <p class="text-xs mt-1 italic">{{ proratedResult.message }}</p>
                                </div>
                            </div>
                            <!-- End Proration Display -->

                            <div class="mt-6">
                                <PrimaryButton
                                    @click="handlePlanChange"
                                    :disabled="!selectedProductPricingId || isCalculatingProration || !!prorationError"
                                    class="w-full justify-center"
                                    :class="{'opacity-50 cursor-not-allowed': !selectedProductPricingId || isCalculatingProration || !!prorationError }">
                                    Actualizar Plan
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-8 text-center">
                    <Link :href="route('client.services.index')" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                        &laquo; Volver a Mis Servicios
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
