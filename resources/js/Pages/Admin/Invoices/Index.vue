<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3'; // Add router and usePage
import Pagination from '@/Components/UI/Pagination.vue'; // Asumiendo que tienes este componente
import { format } from 'date-fns'; // Para formateo de fechas
import { computed } from 'vue';
import Alert from '@/Components/UI/Alert.vue';
import { CheckCircleIcon, XCircleIcon, PlusIcon } from '@heroicons/vue/24/outline';

defineProps({
    invoices: Object, // Inertia pasa un objeto paginado
    pendingFundAdditions: Array, // Add this new prop
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash && page.props.flash.success);
const flashError = computed(() => page.props.flash && page.props.flash.error);
const flashInfo = computed(() => page.props.flash && page.props.flash.info);

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

const confirmFundAddition = (transaction) => {
    if (confirm('¿Estás seguro de que quieres confirmar esta adición de fondos?')) {
        router.post(route('admin.transactions.confirm', { transaction: transaction.id }), {}, {
            preserveScroll: true,
            onSuccess: () => {
                // La página debería recargarse o el controlador debería devolver los datos actualizados.
                // Si se usa Redirect desde el controlador, Inertia maneja la actualización.
                // Podríamos añadir un mensaje flash si el layout lo maneja.
                // Por ahora, confiamos en la redirección del backend con mensaje flash.
            },
            onError: (errors) => {
                // Manejar errores, quizás mostrar una alerta.
                // Por ahora, los errores de validación del backend se mostrarían en page.props.errors
                // o como un mensaje flash de error si el controlador lo envía.
                if (errors.message) { // Ejemplo básico de manejo de error general
                    alert('Error: ' + errors.message);
                } else {
                    alert('Ocurrió un error al intentar confirmar la transacción.');
                }
            }
        });
    }
};

const rejectFundAddition = (transaction) => {
    if (confirm('¿Estás seguro de que quieres rechazar esta adición de fondos?')) {
        router.post(route('admin.transactions.reject', { transaction: transaction.id }), {}, {
            preserveScroll: true,
            // onSuccess and onError can be handled similarly to confirmFundAddition
            // if specific UI feedback beyond flash messages is needed directly here.
        });
    }
};
</script>

<template>

    <Head title="Gestión de Facturas" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Gestión de Facturas
                </h2>
                <Link :href="route('admin.invoices.create')"
                    class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                <PlusIcon class="w-4 h-4 mr-2" />
                CREAR FACTURA
                </Link>
            </div>
        </template>

        <div v-if="flashSuccess || flashError || flashInfo" class="max-w-full px-4 pt-6 mx-auto sm:px-6 lg:px-8">
            <Alert :message="flashSuccess" type="success" v-if="flashSuccess" class="mb-4" />
            <Alert :message="flashError" type="danger" v-if="flashError" class="mb-4" />
            <Alert :message="flashInfo" type="info" v-if="flashInfo" class="mb-4" />
        </div>

        <div class="py-12">
            <div class="max-w-full px-4 mx-auto sm:px-6 lg:px-8">
                <div class="p-6 overflow-hidden bg-white shadow-sm dark:bg-gray-900 sm:rounded-lg">


                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th scope="col"
                                        class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                        Número</th>
                                    <th scope="col"
                                        class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                        Cliente</th>
                                    <th scope="col"
                                        class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                        Solicitada</th>
                                    <th scope="col"
                                        class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                        Emitida</th>
                                    <th scope="col"
                                        class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                        Vencimiento</th>
                                    <th scope="col"
                                        class="px-4 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-400">
                                        Total</th>
                                    <th scope="col"
                                        class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-400">
                                        Estado</th>
                                    <th scope="col" class="relative px-4 py-3"><span class="sr-only">Acciones</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-700/50 dark:divide-gray-600">
                                <tr v-for="invoice in invoices.data" :key="invoice.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-600/50">
                                    <td
                                        class="px-4 py-3 text-sm font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{
                                            invoice.invoice_number }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap dark:text-gray-300">{{
                                        invoice.client ? invoice.client.name : 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap dark:text-gray-300">{{
                                        formatDate(invoice.requested_date) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap dark:text-gray-300">{{
                                        formatDate(invoice.issue_date) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap dark:text-gray-300">{{
                                        formatDate(invoice.due_date) }}</td>
                                    <td
                                        class="px-4 py-3 text-sm text-right text-gray-500 whitespace-nowrap dark:text-gray-300">
                                        {{ formatCurrency(invoice.total_amount, invoice.currency_code) }}</td>
                                    <td class="px-4 py-3 text-sm text-center whitespace-nowrap">
                                        <span :class="getInvoiceStatusClass(invoice.status)">
                                            {{ getFriendlyInvoiceStatusText(invoice.status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-medium text-right whitespace-nowrap">
                                        <Link :href="route('admin.invoices.show', invoice.id)"
                                            class="mr-2 text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        Ver</Link>
                                        <Link :href="route('admin.invoices.edit', invoice.id)"
                                            class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">
                                        Editar</Link>
                                    </td>
                                </tr>
                                <tr v-if="invoices.data.length === 0">
                                    <td colspan="8"
                                        class="px-6 py-4 text-sm text-center text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        No hay facturas para mostrar.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <Pagination :links="invoices.links" class="mt-6"
                        v-if="invoices.links && invoices.links.length > 3" />
                </div>

                <!-- Sección para Adiciones de Fondos Pendientes -->
                <div class="p-6 mt-8 overflow-hidden bg-white shadow-sm dark:bg-gray-900 sm:rounded-lg">
                    <h3 class="mb-4 text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        Adiciones de Fondos Pendientes de Confirmación
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th scope="col"
                                        class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                        Cliente</th>
                                    <th scope="col"
                                        class="px-4 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-400">
                                        Monto</th>
                                    <th scope="col"
                                        class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                        Método de Pago</th>
                                    <th scope="col"
                                        class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                        Fecha Transacción</th>
                                    <th scope="col"
                                        class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                        Referencia</th>
                                    <th scope="col"
                                        class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-400">
                                        Estado</th>
                                    <th scope="col" class="relative px-4 py-3"><span class="sr-only">Acciones</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-700/50 dark:divide-gray-600">
                                <tr v-if="pendingFundAdditions && pendingFundAdditions.length === 0">
                                    <td colspan="7"
                                        class="px-6 py-4 text-sm text-center text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        No hay adiciones de fondos pendientes de confirmación.
                                    </td>
                                </tr>
                                <tr v-for="transaction in pendingFundAdditions" :key="transaction.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-600/50">
                                    <td
                                        class="px-4 py-3 text-sm font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ transaction.client ? transaction.client.name : 'N/A' }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-sm text-right text-gray-500 whitespace-nowrap dark:text-gray-300">
                                        {{ formatCurrency(transaction.amount, transaction.currency_code) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap dark:text-gray-300">
                                        {{ transaction.payment_method ? transaction.payment_method.name :
                                            (transaction.gateway_slug || 'N/A') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap dark:text-gray-300">
                                        {{ formatDate(transaction.transaction_date) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap dark:text-gray-300">
                                        {{ transaction.gateway_transaction_id || 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center whitespace-nowrap">
                                        <span
                                            class="inline-flex px-2 text-xs font-semibold leading-5 text-yellow-800 bg-yellow-100 rounded-full dark:bg-yellow-900 dark:text-yellow-100">
                                            Pendiente Confirmación
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 space-x-2 text-sm font-medium text-right whitespace-nowrap">
                                        <button @click="confirmFundAddition(transaction)" type="button"
                                            class="inline-flex items-center justify-center p-1 text-green-600 rounded-full hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                            title="Confirmar">
                                            <CheckCircleIcon class="w-5 h-5" />
                                        </button>
                                        <button @click="rejectFundAddition(transaction)" type="button"
                                            class="inline-flex items-center justify-center p-1 text-red-600 rounded-full hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                            title="Rechazar">
                                            <XCircleIcon class="w-5 h-5" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
