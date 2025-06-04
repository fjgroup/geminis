<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'; // Assuming AdminLayout
import { Head, Link, useForm, router } from '@inertiajs/vue3'; // Added useForm and router
import PrimaryButton from '@/Components/Forms/Buttons/PrimaryButton.vue';
import SecondaryButton from '@/Components/Forms/Buttons/SecondaryButton.vue';

const props = defineProps({
    order: Object,
});

const deleteForm = useForm({}); // For delete action

const confirmDeleteOrder = () => {
    if (window.confirm('Are you sure you want to delete this order? This action cannot be undone easily.')) {
        deleteForm.delete(route('admin.orders.destroy', props.order.id), {
            preserveScroll: true,
        });
    }
};

const confirmApproveCancellation = () => {
    if (window.confirm('Are you sure you want to approve this cancellation request? This will cancel the order, mark the invoice as refunded, and issue a credit to the client.')) {
        router.post(route('admin.orders.approveCancellation', props.order.id), {}, {
            preserveScroll: true,
        });
    }
};

const confirmStartExecution = () => {
    if (window.confirm('Are you sure you want to mark this order as "Processing/Pending Provisioning"?')) {
        router.post(route('admin.orders.startExecution', props.order.id), {}, {
            preserveScroll: true,
        });
    }
};

const confirmCompleteExecution = () => {
    if (window.confirm('Are you sure you want to mark this order as "Active / Service Activated"?')) {
        router.post(route('admin.orders.completeExecution', props.order.id), {}, {
            preserveScroll: true,
        });
    }
};

const formatDate = (datetime) => {
    if (!datetime) return '';
    return new Date(datetime).toLocaleString();
};

const formatCurrency = (amount, currencyCode = 'USD') => {
    if (amount === null || amount === undefined) return 'N/A';
    // Assuming currency_code is on the order or invoice, not directly on productPricing for this display
    const displayCurrency = props.order.currency_code || currencyCode;
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: displayCurrency }).format(amount);
};

const triggerConfirmPayment = () => {
  if (confirm('Are you sure you want to confirm payment for this order? This will change its status to "Paid, Pending Execution".')) {
    router.post(route('admin.orders.confirmPayment', { order: props.order.id }), {}, {
      preserveScroll: true,
      // onSuccess and onError are handled by Inertia's default behavior with flash messages.
    });
  }
};
</script>

<template>
    <Head :title="'Order #' + order.order_number" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Order Details: {{ order.order_number }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200 space-y-6">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Order Information</h3>
                                <p><strong>Order Number:</strong> {{ order.order_number }}</p>
                                <p><strong>Order Date:</strong> {{ formatDate(order.order_date) }}</p>
                                <p><strong>Status:</strong> <span class="font-semibold"
                                    :class="{
                                        'text-green-600': order.status === 'active' || order.status === 'completed',
                                        'text-yellow-600': order.status === 'pending_payment' || order.status === 'pending_provisioning',
                                        'text-red-600': order.status === 'cancelled' || order.status === 'fraud',
                                    }">{{ order.status }}</span>
                                </p>
                                <p><strong>Total Amount:</strong> {{ formatCurrency(order.total_amount, order.currency_code) }}</p>
                                <p v-if="order.ip_address"><strong>IP Address:</strong> {{ order.ip_address }}</p>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Client Information</h3>
                                <p v-if="order.client"><strong>Name:</strong> {{ order.client.name }}</p>
                                <p v-if="order.client"><strong>Email:</strong> {{ order.client.email }}</p>
                                <p v-if="!order.client">Client details not available.</p>

                                <div v-if="order.reseller" class="mt-4">
                                    <h4 class="text-md font-medium text-gray-700">Reseller:</h4>
                                    <p><strong>Name:</strong> {{ order.reseller.name }}</p>
                                    <p><strong>Email:</strong> {{ order.reseller.email }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="order.invoice" class="mt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Associated Invoice</h3>
                            <p>
                                <strong>Invoice Number:</strong> 
                                <Link :href="route('admin.invoices.show', order.invoice.id)" class="text-indigo-600 hover:text-indigo-900">
                                    {{ order.invoice.invoice_number }}
                                </Link>
                            </p>
                            <p><strong>Invoice Status:</strong> {{ order.invoice.status }}</p>
                            <p><strong>Invoice Total:</strong> {{ formatCurrency(order.invoice.total_amount, order.currency_code) }}</p>
                        </div>
                        
                        <div v-if="order.notes && order.notes.trim() !== ''" class="mt-6 pt-6 border-t dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Notas del Cliente:</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-md whitespace-pre-wrap">{{ order.notes }}</p>
                        </div>

                        <div class="mt-6 pt-6 border-t dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Order Items</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total Price</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-if="order.items && order.items.length === 0">
                                            <td colspan="5" class="px-4 py-3 text-center text-sm text-gray-500">No items in this order.</td>
                                        </tr>
                                        <template v-for="item in order.items" :key="item.id">
                                            <tr>
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ item.product ? item.product.name : 'N/A' }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-500">{{ item.description }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-500">{{ item.quantity }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-500">{{ formatCurrency(item.unit_price, order.currency_code) }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ formatCurrency(item.total_price, order.currency_code) }}</td>
                                            </tr>
                                            <tr v-if="item.client_service && item.client_service.status === 'pending_configuration'">
                                                <td colspan="5" class="px-4 py-3 bg-yellow-100 border-l-4 border-yellow-500">
                                                    <div class="flex justify-between items-center">
                                                        <p class="text-yellow-700 font-medium">
                                                            Este ítem requiere configuración del servicio.
                                                        </p>
                                                        <Link :href="route('admin.client-services.edit', item.client_service.id)"
                                                              class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                                            Configurar Servicio
                                                        </Link>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Admin Actions Section -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Admin Actions</h3>
                            <div class="flex space-x-3 items-start flex-wrap gap-y-2">
                                <div v-if="order.status === 'pending_payment'">
                                    <PrimaryButton @click="triggerConfirmPayment" class="bg-teal-500 hover:bg-teal-600 focus:ring-teal-400">
                                        Confirm Payment
                                    </PrimaryButton>
                                    <p class="text-xs text-gray-600 mt-1">Set status to "Paid, Pending Execution".</p>
                                </div>

                                <div v-if="order.status === 'paid_pending_execution'">
                                    <PrimaryButton @click="confirmStartExecution">
                                        Start Execution (Set to Pending Provisioning)
                                    </PrimaryButton>
                                </div>

                                <div v-if="order.status === 'pending_provisioning' || order.status === 'paid_pending_execution'">
                                    <!-- Allow complete from paid_pending_execution if admin wants to skip 'start' -->
                                    <SecondaryButton @click="confirmCompleteExecution" class="bg-green-500 hover:bg-green-600 focus:ring-green-400 text-white">
                                        Complete Execution (Set to Active)
                                    </SecondaryButton>
                                </div>
                                
                                <div v-if="order.status === 'cancellation_requested_by_client'" class="space-y-2">
                                    <PrimaryButton @click="confirmApproveCancellation" class="bg-yellow-500 hover:bg-yellow-600 text-white focus:ring-yellow-400">
                                        Approve Cancellation & Issue Credit
                                    </PrimaryButton>
                                    <p class="text-xs text-gray-600 mt-1">Client requested cancellation. Approving will cancel the order, mark invoice as refunded, and create a credit transaction for the client.</p>
                                    <!-- Deny Cancellation button can be added here -->
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-8 pt-6 border-t border-gray-200 flex justify-between items-center">
                            <Link :href="route('admin.orders.index')" class="text-indigo-600 hover:text-indigo-900">
                                &laquo; Back to Orders
                            </Link>
                            <div class="flex space-x-3">
                                <Link :href="route('admin.orders.edit', order.id)" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Edit Order Status/Notes
                                </Link>
                                <PrimaryButton @click="confirmDeleteOrder" :disabled="deleteForm.processing" type="button" class="bg-red-600 hover:bg-red-700 focus:ring-red-500">
                                    Delete Order (Soft)
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
