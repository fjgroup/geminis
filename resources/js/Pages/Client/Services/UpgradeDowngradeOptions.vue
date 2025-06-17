<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/Forms/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Forms/InputLabel.vue'; // Assuming this component exists
import { ref, computed, watch } from 'vue';

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
        selectedProductPricingId.value = null; // Reset cycle selection
    }
});

const handlePlanChange = () => {
    if (!selectedPlanDetails.value) {
        alert('Por favor, selecciona un plan válido.'); // Should not happen if button is disabled
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

                            <div class="mt-6">
                                <PrimaryButton @click="handlePlanChange" :disabled="!selectedProductPricingId" class="w-full justify-center">
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
