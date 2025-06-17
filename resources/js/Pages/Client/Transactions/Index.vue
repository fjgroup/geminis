<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Pagination from '@/Components/UI/Pagination.vue'; // Assuming this component exists

const props = defineProps({
    transactions: {
        type: Object,
        required: true,
    },
});

const formatCurrency = (amount, currencyCode = 'USD') => {
    if (amount === null || amount === undefined) return '';
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: currencyCode }).format(amount);
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('es-ES', { // Changed to Spanish locale
        year: 'numeric', month: 'long', day: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
};

const transactionTypeLabel = (type) => {
    const map = {
        'payment': 'Pago',
        'refund': 'Reembolso',
        'credit_added': 'Crédito Agregado',
        'balance_payment': 'Pago desde Saldo', // Assuming 'balance' gateway maps to this
        // Add other types as needed
    };
    return map[type] || type.charAt(0).toUpperCase() + type.slice(1);
};

const transactionStatusLabel = (status) => {
    const map = {
        'completed': 'Completado',
        'pending': 'Pendiente',
        'failed': 'Fallido',
        // Add other statuses as needed
    };
    return map[status] || status.charAt(0).toUpperCase() + status.slice(1);
};
</script>

<template>
    <Head title="Mis Transacciones" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Historial de Transacciones
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div v-if="transactions.data.length === 0" class="text-center text-gray-500 dark:text-gray-400">
                            Aún no tienes transacciones.
                        </div>
                        <div v-else>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Fecha</th>
                                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Descripción</th>
                                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-300">Monto</th>
                                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Tipo</th>
                                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Estado</th>
                                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Factura</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                        <tr v-for="transaction in transactions.data" :key="transaction.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ formatDate(transaction.transaction_date) }}</td>
                                            <td class="px-6 py-4 whitespace-normal text-sm text-gray-900 dark:text-gray-100">{{ transaction.description }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right"
                                                :class="{
                                                    'text-green-600 dark:text-green-400': transaction.type === 'payment' || transaction.type === 'credit_added',
                                                    'text-red-600 dark:text-red-400': transaction.type === 'refund'
                                                }">
                                                {{ formatCurrency(transaction.amount, transaction.currency_code) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ transactionTypeLabel(transaction.type) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                      :class="{
                                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100': transaction.status === 'completed',
                                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100': transaction.status === 'pending',
                                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100': transaction.status === 'failed',
                                                      }">
                                                    {{ transactionStatusLabel(transaction.status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                <Link v-if="transaction.invoice" :href="route('client.invoices.show', transaction.invoice.id)">
                                                    Factura #{{ transaction.invoice.invoice_number }}
                                                </Link>
                                                <span v-else>-</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <Pagination v-if="transactions.links.length > 3" :links="transactions.links" class="mt-6" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
