<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'; // Added router, usePage
import PrimaryButton from '@/Components/Forms/Buttons/PrimaryButton.vue';
import SecondaryButton from '@/Components/Forms/Buttons/SecondaryButton.vue'; // Para el botón de Activar
import InputLabel from '@/Components/Forms/InputLabel.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import InputError from '@/Components/Forms/InputError.vue';
import SelectInput from '@/Components/Forms/SelectInput.vue';
import { computed } from 'vue';
import { format } from 'date-fns'; // Para formateo de fechas


const props = defineProps({
    invoice: {
        type: Object,
        required: true,
    },
    manualPaymentFormData: Object, // { amount, currency_code, transaction_date } - NUEVO
    showManualPaymentForm: Boolean, // NUEVO
    // flash: Object, // Se accede vía usePage().props.flash
});

const page = usePage();

// Comentario en español: Formulario para la NUEVA funcionalidad de registrar transacción manual específica
// Este es el formulario que se envía a la ruta 'admin.invoices.storeManualTransaction'
const manualTransactionForm = useForm({
    transaction_date: props.manualPaymentFormData?.transaction_date || new Date().toISOString().slice(0,10),
    amount: props.manualPaymentFormData?.amount || 0.00,
    currency_code: props.manualPaymentFormData?.currency_code || props.invoice?.currency_code || 'USD',
    payment_gateway_name: props.manualPaymentFormData?.payment_gateway_name || '', // Actualizado para usar props
    gateway_transaction_id: props.manualPaymentFormData?.gateway_transaction_id || '', // Actualizado para usar props
    description: `Pago manual para Factura #${props.invoice?.invoice_number}`, // Mantenido como estaba, ya que el backend no envía 'description' en manualPaymentFormData
});

// Comentario en español: Envía el formulario de transacción manual específica
const submitStoreManualTransaction = () => {
    if (props.invoice && props.invoice.id) {
        manualTransactionForm.post(route('admin.invoices.storeManualTransaction', props.invoice.id), {
            preserveScroll: true,
            onSuccess: () => {
                // manualTransactionForm.reset(); // Opcional, los mensajes flash indicarán el éxito
                // La página debería recargarse o actualizarse con los nuevos datos de la factura si el backend lo maneja.
            },
            onError: (errors) => {
                // Los errores se mostrarán usualmente por los componentes InputError
                console.error("Error al registrar pago manual:", errors);
            }
        });
    }
};

// Funciones Helper (consistentes con las usadas en el lado del cliente)
// La lógica de paymentForm, transactionTypes, transactionStatuses y submitPayment ha sido eliminada.
const formatCurrency = (amount, currencyCode = 'USD') => {
    const number = parseFloat(amount);
    if (isNaN(number)) return 'N/A';
    try {
        return new Intl.NumberFormat(undefined, { style: 'currency', currency: currencyCode }).format(number);
    } catch (e) {
        return `${currencyCode} ${number.toFixed(2)}`;
    }
};

const formatDate = (datetime) => {
    if (!datetime) return 'N/A';
    if (datetime.length <= 10) {
        const [year, month, day] = datetime.split('-');
        return new Date(year, month - 1, day).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
    }
    return new Date(datetime).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });
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
        'px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full': true,
        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100': status === 'paid' || status === 'active_service',
        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100': status === 'unpaid' || status === 'overdue' || status === 'pending_confirmation' || status === 'pending_activation',
        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100': status === 'cancelled' || status === 'failed_payment',
        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100': status === 'refunded',
        'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100': status === 'collections',
        'bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-300': status === 'draft',
    };
};

const getFriendlyItemType = (itemType) => {
    if (!itemType) return '';
    const mappings = { 'new_service': 'Nuevo Servicio', 'renewal': 'Renovación', 'upgrade': 'Mejora', 'addon': 'Complemento', 'manual_item': 'Ítem Manual'};
    return mappings[itemType] || itemType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getFriendlyServiceStatusText = (status) => {
    if (!status) return 'N/D';
    const mappings = { 'pending': 'Pendiente Activación', 'active': 'Activo', 'suspended': 'Suspendido', 'terminated': 'Terminado', 'cancelled': 'Cancelado', 'fraud': 'Fraude', 'pending_configuration': 'Pendiente Configuración', 'provisioning_failed': 'Falló Aprovisionamiento' };
    return mappings[status] || status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getServiceStatusClass = (status) => {
    if (!status) return 'text-gray-500 dark:text-gray-400';
    return {
        'text-green-600 dark:text-green-400': status === 'active',
        'text-yellow-600 dark:text-yellow-400': status === 'pending' || status === 'pending_configuration',
        'text-red-600 dark:text-red-400': status === 'suspended' || status === 'terminated' || status === 'cancelled' || status === 'fraud' || status === 'provisioning_failed',
    };
};

const completedTransaction = computed(() => {
    // Asumimos que el controlador ya ha filtrado y ordenado las transacciones,
    // y la primera es la más relevante (ej. el pago completado).
    return props.invoice.transactions && props.invoice.transactions.length > 0 ? props.invoice.transactions[0] : null;
});

const canActivateServices = computed(() => {
    return ['paid', 'pending_activation'].includes(props.invoice.status) &&
           props.invoice.items.some(item => item.client_service && item.client_service.status === 'pending');
});

const activateServicesForm = useForm({});

const submitActivateServices = () => {
    activateServicesForm.post(route('admin.invoices.activateServices', props.invoice.id), {
        preserveScroll: true,
        // onSuccess y onError se manejan con los mensajes flash de Inertia
    });
};

</script>

<template>
    <Head :title="`Factura #${invoice.invoice_number}`" />

    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Detalles de Factura
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Flash Messages -->
                <div v-if="page.props.flash && page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-700 rounded dark:bg-green-800 dark:text-green-200">
                    {{ page.props.flash.success }}
                </div>
                <div v-if="page.props.flash && page.props.flash.error" class="mb-4 p-4 bg-red-100 text-red-700 rounded dark:bg-red-800 dark:text-red-200">
                    {{ page.props.flash.error }}
                </div>
                <div v-if="page.props.flash && page.props.flash.info" class="mb-4 p-4 bg-blue-100 text-blue-700 rounded dark:bg-blue-800 dark:text-blue-200">
                    {{ page.props.flash.info }}
                </div>
                <div v-if="page.props.flash && page.props.flash.warning" class="mb-4 p-4 bg-yellow-100 text-yellow-700 rounded dark:bg-yellow-800 dark:text-yellow-200">
                    {{ page.props.flash.warning }}
                </div>

                <div class="p-6 bg-white dark:bg-gray-900 shadow-sm sm:rounded-lg">
                    <div class="flex justify-between items-start mb-4">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Factura #{{ invoice.invoice_number }}</h2>
                        <span class="px-3 py-1 text-sm font-semibold rounded-full" :class="getInvoiceStatusClass(invoice.status)">
                            {{ getFriendlyInvoiceStatusText(invoice.status) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 text-sm text-gray-700 dark:text-gray-300 mb-6">
                        <div><strong>Cliente:</strong> <Link :href="route('admin.users.show', invoice.client.id)" class="text-indigo-600 hover:underline">{{ invoice.client?.name }}</Link> ({{ invoice.client?.email }})</div>
                        <div v-if="invoice.reseller"><strong>Revendedor:</strong> <Link :href="route('admin.users.show', invoice.reseller.id)" class="text-indigo-600 hover:underline">{{ invoice.reseller?.name }}</Link></div>
                        <div :class="{'md:col-span-1': invoice.reseller, 'md:col-span-2': !invoice.reseller}"></div>


                        <div><strong>Fecha Solicitud:</strong> {{ formatDate(invoice.requested_date) }}</div>
                        <div><strong>Fecha Emisión:</strong> {{ formatDate(invoice.issue_date) }}</div>
                        <div><strong>Fecha Vencimiento:</strong> {{ formatDate(invoice.due_date) }}</div>

                        <div><strong>Subtotal:</strong> {{ formatCurrency(invoice.subtotal, invoice.currency_code) }}</div>
                        <div><strong>Total Impuestos:</strong> {{ formatCurrency( (invoice.tax1_amount || 0) + (invoice.tax2_amount || 0), invoice.currency_code) }}</div>
                        <div class="text-lg font-semibold"><strong>Total General:</strong> {{ formatCurrency(invoice.total_amount, invoice.currency_code) }}</div>

                        <div v-if="invoice.paid_date"><strong>Fecha de Pago:</strong> {{ formatDate(invoice.paid_date) }}</div>
                        <div v-if="invoice.payment_gateway_slug"><strong>Pasarela Pago (Solicitud):</strong> {{ invoice.payment_gateway_slug }}</div>
                        <div v-if="invoice.ip_address"><strong>IP Solicitud:</strong> {{ invoice.ip_address }}</div>

                        <div v-if="invoice.notes_to_client" class="md:col-span-3"><strong>Notas para Cliente:</strong> <p class="whitespace-pre-wrap p-2 bg-gray-50 dark:bg-gray-800 rounded">{{ invoice.notes_to_client }}</p></div>
                        <div v-if="invoice.admin_notes" class="md:col-span-3"><strong>Notas Admin:</strong> <p class="whitespace-pre-wrap p-2 bg-gray-50 dark:bg-gray-800 rounded">{{ invoice.admin_notes }}</p></div>
                    </div>

                    <!-- Botón Activar Servicios -->
                    <div v-if="canActivateServices" class="my-6 text-center">
                        <form @submit.prevent="submitActivateServices">
                            <PrimaryButton :disabled="activateServicesForm.processing" class="bg-green-600 hover:bg-green-700 focus:ring-green-500">
                                {{ activateServicesForm.processing ? 'Activando...' : 'Activar Servicios Pendientes' }}
                            </PrimaryButton>
                        </form>
                    </div>

                    <h3 class="mt-6 mb-3 text-md font-semibold text-gray-800 dark:text-gray-100">Ítems de la Factura:</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Descripción</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tarifa Config.</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cant.</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Precio Unit.</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado Servicio</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-700/50 divide-y divide-gray-200 dark:divide-gray-600">
                                <template v-if="invoice.items && invoice.items.length > 0">
                                    <tr v-for="item in invoice.items" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-600/50">
                                        <td class="px-4 py-3 whitespace-normal text-sm">
                                            <div class="font-medium text-gray-900 dark:text-white">{{ item.product ? item.product.name : item.description }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                <span v-if="item.product_pricing && item.product_pricing.billing_cycle">{{ item.product_pricing.billing_cycle.name }}</span>
                                                <span v-if="item.domain_name"> - {{ item.domain_name }}</span>
                                                <span v-if="item.item_type && item.item_type !== 'manual_item'"> ({{ getFriendlyItemType(item.item_type) }})</span>
                                            </div>
                                            <div v-if="item.description && (!item.product || item.description !== item.product.name)" class="mt-1 text-xs text-gray-600 dark:text-gray-300 italic">
                                                Nota Ítem: {{ item.description }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-700 dark:text-gray-300">{{ formatCurrency(item.setup_fee, invoice.currency_code) }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-700 dark:text-gray-300">{{ item.quantity }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-700 dark:text-gray-300">{{ formatCurrency(item.unit_price, invoice.currency_code) }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold text-gray-900 dark:text-white">{{ formatCurrency(item.total_price, invoice.currency_code) }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            <span v-if="item.client_service" :class="getServiceStatusClass(item.client_service.status)">
                                                {{ getFriendlyServiceStatusText(item.client_service.status) }}
                                            </span>
                                            <span v-else-if="item.product && item.product.product_type && item.product.product_type.creates_service_instance" class="text-gray-400 dark:text-gray-500 italic">
                                                (No Provisionado)
                                            </span>
                                            <span v-else class="text-gray-400 dark:text-gray-500">-</span>
                                        </td>
                                    </tr>
                                </template>
                                <tr v-else>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No hay ítems en esta factura.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Payment Details if Paid -->
                 <div v-if="invoice.status === 'paid' || invoice.status === 'active_service' || invoice.status === 'pending_activation' || invoice.status === 'refunded'" class="p-6 bg-white dark:bg-gray-900 shadow-sm sm:rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Historial de Transacciones</h3>
                    <div v-if="invoice.transactions && invoice.transactions.length > 0" class="space-y-4">
                        <div v-for="transaction in invoice.transactions" :key="transaction.id" class="p-3 border dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-800/50">
                            <p><strong>ID Transacción:</strong> {{ transaction.gateway_transaction_id || transaction.id }}</p>
                            <p><strong>Fecha:</strong> {{ formatDate(transaction.transaction_date) }}</p>
                            <p><strong>Monto:</strong> {{ formatCurrency(transaction.amount, transaction.currency_code) }}</p>
                            <p><strong>Pasarela:</strong> {{ transaction.gateway_slug }}</p>
                            <p><strong>Estado:</strong> {{ transaction.status }}</p>
                            <p v-if="transaction.description"><strong>Descripción:</strong> {{ transaction.description }}</p>
                            <div v-if="transaction.payment_method && transaction.payment_method.formatted_details" class="mt-2">
                                <h5 class="font-medium text-gray-800 dark:text-gray-200">Método de Pago: {{ transaction.payment_method.formatted_details.name }}</h5>
                                <!-- ... (lógica detallada para formatted_details como en Client/Invoices/Show) ... -->
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-500 dark:text-gray-400">No hay transacciones registradas para esta factura.</p>
                </div>

                <!-- Formulario Consolidado para Registrar Pago Manual -->
                <div v-if="props.showManualPaymentForm" class="p-6 bg-white dark:bg-gray-900 shadow-sm sm:rounded-lg mt-6 border-t border-indigo-300 dark:border-indigo-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Registrar Pago Manual Recibido</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Utilice este formulario para registrar un pago que ya ha sido recibido externamente (ej. transferencia bancaria o efectivo).
                        Esto marcará la factura como pagada y creará/activará los servicios correspondientes si aplica.
                    </p>
                    <form @submit.prevent="submitStoreManualTransaction" class="mt-6 space-y-6">
                        <div>
                            <InputLabel for="manual_transaction_date" value="Fecha de Transacción del Pago" />
                            <TextInput id="manual_transaction_date" type="date" class="mt-1 block w-full" v-model="manualTransactionForm.transaction_date" required />
                            <InputError class="mt-2" :message="manualTransactionForm.errors.transaction_date" />
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="manual_amount" value="Monto del Pago" />
                                <TextInput id="manual_amount" type="number" step="0.01" class="mt-1 block w-full bg-gray-100 dark:bg-gray-800" v-model="manualTransactionForm.amount" required readonly />
                                <InputError class="mt-2" :message="manualTransactionForm.errors.amount" />
                            </div>
                            <div>
                                <InputLabel for="manual_currency_code" value="Moneda del Pago" />
                                <TextInput id="manual_currency_code" type="text" class="mt-1 block w-full bg-gray-100 dark:bg-gray-800" v-model="manualTransactionForm.currency_code" required readonly />
                                <InputError class="mt-2" :message="manualTransactionForm.errors.currency_code" />
                            </div>
                        </div>
                        <div>
                            <InputLabel for="manual_payment_gateway_name" value="Método/Pasarela de Pago (Ej: Transferencia Bancaria, Efectivo)" />
                            <TextInput id="manual_payment_gateway_name" type="text" class="mt-1 block w-full" v-model="manualTransactionForm.payment_gateway_name" placeholder="Ej: Transferencia Bancaria XYZ" required />
                            <InputError class="mt-2" :message="manualTransactionForm.errors.payment_gateway_name" />
                        </div>
                        <div>
                            <InputLabel for="manual_gateway_transaction_id" value="ID Transacción Externa / Referencia del Pago" />
                            <TextInput id="manual_gateway_transaction_id" type="text" class="mt-1 block w-full" v-model="manualTransactionForm.gateway_transaction_id" placeholder="Ej: REF12345BANCO" />
                            <InputError class="mt-2" :message="manualTransactionForm.errors.gateway_transaction_id" />
                        </div>
                        <div>
                            <InputLabel for="manual_description" value="Descripción / Notas Adicionales (Opcional)" />
                            <textarea id="manual_description" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" v-model="manualTransactionForm.description" rows="3"></textarea>
                            <InputError class="mt-2" :message="manualTransactionForm.errors.description" />
                        </div>
                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="manualTransactionForm.processing">
                                {{ manualTransactionForm.processing ? 'Registrando Pago...' : 'Confirmar y Registrar Pago Manual' }}
                            </PrimaryButton>
                            <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                                <p v-if="manualTransactionForm.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-300">Pago manual registrado.</p>
                            </Transition>
                        </div>
                         <InputError class="mt-2" :message="manualTransactionForm.errors.general" /> <!-- For general errors -->
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
