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

// Renamed from selectedPaymentMethod to selectedMethodDetails as per plan
const selectedMethodDetails = computed(() => {
    if (!form.payment_method_id) return null;
    const method = props.paymentMethods.find(m => m.id === form.payment_method_id);
    // formatted_details should be directly available on the method object due to $appends
    return method ? method.formatted_details : null;
});

const submitForm = () => {
    form.post(route('client.funds.store'), {
        onSuccess: () => {
            form.reset('amount', 'reference_number', 'payment_date', 'payment_method_id');
            // selectedMethodDetails.value = null; // This is a computed prop, will update when form.payment_method_id resets
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

            <!-- Display Formatted Payment Method Details -->
            <div v-if="selectedMethodDetails" class="mt-4 p-4 border border-gray-200 dark:border-gray-700 rounded bg-gray-50 dark:bg-gray-700/50">
                <h4 class="font-semibold text-gray-700 dark:text-gray-200">Detalles para {{ selectedMethodDetails.name }}:</h4>
                <div class="text-sm text-gray-600 dark:text-gray-300 mt-2 space-y-1">
                    <p v-if="selectedMethodDetails.type === 'bank'">
                        <strong class="text-gray-800 dark:text-gray-100">Banco:</strong> {{ selectedMethodDetails.bank_name }}<br>
                        <strong class="text-gray-800 dark:text-gray-100">Nro. Cuenta:</strong> {{ selectedMethodDetails.account_number }}<br>
                        <strong class="text-gray-800 dark:text-gray-100">Titular:</strong> {{ selectedMethodDetails.account_holder_name }}<br>
                        <span v-if="selectedMethodDetails.identification_number"><strong class="text-gray-800 dark:text-gray-100">Cédula/RIF:</strong> {{ selectedMethodDetails.identification_number }}<br></span>
                        <span v-if="selectedMethodDetails.swift_code"><strong class="text-gray-800 dark:text-gray-100">SWIFT:</strong> {{ selectedMethodDetails.swift_code }}<br></span>
                        <span v-if="selectedMethodDetails.iban"><strong class="text-gray-800 dark:text-gray-100">IBAN:</strong> {{ selectedMethodDetails.iban }}<br></span>
                        <span v-if="selectedMethodDetails.branch_name"><strong class="text-gray-800 dark:text-gray-100">Sucursal:</strong> {{ selectedMethodDetails.branch_name }}</span>
                    </p>
                    <p v-if="selectedMethodDetails.type === 'wallet' || selectedMethodDetails.type === 'paypal_manual'">
                        <strong class="text-gray-800 dark:text-gray-100">Plataforma:</strong> {{ selectedMethodDetails.platform_name }}<br>
                        <span v-if="selectedMethodDetails.email_address"><strong class="text-gray-800 dark:text-gray-100">Email:</strong> {{ selectedMethodDetails.email_address }}<br></span>
                        <span v-if="selectedMethodDetails.account_holder_name"><strong class="text-gray-800 dark:text-gray-100">Titular/Usuario:</strong> {{ selectedMethodDetails.account_holder_name }}<br></span>
                        <span v-if="selectedMethodDetails.payment_link"><strong class="text-gray-800 dark:text-gray-100">Enlace de Pago:</strong> <a :href="selectedMethodDetails.payment_link" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ selectedMethodDetails.payment_link }}</a></span>
                    </p>
                    <p v-if="selectedMethodDetails.type === 'crypto_wallet'">
                        <strong class="text-gray-800 dark:text-gray-100">Red/Moneda:</strong> {{ selectedMethodDetails.platform_name }}<br>
                        <strong class="text-gray-800 dark:text-gray-100">Dirección:</strong> {{ selectedMethodDetails.wallet_address }} <span v-if="selectedMethodDetails.crypto_network"> (Red: {{ selectedMethodDetails.crypto_network }})</span><br>
                        <span v-if="selectedMethodDetails.account_holder_name"><strong class="text-gray-800 dark:text-gray-100">Referencia/Titular:</strong> {{ selectedMethodDetails.account_holder_name }}</span>
                    </p>
                    <p v-if="selectedMethodDetails.instructions" class="mt-2 whitespace-pre-wrap border-t border-gray-300 dark:border-gray-600 pt-2">
                        <strong class="text-gray-800 dark:text-gray-100">Instrucciones Adicionales:</strong><br>{{ selectedMethodDetails.instructions }}
                    </p>
                     <p v-if="!selectedMethodDetails.instructions && selectedMethodDetails.type !== 'bank' && selectedMethodDetails.type !== 'wallet' && selectedMethodDetails.type !== 'paypal_manual' && selectedMethodDetails.type !== 'crypto_wallet'" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        No hay detalles específicos adicionales para este método de pago.
                    </p>
                </div>
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
