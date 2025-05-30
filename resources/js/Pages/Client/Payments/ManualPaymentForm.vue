<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import SelectInput from '@/Components/SelectInput.vue'; // Assuming this component exists for consistency
import { computed, ref, watch } from 'vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';


const props = defineProps({
  invoice: Object,
  paymentMethods: Array,
  errors: Object, // For displaying general errors if any, or handled by form.errors
});

const form = useForm({
    payment_method_id: null,
    reference_number: '',
    payment_date: '',
});

const paymentMethodOptions = computed(() => {
    return [
        { value: null, label: '-- Seleccione un método de pago --', disabled: true },
        ...props.paymentMethods.map(method => ({
            value: method.id,
            label: method.name,
        })),
    ];
});

const selectedPaymentMethod = ref(null);

watch(() => form.payment_method_id, (newId) => {
    if (newId) {
        selectedPaymentMethod.value = props.paymentMethods.find(pm => pm.id === newId);
    } else {
        selectedPaymentMethod.value = null;
    }
});

const submitForm = () => {
    form.post(route('client.invoices.manualPayment.store', { invoice: props.invoice.id }), {
        onSuccess: () => {
            form.reset('reference_number', 'payment_date');
            // selectedPaymentMethod.value = null; // Keep selected method visible after successful submit for now
        },
        // onError: (errors) => { /* Global errors can be handled here if needed */ }
    });
};

const formatCurrency = (amount, currencyCode = 'USD') => {
    if (amount === null || amount === undefined) return 'N/A';
    const displayCurrency = currencyCode || 'USD';
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: displayCurrency }).format(amount);
};

// Get today's date in YYYY-MM-DD format for the max attribute of date input
const today = new Date().toISOString().split('T')[0];

</script>

<template>
  <AuthenticatedLayout>
    <Head title="Registrar Pago Manual" />

    <template #header>
        <div class="flex items-center">
            <Link :href="route('client.invoices.show', props.invoice.id)" class="mr-4 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                <ArrowLeftIcon class="w-6 h-6" />
            </Link>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Registrar Pago Manual para Factura #{{ invoice.invoice_number }}
            </h2>
        </div>
    </template>

    <div class="py-12">
      <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <form @submit.prevent="submitForm" class="p-6 space-y-6">
            <div>
              <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Detalles de la Factura</h3>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Monto a Pagar:
                <span class="font-semibold">{{ formatCurrency(invoice.total_amount, invoice.currency_code) }}</span>
              </p>
            </div>

            <hr class="dark:border-gray-700">

            <div>
              <InputLabel for="payment_method_id" value="Método de Pago Utilizado *" />
              <SelectInput
                id="payment_method_id"
                class="mt-1 block w-full"
                v-model="form.payment_method_id"
                :options="paymentMethodOptions"
                required
              />
              <InputError class="mt-2" :message="form.errors.payment_method_id" />
            </div>

            <div v-if="selectedPaymentMethod && selectedPaymentMethod.instructions" class="p-4 mt-4 bg-gray-50 dark:bg-gray-700 rounded-md">
              <h4 class="font-semibold text-gray-800 dark:text-gray-200">Instrucciones para {{ selectedPaymentMethod.name }}:</h4>
              <div class="mt-2 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line" v-html="selectedPaymentMethod.instructions"></div>
            </div>
             <div v-else-if="selectedPaymentMethod && !selectedPaymentMethod.instructions" class="p-4 mt-4 bg-gray-50 dark:bg-gray-700 rounded-md">
                 <h4 class="font-semibold text-gray-800 dark:text-gray-200">Instrucciones para {{ selectedPaymentMethod.name }}:</h4>
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">No hay instrucciones específicas para este método de pago.</p>
            </div>


            <div>
              <InputLabel for="reference_number" value="Número de Referencia / ID de Transacción *" />
              <TextInput
                id="reference_number"
                type="text"
                class="mt-1 block w-full"
                v-model="form.reference_number"
                required
                autocomplete="off"
              />
              <InputError class="mt-2" :message="form.errors.reference_number" />
            </div>

            <div>
              <InputLabel for="payment_date" value="Fecha de Pago *" />
              <TextInput
                id="payment_date"
                type="date"
                class="mt-1 block w-full"
                v-model="form.payment_date"
                required
                :max="today" 
              />
              <InputError class="mt-2" :message="form.errors.payment_date" />
            </div>
            
            <!-- Placeholder for future file upload
            <div>
              <InputLabel for="payment_receipt" value="Comprobante de Pago (Opcional)" />
              <input type="file" @input="form.payment_receipt = $event.target.files[0]" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
              <progress v-if="form.progress" :value="form.progress.percentage" max="100">
                {{ form.progress.percentage }}%
              </progress>
              <InputError class="mt-2" :message="form.errors.payment_receipt" />
            </div>
            -->

            <div class="flex items-center justify-end space-x-4">
              <Link :href="route('client.invoices.show', props.invoice.id)" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                Volver a la Factura
              </Link>
              <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Enviar Información de Pago
              </PrimaryButton>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
