<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'; // Asumiendo que usarás este layout

const props = defineProps({
    invoice: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <AdminLayout title="Detalles de Factura">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h2 class="mb-4 text-lg font-semibold">Detalles de Factura</h2>
                        <pre>{{ invoice }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
