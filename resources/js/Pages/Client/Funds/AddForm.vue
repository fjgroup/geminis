<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import SelectInput from '@/Components/SelectInput.vue';
import { computed, ref, watch } from 'vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  paymentMethods: Array,
  currencyCode: String,
  errors: Object, // For displaying general errors if any, or handled by form.errors
});

const form = useForm({
    amount: '',
    payment_method_id: null,
    reference_number: '',
    payment_date: '',
    // payment_receipt: null, // For optional file upload later
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
    form.post(route('client.funds.store'), {
        onSuccess: () => {
            form.reset('amount', 'reference_number', 'payment_date', 'payment_method_id');
            // selectedPaymentMethod.value = null; // Reset this as well if form.payment_method_id is reset
        },
    });
};

// Get today's date in YYYY-MM-DD format for the max attribute of date input
const today = new Date().toISOString().split('T')[0];

</script>

<template>
  <AuthenticatedLayout>
    <Head title="Agregar Fondos" />

    <template #header>
        <div class="flex items-center">
            <Link :href="route('client.dashboard')" class="mr-4 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                <ArrowLeftIcon class="w-6 h-6" />
            </Link>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Agregar Fondos a tu Cuenta
            </h2>
        </div>
    </template>

    <div class="py-12">
      <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <form @submit.prevent="submitForm" class="p-6 space-y-6">
            
            <div>
              <InputLabel for="amount">Monto a Agregar ({{ currencyCode }}) *</InputLabel>
              <TextInput
                id="amount"
                type="number"
                class="mt-1 block w-full"
                v-model="form.amount"
                step="0.01"
                min="0.01" 
                required
                autofocus
              />
              <InputError class="mt-2" :message="form.errors.amount" />
            </div>

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
              <Link :href="route('client.dashboard')" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                Volver al Dashboard
              </Link>
              <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Enviar Solicitud de Fondos
              </PrimaryButton>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
