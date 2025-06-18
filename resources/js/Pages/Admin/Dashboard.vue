<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

// defineProps({
//   userCount: Number,
//   activeServicesCount: Number,
// });
</script>

<template>
    <AdminLayout title="Admin Dashboard">

        <Head title="Admin Dashboard" />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Admin Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        Welcome to the Admin Dashboard!
                    </div>
                </div>

                <!-- Ejemplo de cómo mostrar estadísticas si las pasas desde el controlador -->
                <!--
                <div class="grid grid-cols-1 gap-6 mt-6 md:grid-cols-2 lg:grid-cols-3">
                    ... (estadísticas comentadas) ...
                </div>
                -->
            </div>
        </div>
    </AdminLayout>
</template>