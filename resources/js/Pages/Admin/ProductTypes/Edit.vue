<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ProductTypeForm from '@/Pages/Admin/ProductTypes/_Form.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    productType: Object,
});

const form = useForm({
    _method: 'PUT', // Important for PUT requests with Inertia forms
    name: props.productType.name,
    slug: props.productType.slug,
    description: props.productType.description || '',
    requires_domain: Boolean(props.productType.requires_domain), // Ensure boolean
    creates_service_instance: Boolean(props.productType.creates_service_instance), // Ensure boolean
});

const submit = () => {
    form.post(route('admin.product-types.update', props.productType.id), {
        // Using form.post with _method: 'PUT'
        // onSuccess: () => { /* Controller redirects, flash message should appear */ }
    });
};
</script>

<template>
    <Head :title="'Editar Tipo de Producto - ' + productType.name" />

    <AdminLayout :title="'Editar Tipo de Producto - ' + productType.name">
        <template #header>
             <div class="flex items-center">
                <Link :href="route('admin.product-types.index')"
                      class="inline-flex items-center justify-center p-2 mr-4 text-gray-600 transition duration-150 ease-in-out rounded-md dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-gray-800 dark:focus:text-gray-200">
                    <ArrowLeftIcon class="w-5 h-5" />
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Editar Tipo de Producto: <span class="italic">{{ productType.name }}</span>
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                 <div class="p-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <ProductTypeForm :form="form" @submit="submit" :isEdit="true" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
