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

// Sorting state
const sortField = ref('domain_name'); // Default sort by domain
const sortDirection = ref('asc');

// Grouping state
const expandedDomains = ref(new Set());

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

// Sorting functions
const sortBy = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
};

const getSortIcon = (field) => {
    if (sortField.value !== field) return '↕️';
    return sortDirection.value === 'asc' ? '↑' : '↓';
};

// Computed property for sorted services
const sortedServices = computed(() => {
    if (!props.clientServices || props.clientServices.length === 0) {
        return [];
    }

    const services = [...props.clientServices];

    return services.sort((a, b) => {
        let aValue, bValue;

        switch (sortField.value) {
            case 'domain_name':
                aValue = a.domain_name || '';
                bValue = b.domain_name || '';
                break;
            case 'product_name':
                aValue = a.product?.name || '';
                bValue = b.product?.name || '';
                break;
            case 'next_due_date':
                aValue = new Date(a.next_due_date || 0);
                bValue = new Date(b.next_due_date || 0);
                break;
            case 'status':
                aValue = a.status || '';
                bValue = b.status || '';
                break;
            case 'price':
                aValue = parseFloat(a.price || 0);
                bValue = parseFloat(b.price || 0);
                break;
            default:
                return 0;
        }

        if (aValue < bValue) {
            return sortDirection.value === 'asc' ? -1 : 1;
        }
        if (aValue > bValue) {
            return sortDirection.value === 'asc' ? 1 : -1;
        }
        return 0;
    });
});

// Group services by domain
const groupedServices = computed(() => {
    const groups = {};

    sortedServices.value.forEach(service => {
        const domain = service.domain_name || 'Sin dominio';
        if (!groups[domain]) {
            groups[domain] = [];
        }
        groups[domain].push(service);
    });

    return groups;
});

// Toggle domain expansion
const toggleDomain = (domain) => {
    if (expandedDomains.value.has(domain)) {
        expandedDomains.value.delete(domain);
    } else {
        expandedDomains.value.add(domain);
    }
};

// Check if domain is expanded
const isDomainExpanded = (domain) => {
    return expandedDomains.value.has(domain);
};

// Get domain summary info
const getDomainSummary = (services) => {
    const totalPrice = services.reduce((sum, service) => sum + parseFloat(service.billing_amount || 0), 0);
    const nextDueDate = services.reduce((earliest, service) => {
        const serviceDate = new Date(service.next_due_date);
        return !earliest || serviceDate < earliest ? serviceDate : earliest;
    }, null);

    return {
        totalPrice,
        nextDueDate,
        serviceCount: services.length,
        statuses: [...new Set(services.map(s => s.status))]
    };
};

// Las funciones getOptionPrice y getBasePrice ya no son necesarias
// porque ahora usamos los datos procesados desde el backend

</script>

<template>

    <Head title="Mis Servicios" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mis Servicios</h2>
        </template>

        <!-- Service Details Modal -->
        <ServiceDetailsModal :show="isServiceModalOpen" :service="selectedService" @close="closeServiceModal" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <!-- Service List Section -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Lista de Servicios</h3>
                            <div class="space-y-4" v-if="clientServices && clientServices.length > 0">
                                <!-- Grouped Services by Domain -->
                                <div v-for="(services, domain) in groupedServices" :key="domain"
                                    class="border border-gray-200 rounded-lg overflow-hidden">

                                    <!-- Domain Header (Clickable) -->
                                    <div class="bg-gray-50 px-6 py-4 cursor-pointer hover:bg-gray-100 transition-colors"
                                        @click="toggleDomain(domain)">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-3">
                                                <span class="text-lg">{{ isDomainExpanded(domain) ? '▼' : '▶' }}</span>
                                                <div>
                                                    <h4 class="text-lg font-semibold text-gray-900">{{ domain }}</h4>
                                                    <p class="text-sm text-gray-600">
                                                        {{ getDomainSummary(services).serviceCount }} servicio(s) •
                                                        Próximo vencimiento: {{ getDomainSummary(services).nextDueDate ?
                                                            formatDate(getDomainSummary(services).nextDueDate, 'dd/MM/yyyy')
                                                            : 'N/A'
                                                        }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-lg font-semibold text-gray-900">
                                                    {{ formatCurrency(getDomainSummary(services).totalPrice) }}
                                                </div>
                                                <div class="text-sm text-gray-600">
                                                    {{ getDomainSummary(services).statuses.join(', ') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Services Table (Expandable) -->
                                    <div v-if="isDomainExpanded(domain)" class="bg-white">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col"
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Servicio
                                                    </th>
                                                    <th scope="col"
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Fecha de Vencimiento
                                                    </th>
                                                    <th scope="col"
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Estado
                                                    </th>
                                                    <th scope="col"
                                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Precio
                                                    </th>
                                                    <th scope="col"
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Acciones
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <tr v-for="service in services" :key="service.id">
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ service.product?.name || 'N/A' }}
                                                        </div>
                                                        <div v-if="service.billingCycle" class="text-xs text-gray-500">
                                                            ({{ service.billingCycle.name }})
                                                        </div>

                                                        <!-- Mostrar indicador de configuraciones adicionales en el servicio base -->
                                                        <div v-if="!service.is_additional_config && service.has_configurable_options"
                                                            class="mt-2">
                                                            <div
                                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
                                                                <svg class="w-3 h-3 mr-1" fill="currentColor"
                                                                    viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd"
                                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                                        clip-rule="evenodd"></path>
                                                                </svg>
                                                                Con configuraciones adicionales
                                                            </div>
                                                        </div>

                                                        <!-- Mostrar detalles de configuraciones adicionales si es un item de configuraciones -->
                                                        <div v-if="service.is_additional_config && service.configurable_options_details"
                                                            class="mt-2">
                                                            <div class="text-xs text-gray-600 mb-2 font-medium">Desglose
                                                                de
                                                                configuraciones:</div>
                                                            <div class="space-y-1">
                                                                <div v-for="option in service.configurable_options_details"
                                                                    :key="option.name"
                                                                    class="bg-blue-50 px-3 py-2 rounded-md border border-blue-200">
                                                                    <div class="flex justify-between items-center">
                                                                        <div class="flex-1">
                                                                            <div
                                                                                class="text-sm font-medium text-blue-900">
                                                                                {{ option.quantity }} {{ option.unit }}
                                                                                de {{
                                                                                option.name }}
                                                                            </div>
                                                                            <div class="text-xs text-blue-700">
                                                                                {{ formatCurrency(option.unit_price) }}
                                                                                por {{
                                                                                option.unit }}
                                                                            </div>
                                                                        </div>
                                                                        <div class="text-right">
                                                                            <div
                                                                                class="text-sm font-semibold text-green-700">
                                                                                {{ formatCurrency(option.price) }}
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm text-gray-500">
                                                            {{ service.next_due_date ? formatDate(service.next_due_date,
                                                                'dd/MM/yyyy') :
                                                                'N/A' }}
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
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                                        {{ formatCurrency(service.billing_amount,
                                                            service.productPricing?.currency_code
                                                            || 'USD') }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <div class="flex items-center space-x-2">
                                                            <button @click="showServiceDetails(service)"
                                                                class="text-gray-500 hover:text-blue-700 p-1"
                                                                aria-label="Ver detalles del servicio"
                                                                title="Ver detalles">
                                                                <EyeIcon class="h-5 w-5" />
                                                            </button>

                                                            <template
                                                                v-if="service.status && service.status.toLowerCase() === 'active'">
                                                                <Link
                                                                    :href="route('client.services.showUpgradeDowngradeOptions', { service: service.id })"
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

                                                            <span
                                                                v-if="service.status && service.status.toLowerCase() === 'pending_cancellation'"
                                                                class="text-xs text-yellow-700 font-semibold">
                                                                Cancelación Pendiente
                                                            </span>

                                                            <!-- Fallback for other states where no specific actions are available -->
                                                            <span
                                                                v-else-if="service.status && !['active', 'suspended'].includes(service.status.toLowerCase()) && service.status.toLowerCase() !== 'pending_cancellation'"
                                                                class="text-xs text-gray-400">
                                                                ---
                                                            </span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- No services message -->
                                <div v-if="Object.keys(groupedServices).length === 0"
                                    class="py-6 text-center text-gray-500">
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
