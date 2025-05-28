<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3'; // Added router
import Pagination from '@/Components/Pagination.vue';
// import PrimaryButton from '@/Components/PrimaryButton.vue'; // Not using PrimaryButton for simple link-style button here

const props = defineProps({
    orders: Object, // Esto recibirá los datos paginados de las órdenes del cliente
});

const confirmCancelOrder = (orderId) => {
    if (window.confirm('Are you sure you want to cancel this order request?')) {
        router.delete(route('client.orders.cancelPrePayment', orderId), {
            preserveScroll: true, 
            // onSuccess and onError are handled by Inertia's global event listeners 
            // or can be handled here if specific logic is needed.
            // The controller will redirect with a success/error flash message.
        });
    }
};

const confirmRequestPostPaymentCancellation = (orderId) => {
    if (window.confirm('Are you sure you want to request cancellation for this order? If approved by an administrator, the paid amount will be credited to your account balance for future use.')) {
        router.post(route('client.orders.requestPostPaymentCancellation', orderId), {}, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Mis Órdenes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Mis Órdenes
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Número de Orden
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Fecha
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Total
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Estado
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="orders.data.length === 0">
                                    <td colspan="5" class="px-6 py-4 text-sm text-center text-gray-500 whitespace-nowrap">
                                        No hay órdenes disponibles.
                                    </td>
                                </tr>
                                <tr v-for="order in orders.data" :key="order.id">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                                        {{ order.order_number }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        {{ new Date(order.order_date).toLocaleDateString() }} <!-- Use order_date instead of created_at for consistency -->
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        {{ order.total_amount }} {{ order.currency_code }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                         <span :class="{
                                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                            'bg-yellow-100 text-yellow-800': order.status === 'pending_payment',
                                            'bg-blue-100 text-blue-800': order.status === 'pending_provisioning' || order.status === 'paid_pending_execution',
                                            'bg-green-100 text-green-800': order.status === 'active' || order.status === 'completed',
                                            'bg-red-100 text-red-800': order.status === 'fraud' || order.status === 'cancelled',
                                        }">
                                            {{ order.status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium whitespace-nowrap space-x-2">
                                        <Link v-if="order.invoice_id" :href="route('client.invoices.show', order.invoice_id)" class="text-indigo-600 hover:text-indigo-900">
                                            Ver Factura
                                        </Link>
                                        <span v-else class="text-gray-400">Sin Factura</span>
                                        
                                        <button 
                                            v-if="order.status === 'pending_payment'" 
                                            @click="confirmCancelOrder(order.id)" 
                                            class="text-red-600 hover:text-red-800 focus:outline-none text-sm">
                                            Cancelar (Pre-Pago)
                                        </button>
                                        <button
                                            v-if="order.status === 'paid_pending_execution'"
                                            @click="confirmRequestPostPaymentCancellation(order.id)"
                                            class="text-orange-600 hover:text-orange-800 focus:outline-none text-sm">
                                            Solicitar Cancelación (Post-Pago)
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <Pagination :links="orders.links" class="mt-6" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
