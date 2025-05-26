<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    order: Object,
});
</script>

<template>
    <Head title="Detalles de la Orden" />

    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Detalles de la Orden
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="mb-4 text-lg font-semibold">Detalles de la Orden:</h3>
                        <pre>{{ JSON.stringify(props.order, null, 2) }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
