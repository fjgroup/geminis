<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3'; // Or useForm
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    order: Object, // Passed from a new controller method for showing client order details
});

const formatDate = (datetime) => {
    if (!datetime) return '';
    return new Date(datetime).toLocaleDateString();
};

const formatCurrency = (amount, currencyCode = 'USD') => {
    if (amount === null || amount === undefined) return 'N/A';
    const displayCurrency = props.order.currency_code || currencyCode;
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: displayCurrency }).format(amount);
};

const confirmRequestPostPaymentCancellation = () => {
    if (window.confirm('Are you sure you want to request cancellation for this order? If approved by an administrator, the paid amount will be credited to your account balance for future use.')) {
        router.post(route('client.orders.requestPostPaymentCancellation', props.order.id), {}, {
            preserveScroll: true,
            // onSuccess: page => { /* Controller redirects, page will update with flash message */ }
        });
    }
};

const confirmCancelPrePaymentOrder = (orderId) => {
    if (window.confirm('Are you sure you want to cancel this order request? This will also cancel the associated unpaid invoice.')) {
        router.delete(route('client.orders.cancelPrePayment', orderId), {
            preserveScroll: true,
        });
    }
};

</script>

<template>
    <Head :title="'Order Details #' + order.order_number" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Order Details: {{ order.order_number }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Flash Messages -->
                        <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                            {{ $page.props.flash.success }}
                        </div>
                        <div v-if="$page.props.flash && $page.props.flash.error" class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                            {{ $page.props.flash.error }}
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div><strong>Order Number:</strong> {{ order.order_number }}</div>
                            <div><strong>Order Date:</strong> {{ formatDate(order.order_date) }}</div>
                            <div><strong>Status:</strong> 
                                <span :class="{
                                    'text-yellow-600 font-semibold': order.status === 'pending_payment',
                                    'text-blue-600 font-semibold': order.status === 'paid_pending_execution' || order.status === 'pending_provisioning',
                                    'text-purple-600 font-semibold': order.status === 'cancellation_requested_by_client',
                                    'text-green-600 font-semibold': order.status === 'active' || order.status === 'completed',
                                    'text-red-600 font-semibold': order.status === 'fraud' || order.status === 'cancelled',
                                }">{{ order.status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}</span>
                            </div>
                            <div><strong>Total Amount:</strong> {{ formatCurrency(order.total_amount, order.currency_code) }}</div>
                        </div>

                        <div v-if="order.invoice" class="mb-6">
                            <h4 class="font-medium text-gray-700">Associated Invoice:</h4>
                            <p>
                                <Link :href="route('client.invoices.show', order.invoice.id)" class="text-indigo-600 hover:text-indigo-900">
                                    View Invoice #{{ order.invoice.invoice_number }} (Status: {{ order.invoice.status }})
                                </Link>
                            </p>
                        </div>

                        <div v-if="order.notes" class="mb-6">
                            <h4 class="font-medium text-gray-700">Order Notes:</h4>
                            <p class="whitespace-pre-wrap text-sm text-gray-600">{{ order.notes }}</p>
                        </div>
                        
                        <h4 class="font-medium text-gray-700 mb-2">Order Items:</h4>
                        <ul v-if="order.items && order.items.length > 0" class="list-disc pl-5 text-sm text-gray-600">
                            <li v-for="item in order.items" :key="item.id">
                                {{ item.description }} - {{ item.quantity }} x {{ formatCurrency(item.unit_price, order.currency_code) }}
                            </li>
                        </ul>
                        <p v-else class="text-sm text-gray-500">No items in this order.</p>

                        <!-- Action Buttons -->
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <div v-if="order.status === 'pending_payment'" class="flex justify-end">
                                <PrimaryButton @click="confirmCancelPrePaymentOrder(order.id)" class="bg-red-600 hover:bg-red-700 focus:ring-red-500">
                                    Cancel Order
                                </PrimaryButton>
                            </div>
                            <div v-if="order.status === 'paid_pending_execution'" class="flex justify-end">
                                <PrimaryButton @click="confirmRequestPostPaymentCancellation" class="bg-orange-500 hover:bg-orange-600 focus:ring-orange-400">
                                    Request Order Cancellation
                                </PrimaryButton>
                                <p class="text-sm text-gray-600 mt-2 ml-4 self-center">
                                    If your request is approved, the amount paid for this order will be credited to your account.
                                </p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <Link :href="route('client.orders.index')" class="text-indigo-600 hover:text-indigo-900 text-sm">
                                &laquo; Back to My Orders
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
