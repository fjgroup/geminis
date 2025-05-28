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
                                        {{ order.client ? order.client.name : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        {{ new Date(order.order_date).toLocaleDateString() }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        {{ order.total_amount }} {{ order.currency_code }}
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
