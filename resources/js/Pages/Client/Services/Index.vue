<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { format as formatDate } from 'date-fns';
import { router } from '@inertiajs/vue3';
import ServiceDetailsModal from '@/Components/UI/ServiceDetailsModal.vue';
import { EyeIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline';

// Modal state
const isServiceModalOpen = ref(false);
const selectedService = ref(null);

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

// Compute active services count for the summary - kept for consistency if needed, though not displayed as a separate card here
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
const getFriendlyServiceStatusText = (status) => {
    if (!status) return 'N/A';
    const lowerStatus = status.toLowerCase(); // Convertir a minúsculas para la comparación
    const mappings = {
        'active': 'Activo', // Clave en minúsculas
        'pending': 'Pendiente',
        'pending_configuration': 'Pendiente de Configuración',
        'suspended': 'Suspendido', // Clave en minúsculas
        'terminated': 'Terminado',
        'cancelled': 'Cancelado',
        'pending_cancellation': 'Cancelación Pendiente', // <-- AÑADIDO
        // Add other specific statuses here if they come directly from backend (use lowercase keys)
    };
    if (mappings[lowerStatus]) {
        return mappings[lowerStatus];
    }
    // Fallback para cualquier otro estado
    // Capitaliza la primera letra de cada palabra después de reemplazar guiones bajos.
    return status.replace(/_/g, ' ').toLowerCase().split(' ').map(word => word.charAt(0).toUpperCase() + word.substring(1)).join(' ');
};


const confirmRequestCancellation = (serviceId) => { // Removed event parameter
    // Consider translating this confirmation message
    if (confirm('¿Estás seguro de que deseas solicitar la cancelación de este servicio?')) {
        router.post(route('client.services.requestCancellation', { service: serviceId }), {}, {
            preserveScroll: true,
            // onSuccess: () => { /* Flash message will handle feedback */ },
            // onError: () => { /* Flash error message or specific error handling */ }
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

// Function to open the modal and set the service
const showServiceDetails = (service) => {
    selectedService.value = service;
    isServiceModalOpen.value = true;
};

// Function to close the modal
const closeServiceModal = () => {
    isServiceModalOpen.value = false;
    selectedService.value = null;
};

</script>

<template>
    <Head title="Mis Servicios" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mis Servicios</h2>
        </template>

        <!-- Service Details Modal -->
        <ServiceDetailsModal
            :show="isServiceModalOpen"
            :service="selectedService"
            @close="closeServiceModal"
        />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <!-- Service List Section -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Lista de Servicios</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200" v-if="clientServices && clientServices.length > 0">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Nombre del producto</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Dominio</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Próxima Fecha de Vencimiento</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Estado</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Precio</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="service in clientServices" :key="service.id">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ service.product?.name || 'N/A' }}
                                                </div>
                                                 <div v-if="service.billingCycle" class="text-xs text-gray-500">
                                                    ({{ service.billingCycle.name }})
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">{{ service.domain_name || 'N/A' }}</div>
                                            </td>
                                             <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">
                                                    {{ service.next_due_date ? formatDate(service.next_due_date, 'dd/MM/yyyy') : 'N/A' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span :class="{
                                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                                    'bg-green-100 text-green-800': service.status && service.status.toLowerCase() === 'active',
                                                    'bg-yellow-100 text-yellow-800': service.status && (service.status.toLowerCase() === 'pending' || service.status.toLowerCase() === 'pending_cancellation'),
                                                    'bg-blue-100 text-blue-800': service.status && service.status.toLowerCase() === 'pending_configuration',
                                                    'bg-orange-100 text-orange-800': service.status && service.status.toLowerCase() === 'suspended',
                                                    'bg-red-100 text-red-800': service.status && (service.status.toLowerCase() === 'terminated' || service.status.toLowerCase() === 'cancelled'),
                                                    'bg-gray-100 text-gray-800': service.status && !['active', 'pending', 'pending_configuration', 'suspended', 'terminated', 'cancelled', 'pending_cancellation'].includes(service.status.toLowerCase())
                                                }">
                                                    {{ getFriendlyServiceStatusText(service.status) }}
                                                </span>
                                            </td>
                                             <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                                 {{ formatCurrency(service.billing_amount, service.productPricing?.currency_code || 'USD') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex items-center space-x-2">
                                                    <button @click="showServiceDetails(service)"
                                                            class="text-gray-500 hover:text-blue-700 p-1"
                                                            aria-label="Ver detalles del servicio"
                                                            title="Ver detalles">
                                                        <EyeIcon class="h-5 w-5" />
                                                    </button>

                                                    <template v-if="service.status && service.status.toLowerCase() === 'active'">
                                                        <Link :href="route('client.services.showUpgradeDowngradeOptions', { service: service.id })"
                                                              class="text-gray-500 hover:text-indigo-700 p-1"
                                                              aria-label="Actualizar plan"
                                                              title="Actualizar plan">
                                                            <PencilSquareIcon class="h-5 w-5" />
                                                        </Link>
                                                        <button type="button"
                                                                @click="confirmRequestCancellation(service.id)"
                                                                class="text-gray-500 hover:text-red-700 p-1"
                                                                aria-label="Solicitar cancelación"
                                                                title="Solicitar cancelación">
                                                            <TrashIcon class="h-5 w-5" />
                                                        </button>
                                                    </template>

                                                    <Link v-if="service.status && (service.status.toLowerCase() === 'active' || service.status.toLowerCase() === 'suspended')"
                                                          :href="route('client.services.requestRenewal', { service: service.id })"
                                                          method="post" as="button"
                                                          class="text-xs font-semibold text-green-600 hover:text-green-700">
                                                        Renew Service
                                                    </Link>

                                                    <span v-if="service.status && service.status.toLowerCase() === 'pending_cancellation'" class="text-xs text-yellow-700 font-semibold">
                                                        Cancelación Pendiente
                                                    </span>

                                                    <!-- Fallback for other states where no specific actions are available -->
                                                    <span v-else-if="service.status && !['active', 'suspended'].includes(service.status.toLowerCase()) && service.status.toLowerCase() !== 'pending_cancellation'" class="text-xs text-gray-400">
                                                        ---
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div v-else class="py-6 text-center text-gray-500">
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
/* Estilos específicos para esta vista, si es necesario */
</style>
