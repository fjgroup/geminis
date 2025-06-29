<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/Forms/InputLabel.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import SelectInput from '@/Components/Forms/SelectInput.vue';
import InputError from '@/Components/Forms/InputError.vue';
import PrimaryButton from '@/Components/Forms/Buttons/PrimaryButton.vue';
import SecondaryButton from '@/Components/Forms/Buttons/SecondaryButton.vue';

const props = defineProps({
    option: Object,
    billingCycles: Array,
    show: Boolean,
});

const emit = defineEmits(['close', 'saved']);

const form = useForm({
    pricings: []
});

// Inicializar precios existentes o crear nuevos
const initializePricings = () => {
    if (props.option?.pricings && props.option.pricings.length > 0) {
        form.pricings = props.option.pricings.map(pricing => ({
            id: pricing.id,
            billing_cycle_id: pricing.billing_cycle_id,
            price: pricing.price,
            setup_fee: pricing.setup_fee || 0,
            currency_code: pricing.currency_code || 'USD',
            is_active: pricing.is_active ?? true,
        }));
    } else {
        // Crear precios para todos los ciclos de facturación
        form.pricings = props.billingCycles.map(cycle => ({
            id: null,
            billing_cycle_id: cycle.id,
            price: 0,
            setup_fee: 0,
            currency_code: 'USD',
            is_active: true,
        }));
    }
};

// Reinicializar cuando cambie la opción
watch(() => props.option, () => {
    if (props.option) {
        initializePricings();
    }
}, { immediate: true });

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-ES', { 
        style: 'currency', 
        currency: 'USD' 
    }).format(value || 0);
};

const getBillingCycleName = (billingCycleId) => {
    const cycle = props.billingCycles.find(c => c.id === billingCycleId);
    return cycle ? cycle.name : 'Desconocido';
};

const savePricings = () => {
    form.post(route('admin.configurable-options.update-pricings', props.option.id), {
        onSuccess: () => {
            emit('saved');
            emit('close');
        },
        onError: (errors) => {
            console.error('Error saving pricings:', errors);
        }
    });
};

const closeModal = () => {
    form.reset();
    emit('close');
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModal"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                Gestionar Precios - {{ option?.name }}
                            </h3>

                            <div v-if="option?.description" class="mb-4 p-3 bg-blue-50 rounded-lg">
                                <p class="text-sm text-blue-800">{{ option.description }}</p>
                            </div>

                            <div class="space-y-4">
                                <div v-for="(pricing, index) in form.pricings" :key="pricing.billing_cycle_id" 
                                     class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="font-medium text-gray-900">
                                            {{ getBillingCycleName(pricing.billing_cycle_id) }}
                                        </h4>
                                        <label class="flex items-center">
                                            <input 
                                                type="checkbox" 
                                                v-model="pricing.is_active"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            >
                                            <span class="ml-2 text-sm text-gray-600">Activo</span>
                                        </label>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <InputLabel for="`price_${index}`" value="Precio" />
                                            <TextInput
                                                :id="`price_${index}`"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                v-model="pricing.price"
                                                class="mt-1 block w-full"
                                                placeholder="0.00"
                                            />
                                            <InputError class="mt-2" :message="form.errors[`pricings.${index}.price`]" />
                                        </div>

                                        <div>
                                            <InputLabel for="`setup_fee_${index}`" value="Tarifa de Configuración" />
                                            <TextInput
                                                :id="`setup_fee_${index}`"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                v-model="pricing.setup_fee"
                                                class="mt-1 block w-full"
                                                placeholder="0.00"
                                            />
                                            <InputError class="mt-2" :message="form.errors[`pricings.${index}.setup_fee`]" />
                                        </div>

                                        <div>
                                            <InputLabel for="`currency_${index}`" value="Moneda" />
                                            <SelectInput
                                                :id="`currency_${index}`"
                                                v-model="pricing.currency_code"
                                                :options="[
                                                    { value: 'USD', label: 'USD - Dólar Americano' },
                                                    { value: 'EUR', label: 'EUR - Euro' },
                                                    { value: 'GBP', label: 'GBP - Libra Esterlina' }
                                                ]"
                                                class="mt-1 block w-full"
                                            />
                                            <InputError class="mt-2" :message="form.errors[`pricings.${index}.currency_code`]" />
                                        </div>
                                    </div>

                                    <div v-if="pricing.price > 0" class="mt-2 text-sm text-gray-600">
                                        <span class="font-medium">Vista previa:</span> 
                                        {{ formatCurrency(pricing.price) }} / {{ getBillingCycleName(pricing.billing_cycle_id) }}
                                        <span v-if="pricing.setup_fee > 0">
                                            + {{ formatCurrency(pricing.setup_fee) }} (configuración)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <PrimaryButton
                        @click="savePricings"
                        :disabled="form.processing"
                        class="w-full sm:w-auto sm:ml-3"
                    >
                        {{ form.processing ? 'Guardando...' : 'Guardar Precios' }}
                    </PrimaryButton>
                    
                    <SecondaryButton
                        @click="closeModal"
                        :disabled="form.processing"
                        class="mt-3 w-full sm:mt-0 sm:w-auto"
                    >
                        Cancelar
                    </SecondaryButton>
                </div>
            </div>
        </div>
    </div>
</template>
