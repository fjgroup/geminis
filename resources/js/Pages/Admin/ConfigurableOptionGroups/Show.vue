<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    group: Object,
    billingCycles: Array,
    products: Array,
});

const showAddOptionModal = ref(false);
const editingOption = ref(null);

const optionForm = useForm({
    name: '',
    slug: '',
    value: '',
    description: '',
    option_type: 'dropdown',
    is_required: false,
    is_active: true,
    min_value: null,
    max_value: null,
    display_order: 0,
    pricings: []
});

const optionTypes = [
    { value: 'dropdown', label: 'Lista desplegable' },
    { value: 'radio', label: 'Botones de radio' },
    { value: 'checkbox', label: 'Casilla de verificación' },
    { value: 'quantity', label: 'Cantidad numérica' },
    { value: 'text', label: 'Texto libre' },
];

const initializePricings = () => {
    optionForm.pricings = props.billingCycles.map(cycle => ({
        billing_cycle_id: cycle.id,
        price: 0,
        setup_fee: 0
    }));
};

const openAddOptionModal = () => {
    optionForm.reset();
    initializePricings();
    editingOption.value = null;
    showAddOptionModal.value = true;
};

const openEditOptionModal = (option) => {
    editingOption.value = option;
    optionForm.name = option.name;
    optionForm.slug = option.slug;
    optionForm.value = option.value;
    optionForm.description = option.description;
    optionForm.option_type = option.option_type;
    optionForm.is_required = option.is_required;
    optionForm.is_active = option.is_active;
    optionForm.min_value = option.min_value;
    optionForm.max_value = option.max_value;
    optionForm.display_order = option.display_order;
    
    // Cargar precios existentes
    optionForm.pricings = props.billingCycles.map(cycle => {
        const existingPricing = option.pricings.find(p => p.billing_cycle_id === cycle.id);
        return {
            billing_cycle_id: cycle.id,
            price: existingPricing ? existingPricing.price : 0,
            setup_fee: existingPricing ? existingPricing.setup_fee : 0
        };
    });
    
    showAddOptionModal.value = true;
};

const submitOption = () => {
    if (editingOption.value) {
        // Actualizar opción existente
        optionForm.patch(route('admin.configurable-option-groups.options.update', [props.group.id, editingOption.value.id]), {
            onSuccess: () => {
                showAddOptionModal.value = false;
                optionForm.reset();
            }
        });
    } else {
        // Crear nueva opción
        optionForm.post(route('admin.configurable-option-groups.add-option', props.group.id), {
            onSuccess: () => {
                showAddOptionModal.value = false;
                optionForm.reset();
            }
        });
    }
};

const deleteOption = (option) => {
    if (confirm(`¿Estás seguro de que quieres eliminar la opción "${option.name}"?`)) {
        router.delete(route('admin.configurable-option-groups.remove-option', [props.group.id, option.id]));
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};
</script>

<template>
    <Head :title="`${group.name} - Opciones Configurables`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ group.name }}
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">{{ group.description }}</p>
                </div>
                <div class="flex space-x-3">
                    <button 
                        @click="openAddOptionModal"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Agregar Opción
                    </button>
                    <Link 
                        :href="route('admin.configurable-option-groups.edit', group.id)"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Editar Grupo
                    </Link>
                    <Link 
                        :href="route('admin.configurable-option-groups.index')"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Volver
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Información del grupo -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Información del Grupo</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nombre</label>
                                <p class="mt-1 text-sm text-gray-900">{{ group.name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Slug</label>
                                <p class="mt-1 text-sm text-gray-900">{{ group.slug }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Estado</label>
                                <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="group.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                    {{ group.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de opciones -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Opciones Configurables</h3>
                        
                        <div v-if="group.options && group.options.length > 0" class="space-y-4">
                            <div v-for="option in group.options" :key="option.id" 
                                 class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3">
                                            <h4 class="text-lg font-medium text-gray-900">{{ option.name }}</h4>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ optionTypes.find(t => t.value === option.option_type)?.label || option.option_type }}
                                            </span>
                                            <span v-if="option.is_required" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Requerido
                                            </span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                  :class="option.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                                {{ option.is_active ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </div>
                                        <p v-if="option.description" class="text-sm text-gray-600 mt-1">{{ option.description }}</p>
                                        <div class="mt-2 text-xs text-gray-500">
                                            <span>Slug: {{ option.slug }}</span>
                                            <span v-if="option.value" class="ml-4">Valor: {{ option.value }}</span>
                                            <span v-if="option.min_value !== null || option.max_value !== null" class="ml-4">
                                                Rango: {{ option.min_value || 0 }} - {{ option.max_value || '∞' }}
                                            </span>
                                        </div>
                                        
                                        <!-- Precios -->
                                        <div v-if="option.pricings && option.pricings.length > 0" class="mt-3">
                                            <h5 class="text-sm font-medium text-gray-700 mb-2">Precios por Ciclo:</h5>
                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                                <div v-for="pricing in option.pricings" :key="pricing.id" 
                                                     class="text-xs bg-gray-50 p-2 rounded">
                                                    <div class="font-medium">{{ pricing.billing_cycle_name }}</div>
                                                    <div>{{ formatCurrency(pricing.price) }}</div>
                                                    <div v-if="pricing.setup_fee > 0" class="text-gray-500">
                                                        Setup: {{ formatCurrency(pricing.setup_fee) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex space-x-2 ml-4">
                                        <button 
                                            @click="openEditOptionModal(option)"
                                            class="text-blue-600 hover:text-blue-900 text-sm"
                                        >
                                            Editar
                                        </button>
                                        <button 
                                            @click="deleteOption(option)"
                                            class="text-red-600 hover:text-red-900 text-sm"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-else class="text-center py-8">
                            <p class="text-gray-500">No hay opciones configuradas para este grupo.</p>
                            <button 
                                @click="openAddOptionModal"
                                class="mt-4 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Agregar Primera Opción
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para agregar/editar opción -->
        <div v-if="showAddOptionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        {{ editingOption ? 'Editar Opción' : 'Agregar Nueva Opción' }}
                    </h3>
                    
                    <form @submit.prevent="submitOption" class="space-y-4">
                        <!-- Campos básicos -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nombre *</label>
                                <input v-model="optionForm.name" type="text" required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <div v-if="optionForm.errors.name" class="text-red-600 text-sm mt-1">{{ optionForm.errors.name }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Slug</label>
                                <input v-model="optionForm.slug" type="text"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <div v-if="optionForm.errors.slug" class="text-red-600 text-sm mt-1">{{ optionForm.errors.slug }}</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Descripción</label>
                            <textarea v-model="optionForm.description" rows="2"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tipo de Opción *</label>
                                <select v-model="optionForm.option_type" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option v-for="type in optionTypes" :key="type.value" :value="type.value">
                                        {{ type.label }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Valor Interno</label>
                                <input v-model="optionForm.value" type="text"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Orden</label>
                                <input v-model="optionForm.display_order" type="number" min="0"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                        </div>

                        <!-- Rangos para opciones de cantidad -->
                        <div v-if="optionForm.option_type === 'quantity'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Valor Mínimo</label>
                                <input v-model="optionForm.min_value" type="number" min="0"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Valor Máximo</label>
                                <input v-model="optionForm.max_value" type="number" min="0"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                        </div>

                        <!-- Checkboxes -->
                        <div class="flex space-x-6">
                            <label class="flex items-center">
                                <input v-model="optionForm.is_required" type="checkbox" class="rounded border-gray-300">
                                <span class="ml-2 text-sm text-gray-700">Requerido</span>
                            </label>
                            <label class="flex items-center">
                                <input v-model="optionForm.is_active" type="checkbox" class="rounded border-gray-300">
                                <span class="ml-2 text-sm text-gray-700">Activo</span>
                            </label>
                        </div>

                        <!-- Precios por ciclo -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Precios por Ciclo de Facturación</h4>
                            <div class="space-y-3">
                                <div v-for="(pricing, index) in optionForm.pricings" :key="pricing.billing_cycle_id"
                                     class="grid grid-cols-1 md:grid-cols-3 gap-4 p-3 bg-gray-50 rounded">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">
                                            {{ billingCycles.find(c => c.id === pricing.billing_cycle_id)?.name }}
                                        </label>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Precio</label>
                                        <input v-model="pricing.price" type="number" step="0.01" min="0"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Tarifa de Setup</label>
                                        <input v-model="pricing.setup_fee" type="number" step="0.01" min="0"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" @click="showAddOptionModal = false"
                                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="optionForm.processing"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50">
                                {{ editingOption ? 'Actualizar' : 'Crear' }} Opción
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
