<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3'; // Added router
import { ref, watch } from 'vue'; // Added ref, watch
import Pagination from '@/Components/Pagination.vue';
import TextInput from '@/Components/TextInput.vue'; // Assuming TextInput exists
import SelectInput from '@/Components/SelectInput.vue'; // Assuming SelectInput exists
// import PrimaryButton from '@/Components/PrimaryButton.vue'; // Not needed for watch approach

const props = defineProps({
    orders: Object, // Esto recibirá los datos paginados de las órdenes
    filters: Object, // search, status from controller
    possibleStatuses: Array, // from controller
});

// Helper function for formatting currency if formatted_balance is not available
const formatCurrency = (amount, currencyCode = 'USD') => {
    const number = parseFloat(amount);
    if (isNaN(number)) {
        return 'N/A';
    }
    // Using Intl.NumberFormat for better localization and currency handling
    try {
        return new Intl.NumberFormat(undefined, { style: 'currency', currency: currencyCode }).format(number);
    } catch (e) {
        // Fallback for invalid currency code or if Intl is not fully supported in a specific environment
        console.warn(`Currency formatting failed for ${currencyCode}: ${e.getMessage()}`);
        return `${currencyCode} ${number.toFixed(2)}`;
    }
};


const searchFilter = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

const statusOptions = props.possibleStatuses.map(status => ({
    value: status,
    label: status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}));
statusOptions.unshift({ value: '', label: 'All Statuses' });

// Debounce function
let debounceTimeout = null;
const debounce = (func, delay) => {
    return (...args) => {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => func.apply(this, args), delay);
    };
};

const applyFilters = () => {
    const currentParams = {};
    if (searchFilter.value) currentParams.search = searchFilter.value;
    if (statusFilter.value) currentParams.status = statusFilter.value;

    router.get(route('admin.orders.index'), currentParams, {
        preserveState: true,
        replace: true, // Avoids polluting browser history for filter changes
        preserveScroll: true,
    });
};

watch(searchFilter, debounce(applyFilters, 500)); // Debounce search input
watch(statusFilter, applyFilters); // Apply status filter immediately

</script>

<template>
    <Head title="Órdenes" />

    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Listado de Órdenes
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Filter UI -->
                        <div class="mb-6 p-4 bg-gray-100 rounded-lg">
                            <h3 class="font-semibold text-lg mb-3">Filtrar Órdenes</h3>
                            <form @submit.prevent="applyFilters"> 
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label for="searchFilter" class="block text-sm font-medium text-gray-700">Buscar</label>
                                        <TextInput id="searchFilter" type="text" class="mt-1 block w-full" 
                                                   v-model="searchFilter" placeholder="Orden #, Cliente, Factura #" />
                                    </div>
                                    <div>
                                        <label for="statusFilter" class="block text-sm font-medium text-gray-700">Estado</label>
                                        <SelectInput id="statusFilter" class="mt-1 block w-full" 
                                                     v-model="statusFilter" :options="statusOptions" />
                                    </div>
                                </div>
                            </form>
                        </div>

                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Número de Orden
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Cliente
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Client Balance
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
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Detalles</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="order in orders.data" :key="order.id">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                                        {{ order.order_number }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        <div v-if="order.client">
                                            <Link :href="route('admin.users.edit', order.client.id)" class="text-indigo-600 hover:text-indigo-900">
                                                {{ order.client.name }}
                                            </Link>
                                            <div class="text-xs text-gray-400">{{ order.client.email }}</div>
                                        </div>
                                        <span v-else>N/A</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        <span v-if="order.client && order.client.formatted_balance !== undefined">
                                            {{ order.client.formatted_balance }}
                                        </span>
                                        <span v-else-if="order.client && typeof order.client.balance === 'number' && order.client.currency_code">
                                            {{ formatCurrency(order.client.balance, order.client.currency_code) }}
                                        </span>
                                        <span v-else>N/A</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        {{ new Date(order.order_date).toLocaleDateString() }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        {{ formatCurrency(order.total_amount, order.currency_code) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        <span :class="{
                                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                            'bg-yellow-100 text-yellow-800': order.status === 'pending_payment',
                                            'bg-blue-100 text-blue-800': order.status === 'pending_provisioning' || order.status === 'paid_pending_execution',
                                            'bg-purple-100 text-purple-800': order.status === 'cancellation_requested_by_client',
                                            'bg-green-100 text-green-800': order.status === 'active' || order.status === 'completed',
                                            'bg-red-100 text-red-800': order.status === 'fraud' || order.status === 'cancelled',
                                        }">
                                            {{ order.status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                        <Link :href="route('admin.orders.show', order.id)" class="text-indigo-600 hover:text-indigo-900">
                                            Detalles
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="orders.data.length === 0">
                                    <td colspan="6" class="px-6 py-4 text-sm text-center text-gray-500 whitespace-nowrap">
                                        No hay órdenes disponibles.
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <Pagination :links="orders.links" class="mt-6" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
