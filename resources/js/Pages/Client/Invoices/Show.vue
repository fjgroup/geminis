<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // Usar el layout de cliente
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    invoice: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head title="Detalles de Factura" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Detalles de Factura</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="mb-4 text-lg font-semibold">Factura #{{ invoice.number }}</h3>

                        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2">
                            <div><strong>Fecha de Emisión:</strong> {{ new Date(invoice.issue_date).toLocaleDateString() }}</div>
                            <div><strong>Fecha de Vencimiento:</strong> {{ new Date(invoice.due_date).toLocaleDateString() }}</div>
                            <div><strong>Estado:</strong> {{ invoice.status }}</div>
                            <div><strong>Total:</strong> {{ invoice.total }}</div>
                            <div v-if="invoice.client"><strong>Cliente:</strong> {{ invoice.client.name }}</div>
                            <div v-if="invoice.reseller"><strong>Revendedor:</strong> {{ invoice.reseller.name }}</div>
                        </div>

                        <h4 class="mb-3 font-semibold text-md">Ítems de la Factura:</h4>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Descripción</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Cantidad</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Precio Unitario</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in invoice.items" :key="item.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ item.description }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ item.quantity }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ item.unit_price }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ item.subtotal }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
