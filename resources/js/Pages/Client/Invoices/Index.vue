<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Pagination from '@/Components/UI/Pagination.vue';
// Removed: import { formatCurrency } from '@/utils';
import { format } from 'date-fns'; // O usa otra librería para formateo de fechas

defineProps({
    invoices: Object, // Inertia pasa un objeto paginado
});

// Local formatCurrency helper function
const formatCurrency = (amount, currencyCode = 'USD') => {
    const number = parseFloat(amount);
    if (isNaN(number)) {
        return 'N/A'; // Or some other placeholder for invalid numbers
    }
    try {
        // Using Intl.NumberFormat for robust currency formatting
        return new Intl.NumberFormat(undefined, { style: 'currency', currency: currencyCode }).format(number);
    } catch (e) {
        // Fallback for invalid currency code or if Intl is not fully supported
        return `${currencyCode} ${number.toFixed(2)}`;
    }
};

// Función de utilidad para formatear fechas
const formatDate = (dateString) => {
    return dateString ? format(new Date(dateString), 'dd/MM/yyyy') : 'N/A';
};
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Mis Facturas" />

        <!-- Standard page header, if AuthenticatedLayout provides a slot for it -->
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Mis Facturas
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="p-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <!-- Removed redundant h2 title, assuming header slot is used -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                        Número de Factura
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                        Fecha de Emisión
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                        Fecha de Vencimiento
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-300">
                                        Total
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-300">
                                        Estado
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Ver</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                <tr v-for="invoice in invoices.data" :key="invoice.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td
                                        class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ invoice.invoice_number }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-300">
                                        {{ formatDate(invoice.issue_date) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-300">
                                        {{ formatDate(invoice.due_date) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm text-right text-gray-500 whitespace-nowrap dark:text-gray-300">
                                        {{ formatCurrency(invoice.total_amount, invoice.currency_code) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                                        <span :class="{
                                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100': invoice.status === 'unpaid' || invoice.status === 'pending' || invoice.status === 'overdue',
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100': invoice.status === 'paid',
                                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100': invoice.status === 'cancelled',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100': invoice.status === 'refunded',
                                            'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200': !['unpaid', 'pending', 'overdue', 'paid', 'cancelled', 'refunded'].includes(invoice.status)
                                        }">
                                            {{ invoice.status ? invoice.status.replace(/_/g, ' ').replace(/\w/g,
                                                function (char)
                                            { return char.toUpperCase(); }) : 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                        <Link :href="route('client.invoices.show', invoice.id)"
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        Ver
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="invoices.data.length === 0">
                                    <td colspan="6"
                                        class="px-6 py-4 text-sm text-center text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        No tienes facturas disponibles.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <Pagination :links="invoices.links" class="mt-6" v-if="invoices.links.length > 3" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
