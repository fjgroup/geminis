<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import ClientServiceForm from './_Form.vue'; // Importar el componente de formulario
import { computed } from 'vue';

const props = defineProps({
    products: Array, // [{ value: id, label: name }, ...]
    statusOptions: Array, // [{ value: 'status_val', label: 'Status Label' }, ...]
    // clients: Array, // No se pasan todos si se usa búsqueda asíncrona
    // resellers: Array, // No se pasan todos si se usa búsqueda asíncrona
    // errors: Object, // Los errores de validación vienen en form.errors
});

const form = useForm({
    client_id: null,
    product_id: null,
    billing_cycle_id:null,  //LO HE AGREGADO 21-05-2025
    billing_amount: 0.00,
    product_pricing_id: null,  //nose que es
    registration_date: new Date().toISOString().split('T')[0], // Fecha actual por defecto
    next_due_date: '',

    status: 'pending', // Estado por defecto
    domain_name: '',
    username: '',
    password_encrypted: '',
    reseller_id: null,
    server_id: null,
    notes: '',
});

const submit = () => {
    form.post(route('admin.client-services.store'), {
        // onSuccess: () => form.reset(), // Opcional: resetear el formulario
    });
};

</script>

<template>
    <AdminLayout title="Crear Servicio de Cliente">

        <Head title="Crear Nuevo Servicio de Cliente" />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Crear Nuevo Servicio de Cliente
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="p-6 overflow-hidden bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg md:p-8">
                    <ClientServiceForm
                        :form="form"
                        :products="props.products"
                        :statusOptions="props.statusOptions"
                        @submit="submit"
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
```
