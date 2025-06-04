<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3'; // Added usePage
import PrimaryButton from '@/Components/Forms/Buttons/PrimaryButton.vue';
import { computed } from 'vue'; // Added computed import

const props = defineProps({
    order: Object,
});

// Use page().props to access flash messages if needed, though AuthenticatedLayout often handles this.
const page = usePage();

const formatDate = (datetime) => {
    if (!datetime) return '';
    return new Date(datetime).toLocaleDateString();
};

const formatCurrency = (amount, currencyCode = 'USD') => {
    if (amount === null || amount === undefined) return 'N/A';
    const displayCurrency = props.order.currency_code || currencyCode;
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: displayCurrency }).format(amount);
};

// Updated function names and logic to match the subtask description
function confirmCancelPrePayment() {
  if (confirm('¿Estás seguro de que deseas cancelar esta orden y su factura no pagada? Esta acción no se puede deshacer.')) {
    router.delete(route('client.orders.cancelPrePayment', { order: props.order.id }), {
      preserveScroll: true,
      // onSuccess and onError can be added if specific toast notifications beyond flash messages are needed
    });
  }
}

function confirmRequestPostPaymentCancellation() {
  if (confirm('¿Estás seguro de que deseas solicitar la cancelación de esta orden? Un administrador revisará tu solicitud. Si se aprueba, cualquier reembolso o crédito aplicable se procesará de acuerdo con nuestras políticas.')) {
    router.post(route('client.orders.requestPostPaymentCancellation', { order: props.order.id }), {}, {
      preserveScroll: true,
    });
  }
}

// Statuses where client can request cancellation
const cancellablePostPaymentStatuses = ['paid_pending_execution', 'active', 'pending_provisioning'];

const user = computed(() => page.props.auth.user); // Make user fully reactive for template

const userBalanceNumeric = computed(() => {
    if (user.value && typeof user.value.balance !== 'undefined') {
        return parseFloat(user.value.balance);
    }
    return 0; // Default to 0 if balance is not available
});

const invoiceTotalNumeric = computed(() => {
    if (props.order.invoice && typeof props.order.invoice.total_amount !== 'undefined') {
        return parseFloat(props.order.invoice.total_amount);
    }
    return 0; // Default to 0 if not available
});

const payWithBalance = (invoiceId) => {
    if (confirm('¿Confirmas que deseas pagar esta factura utilizando tu saldo disponible?')) {
        router.post(route('client.invoices.payment.store', { invoice: invoiceId }), {
            payment_method: 'account_credit' // This matches the existing controller logic for balance payments
        }, {
            preserveScroll: true,
            // Optional: onSuccess/onError for specific feedback
            // onSuccess: () => { console.log('Paid with balance successfully'); },
            // onError: (errors) => { console.error('Error paying with balance', errors); }
        });
    }
};

</script>

<template>
    <Head :title="'Detalles de Orden #' + order.order_number" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalles de la Orden: {{ order.order_number }}
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
                            <div><strong>Número de Orden:</strong> {{ order.order_number }}</div>
                            <div><strong>Fecha de Orden:</strong> {{ formatDate(order.order_date) }}</div>
                            <div><strong>Estado:</strong>
                                <span :class="{
                                    'text-yellow-600 font-semibold': order.status === 'pending_payment',
                                    'text-blue-600 font-semibold': order.status === 'paid_pending_execution' || order.status === 'pending_provisioning',
                                    'text-purple-600 font-semibold': order.status === 'cancellation_requested_by_client',
                                    'text-green-600 font-semibold': order.status === 'active' || order.status === 'completed',
                                    'text-red-600 font-semibold': order.status === 'fraud' || order.status === 'cancelled',
                                }">{{ order.status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}</span>
                            </div>
                            <div><strong>Monto Total:</strong> {{ formatCurrency(order.total_amount, order.currency_code) }}</div>
                        </div>

                        <div v-if="order.invoice" class="mb-6">
                            <h4 class="font-medium text-gray-700">Factura Asociada:</h4>
                            <p>
                                <Link :href="route('client.invoices.show', order.invoice.id)" class="text-indigo-600 hover:text-indigo-900">
                                    Ver Factura #{{ order.invoice.invoice_number }} (Estado: {{ order.invoice.status }})
                                </Link>
                            </p>
                        </div>

                        <div v-if="order.notes" class="mb-6">
                            <h4 class="font-medium text-gray-700">Notas de la Orden:</h4>
                            <p class="whitespace-pre-wrap text-sm text-gray-600">{{ order.notes }}</p>
                        </div>
                        
                        <h4 class="font-medium text-gray-700 mb-2">Ítems de la Orden:</h4>
                        <ul v-if="order.items && order.items.length > 0" class="list-disc pl-5 text-sm text-gray-600">
                            <li v-for="item in order.items" :key="item.id">
                                {{ item.description }} - {{ item.quantity }} x {{ formatCurrency(item.unit_price, order.currency_code) }}
                            </li>
                        </ul>
                        <p v-else class="text-sm text-gray-500">No hay ítems en esta orden.</p>

                        <!-- Action Buttons -->
                        <div class="mt-8 pt-6 border-t border-gray-200 space-y-3">
                            <!-- Edit Order Button -->
                            <div v-if="order.status === 'pending_payment'" class="flex flex-col items-end">
                                <Link :href="route('client.orders.editOrderForm', { order: order.id })">
                                    <PrimaryButton class="bg-blue-500 hover:bg-blue-600 focus:ring-blue-400">
                                        Editar Orden
                                    </PrimaryButton>
                                </Link>
                                <p class="text-xs text-gray-500 mt-1">Cambiar cantidad o ciclo de facturación.</p>
                            </div>

                            <!-- Cancel Pre-Payment Order Button -->
                            <div v-if="order.status === 'pending_payment'" class="flex flex-col items-end">
                                <!-- Using PrimaryButton with red styling as DangerButton might not be defined -->
                                <PrimaryButton @click="confirmCancelPrePayment"
                                               class="bg-red-600 hover:bg-red-700 focus:ring-red-500">
                                    Cancelar Orden
                                </PrimaryButton>
                                <p class="text-xs text-gray-500 mt-1">Esto cancelará la orden y su factura no pagada.</p>
                            </div>

                            <!-- Request Post-Payment Cancellation Button -->
                            <div v-if="cancellablePostPaymentStatuses.includes(order.status)" class="flex flex-col items-end">
                                 <!-- Using PrimaryButton with orange styling as SecondaryButton might not be defined or to make it distinct -->
                                <PrimaryButton @click="confirmRequestPostPaymentCancellation"
                                               class="bg-orange-500 hover:bg-orange-600 focus:ring-orange-400">
                                    Solicitar Cancelación de Orden
                                </PrimaryButton>
                                <p class="text-xs text-gray-500 mt-1">Enviar una solicitud de cancelación. Sujeto a revisión.</p>
                            </div>

                            <!-- Display message if cancellation is already requested -->
                            <div v-if="order.status === 'cancellation_requested_by_client'" class="text-right">
                                <p class="text-sm text-yellow-700 bg-yellow-100 p-3 rounded-md inline-block">
                                    Se ha solicitado la cancelación para esta orden y está pendiente de revisión.
                                </p>
                            </div>

                            <!-- Display message if order is already cancelled or completed -->
                             <div v-if="['cancelled', 'completed', 'fraud'].includes(order.status) && order.status !== 'cancellation_requested_by_client'" class="text-right">
                                <p class="text-sm text-gray-600 bg-gray-100 p-3 rounded-md inline-block">
                                    Esta orden se encuentra en un estado final ({{ order.status.replace(/_/g, ' ') }}) y no puedes realizar más acciones de cancelación.
                                </p>
                            </div>

                            <!-- Pay with Balance Button -->
                            <div v-if="order.invoice && order.invoice.status === 'unpaid' && userBalanceNumeric >= invoiceTotalNumeric" class="mt-4 text-right">
                                <PrimaryButton @click="payWithBalance(order.invoice.id)" class="bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500">
                                    Pagar con Saldo (Disponible: {{ user.value.formatted_balance }})
                                </PrimaryButton>
                                <p class="text-xs text-gray-500 mt-1">Utilizar tu crédito disponible para pagar esta factura.</p>
                            </div>
                             <div v-else-if="order.invoice && order.invoice.status === 'unpaid' && userBalanceNumeric > 0 && userBalanceNumeric < invoiceTotalNumeric" class="mt-4 text-right">
                                <PrimaryButton class="bg-gray-400 cursor-not-allowed" disabled>
                                    Saldo Insuficiente (Disponible: {{ user.value.formatted_balance }})
                                </PrimaryButton>
                                <p class="text-xs text-gray-500 mt-1">Necesitas {{ formatCurrency(invoiceTotalNumeric - userBalanceNumeric, order.currency_code) }} más para pagar con saldo.</p>
                            </div>


                            <!-- Pagar Factura Button (Manual Payment) -->
                            <div v-if="order.invoice && order.invoice.status === 'unpaid'" class="mt-4 text-right">
                                <Link :href="route('client.invoices.manualPayment.create', { invoice: order.invoice.id })" class="mr-2">
                                    <PrimaryButton class="bg-blue-600 hover:bg-blue-700 focus:ring-blue-500">Informar Pago Manual</PrimaryButton>
                                </Link>
                                <!-- PayPal Payment Button -->
                                <Link :href="route('client.paypal.checkout', { invoice: order.invoice.id })"
                                      class="inline-flex items-center px-4 py-2 bg-paypal-blue border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-paypal-darkblue active:bg-paypal-darkerblue focus:outline-none focus:ring-2 focus:ring-paypal-blue focus:ring-offset-2 transition ease-in-out duration-150">
                                    Pagar con PayPal
                                </Link>
                                <p class="text-xs text-gray-500 mt-1">Pagar de forma segura con PayPal o registrar un pago manual.</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <Link :href="route('client.orders.index')" class="text-indigo-600 hover:text-indigo-900 text-sm">
                                &laquo; Volver a Mis Órdenes
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
