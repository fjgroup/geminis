<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextareaInput from '@/Components/TextareaInput.vue'; // Asumiendo que tienes este componente
import SelectInput from '@/Components/SelectInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue'; // <-- AÑADIR ESTA LÍNEA

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

const productOptions = [{ value: null, label: 'Global (ningún producto específico)' }, ...props.products.map(p => ({ value: p.id, label: p.name }))];


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
                <InputLabel for="product_id" value="Asociar a Producto (Opcional)" />
                <SelectInput id="product_id" class="block w-full mt-1" v-model="form.product_id"
                    :options="productOptions" />
                <InputError class="mt-2" :message="form.errors.product_id" />
            </div>
        </div>

        <div class="mt-4">
            <InputLabel for="description" value="Descripción" />
            <TextareaInput id="description" class="block w-full mt-1" v-model="form.description" :rows="3" />
            <InputError class="mt-2" :message="form.errors.description" />
        </div>

        <div class="mt-4">
            <InputLabel for="display_order" value="Orden de Visualización" />
            <TextInput id="display_order" type="number" class="block w-full mt-1" v-model="displayOrderModel" />
            <InputError class="mt-2" :message="form.errors.display_order" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <Link :href="route('admin.configurable-option-groups.index')"
                class="px-4 py-2 mr-4 text-sm text-gray-600 border border-gray-300 rounded-md hover:text-gray-900 hover:bg-gray-50">
            Cancelar
            </Link>
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                {{ isEdit ? 'Actualizar Grupo' : 'Crear Grupo' }}
            </PrimaryButton>
        </div>
    </form>
</template>

<style scoped>
/* Add any specific styles here */
</style>
