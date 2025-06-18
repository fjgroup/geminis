<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ProductTypeForm from '@/Pages/Admin/ProductTypes/_Form.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'; // For back button icon

const form = useForm({
    name: '',
    slug: '',
    description: '',
    requires_domain: false,
    creates_service_instance: false,
});

const submit = () => {
    form.post(route('admin.product-types.store'), {
        // On success, the controller redirects to index, which should show the flash message.
        // We can reset the form if staying on the page, but redirect is typical.
        // onSuccess: () => form.reset(), // Only if not redirecting or if needed before redirect
    });
};
</script>

<template>
    <Head title="Crear Tipo de Producto" />

    <AdminLayout title="Crear Tipo de Producto">
        <template #header>
            <div class="flex items-center">
                <Link :href="route('admin.product-types.index')"
                      class="inline-flex items-center justify-center p-2 mr-4 text-gray-600 transition duration-150 ease-in-out rounded-md dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-gray-800 dark:focus:text-gray-200">
                    <ArrowLeftIcon class="w-5 h-5" />
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Crear Nuevo Tipo de Producto
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="p-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <ProductTypeForm :form="form" @submit="submit" :isEdit="false" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
