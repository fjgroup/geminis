<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Pagination from '@/Components/UI/Pagination.vue';
import PrimaryButton from '@/Components/Forms/Buttons/PrimaryButton.vue';
import SecondaryButton from '@/Components/Forms/Buttons/SecondaryButton.vue';
import DangerButton from '@/Components/Forms/Buttons/DangerButton.vue';
import { ref, watch } from 'vue';

import TextInput from '@/Components/Forms/TextInput.vue'; // O tu componente de input

import { PlusIcon, PencilSquareIcon, TrashIcon, EyeIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    clientServices: Object,
    filters: Object, // Recibe los filtros del controlador
});

const searchTerm = ref(props.filters.search || '');

// Para hacer la búsqueda con debounce (esperar a que el usuario deje de teclear)
let searchDebounceTimeout = null;
watch(searchTerm, (newValue) => {
    clearTimeout(searchDebounceTimeout);
    searchDebounceTimeout = setTimeout(() => {
        router.get(route('admin.client-services.index'), { search: newValue }, {
            preserveState: true,
            replace: true, // Evita entradas múltiples en el historial del navegador para la misma búsqueda
        });
    }, 500); // Espera 500ms
});

// TODO: Implementar funciones para editar y eliminar
const editService = (serviceId) => {
    router.get(route('admin.client-services.edit', serviceId));
};

const deleteService = (serviceId) => {
    if (confirm('¿Estás seguro de que deseas eliminar este servicio?')) {
        router.delete(route('admin.client-services.destroy', serviceId), {
            preserveScroll: true,
            // onSuccess: () => { /* Opcional: mostrar notificación */ },
            // onError: () => { /* Opcional: mostrar notificación */ }
        });
    }
};

const getAdminFriendlyServiceStatusText = (status) => {
    const mappings = {
        'pending': 'Pendiente',
        'active': 'Activo',
        'suspended': 'Suspendido',
        'terminated': 'Terminado',
        'cancelled': 'Cancelado',
        'fraud': 'Fraude',
        'pending_configuration': 'Pend. Configuración',
        'provisioning_failed': 'Fallo de Aprovisionamiento',
    };
    if (mappings[status]) {
        return mappings[status];
    }
    // Fallback for any other status
    return status ? status.charAt(0).toUpperCase() + status.slice(1).replace(/_/g, ' ') : 'N/A';
};
</script>

<template>
    <AdminLayout title="Servicios de Clientes">

        <Head title="Gestionar Servicios de Clientes" />

        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Servicios de Clientes
                </h2>
                <Link :href="route('admin.client-services.create')">
                <PrimaryButton class="flex items-center">
                    <PlusIcon class="w-5 h-5 mr-2" />
                    Crear Servicio
                </PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                        <div class="overflow-x-auto">

                            <!-- Campo de Búsqueda -->
                            <div class="mb-4">
                                <TextInput type="text" v-model="searchTerm"
                                    placeholder="Buscar por cliente, producto, dominio..."
                                    class="block w-full md:w-1/2" />
                            </div>

                            <!-- Tabla de Servicios de Clientes -->
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                            ID</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                            Cliente</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                            Producto</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                            Dominio</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                            Próx. Venc.</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                            Monto</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                            Estado</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                            Revendedor</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                            Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                    <tr v-if="clientServices.data.length === 0">
                                        <td colspan="9"
                                            class="px-6 py-4 text-sm text-center text-gray-500 whitespace-nowrap dark:text-gray-400">
                                            No se encontraron servicios de clientes.
                                        </td>
                                    </tr>
                                    <tr v-for="service in clientServices.data" :key="service.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td
                                            class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">
                                            {{ service.id }}</td>
                                        <td
                                            class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">
                                            {{ service.client_name }}</td>
                                        <td
                                            class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                                            {{ service.product_name }}</td>
                                        <td
                                            class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                                            {{ service.domain_name || 'N/A' }}</td>
                                        <td
                                            class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                                            {{ service.next_due_date_formatted }}</td>
                                        <td
                                            class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                                            {{ service.billing_amount }}</td>
                                        <td
                                            class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                                            <span class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full"
                                                :class="{
                                                    'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100': service.status === 'active',
                                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100': service.status === 'pending',
                                                    'bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-100': service.status === 'pending_configuration',
                                                    'bg-orange-100 text-orange-800 dark:bg-orange-700 dark:text-orange-100': service.status === 'suspended',
                                                    'bg-red-200 text-red-900 border border-red-400 dark:bg-red-800 dark:text-red-100 dark:border-red-600': service.status === 'provisioning_failed',
                                                    'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-200': service.status === 'terminated' || service.status === 'cancelled' || service.status === 'fraud',
                                                    'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200': !['active', 'pending', 'pending_configuration', 'suspended', 'terminated', 'cancelled', 'fraud', 'provisioning_failed'].includes(service.status)
                                                }">
                                                {{ getAdminFriendlyServiceStatusText(service.status) }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                                            {{ service.reseller_name }}</td>
                                        <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                            <!-- Botón Ver - Ingresar al panel del cliente -->
                                            <Link :href="route('admin.client-services.impersonate', service.id)"
                                                class="mr-2 text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                                title="Ingresar al panel del cliente">
                                            <EyeIcon class="inline-block w-5 h-5" />
                                            </Link>
                                            <!-- Botón Editar -->
                                            <button @click="editService(service.id)"
                                                class="mr-2 text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                title="Editar servicio">
                                                <PencilSquareIcon class="inline-block w-5 h-5" />
                                            </button>
                                            <!-- Botón Eliminar -->
                                            <button @click="deleteService(service.id)"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                title="Eliminar servicio">
                                                <TrashIcon class="inline-block w-5 h-5" />
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <Pagination class="mt-6" :links="clientServices.links" v-if="clientServices.data.length > 0" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
