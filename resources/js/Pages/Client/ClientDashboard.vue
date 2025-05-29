<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { defineProps, computed } from 'vue'; // Added computed
import { format as formatDate } from 'date-fns'; // Import date-fns

const props = defineProps({
    clientServices: {
        type: Array,
        default: () => [],
    },
    pendingOrdersCount: {
        type: Number,
        default: 0,
    },
    unpaidInvoicesCount: {
        type: Number,
        default: 0,
    },
    accountBalance: { // Raw balance
        type: [Number, String], // Allow both Number and String
        default: 0,
    },
    formattedAccountBalance: { // Pre-formatted balance string
        type: String,
        default: '$0.00',
    },
});

// Compute active services count for the summary
const activeServicesCount = computed(() => props.clientServices.length);

// Helper function for formatting currency
const formatCurrency = (amount, currencyCode = 'USD') => {
    const number = parseFloat(amount);
    if (isNaN(number)) {
        return 'N/A'; // Or some other placeholder for invalid numbers
    }
    try {
        return new Intl.NumberFormat(undefined, { style: 'currency', currency: currencyCode }).format(number);
    } catch (e) {
        // Fallback for invalid currency code or if Intl is not fully supported
        return `${currencyCode} ${number.toFixed(2)}`;
    }
};

// Helper for status display
const formatStatus = (status) => {
    if (!status) return 'N/A';
    return status.replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase());
};

import { router } from '@inertiajs/vue3'; // Ensure this is imported

const confirmRequestCancellation = (event, serviceId) => {
    event.preventDefault();
    if (confirm('Are you sure you want to request cancellation for this service?')) {
        router.post(route('client.services.requestCancellation', { service: serviceId }), {}, {
            preserveScroll: true,
        });
    }
};

const confirmRenewalRequest = (event, serviceId) => {
    event.preventDefault();
    if (confirm('Are you sure you want to generate a renewal invoice for this service?')) {
        router.post(route('client.services.requestRenewal', { service: serviceId }), {}, {
            preserveScroll: true,
        });
    }
};
</script>

<template>

    <Head title="Panel del Cliente" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Panel del Cliente</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">

                        <!-- Dashboard Summary Section -->
                        <div class="mb-8">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Account Overview
                            </h3>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                                <!-- Account Balance Card -->
                                <div
                                    class="p-6 bg-white border border-gray-200 rounded-lg shadow-md dark:bg-gray-700 dark:border-gray-600">
                                    <h4 class="mb-2 font-semibold text-gray-700 text-md dark:text-gray-300">Account
                                        Balance</h4>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{
                                        formattedAccountBalance }}
                                    </p>
                                    <!-- Optional: Link to add funds or view transactions -->
                                    <Link :href="route('client.transactions.index')"
                                        class="mt-2 text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    View Transactions
                                    </Link>
                                </div>
                                <!-- Unpaid Invoices Card -->
                                <div
                                    class="p-6 bg-white border border-gray-200 rounded-lg shadow-md dark:bg-gray-700 dark:border-gray-600">
                                    <h4 class="mb-2 font-semibold text-gray-700 text-md dark:text-gray-300">Unpaid
                                        Invoices</h4>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ unpaidInvoicesCount
                                        }}</p>
                                    <Link :href="route('client.invoices.index')"
                                        class="mt-2 text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    View Invoices
                                    </Link>
                                </div>
                                <!-- Pending Orders Card -->
                                <div
                                    class="p-6 bg-white border border-gray-200 rounded-lg shadow-md dark:bg-gray-700 dark:border-gray-600">
                                    <h4 class="mb-2 font-semibold text-gray-700 text-md dark:text-gray-300">Pending
                                        Orders</h4>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ pendingOrdersCount }}
                                    </p>
                                    <Link :href="route('client.orders.index')"
                                        class="mt-2 text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    View Orders
                                    </Link>
                                </div>
                                <!-- Active Services Card -->
                                <div
                                    class="p-6 bg-white border border-gray-200 rounded-lg shadow-md dark:bg-gray-700 dark:border-gray-600">
                                    <h4 class="mb-2 font-semibold text-gray-700 text-md dark:text-gray-300">Active
                                        Services</h4>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ activeServicesCount
                                        }}</p>
                                    <Link :href="route('client.services.index')"
                                        class="mt-2 text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    View Services
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Navigation (remains as is or can be integrated differently if desired) -->
                        <div class="mb-6">
                            <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Navegación Rápida</h3>
                            <div class="flex flex-wrap gap-4">
                                <Link :href="route('client.services.index')"
                                    class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-800 border border-transparent rounded-md dark:bg-gray-700 hover:bg-gray-700 dark:hover:bg-gray-600 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                Mis Servicios
                                </Link>
                                <Link :href="route('client.orders.index')"
                                    class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-800 border border-transparent rounded-md dark:bg-gray-700 hover:bg-gray-700 dark:hover:bg-gray-600 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                Mis Órdenes
                                </Link>
                                <Link :href="route('client.invoices.index')"
                                    class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-800 border border-transparent rounded-md dark:bg-gray-700 hover:bg-gray-700 dark:hover:bg-gray-600 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                Mis Facturas
                                </Link>
                                <Link :href="route('client.products.index')"
                                    class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-600 border border-transparent rounded-md dark:bg-blue-500 hover:bg-blue-500 dark:hover:bg-blue-400 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                Comprar Productos
                                </Link>
                            </div>
                        </div>

                        <div>
                            <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Mis Servicios</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                                    v-if="clientServices && clientServices.length > 0">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                                Nombre del producto</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                                Dominio</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                                Próxima Fecha de Vencimiento</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                                Estado</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-300">
                                                Precio</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                                Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                        <tr v-for="service in clientServices" :key="service.id"
                                            class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td
                                                class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                {{ service.product?.name || 'N/A' }}
                                                <div v-if="service.billingCycle"
                                                    class="text-xs text-gray-500 dark:text-gray-400">
                                                    ({{ service.billingCycle.name }})
                                                </div>
                                            </td>
                                            <td
                                                class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-300">
                                                {{
                                                service.domain_name || 'N/A' }}</td>
                                            <td
                                                class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-300">
                                                {{
                                                    service.next_due_date ? formatDate(service.next_due_date, 'dd/MM/yyyy')
                                                : 'N/A'
                                                }}</td>
                                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                                <span :class="{
                                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100': service.status === 'Active',
                                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100': service.status === 'Pending',
                                                    'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-100': service.status === 'Suspended',
                                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100': service.status === 'Terminated' || service.status === 'Cancelled',
                                                    'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200': !['Active', 'Pending', 'Suspended', 'Terminated', 'Cancelled'].includes(service.status)
                                                }">
                                                    {{ formatStatus(service.status) }}
                                                </span>
                                            </td>
                                            <td
                                                class="px-6 py-4 text-sm text-right text-gray-500 whitespace-nowrap dark:text-gray-300">
                                                {{ formatCurrency(service.billing_amount,
                                                service.productPricing?.currency_code
                                                || 'USD') }}
                                            </td>
                                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                                <div class="flex flex-col items-start space-y-1">
                                                    <Link v-if="service.status === 'Active'"
                                                        :href="route('client.services.showUpgradeDowngradeOptions', { service: service.id })"
                                                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                    Change Plan
                                                    </Link>
                                                    <Link v-if="service.status === 'Active'"
                                                        :href="route('client.services.requestCancellation', { service: service.id })"
                                                        method="post" as="button"
                                                        class="text-xs font-semibold text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                                        @click.prevent="confirmRequestCancellation($event, service.id)">
                                                    Request Cancellation
                                                    </Link>
                                                    <Link
                                                        v-if="service.status === 'Active' || service.status === 'Suspended'"
                                                        :href="route('client.services.requestRenewal', { service: service.id })"
                                                        method="post" as="button"
                                                        class="text-xs font-semibold text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300"
                                                        @click.prevent="confirmRenewalRequest($event, service.id)">
                                                    Renew Service
                                                    </Link>
                                                    <span v-else-if="service.status === 'Cancellation Requested'"
                                                        class="text-xs text-yellow-600 dark:text-yellow-400">
                                                        Pending Review
                                                    </span>
                                                    <span v-else class="text-xs text-gray-400 dark:text-gray-500">
                                                        ---
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div v-else class="py-6 text-center text-gray-500 dark:text-gray-400">
                                    <p>No tienes servicios activos en este momento.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Scoped styles removed as Tailwind is now used for table styling */
</style>
