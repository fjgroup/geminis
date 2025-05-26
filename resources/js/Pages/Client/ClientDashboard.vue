<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { defineProps } from 'vue';

const props = defineProps({
    clientServices: { // Cambiado para coincidir con el controlador

        type: Array,
        default: () => [],
    },
});
</script>

<template>

    <Head title="Panel del Cliente" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Panel del Cliente</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-6">
                            <h3 class="mb-4 text-lg font-medium text-gray-900">Navegación Rápida</h3>
                            <ul class="flex space-x-4">
                                <li>
                                    <Link :href="route('client.services.index')"
                                        class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-800 border border-transparent rounded-md hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Mis Servicios
                                    </Link>
                                </li>
                                <li>
                                    <!-- Enlace placeholder para futuras Órdenes -->
                                    <Link :href="route('client.orders.index')"
                                        class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-800 border border-transparent rounded-md hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Mis Órdenes
                                    </Link>
                                </li>
                                <li>
                                    <Link :href="route('client.invoices.index')"
                                        class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-800 border border-transparent rounded-md hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Mis Facturas
                                    </Link>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <h3 class="mb-4 text-lg font-medium text-gray-900">Mis Servicios Activos</h3>
                            <table v-if="clientServices && clientServices.length > 0">
                                <thead>
                                    <tr>
                                        <th>Nombre del producto</th>
                                        <th>Dominio</th>
                                        <th>Próxima Fecha de Vencimiento</th>
                                        <th>Estado</th>
                                        <th>Precio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="service in clientServices" :key="service.id">
                                        <td>{{ service.product?.name || service.product_name }}</td>
                                        <td>{{ service.domain_name || 'N/A' }}</td>
                                        <td>{{ service.next_due_date_formatted || service.next_due_date }}</td>
                                        <td>{{ service.status_display || service.status }}</td>
                                        <td>{{ service.billing_amount_formatted || service.billing_amount }} {{
                                            service.currency_code }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else>
                                <p>No tienes servicios activos en este momento.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Añadir estilos básicos si es necesario */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
th, td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}
th {
    background-color: #f2f2f2;
    color: #333;
    /* Color de texto más oscuro para encabezados */
}

td {
    color: #e5e7eb;
    /* Color de texto para modo oscuro, ajusta si es necesario */
}
</style>
