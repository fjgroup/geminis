<script setup>
import { ref, computed, watch } from 'vue';
import InputLabel from '@/Components/Forms/InputLabel.vue';
import SelectInput from '@/Components/Forms/SelectInput.vue';

const props = defineProps({
    product: Object,
    billingCycles: Array,
    availableResourceGroups: Array,
});

const selectedBillingCycle = ref(props.billingCycles?.[0]?.id || null);
const selectedOptions = ref({});

// Inicializar opciones seleccionadas
const initializeOptions = () => {
    if (props.availableResourceGroups) {
        props.availableResourceGroups.forEach(group => {
            group.options.forEach(option => {
                if (option.option_type === 'quantity') {
                    selectedOptions.value[option.id] = 0;
                } else if (option.option_type === 'checkbox') {
                    selectedOptions.value[option.id] = false;
                }
            });
        });
    }
};

// Inicializar al montar
initializeOptions();

// Precio base del producto para el ciclo seleccionado
const basePrice = computed(() => {
    if (!props.product?.pricings || !selectedBillingCycle.value) return 0;

    const pricing = props.product.pricings.find(p => p.billing_cycle_id === selectedBillingCycle.value);
    return pricing ? parseFloat(pricing.price) : 0;
});

// Calcular precio de opciones configurables
const optionsPrice = computed(() => {
    let total = 0;

    if (!props.availableResourceGroups) return total;

    props.availableResourceGroups.forEach(group => {
        group.options.forEach(option => {
            const selectedValue = selectedOptions.value[option.id];

            if (option.option_type === 'quantity' && selectedValue > 0) {
                // Para opciones de cantidad, multiplicar por la cantidad
                const optionPrice = getOptionPrice(option);
                total += optionPrice * selectedValue;
            } else if (option.option_type === 'checkbox' && selectedValue) {
                // Para checkboxes, solo agregar el precio si está marcado
                total += getOptionPrice(option);
            }
        });
    });

    return total;
});

// Obtener precio de una opción para el ciclo seleccionado
const getOptionPrice = (option) => {
    if (!option.pricings || !selectedBillingCycle.value) return 0;

    const pricing = option.pricings.find(p => p.billing_cycle_id === selectedBillingCycle.value);
    return pricing ? parseFloat(pricing.price) : 0;
};

// Precio total
const totalPrice = computed(() => {
    return basePrice.value + optionsPrice.value;
});

// Información del ciclo seleccionado
const selectedCycleInfo = computed(() => {
    if (!selectedBillingCycle.value || !props.billingCycles) return null;

    return props.billingCycles.find(cycle => cycle.id === selectedBillingCycle.value);
});

// Formatear moneda
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'USD'
    }).format(amount || 0);
};

// Obtener recursos base totales desde base_resources dinámico
const getTotalResources = computed(() => {
    const baseResources = props.product?.base_resources || {};
    const base = {
        disk: parseFloat(baseResources.disk_space || 0),
        vcpu: parseInt(baseResources.vcpu_cores || 0),
        ram: parseFloat(baseResources.ram_memory || 0),
        bandwidth: parseInt(baseResources.bandwidth || 0),
        emails: parseInt(baseResources.email_accounts || 0),
        databases: parseInt(baseResources.databases || 0),
        domains: parseInt(baseResources.domains || 0),
        subdomains: parseInt(baseResources.subdomains || 0),
    };

    // Agregar recursos de opciones configurables
    if (props.availableResourceGroups) {
        props.availableResourceGroups.forEach(group => {
            group.options.forEach(option => {
                const selectedValue = selectedOptions.value[option.id];

                if (option.option_type === 'quantity' && selectedValue > 0) {
                    // Mapear opciones a recursos (esto se puede hacer más dinámico)
                    if (group.name.toLowerCase().includes('espacio') || group.name.toLowerCase().includes('disco')) {
                        base.disk += selectedValue;
                    } else if (group.name.toLowerCase().includes('vcpu') || group.name.toLowerCase().includes('cpu')) {
                        base.vcpu += selectedValue;
                    } else if (group.name.toLowerCase().includes('ram') || group.name.toLowerCase().includes('memoria')) {
                        base.ram += selectedValue;
                    }
                }
            });
        });
    }

    return base;
});
</script>

<template>
    <div class="bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-6 border border-blue-200 dark:border-blue-800">
        <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200 mb-4 flex items-center">
            🧮 Calculadora de Precios
        </h3>

        <!-- Selector de Ciclo de Facturación -->
        <div class="mb-6">
            <InputLabel for="billing_cycle" value="Ciclo de Facturación" />
            <SelectInput
                id="billing_cycle"
                v-model="selectedBillingCycle"
                :options="billingCycles.map(cycle => ({ value: cycle.id, label: cycle.name }))"
                class="mt-1 block w-full max-w-xs"
            />
        </div>

        <!-- Configuración de Opciones -->
        <div v-if="availableResourceGroups && availableResourceGroups.length > 0" class="mb-6">
            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Opciones Configurables:</h4>
            <div class="space-y-4">
                <div v-for="group in availableResourceGroups" :key="group.id" class="bg-white dark:bg-gray-800 rounded-lg p-4 border">
                    <h5 class="font-medium text-gray-800 dark:text-gray-200 mb-2">{{ group.name }}</h5>
                    <div class="space-y-2">
                        <div v-for="option in group.options" :key="option.id" class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ option.name }}</span>
                            <div class="flex items-center space-x-2">
                                <input
                                    v-if="option.option_type === 'quantity'"
                                    type="number"
                                    min="0"
                                    max="1000"
                                    v-model.number="selectedOptions[option.id]"
                                    class="w-20 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500"
                                />
                                <input
                                    v-else-if="option.option_type === 'checkbox'"
                                    type="checkbox"
                                    v-model="selectedOptions[option.id]"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                />
                                <span class="text-xs text-green-600 font-medium">
                                    {{ formatCurrency(getOptionPrice(option)) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen de Recursos Totales -->
        <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg p-4 border">
            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Recursos Totales:</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <div v-if="getTotalResources.disk > 0">
                    <span class="text-gray-600 dark:text-gray-400">Disco:</span>
                    <span class="font-medium ml-1">{{ getTotalResources.disk }}GB</span>
                </div>
                <div v-if="getTotalResources.vcpu > 0">
                    <span class="text-gray-600 dark:text-gray-400">vCPU:</span>
                    <span class="font-medium ml-1">{{ getTotalResources.vcpu }} cores</span>
                </div>
                <div v-if="getTotalResources.ram > 0">
                    <span class="text-gray-600 dark:text-gray-400">RAM:</span>
                    <span class="font-medium ml-1">{{ getTotalResources.ram }}GB</span>
                </div>
                <div v-if="getTotalResources.bandwidth > 0">
                    <span class="text-gray-600 dark:text-gray-400">Transferencia:</span>
                    <span class="font-medium ml-1">{{ getTotalResources.bandwidth }}GB</span>
                </div>
                <div v-if="getTotalResources.emails > 0">
                    <span class="text-gray-600 dark:text-gray-400">Emails:</span>
                    <span class="font-medium ml-1">{{ getTotalResources.emails }}</span>
                </div>
                <div v-if="getTotalResources.databases > 0">
                    <span class="text-gray-600 dark:text-gray-400">BD:</span>
                    <span class="font-medium ml-1">{{ getTotalResources.databases }}</span>
                </div>
                <div v-if="getTotalResources.domains > 0">
                    <span class="text-gray-600 dark:text-gray-400">Dominios:</span>
                    <span class="font-medium ml-1">{{ getTotalResources.domains }}</span>
                </div>
                <div v-if="getTotalResources.subdomains > 0">
                    <span class="text-gray-600 dark:text-gray-400">Subdominios:</span>
                    <span class="font-medium ml-1">{{ getTotalResources.subdomains }}</span>
                </div>
            </div>
        </div>

        <!-- Resumen de Precios -->
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg p-4 border border-green-200 dark:border-green-800">
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Precio base:</span>
                    <span class="font-medium">{{ formatCurrency(basePrice) }}</span>
                </div>
                <div v-if="optionsPrice > 0" class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Opciones adicionales:</span>
                    <span class="font-medium">{{ formatCurrency(optionsPrice) }}</span>
                </div>
                <hr class="border-gray-300 dark:border-gray-600">
                <div class="flex justify-between items-center">
                    <span class="font-semibold text-gray-900 dark:text-gray-100">Total {{ selectedCycleInfo?.name }}:</span>
                    <span class="text-xl font-bold text-green-600 dark:text-green-400">{{ formatCurrency(totalPrice) }}</span>
                </div>
                <div v-if="selectedCycleInfo && selectedCycleInfo.discount_percentage > 0" class="text-xs text-green-600 dark:text-green-400 text-center">
                    ✨ Incluye {{ selectedCycleInfo.discount_percentage }}% de descuento
                </div>
            </div>
        </div>
    </div>
</template>
