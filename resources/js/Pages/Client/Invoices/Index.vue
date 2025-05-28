<script setup>
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import { formatCurrency } from '@/utils'; // Asumiendo que tienes una utilidad para formatear moneda
import { format } from 'date-fns'; // O usa otra librería para formateo de fechas

defineProps({
    invoices: Object, // Inertia pasa un objeto paginado
});

// Función de utilidad para formatear fechas
const formatDate = (dateString) => {
    return dateString ? format(new Date(dateString), 'dd/MM/yyyy') : 'N/A';
};
</script>

<template>
    <ClientLayout>
        <Head title="Mis Facturas" />

        <div class="container py-12 mx-auto">
            <div class="p-6 bg-white rounded-lg shadow-sm">
                <h2 class="mb-4 text-2xl font-semibold">Mis Facturas</h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Número de Factura
                                </th>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Fecha de Emisión
                                </th>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Fecha de Vencimiento
                                </th>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Total
                                </th>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Estado
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Ver</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="invoice in invoices.data" :key="invoice.id">
                                <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                    {{ invoice.invoice_number }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                    {{ formatDate(invoice.issue_date) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                    {{ formatDate(invoice.due_date) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                    {{ formatCurrency(invoice.total_amount, invoice.currency_code) }}
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    <span :class="{
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100': invoice.status === 'unpaid' || invoice.status === 'pending' || invoice.status === 'overdue',
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100': invoice.status === 'paid',
                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100': invoice.status === 'cancelled',
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100': invoice.status === 'refunded',
                                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': !['unpaid', 'pending', 'overdue', 'paid', 'cancelled', 'refunded'].includes(invoice.status)
                                    }">
                                        {{ invoice.status ? invoice.status.replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase()) : 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                    <Link :href="route('client.invoices.show', invoice.id)" class="text-indigo-600 hover:text-indigo-900">
                                        Ver
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="invoices.data.length === 0">
                                <td colspan="6" class="px-6 py-4 text-sm text-center text-gray-500">
                                    No tienes facturas disponibles.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Pagination :links="invoices.links" class="mt-6" />
            </div>
        </div>
    </ClientLayout>
</template>
