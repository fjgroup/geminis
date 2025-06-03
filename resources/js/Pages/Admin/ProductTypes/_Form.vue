<script setup>
import InputLabel from '@/Components/Forms/InputLabel.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import TextareaInput from '@/Components/Forms/TextareaInput.vue';
import Checkbox from '@/Components/Forms/Checkbox.vue';
import InputError from '@/Components/Forms/InputError.vue';
import PrimaryButton from '@/Components/Forms/Buttons/PrimaryButton.vue';
import SecondaryButton from '@/Components/Forms/Buttons/SecondaryButton.vue'; // For Cancel
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    form: Object, // Inertia useForm object
    isEdit: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['submit']);

// Helper function to generate slug-like string from name, can be enhanced
const generateSlug = () => {
    if (props.form.name && !props.form.slug && !props.isEdit) { // Only auto-generate on create if slug is empty
        props.form.slug = props.form.name
            .toLowerCase()
            .trim()
            .replace(/\s+/g, '-')           // Replace spaces with -
            .replace(/[^\w-]+/g, '')       // Remove all non-word chars
            .replace(/--+/g, '-');          // Replace multiple - with single -
    }
};

</script>

<template>
  <form @submit.prevent="$emit('submit')">
    <div class="space-y-6">
        <div>
            <InputLabel for="name" value="Nombre del Tipo de Producto *" />
            <TextInput
                id="name"
                type="text"
                class="mt-1 block w-full"
                v-model="form.name"
                @input="generateSlug"
                required
                autofocus />
            <InputError class="mt-2" :message="form.errors.name" />
        </div>

        <div>
            <InputLabel for="slug" value="Slug (Identificador URL) *" />
            <TextInput
                id="slug"
                type="text"
                class="mt-1 block w-full"
                v-model="form.slug"
                required />
            <InputError class="mt-2" :message="form.errors.slug" />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">El slug se usa en URLs y debe ser único. Se auto-genera al escribir el nombre (solo en creación).</p>
        </div>

        <div>
            <InputLabel for="description" value="Descripción (Opcional)" />
            <TextareaInput
                id="description"
                class="mt-1 block w-full"
                v-model="form.description"
                rows="4" />
            <InputError class="mt-2" :message="form.errors.description" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex items-center space-x-3 p-4 border rounded-md dark:border-gray-700">
                <Checkbox id="requires_domain" v-model:checked="form.requires_domain" />
                <InputLabel for="requires_domain" value="¿Requiere Dominio?" class="mb-0" />
                <InputError class="mt-2" :message="form.errors.requires_domain" />
            </div>

            <div class="flex items-center space-x-3 p-4 border rounded-md dark:border-gray-700">
                <Checkbox id="creates_service_instance" v-model:checked="form.creates_service_instance" />
                <InputLabel for="creates_service_instance" value="¿Crea Instancia de Servicio?" class="mb-0" />
                <InputError class="mt-2" :message="form.errors.creates_service_instance" />
            </div>
        </div>

        <div class="flex items-center justify-end mt-8 space-x-4 border-t dark:border-gray-700 pt-6">
            <Link :href="route('admin.product-types.index')" class="text-sm">
                <SecondaryButton type="button">Cancelar</SecondaryButton>
            </Link>
            <PrimaryButton :disabled="form.processing">
                {{ isEdit ? 'Actualizar Tipo de Producto' : 'Crear Tipo de Producto' }}
            </PrimaryButton>
        </div>
    </div>
  </form>
</template>
