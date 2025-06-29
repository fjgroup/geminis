<script setup>
import InputLabel from '@/Components/Forms/InputLabel.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import TextareaInput from '@/Components/Forms/TextareaInput.vue'; // Asumiendo que tienes este componente
import SelectInput from '@/Components/Forms/SelectInput.vue';
import InputError from '@/Components/Forms/InputError.vue';
import PrimaryButton from '@/Components/Forms/Buttons/PrimaryButton.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ArrowDownTrayIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    form: Object, // El objeto useForm de Inertia
    products: Array,
    isEdit: {
        type: Boolean,
        default: false,
    }
});

const emit = defineEmits(['submit']);

const submitForm = () => {
    emit('submit');
};

// Inicializar product_ids si no existe
if (!props.form.product_ids) {
    props.form.product_ids = [];
}

// Función para manejar la selección/deselección de productos
const toggleProduct = (productId) => {
    const index = props.form.product_ids.indexOf(productId);
    if (index > -1) {
        props.form.product_ids.splice(index, 1);
    } else {
        props.form.product_ids.push(productId);
    }
};


// Propiedad computada para manejar display_order como string para TextInput
const displayOrderModel = computed({
    get: () => String(props.form.display_order ?? 0), // Default a 0 si es null/undefined, luego a String
    set: (value) => {
        props.form.display_order = value === '' ? 0 : Number(value);
    }
});

</script>

<template>
    <form @submit.prevent="submitForm">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <InputLabel for="name" value="Nombre del Grupo" />
                <TextInput id="name" type="text" class="block w-full mt-1" v-model="form.name" required />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="display_order" value="Prioridad" />
                <TextInput id="display_order" type="number" class="block w-full mt-1" v-model="displayOrderModel" />
                <InputError class="mt-2" :message="form.errors.display_order" />
            </div>
        </div>

        <div class="mt-4">
            <InputLabel for="description" value="Descripción" />
            <TextareaInput id="description" class="block w-full mt-1" v-model="form.description" :rows="3" />
            <InputError class="mt-2" :message="form.errors.description" />
        </div>

        <!-- Sección de selección de productos -->
        <div class="mt-6">
            <InputLabel value="Productos Asociados" />
            <p class="text-sm text-gray-600 mb-3">Selecciona los productos a los que se aplicará este grupo de opciones. Si no seleccionas ninguno, será un grupo global.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-60 overflow-y-auto border border-gray-200 rounded-lg p-4">
                <div v-for="product in products" :key="product.id" class="flex items-center">
                    <input
                        :id="`product_${product.id}`"
                        type="checkbox"
                        :value="product.id"
                        :checked="form.product_ids.includes(product.id)"
                        @change="toggleProduct(product.id)"
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    />
                    <label :for="`product_${product.id}`" class="ml-2 text-sm text-gray-700 cursor-pointer">
                        {{ product.name }}
                    </label>
                </div>
            </div>

            <div v-if="products.length === 0" class="text-center py-4 text-gray-500">
                No hay productos disponibles
            </div>

            <InputError class="mt-2" :message="form.errors.product_ids" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <Link :href="route('admin.configurable-option-groups.index')"
                class="flex items-center px-4 py-2 mr-4 text-sm text-gray-600 border border-gray-300 rounded-md hover:text-gray-900 hover:bg-gray-50">
                <XMarkIcon class="w-5 h-5 mr-1" />
                Cancelar
            </Link>
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="flex items-center">
                <ArrowDownTrayIcon class="w-5 h-5 mr-2" />
                {{ isEdit ? 'Actualizar Grupo' : 'Crear Grupo' }}
            </PrimaryButton>
        </div>
    </form>
</template>

<style scoped>
/* Add any specific styles here */
</style>
