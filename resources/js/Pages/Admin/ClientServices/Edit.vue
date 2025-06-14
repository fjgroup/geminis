<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, useForm, router } from "@inertiajs/vue3"; // Added router
import { ref } from "vue"; // Add ref
import ClientServiceForm from "./_Form.vue"; // Cambiar a _Form.vue
import PrimaryButton from "@/Components/Forms/Buttons/PrimaryButton.vue"; // Added
import ConfirmManualPaymentModal from "@/Components/Admin/ConfirmManualPaymentModal.vue"; // Add this import

const props = defineProps({
    clientService: Object, // El servicio de cliente a editar
    products: Array,
    statusOptions: Array,
    paymentMethods: Array, // Add this line
    // clients: Array, // No se pasan todos si se usa búsqueda asíncrona
    // resellers: Array, // No se pasan todos si se usa búsqueda asíncrona
    // errors: Object, // Los errores de validación vienen en form.errors
});

const showConfirmPaymentModal = ref(false);

const form = useForm({
    _method: "PUT", // Importante para la actualización
    client_id: props.clientService.client_id,
    product_id: props.clientService.product_id,
    billing_cycle_id: props.clientService.billing_cycle_id,
    billing_amount: props.clientService.billing_amount,
    registration_date: props.clientService.registration_date_formatted, // Usar la fecha formateada
    next_due_date: props.clientService.next_due_date_formatted, // Usar la fecha formateada
    status: props.clientService.status,
    domain_name: props.clientService.domain_name || "",
    username: props.clientService.username || "",
    password_encrypted: "", // Dejar vacío, el usuario lo llenará si quiere cambiarlo
    reseller_id: props.clientService.reseller_id,
    server_id: props.clientService.server_id,
    notes: props.clientService.notes || "",
    product_pricing_id: props.clientService.product_pricing_id,
    // Añade aquí cualquier otro campo que esté en tu _Form.vue y modelo ClientService
    // termination_date: props.clientService.termination_date_formatted, // Si lo tienes en el form
});

const submit = () => {
    // Para actualizar, se usa form.put o form.patch.
    // form.post es para crear nuevos recursos.
    form.put(route("admin.client-services.update", props.clientService.id), {
        // onSuccess: () => { /* Quizás no resetear en edición */ },
    });
};

const confirmRetryProvisioning = () => {
    if (
        window.confirm(
            "¿Estás seguro de que quieres reintentar el aprovisionamiento para este servicio?"
        )
    ) {
        router.post(
            route("admin.client-services.retryProvisioning", props.clientService.id),
            {},
            {
                preserveScroll: true,
                // onSuccess: () => {
                //     // Optional: force a reload or expect Inertia to update props
                //     // router.reload({ only: ['clientService'] });
                // }
            }
        );
    }
};
</script>

<template>
    <AdminLayout :title="'Editar Servicio de Cliente #' + clientService.id">

        <Head :title="'Editar Servicio de Cliente #' + clientService.id" />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Editar Servicio de Cliente:
                {{ clientService.client?.name || "#" + clientService.id }}
                <span v-if="clientService.domain_name" class="text-base font-normal text-gray-500">
                    ({{ clientService.domain_name }})
                </span>
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="p-6 overflow-hidden bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg md:p-8">
                    <ClientServiceForm :form="form" :products="props.products" :statusOptions="props.statusOptions"
                        :isEdit="true" @submit="submit" />

                    <!-- Retry Provisioning Button Section -->
                    <div v-if="clientService.status === 'provisioning_failed'"
                        class="pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
                        <div
                            class="p-4 border border-yellow-300 rounded-md bg-yellow-50 dark:bg-gray-700/50 dark:border-yellow-600">
                            <p class="mb-3 text-sm text-yellow-700 dark:text-yellow-300">
                                Este servicio falló durante el último intento de aprovisionamiento
                                automático. Puedes revisar las notas del servicio para más detalles sobre
                                el error.
                            </p>
                            <PrimaryButton @click="confirmRetryProvisioning"
                                class="bg-orange-500 hover:bg-orange-600 focus:ring-orange-400">
                                Reintentar Aprovisionamiento
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
                <!-- Modal de Confirmación de Pago Manual -->
                <ConfirmManualPaymentModal :invoiceId="clientService.id" :paymentMethods="paymentMethods"
                    :showModal="showConfirmPaymentModal" @close="showConfirmPaymentModal = false" @paymentConfirmed="
                        () => {
                            showConfirmPaymentModal = false; /* Opcional: Recargar datos del servicio */
                        }
                    " />
            </div>
        </div>
    </AdminLayout>
</template>
