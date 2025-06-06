<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Pagination from '@/Components/UI/Pagination.vue';
// import { format } from 'date-fns'; // Si usas date-fns, descomenta

defineProps({
    invoices: Object, // Inertia pasa un objeto paginado
});

// Local formatCurrency helper function
const formatCurrency = (amount, currencyCode = 'USD') => {
    const number = parseFloat(amount);
    if (isNaN(number)) {
        return 'N/A';
    }
    try {
        return new Intl.NumberFormat(undefined, { style: 'currency', currency: currencyCode }).format(number);
    } catch (e) {
        return `${currencyCode} ${number.toFixed(2)}`;
    }
};

// Función de utilidad para formatear fechas
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    if (dateString.length <= 10) { // Es solo fecha, sin hora
        const [year, month, day] = dateString.split('-');
        return new Date(year, month - 1, day).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
    }
    // Para datetime completo
    return new Date(dateString).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

// Actualizada para incluir todos los estados de Invoice
const getFriendlyInvoiceStatusText = (status) => {
    const mappings = {
        'draft': 'Borrador',
        'unpaid': 'No Pagada',
        'pending_confirmation': 'Pendiente Confirmación',
        'paid': 'Pagada',
        'pending_activation': 'Pendiente Activación',
        'active_service': 'Servicio Activo',
        'overdue': 'Vencida',
        'cancelled': 'Cancelada',
        'refunded': 'Reembolsada',
        'collections': 'En Cobranza',
        'failed_payment': 'Pago Fallido'
    };
    return mappings[status] || status?.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) || 'N/A';
};

// Actualizada para incluir clases para todos los estados de Invoice
const getInvoiceStatusClass = (status) => {
    return {
        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100': status === 'paid' || status === 'active_service',
        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100': status === 'unpaid' || status === 'overdue' || status === 'pending_confirmation' || status === 'pending_activation',
        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100': status === 'cancelled' || status === 'failed_payment',
        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100': status === 'refunded',
        'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100': status === 'collections',
        'bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-gray-300': status === 'draft', // Clase para draft
    };
};
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Mis Facturas" />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Mis Facturas
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="p-6 overflow-hidden bg-white shadow-sm dark:bg-gray-900 sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                        Número
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                        Emitida
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                        Vencimiento
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                        Solicitada
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-400">
                                        Total
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-400">
                                        Estado
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Ver</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-700/50 dark:divide-gray-600">
                                <template v-for="(invoice, index) in invoices.data" :key="invoice?.id || index">
                                    <tr v-if="typeof invoice === 'object' && invoice !== null && (typeof invoice.id === 'string' || typeof invoice.id === 'number')"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-600/50">
                                        <td
                                            class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{ invoice.invoice_number }}
                                        </td>
                                        <td
                                            class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-300">
                                            {{ formatDate(invoice.issue_date) }}
                                        </td>
                                        <td
                                            class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-300">
                                            {{ formatDate(invoice.due_date) }}
                                        </td>
                                        <td
                                            class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-300">
                                            {{ formatDate(invoice.requested_date) }}
                                        </td>
                                        <td
                                            class="px-6 py-4 text-sm text-right text-gray-500 whitespace-nowrap dark:text-gray-300">
                                            {{ formatCurrency(invoice.total_amount, invoice.currency_code) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-center whitespace-nowrap">
                                            <span :class="getInvoiceStatusClass(invoice.status)">
                                                {{ getFriendlyInvoiceStatusText(invoice.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                            <Link :href="route('client.invoices.show', invoice.id)"
                                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            Ver
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-else>
                                        <td colspan="7"
                                            class="px-6 py-4 text-sm text-center text-red-500 whitespace-nowrap dark:text-red-400">
                                            Error: Se encontró un dato de factura inválido (índice: {{ index }}).
                                        </td>
                                    </tr>
                                </template>
                                <tr v-if="invoices.data && invoices.data.length === 0">
                                    <td colspan="7"
                                        class="px-6 py-4 text-sm text-center text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        No tienes facturas disponibles.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <Pagination :links="invoices.links" class="mt-6"
                        v-if="invoices.links && invoices.links.length > 3" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
