<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import ClientServiceForm from './_Form.vue'; // Cambiar a _Form.vue


const props = defineProps({
    clientService: Object, // El servicio de cliente a editar
    products: Array,
    statusOptions: Array,
    // clients: Array, // No se pasan todos si se usa búsqueda asíncrona
    // resellers: Array, // No se pasan todos si se usa búsqueda asíncrona
    // errors: Object, // Los errores de validación vienen en form.errors
});



const form = useForm({
    _method: 'PUT', // Importante para la actualización
    client_id: props.clientService.client_id,
    product_id: props.clientService.product_id,
    billing_cycle_id: props.clientService.billing_cycle_id,
    billing_amount: props.clientService.billing_amount,
    registration_date: props.clientService.registration_date_formatted, // Usar la fecha formateada
    next_due_date: props.clientService.next_due_date_formatted, // Usar la fecha formateada
    status: props.clientService.status,
    domain_name: props.clientService.domain_name || '',
    username: props.clientService.username || '',
    password_encrypted: '', // Dejar vacío, el usuario lo llenará si quiere cambiarlo
    reseller_id: props.clientService.reseller_id,
    server_id: props.clientService.server_id,
    notes: props.clientService.notes || '',
    product_pricing_id: props.clientService.product_pricing_id,
    // Añade aquí cualquier otro campo que esté en tu _Form.vue y modelo ClientService
    // termination_date: props.clientService.termination_date_formatted, // Si lo tienes en el form
});




const submit = () => {
    // Para actualizar, se usa form.put o form.patch.
    // form.post es para crear nuevos recursos.
    form.put(route('admin.client-services.update', props.clientService.id), {
        // onSuccess: () => { /* Quizás no resetear en edición */ },
    });
};

</script>

<template>
    <AdminLayout :title="'Editar Servicio de Cliente #' + clientService.id">

        <Head :title="'Editar Servicio de Cliente #' + clientService.id" />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Editar Servicio de Cliente: {{ clientService.client?.name || '#' + clientService.id }}
                <span v-if="clientService.domain_name" class="text-base font-normal text-gray-500">
                    ({{ clientService.domain_name }})
                </span>
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="p-6 overflow-hidden bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg md:p-8">
                    <ClientServiceForm
                        :form="form"
                        :products="props.products"
                        :statusOptions="props.statusOptions"
                        :isEdit="true"
                        @submit="submit"
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
