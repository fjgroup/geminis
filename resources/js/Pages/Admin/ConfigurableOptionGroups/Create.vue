<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'; // Ajusta tu layout
import { Head, useForm } from '@inertiajs/vue3';
import ConfigurableOptionGroupForm from './_Form.vue';

const props = defineProps({
    products: Array,
});

const form = useForm({
    name: '',
    slug: '',
    description: '',
    product_ids: [],
    display_order: 0,
    is_active: true,
    is_required: false,
});

const submit = () => {
    form.post(route('admin.configurable-option-groups.store'));
};
</script>

<template>
    <AdminLayout title="Crear Grupo de Opciones Configurables">

        <Head title="Crear Nuevo Grupo de Opciones Configurables" />
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Crear Nuevo Grupo de Opciones Configurables
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="p-6 overflow-hidden bg-white shadow-xl sm:rounded-lg">
                    <ConfigurableOptionGroupForm :form="form" :products="products" @submit="submit" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Add any specific styles here */
</style>
