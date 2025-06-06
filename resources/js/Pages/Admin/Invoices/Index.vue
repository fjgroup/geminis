<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Pagination from '@/Components/UI/Pagination.vue'; // Asumiendo que tienes este componente
import { format } from 'date-fns'; // Para formateo de fechas

defineProps({
    invoices: Object, // Inertia pasa un objeto paginado
});

// Funciones Helper (consistentes con las usadas en el lado del cliente)
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

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    if (dateString.length <= 10) { // Es solo fecha
        const [year, month, day] = dateString.split('-');
        return new Date(year, month - 1, day).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
    }
    return new Date(dateString).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

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

const getInvoiceStatusClass = (status) => {
    return {
        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100': status === 'paid' || status === 'active_service',
        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100': status === 'unpaid' || status === 'overdue' || status === 'pending_confirmation' || status === 'pending_activation',
        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100': status === 'cancelled' || status === 'failed_payment',
        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100': status === 'refunded',
        'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100': status === 'collections',
        'bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-gray-300': status === 'draft',
    };
};
</script>

<template>
    <Head title="Gestión de Facturas" />

    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Gestión de Facturas
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8"> {/* max-w-full y px-4 para más espacio */}
                <div class="p-6 overflow-hidden bg-white shadow-sm dark:bg-gray-900 sm:rounded-lg">
                    <div class="mb-4">
                        <Link :href="route('admin.invoices.create')" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition">
                            Crear Factura Manual
                        </Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Número</th>
                                    <th scope="col" class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Cliente</th>
                                    <th scope="col" class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Solicitada</th>
                                    <th scope="col" class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Emitida</th>
                                    <th scope="col" class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Vencimiento</th>
                                    <th scope="col" class="px-4 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-400">Total</th>
                                    <th scope="col" class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-400">Estado</th>
                                    <th scope="col" class="relative px-4 py-3"><span class="sr-only">Acciones</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-700/50 dark:divide-gray-600">
                                <tr v-for="invoice in invoices.data" :key="invoice.id" class="hover:bg-gray-50 dark:hover:bg-gray-600/50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ invoice.invoice_number }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap dark:text-gray-300">{{ invoice.client ? invoice.client.name : 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap dark:text-gray-300">{{ formatDate(invoice.requested_date) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap dark:text-gray-300">{{ formatDate(invoice.issue_date) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap dark:text-gray-300">{{ formatDate(invoice.due_date) }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-500 whitespace-nowrap dark:text-gray-300">{{ formatCurrency(invoice.total_amount, invoice.currency_code) }}</td>
                                    <td class="px-4 py-3 text-sm text-center whitespace-nowrap">
                                        <span :class="getInvoiceStatusClass(invoice.status)">
                                            {{ getFriendlyInvoiceStatusText(invoice.status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-medium text-right whitespace-nowrap">
                                        <Link :href="route('admin.invoices.show', invoice.id)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-2">Ver</Link>
                                        <Link :href="route('admin.invoices.edit', invoice.id)" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">Editar</Link>
                                    </td>
                                </tr>
                                <tr v-if="invoices.data.length === 0">
                                    <td colspan="8" class="px-6 py-4 text-sm text-center text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        No hay facturas para mostrar.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <Pagination :links="invoices.links" class="mt-6" v-if="invoices.links && invoices.links.length > 3" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
