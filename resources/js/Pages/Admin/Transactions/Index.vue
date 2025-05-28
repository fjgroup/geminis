<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'; // Assuming AdminLayout exists
import { Head, Link } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue'; // Assuming Pagination component exists

const props = defineProps({
    transactions: Object, // Paginated transactions object from controller
    filters: Object,
});

const formatDate = (datetime) => {
    if (!datetime) return '';
    return new Date(datetime).toLocaleString();
};

const formatCurrency = (amount, currencyCode = 'USD') => {
    if (amount === null || amount === undefined) return '';
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: currencyCode }).format(amount);
};
</script>

<template>
    <Head title="Transactions" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Transactions</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Add filtering UI here later if needed based on props.filters -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gateway</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="transactions.data.length === 0">
                                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No transactions found.</td>
                                    </tr>
                                    <tr v-for="transaction in transactions.data" :key="transaction.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(transaction.transaction_date) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <Link v-if="transaction.invoice" :href="route('admin.invoices.show', transaction.invoice.id)" class="text-indigo-600 hover:text-indigo-900">
                                                {{ transaction.invoice.invoice_number }}
                                            </Link>
                                            <span v-else>-</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span v-if="transaction.client">{{ transaction.client.name }} ({{ transaction.client.email }})</span>
                                            <span v-else>-</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transaction.type }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(transaction.amount, transaction.currency_code) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transaction.gateway_slug }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span :class="{
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                                'bg-green-100 text-green-800': transaction.status === 'completed',
                                                'bg-yellow-100 text-yellow-800': transaction.status === 'pending',
                                                'bg-red-100 text-red-800': transaction.status === 'failed' || transaction.status === 'reversed',
                                            }">
                                                {{ transaction.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transaction.description }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                         <Pagination class="mt-6" :links="transactions.links" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
