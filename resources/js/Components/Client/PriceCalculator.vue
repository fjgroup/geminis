<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    product: Object,
    billingCycles: Array,
    configurableOptions: Array,
    initialBillingCycle: {
        type: Number,
        default: null
    }
});

const emit = defineEmits(['priceCalculated', 'configurationChanged']);

const selectedBillingCycle = ref(props.initialBillingCycle || props.billingCycles[0]?.id);
const selectedOptions = ref({});
const optionQuantities = ref({});
const isCalculating = ref(false);
const calculationResult = ref(null);
const calculationError = ref(null);

// Computed properties
const selectedBillingCycleData = computed(() => {
    return props.billingCycles.find(cycle => cycle.id === selectedBillingCycle.value);
});

const basePricing = computed(() => {
    if (!props.product?.pricings || !selectedBillingCycle.value) return null;
    return props.product.pricings.find(p => p.billing_cycle_id === selectedBillingCycle.value);
});

const discountPercentage = computed(() => {
    if (!selectedBillingCycleData.value?.discount_percentage) return 0;
    return selectedBillingCycleData.value.discount_percentage.percentage || 0;
});

const hasDiscount = computed(() => discountPercentage.value > 0);

// Calculate pricing
const calculatePricing = async () => {
    if (!props.product?.id || !selectedBillingCycle.value) return;

    isCalculating.value = true;
    calculationError.value = null;

    try {
        const options = Object.entries(selectedOptions.value)
            .filter(([optionId, isSelected]) => isSelected)
            .map(([optionId]) => ({
                option_id: parseInt(optionId),
                quantity: optionQuantities.value[optionId] || 1
            }));

        const response = await axios.post(
            route('admin.products.calculate-pricing', props.product.id),
            {
                billing_cycle_id: selectedBillingCycle.value,
                options: options
            }
        );

        calculationResult.value = response.data;
        
        // Apply discount if applicable
        if (hasDiscount.value) {
            const discountAmount = (calculationResult.value.total_price * discountPercentage.value) / 100;
            calculationResult.value.discount_amount = discountAmount;
            calculationResult.value.discounted_price = calculationResult.value.total_price - discountAmount;
        }

        emit('priceCalculated', {
            ...calculationResult.value,
            billing_cycle: selectedBillingCycleData.value,
            selected_options: selectedOptions.value,
            option_quantities: optionQuantities.value
        });

    } catch (error) {
        console.error('Error calculating pricing:', error);
        calculationError.value = 'Error al calcular el precio. Por favor, intenta de nuevo.';
    } finally {
        isCalculating.value = false;
    }
};

// Watch for changes and recalculate
watch([selectedBillingCycle, selectedOptions, optionQuantities], () => {
    calculatePricing();
    emit('configurationChanged', {
        billing_cycle_id: selectedBillingCycle.value,
        selected_options: selectedOptions.value,
        option_quantities: optionQuantities.value
    });
}, { deep: true });

// Initialize
calculatePricing();

// Helper functions
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};

const getOptionPricing = (option, billingCycleId) => {
    if (!option.pricings) return null;
    return option.pricings.find(p => p.billing_cycle_id === billingCycleId);
};

const isOptionRequired = (option) => {
    return option.is_required || false;
};

const getOptionType = (option) => {
    const types = {
        'dropdown': 'Lista desplegable',
        'radio': 'Selección única',
        'checkbox': 'Activar/Desactivar',
        'quantity': 'Cantidad',
        'text': 'Texto libre'
    };
    return types[option.option_type] || option.option_type;
};
</script>

<template>
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Configurar Producto</h3>

        <!-- Selector de ciclo de facturación -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Ciclo de Facturación
            </label>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                <div v-for="cycle in billingCycles" :key="cycle.id">
                    <label class="relative flex cursor-pointer rounded-lg border p-4 focus:outline-none"
                           :class="{
                               'border-blue-500 bg-blue-50': selectedBillingCycle === cycle.id,
                               'border-gray-300': selectedBillingCycle !== cycle.id
                           }">
                        <input type="radio" 
                               :value="cycle.id" 
                               v-model="selectedBillingCycle"
                               class="sr-only">
                        <div class="flex flex-1 flex-col">
                            <div class="flex items-center justify-between">
                                <span class="block text-sm font-medium text-gray-900">
                                    {{ cycle.name }}
                                </span>
                                <span v-if="cycle.discount_percentage && cycle.discount_percentage.percentage > 0"
                                      class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                    -{{ cycle.discount_percentage.percentage }}%
                                </span>
                            </div>
                            <span class="block text-xs text-gray-500 mt-1">
                                {{ cycle.days }} días
                            </span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Opciones configurables -->
        <div v-if="configurableOptions && configurableOptions.length > 0" class="mb-6">
            <h4 class="text-md font-medium text-gray-900 mb-4">Opciones Adicionales</h4>
            
            <div class="space-y-6">
                <div v-for="group in configurableOptions" :key="group.id" class="border border-gray-200 rounded-lg p-4">
                    <h5 class="text-sm font-medium text-gray-900 mb-3">
                        {{ group.name }}
                        <span v-if="group.is_required" class="text-red-500">*</span>
                    </h5>
                    <p v-if="group.description" class="text-xs text-gray-600 mb-3">{{ group.description }}</p>
                    
                    <div class="space-y-3">
                        <div v-for="option in group.options" :key="option.id" class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <!-- Checkbox para opciones -->
                                <input v-if="option.option_type === 'checkbox'"
                                       :id="`option-${option.id}`"
                                       type="checkbox"
                                       v-model="selectedOptions[option.id]"
                                       class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                
                                <!-- Quantity input para opciones de cantidad -->
                                <div v-else-if="option.option_type === 'quantity'" class="flex items-center space-x-2">
                                    <input type="checkbox"
                                           :id="`option-${option.id}`"
                                           v-model="selectedOptions[option.id]"
                                           class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <input v-if="selectedOptions[option.id]"
                                           type="number"
                                           :min="option.min_value || 1"
                                           :max="option.max_value || 999"
                                           v-model="optionQuantities[option.id]"
                                           class="w-20 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <label :for="`option-${option.id}`" class="flex-1">
                                    <span class="text-sm font-medium text-gray-900">{{ option.name }}</span>
                                    <span v-if="option.description" class="block text-xs text-gray-500">{{ option.description }}</span>
                                    <span class="block text-xs text-gray-400">{{ getOptionType(option) }}</span>
                                </label>
                            </div>
                            
                            <!-- Precio de la opción -->
                            <div class="text-right">
                                <template v-if="getOptionPricing(option, selectedBillingCycle)">
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ formatCurrency(getOptionPricing(option, selectedBillingCycle).price) }}
                                    </span>
                                    <span v-if="option.option_type === 'quantity'" class="text-xs text-gray-500">
                                        / unidad
                                    </span>
                                    <span class="block text-xs text-gray-500">
                                        por {{ selectedBillingCycleData?.name?.toLowerCase() }}
                                    </span>
                                </template>
                                <span v-else class="text-sm text-gray-400">Sin precio</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen de precios -->
        <div class="border-t pt-4">
            <h4 class="text-md font-medium text-gray-900 mb-3">Resumen de Precios</h4>
            
            <div v-if="isCalculating" class="flex items-center justify-center py-4">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                <span class="ml-2 text-sm text-gray-600">Calculando...</span>
            </div>
            
            <div v-else-if="calculationError" class="text-red-600 text-sm py-2">
                {{ calculationError }}
            </div>
            
            <div v-else-if="calculationResult" class="space-y-2">
                <!-- Precio base -->
                <div class="flex justify-between text-sm">
                    <span>{{ product.name }} ({{ selectedBillingCycleData?.name }})</span>
                    <span>{{ formatCurrency(calculationResult.base_price) }}</span>
                </div>
                
                <!-- Opciones seleccionadas -->
                <div v-for="option in calculationResult.options" :key="option.option_name" 
                     class="flex justify-between text-sm text-gray-600">
                    <span>
                        {{ option.option_name }}
                        <span v-if="option.quantity > 1">({{ option.quantity }}x)</span>
                    </span>
                    <span>{{ formatCurrency(option.total_price) }}</span>
                </div>
                
                <!-- Subtotal -->
                <div class="flex justify-between text-sm font-medium border-t pt-2">
                    <span>Subtotal</span>
                    <span>{{ formatCurrency(calculationResult.total_price) }}</span>
                </div>
                
                <!-- Descuento -->
                <div v-if="hasDiscount" class="flex justify-between text-sm text-green-600">
                    <span>Descuento ({{ discountPercentage }}%)</span>
                    <span>-{{ formatCurrency(calculationResult.discount_amount) }}</span>
                </div>
                
                <!-- Total -->
                <div class="flex justify-between text-lg font-bold border-t pt-2">
                    <span>Total</span>
                    <span class="text-blue-600">
                        {{ formatCurrency(hasDiscount ? calculationResult.discounted_price : calculationResult.total_price) }}
                    </span>
                </div>
                
                <!-- Setup fees -->
                <div v-if="calculationResult.total_setup_fee > 0" class="text-xs text-gray-500 mt-2">
                    + {{ formatCurrency(calculationResult.total_setup_fee) }} tarifa de configuración única
                </div>
                
                <!-- Billing cycle info -->
                <div class="text-xs text-gray-500 mt-2">
                    Facturado cada {{ selectedBillingCycleData?.days }} días
                </div>
            </div>
        </div>
    </div>
</template>
