<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { computed } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/Forms/Buttons/PrimaryButton.vue";
// import SecondaryButton from "@/Components/Forms/Buttons/SecondaryButton.vue"; // No se usa actualmente

const props = defineProps({
    invoice: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const relevantTransaction = computed(() => {
    return (props.invoice.transactions && props.invoice.transactions.length > 0) ? props.invoice.transactions[0] : null;
});

const showPaymentInfoSection = computed(() => {
    const relevantStatuses = ['paid', 'pending_confirmation', 'active_service', 'pending_activation'];
    return relevantStatuses.includes(props.invoice.status) && relevantTransaction.value;
});

const paymentInfo = computed(() => {
    if (!showPaymentInfoSection.value) return {};

    let dateToShow = null;
    // Usar paid_date si la factura está pagada o el servicio activo/pendiente activación
    if (['paid', 'active_service', 'pending_activation'].includes(props.invoice.status) && props.invoice.paid_date) {
        dateToShow = formatDate(props.invoice.paid_date);
    }
    // Si está pendiente de confirmación, usar la fecha de la transacción
    else if (props.invoice.status === 'pending_confirmation' && relevantTransaction.value) {
        dateToShow = formatDate(relevantTransaction.value.transaction_date);
    }

    return {
        method: relevantTransaction.value?.payment_method?.name ||
            (relevantTransaction.value?.gateway_slug === 'balance' ? 'Saldo de Cuenta' :
                'No especificado'),
        transactionId: relevantTransaction.value?.gateway_transaction_id || 'N/A',
        date: dateToShow,
    };
});

// Helper for currency formatting
const formatCurrency = (amount, currencyCode = "USD") => {
    if (amount === null || amount === undefined || isNaN(parseFloat(amount))) {
        return "N/A";
    }
    const displayCurrency = props.invoice?.currency_code || currencyCode;
    return new Intl.NumberFormat("en-US", { // Considerar usar la localización del usuario si está disponible
        style: "currency",
        currency: displayCurrency,
    }).format(parseFloat(amount));
};

const formatDate = (datetime) => {
    if (!datetime) return 'N/A';
    const date = new Date(datetime); // Handles both 'YYYY-MM-DD' and full ISO strings
    return date.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
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
        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100': status === 'paid' || status === 'active_service',
        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100': status === 'unpaid' || status === 'overdue' || status === 'pending_confirmation' || status === 'pending_activation',
        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100': status === 'cancelled' || status === 'failed_payment',
        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100': status === 'refunded',
        'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100': status === 'collections',
        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': status === 'draft',
    };
};

const getFriendlyItemType = (itemType) => {
    if (!itemType) return '';
    const mappings = {
        'new_service': 'Nuevo Servicio',
        'renewal': 'Renovación',
        'upgrade': 'Mejora',
        'addon': 'Complemento',
        'manual_item': 'Ítem Manual',
    };
    return mappings[itemType] || itemType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getFriendlyServiceStatusText = (status) => {
    if (!status) return 'N/D'; // No Determinado o No Aplica
    const mappings = {
        'pending': 'Pendiente Activación',
        'active': 'Activo',
        'suspended': 'Suspendido',
        'terminated': 'Terminado',
        'cancelled': 'Cancelado',
        'fraud': 'Fraude',
        'pending_configuration': 'Pendiente Configuración',
        'provisioning_failed': 'Falló Aprovisionamiento'
    };
    return mappings[status] || status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getServiceStatusClass = (status) => {
    if (!status) return 'text-gray-500';
    return {
        'text-green-600': status === 'active',
        'text-yellow-600': status === 'pending' || status === 'pending_configuration',
        'text-red-600': status === 'suspended' || status === 'terminated' || status === 'cancelled' || status === 'fraud' || status === 'provisioning_failed',
    };
};


const canPayWithBalance = computed(() => {
    if (!user.value || typeof user.value.balance === 'undefined') return false;
    if (typeof props.invoice.total_amount === 'undefined') return false;
    return (
        props.invoice.status === "unpaid" &&
        parseFloat(user.value.balance) >= parseFloat(props.invoice.total_amount)
    );
});

const hasSomeBalance = computed(() => {
    if (!user.value || typeof user.value.balance === 'undefined') return false;
    if (typeof props.invoice.total_amount === 'undefined') return false;
    const userBalance = parseFloat(user.value.balance);
    const invoiceTotal = parseFloat(props.invoice.total_amount);
    return (
        props.invoice.status === "unpaid" &&
        userBalance > 0 &&
        userBalance < invoiceTotal
    );
});

// La función payInvoiceWithBalance fue refactorizada para usar el endpoint correcto y la lógica de `account_credit`
// que ya estaba en el controlador ClientInvoiceController@payWithBalance.
// La ruta `client.invoices.payment.store` es un nombre genérico, pero el controlador
// la usa para `payWithBalance`. Si se añaden otros gateways, este nombre de ruta
// podría necesitar ser más específico o el controlador necesitaría más lógica.
// Por ahora, asumimos que `payment.store` es manejado por `payWithBalance` si el método es `account_credit`.
// En una refactorización posterior, `client.invoices.payment.store` podría ser reemplazado por
// `client.invoices.payWithBalance` directamente si solo hay esa opción o si se decide separar rutas.
const payInvoiceUsingBalance = (invoiceId) => { // Renombrado para claridad
    if (confirm('¿Confirmas que deseas pagar esta factura utilizando tu saldo disponible?')) {
        router.post(route('client.invoices.payWithBalance', { invoice: invoiceId }), {}, { // Ruta actualizada
            preserveScroll: true,
            // onSuccess: () => { ... }, // Podríamos refrescar datos o mostrar notificaciones más específicas
        });
    }
};

const cancelPaymentReportHandler = (invoiceId) => {
    if (confirm('¿Estás seguro de que quieres anular el reporte de pago para esta factura? La factura volverá al estado "No Pagada".')) {
        router.post(route('client.invoices.cancelPaymentReport', { invoice: invoiceId }), {}, {
            preserveScroll: true,
            // onSuccess y onError serán manejados por los mensajes flash globales
            // que ya configuramos para que se muestren en esta página.
        });
    }
};

const isUnpaidInvoice = computed(() => {
    return props.invoice.status === 'unpaid';
});

const canAttemptCancellation = computed(() => {
    if (props.invoice.status !== 'unpaid') {
        return false;
    }
    if (props.invoice.items && props.invoice.items.length > 0) {
        const hasNewServiceItem = props.invoice.items.some(item => item.item_type === 'new_service' || item.item_type === 'web-hosting');
        const hasRenewalItem = props.invoice.items.some(item => item.item_type === 'renewal');
        // Allow cancellation if there's at least one new service item and no renewal items.
        return hasNewServiceItem && !hasRenewalItem;
    }
    return false; // No items, can't determine
});

const requestCancelNewServiceOrder = (invoiceId) => {
    if (confirm('¿Estás seguro de que deseas cancelar este pedido de nuevo servicio? Esta acción no se puede deshacer y los servicios asociados que estén pendientes también se cancelarán.')) {
        router.post(route('client.invoices.cancelNewOrder', { invoice: invoiceId }), {}, {
            preserveScroll: true,
            // onSuccess and onError will be handled by global flash messages
        });
    }
};

const isUnpaidRenewalOfRelevantService = computed(() => {
    if (!props.invoice || props.invoice.status !== 'unpaid') {
        return false;
    }

    if (!props.invoice.items || props.invoice.items.length === 0) {
        return false;
    }

    return props.invoice.items.some(item => {
        return item.item_type === 'renewal' &&
            item.client_service_id &&
            item.clientService &&
            (item.clientService.status === 'Active' || item.clientService.status === 'Suspended'); // Changed to capitalized
    });
});

const confirmServiceCancellationFromInvoice = (event, serviceId, serviceName) => {
    event.preventDefault();
    const message = `¿Estás seguro de que deseas solicitar la cancelación para el servicio "${serviceName || serviceId}"? Esto NO cancela la factura inmediatamente, pero inicia el proceso para dar de baja el servicio.`;
    if (confirm(message)) {
        router.post(route('client.services.requestCancellation', { service: serviceId }), { source_invoice_id: props.invoice.id }, {
            preserveScroll: true,
            // onSuccess: (page) => { /* Optional: specific feedback */ }
        });
    }
};
</script>

<template>

    <Head :title="`Factura #${invoice.invoice_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Detalles de Factura
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-900 sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="mb-4 text-lg font-semibold">
                            Factura #{{ invoice.invoice_number }}
                        </h3>

                        <!-- Información del Pago -->
                        <div v-if="showPaymentInfoSection"
                            class="p-4 mt-3 mb-4 space-y-1 text-sm border border-gray-200 rounded-md dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                            <h4 class="mb-2 font-semibold text-gray-800 text-md dark:text-gray-100">Información del Pago
                                Registrado:</h4>
                            <div v-if="paymentInfo.method" class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Método de Pago:</span>
                                <strong class="text-gray-900 dark:text-gray-100">{{ paymentInfo.method }}</strong>
                            </div>
                            <div v-if="paymentInfo.transactionId && paymentInfo.transactionId !== 'N/A'"
                                class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Referencia:</span>
                                <strong class="text-gray-900 dark:text-gray-100">{{ paymentInfo.transactionId
                                    }}</strong>
                            </div>
                            <div v-if="paymentInfo.date" class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">{{ (invoice.status ===
                                    'pending_confirmation') ?
                                    'Fecha de Registro:' : 'Fecha de Pago:' }}</span>
                                <strong class="text-gray-900 dark:text-gray-100">{{ paymentInfo.date }}</strong>
                            </div>
                            <div v-if="invoice.status === 'pending_confirmation'" class="pt-2">
                                <p class="text-sm text-yellow-700 dark:text-yellow-400">Este pago está pendiente de
                                    confirmación
                                    por un administrador.</p>
                                <div class="mt-3">
                                    <button @click="cancelPaymentReportHandler(invoice.id)" type="button"
                                        class="px-4 py-2 text-xs font-semibold text-red-700 bg-red-100 border border-red-300 rounded-md hover:bg-red-200 hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-800/30 dark:border-red-600 dark:text-red-400 dark:hover:bg-red-700/50 dark:hover:text-red-300">
                                        Anular Pago Reportado
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Fin Información del Pago -->

                        <!-- Sección para Cancelar Pedido de Nuevo Servicio (si aplica) -->
                        <div v-if="canAttemptCancellation.value && !isUnpaidRenewalOfRelevantService.value"
                            class="p-4 mt-3 mb-4 text-sm border border-gray-200 rounded-md dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                            <h4 class="mb-1 font-semibold text-gray-800 text-md dark:text-gray-100">Acciones Adicionales
                            </h4>
                            <p class="mb-3 text-xs text-gray-600 dark:text-gray-400">
                                Si ya no deseas este pedido de nuevo servicio, puedes cancelarlo aquí.
                            </p>
                            <PrimaryButton @click="requestCancelNewServiceOrder(invoice.id)"
                                class="px-4 py-2 text-xs font-semibold text-white bg-orange-500 hover:bg-orange-600 focus:ring-orange-400">
                                Cancelar Factura
                            </PrimaryButton>
                        </div>




                        <!-- Display User Balance -->
                        <div v-if="user && user.formatted_balance"
                            class="p-4 mb-6 text-lg text-center text-blue-700 bg-blue-100 rounded-md dark:bg-blue-900 dark:text-blue-200">
                            Tu crédito disponible:
                            <span class="font-semibold">{{ user.formatted_balance }}</span>
                        </div>

                        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2">
                            <div>
                                <strong>Fecha de Solicitud:</strong>
                                {{ formatDate(invoice.requested_date) }}
                            </div>
                            <div>
                                <strong>Fecha de Emisión:</strong>
                                {{ formatDate(invoice.issue_date) }}
                            </div>
                            <div>
                                <strong>Fecha de Vencimiento:</strong>
                                {{ formatDate(invoice.due_date) }}
                            </div>
                            <div>
                                <strong>Estado:</strong>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                    :class="getInvoiceStatusClass(invoice.status)">{{
                                        getFriendlyInvoiceStatusText(invoice.status) }}</span>
                            </div>
                            <div>
                                <strong>Total:</strong>
                                {{ formatCurrency(invoice.total_amount, invoice.currency_code) }}
                            </div>
                            <div v-if="invoice.client">
                                <strong>Cliente:</strong> {{ invoice.client.name }} ({{ invoice.client.email }})
                            </div>
                            <div v-if="invoice.paid_date">
                                <strong>Fecha de Pago:</strong>
                                {{ formatDate(invoice.paid_date) }}
                            </div>
                        </div>

                        <h4 class="mb-3 font-semibold text-md">Ítems de la Factura:</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full mb-6 divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th scope="col"
                                            class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                            Descripción</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                            Tarifa Config.</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-400">
                                            Cantidad</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-400">
                                            Precio Unitario</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-400">
                                            Total</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                            Estado Servicio</th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white divide-y divide-gray-200 dark:bg-gray-700/50 dark:divide-gray-600">
                                    <template v-if="invoice.items && invoice.items.length > 0">
                                        <tr v-for="item in invoice.items" :key="item.id"
                                            class="hover:bg-gray-50 dark:hover:bg-gray-600/50">
                                            <td class="px-4 py-3 text-sm whitespace-normal">
                                                <div class="font-medium text-gray-900 dark:text-white">{{ item.product ?
                                                    item.product.name : item.description }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    <span
                                                        v-if="item.product_pricing && item.product_pricing.billing_cycle">{{
                                                            item.product_pricing.billing_cycle.name }}</span>
                                                    <span v-if="item.domain_name"> - {{ item.domain_name }}</span>
                                                    <span v-if="item.item_type && item.item_type !== 'manual_item'"> ({{
                                                        getFriendlyItemType(item.item_type) }})</span>
                                                </div>
                                                <div v-if="item.description && (!item.product || item.description !== item.product.name)"
                                                    class="mt-1 text-xs italic text-gray-600 dark:text-gray-300">
                                                    Nota: {{ item.description }}
                                                </div>
                                            </td>
                                            <td
                                                class="px-4 py-3 text-sm text-right text-gray-700 whitespace-nowrap dark:text-gray-300">
                                                {{ formatCurrency(item.setup_fee, invoice.currency_code) }}</td>
                                            <td
                                                class="px-4 py-3 text-sm text-center text-gray-700 whitespace-nowrap dark:text-gray-300">
                                                {{ item.quantity }}</td>
                                            <td
                                                class="px-4 py-3 text-sm text-right text-gray-700 whitespace-nowrap dark:text-gray-300">
                                                {{ formatCurrency(item.unit_price, invoice.currency_code) }}</td>
                                            <td
                                                class="px-4 py-3 text-sm font-semibold text-right text-gray-900 whitespace-nowrap dark:text-white">
                                                {{ formatCurrency(item.total_price, invoice.currency_code) }}</td>
                                            <td class="px-4 py-3 text-sm whitespace-nowrap">
                                                <span v-if="item.client_service"
                                                    :class="getServiceStatusClass(item.client_service.status)">
                                                    {{ getFriendlyServiceStatusText(item.client_service.status) }}
                                                </span>
                                                <span
                                                    v-else-if="item.product && item.product.product_type && item.product.product_type.creates_service_instance"
                                                    class="italic text-gray-400">
                                                    (No Provisionado)
                                                </span>
                                                <span v-else class="text-gray-400">-</span>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr v-else>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            No hay
                                            ítems en esta factura.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Totales de la Factura -->
                        <div class="mt-6 bg-gray-50 dark:bg-gray-800 rounded-lg p-6">
                            <div class="flex justify-end">
                                <div class="w-full max-w-sm space-y-3">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                                        <span class="font-medium">{{ formatCurrency(invoice.subtotal,
                                            invoice.currency_code)
                                            }}</span>
                                    </div>

                                    <div v-if="invoice.tax1_amount && invoice.tax1_amount > 0"
                                        class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">{{ invoice.tax1_description ||
                                            'Impuesto 1' }}:</span>
                                        <span class="font-medium">{{ formatCurrency(invoice.tax1_amount,
                                            invoice.currency_code)
                                        }}</span>
                                    </div>

                                    <div v-if="invoice.tax2_amount && invoice.tax2_amount > 0"
                                        class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">{{ invoice.tax2_description ||
                                            'Impuesto 2' }}:</span>
                                        <span class="font-medium">{{ formatCurrency(invoice.tax2_amount,
                                            invoice.currency_code)
                                            }}</span>
                                    </div>

                                    <div class="border-t border-gray-300 dark:border-gray-600 pt-3">
                                        <div class="flex justify-between">
                                            <span class="text-lg font-bold text-gray-900 dark:text-white">Total:</span>
                                            <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                                {{ formatCurrency(invoice.total_amount, invoice.currency_code) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Details if Paid -->
                        <div v-if="invoice.status === 'paid' && invoice.transactions && invoice.transactions.length > 0"
                            class="p-4 mt-6 border border-gray-200 rounded dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                            <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-gray-100">Detalles del Pago
                                Realizado
                            </h3>
                            <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                <p><strong>Fecha de Transacción (Cliente):</strong> {{
                                    formatDate(invoice.transactions[0].transaction_date) }}</p>
                                <p><strong>Referencia del Cliente:</strong> {{
                                    invoice.transactions[0].gateway_transaction_id ||
                                    'N/A' }}</p>

                                <div
                                    v-if="invoice.transactions[0].payment_method && invoice.transactions[0].payment_method.formatted_details">
                                    <p><strong>Método de Pago:</strong> {{ invoice.transactions[0].payment_method.name
                                        || 'N/A'
                                    }}</p>

                                    <template
                                        v-if="typeof invoice.transactions[0].payment_method.formatted_details === 'object' && invoice.transactions[0].payment_method.formatted_details !== null">
                                        <template
                                            v-for="(value, key) in (Array.isArray(invoice.transactions[0].payment_method.formatted_details) ? invoice.transactions[0].payment_method.formatted_details[0] : invoice.transactions[0].payment_method.formatted_details)"
                                            :key="key">
                                            <p
                                                v-if="String(key).toLowerCase() !== 'name' && String(key).toLowerCase() !== 'id' && !/^\d+$/.test(String(key))">
                                                <strong>{{String(key).replace(/_/g, ' ').replace(/\b\w/g, l =>
                                                    l.toUpperCase())}}:</strong> {{ value }}
                                            </p>
                                        </template>
                                    </template>
                                    <template
                                        v-else-if="typeof invoice.transactions[0].payment_method.formatted_details === 'string'">
                                        <p>{{ invoice.transactions[0].payment_method.formatted_details }}</p>
                                    </template>
                                    <template v-else>
                                        <p>No hay detalles específicos del método de pago disponibles o no están en el
                                            formato esperado.</p>
                                    </template>
                                </div>
                                <div v-else-if="invoice.transactions[0].payment_method">
                                    <p><strong>Método de Pago:</strong> {{ invoice.transactions[0].payment_method.name
                                        || 'N/A'
                                        }}</p>
                                    <p>No se proporcionaron detalles adicionales para este método de pago.</p>
                                </div>
                                <div v-else>
                                    <p>Los detalles del método de pago no están disponibles.</p>
                                </div>
                            </div>
                        </div>


                        <!-- Payment Options -->
                        <div class="mt-6 space-y-4 text-center">
                            <div v-if="invoice.status === 'unpaid' && !isUnpaidRenewalOfRelevantService.value">
                                <!-- 1. Introductory Text -->
                                <p class="mb-3 text-sm text-gray-700 dark:text-gray-300">
                                    Selecciona un método de pago o informa un pago manual:
                                </p>
                                <hr class="my-4 dark:border-gray-600" v-if="user && user.balance > 0">

                                <!-- 2. Flex Container for ALL three payment action items -->
                                <div class="flex flex-wrap items-center justify-center gap-3 py-2 md:gap-4">

                                    <!-- 2a. Pagar con Saldo Button -->
                                    <div v-if="user && user.balance > 0">
                                        <PrimaryButton @click="payInvoiceUsingBalance(invoice.id)"
                                            :disabled="!canPayWithBalance"
                                            :class="canPayWithBalance ? 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500' : 'bg-gray-400 cursor-not-allowed'"
                                            class="px-6 py-3 text-sm font-medium text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 disabled:opacity-50">
                                            <span v-if="canPayWithBalance">
                                                Pagar con Saldo
                                                <template v-if="user && user.formatted_balance">(Disponible: {{
                                                    user.formatted_balance }})</template>
                                            </span>
                                            <span v-else>
                                                Saldo Insuficiente
                                                <template v-if="user && user.formatted_balance">(Disponible: {{
                                                    user.formatted_balance }})</template>
                                                <template v-else>(Saldo no disponible)</template>
                                            </span>
                                        </PrimaryButton>
                                    </div>

                                    <!-- 2b. Informar Pago Manual Link/Button -->
                                    <Link
                                        :href="route('client.invoices.manualPayment.create', { invoice: invoice.id })">
                                    <PrimaryButton
                                        class="px-6 py-3 text-sm font-medium text-white bg-blue-600 rounded-md shadow-sm hover:bg-blue-700 focus:ring-blue-500 focus:outline-none focus:ring-2 focus:ring-opacity-50">
                                        Informar Pago Manual
                                    </PrimaryButton>
                                    </Link>

                                    <!-- 2c. Pagar con PayPal Link/Button -->
                                    <Link :href="route('client.paypal.payment.create', { invoice: invoice.id })">
                                    <button type="button"
                                        class="inline-flex items-center px-6 py-3 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Pagar con PayPal
                                    </button>
                                    </Link>
                                </div>

                                <!-- 3. Insufficient Balance Text (if applicable, AFTER the flex container) -->
                                <p v-if="hasSomeBalance && !canPayWithBalance"
                                    class="mt-3 text-sm text-yellow-600 dark:text-yellow-400">
                                    Tu saldo actual no es suficiente para cubrir el monto total de esta factura.
                                    Necesitas {{ formatCurrency(parseFloat(props.invoice.total_amount) -
                                        parseFloat(user.value.balance), props.invoice.currency_code) }} más.
                                </p>
                            </div>

                            <div v-if="!['unpaid', 'pending_confirmation'].includes(invoice.status) && !isUnpaidRenewalOfRelevantService.value"
                                class="text-sm text-gray-600 dark:text-gray-400">
                                Esta factura no está actualmente pendiente de pago.
                            </div>
                        </div>

                        <!-- New Section for Renewal Invoice Actions -->
                        <div v-if="isUnpaidRenewalOfRelevantService.value"
                            class="p-6 mt-6 border border-yellow-300 rounded-md bg-yellow-50 dark:bg-gray-800 dark:border-yellow-700">
                            <h4 class="mb-3 text-lg font-semibold text-yellow-800 dark:text-yellow-200">Acción Requerida
                                para
                                Renovación</h4>
                            <p class="mb-4 text-sm text-yellow-700 dark:text-yellow-300">
                                Esta factura es para la renovación de uno de sus servicios existentes.
                                Si ya no desea continuar con este servicio, por favor, solicite su cancelación
                                directamente.
                                Ignorar esta factura no cancelará el servicio automáticamente y podría llevar a
                                suspensiones.
                            </p>

                            <div v-for="item in invoice.items" :key="item.id" class="mt-2">
                                <template
                                    v-if="item.item_type === 'renewal' && item.client_service_id && item.clientService && (item.clientService.status === 'Active' || item.clientService.status === 'Suspended')">
                                    {/* Changed to capitalized */}
                                    <Link
                                        :href="route('client.services.requestCancellation', { service: item.client_service_id })"
                                        method="post" as="button"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                        @click.prevent="confirmServiceCancellationFromInvoice($event, item.client_service_id, item.clientService.domain_name || item.clientService.product?.name)">
                                    Solicitar Cancelación del Servicio: {{ item.clientService.domain_name ||
                                        item.clientService.product?.name || 'Servicio ID ' + item.client_service_id }}
                                    </Link>
                                </template>
                            </div>
                        </div>

                        <!-- Botón para volver al listado de facturas -->
                        <div class="mt-8 text-center">
                            <Link :href="route('client.invoices.index')"
                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-200 hover:underline">
                            &laquo; Volver a Mis Facturas</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
