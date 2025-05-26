<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    invoices: Object,
});
</script>

<template>
    <Head title="Facturas" />

    <AdminLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <pre>{{ invoices }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
