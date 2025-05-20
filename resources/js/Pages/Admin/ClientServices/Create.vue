<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import ClientServiceForm from './_Form.vue'; // Importar el componente de formulario

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
    product_pricing_id: null,
    registration_date: new Date().toISOString().split('T')[0], // Fecha actual por defecto
    next_due_date: '',
    billing_amount: 0.00,
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

function calculateNextDueDate(registrationDateStr, billingCycleName) {
    if (!registrationDateStr || !billingCycleName) return '';
    const registrationDate = new Date(registrationDateStr);
    if (isNaN(registrationDate.getTime())) return ''; // Fecha de registro inválida

    let newDate = new Date(registrationDate);

    // Asegúrate que billingCycleName coincida con los valores que devuelve tu API
    // Por ejemplo, si SearchController devuelve 'Monthly', 'Annually', etc.
    switch (billingCycleName.toLowerCase()) {
        case 'monthly':
        case 'mensual':
            newDate.setMonth(newDate.getMonth() + 1);
            break;
        case 'quarterly':
        case 'trimestral':
            newDate.setMonth(newDate.getMonth() + 3);
            break;
        case 'semi_annually':
        case 'semestral':
        case 'semi-annually': // Considera variaciones
            newDate.setMonth(newDate.getMonth() + 6);
            break;
        case 'annually':
        case 'anual':
            newDate.setFullYear(newDate.getFullYear() + 1);
            break;
        case 'biennially':
        case 'bienal':
            newDate.setFullYear(newDate.getFullYear() + 2);
            break;
        case 'triennially':
        case 'trienal':
            newDate.setFullYear(newDate.getFullYear() + 3);
            break;
        case 'one_time':
        case 'pago único':
            // Para 'one_time', la fecha de vencimiento podría no aplicar o ser una fecha muy lejana.
            // Decide la lógica: podrías dejarla vacía, igual a la de registro, o una fecha específica.
            return ''; // Opcional: podrías retornar registrationDate.toISOString().split('T')[0]
        default:
            console.warn('Ciclo de facturación no reconocido:', billingCycleName);
            return ''; // Ciclo no reconocido
    }
    return newDate.toISOString().split('T')[0];
}

const handlePricingSelected = (selectedPricing) => {
    if (selectedPricing && typeof selectedPricing.price !== 'undefined' && selectedPricing.billing_cycle) {
        form.billing_amount = parseFloat(selectedPricing.price).toFixed(2);
        form.next_due_date = calculateNextDueDate(form.registration_date, selectedPricing.billing_cycle);
    } else {
        // Si no hay pricing seleccionado o no tiene los datos esperados, resetea.
        form.billing_amount = 0.00;
        form.next_due_date = '';
    }
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
                        @pricing-selected="handlePricingSelected"
                        @submit="submit"
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
```
