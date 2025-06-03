<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3'; // Added router and usePage
import Pagination from '@/Components/UI/Pagination.vue';
import PrimaryButton from '@/Components/Forms/Buttons/PrimaryButton.vue';
import DangerButton from '@/Components/Forms/Buttons/DangerButton.vue';
import Alert from '@/Components/UI/Alert.vue';
import { computed } from 'vue'; // Added

const props = defineProps({
    transactions: Object,
    filters: Object,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash.success);
const flashError = computed(() => page.props.flash.error);


const formatDate = (datetime) => {
    if (!datetime) return '';
    return new Date(datetime).toLocaleString();
};

const formatCurrency = (amount, currencyCode = 'USD') => {
    if (amount === null || amount === undefined) return '';
    // Ensure currencyCode is a valid string, default if not provided by transaction
    const displayCurrency = currencyCode || 'USD';
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: displayCurrency }).format(amount);
};

const confirmTransaction = (id) => {
    if (confirm('Are you sure you want to confirm this transaction?')) {
        router.post(route('admin.transactions.confirm', { transaction: id }), {}, {
            preserveScroll: true,
            // onSuccess: () => { /* Handled by flash message */ }
        });
    }
};

const rejectTransaction = (id) => {
    if (confirm('Are you sure you want to reject this transaction?')) {
        router.post(route('admin.transactions.reject', { transaction: id }), {}, {
            preserveScroll: true,
            // onSuccess: () => { /* Handled by flash message */ }
        });
    }
};
</script>

<template>
    <Head title="Transactions" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Transactions</h2>
        </template>

        <div class="py-12">
            <div class="max-w-full mx-auto sm:px-6 lg:px-8"> {/* Changed max-w-7xl to max-w-full for wider table */}
                <Alert :message="flashSuccess" type="success" v-if="flashSuccess" class="mb-4" />
                <Alert :message="flashError" type="danger" v-if="flashError" class="mb-4" />

                <div class="mb-4">
                    <nav class="flex space-x-4" aria-label="Tabs">
                        <Link :href="route('admin.transactions.index')"
                              :class="{
                                  'bg-indigo-100 text-indigo-700': !filters.type && !filters.status && !filters.gateway_slug,
                                  'text-gray-500 hover:text-gray-700': filters.type || filters.status || filters.gateway_slug
                              }"
                              class="px-3 py-2 font-medium text-sm rounded-md">
                            Todas las Transacciones
                        </Link>
                        <Link :href="route('admin.transactions.index', {
                                  type: 'credit_added',
                                  status: 'pending',
                                  gateway_slug: 'manual_fund_addition'
                              })"
                              :class="{
                                  'bg-indigo-100 text-indigo-700': filters.type === 'credit_added' && filters.status === 'pending' && filters.gateway_slug === 'manual_fund_addition',
                                  'text-gray-500 hover:text-gray-700': !(filters.type === 'credit_added' && filters.status === 'pending' && filters.gateway_slug === 'manual_fund_addition')
                              }"
                              class="px-3 py-2 font-medium text-sm rounded-md">
                            Solicitudes de Fondos Pendientes
                        </Link>
                        <!-- Add other filters as needed, e.g., Pending Order Payments -->
                        <Link :href="route('admin.transactions.index', {
                                  type: 'order_payment', // Assuming 'order_payment' is the type for invoice payments
                                  status: 'pending'
                              })"
                              :class="{
                                  'bg-indigo-100 text-indigo-700': filters.type === 'order_payment' && filters.status === 'pending' && !filters.gateway_slug, // Ensure gateway_slug is not set for this tab
                                  'text-gray-500 hover:text-gray-700': !(filters.type === 'order_payment' && filters.status === 'pending' && !filters.gateway_slug)
                              }"
                              class="px-3 py-2 font-medium text-sm rounded-md">
                            Pagos de Órdenes Pendientes
                        </Link>
                    </nav>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client's Ref #</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gateway</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="transactions.data.length === 0">
                                        <td colspan="11" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No transactions found.</td> {/* Adjusted colspan */}
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
                                            <span v-if="transaction.client">{{ transaction.client.name }}</span>
                                            <span v-else>-</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transaction.type }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(transaction.amount, transaction.currency_code) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transaction.payment_method?.name || 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transaction.gateway_transaction_id || '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transaction.gateway_slug }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span :class="{
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                                'bg-green-100 text-green-800': transaction.status === 'completed',
                                                'bg-yellow-100 text-yellow-800': transaction.status === 'pending',
                                                'bg-red-100 text-red-800': transaction.status === 'failed' || transaction.status === 'reversed' || transaction.status === 'cancelled',
                                                'bg-blue-100 text-blue-800': transaction.status === 'refunded',
                                            }">
                                                {{ transaction.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 max-w-xs truncate" :title="transaction.description">{{ transaction.description }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div v-if="transaction.status === 'pending'" class="flex space-x-2">
                                                <PrimaryButton @click="confirmTransaction(transaction.id)" class="text-xs">Confirm</PrimaryButton>
                                                <DangerButton @click="rejectTransaction(transaction.id)" class="text-xs">Reject</DangerButton>
                                            </div>
                                            <span v-else class="text-gray-400">-</span>
                                        </td>
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
