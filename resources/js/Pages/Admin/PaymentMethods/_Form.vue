<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextareaInput from '@/Components/TextareaInput.vue';
import SelectInput from '@/Components/SelectInput.vue'; // Added
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { Link } from '@inertiajs/vue3';
import { ArrowDownTrayIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { computed } from 'vue'; // Added

const props = defineProps({
    form: Object, // El objeto useForm de Inertia
    isEdit: {
        type: Boolean,
        default: false,
    },
    paymentMethodTypes: { // Added
        type: Object,
        required: true,
    }
});

const emit = defineEmits(['submit']);

const submitForm = () => {
    emit('submit');
};

const typeOptions = computed(() => {
    return Object.entries(props.paymentMethodTypes).map(([value, label]) => ({ value, label }));
});

</script>

<template>
    <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Common Fields -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <InputLabel for="name" value="Nombre Descriptivo del Método *" />
                <TextInput id="name" type="text" class="block w-full mt-1" v-model="form.name" required autofocus />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>
            <div>
                <InputLabel for="type" value="Tipo de Método de Pago *" />
                <SelectInput id="type" class="block w-full mt-1" v-model="form.type" :options="typeOptions" required />
                <InputError class="mt-2" :message="form.errors.type" />
            </div>
        </div>

        <!-- Type-Specific Fields -->
        <div class="p-4 border border-gray-200 rounded-md dark:border-gray-700">
            <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Detalles Específicos del Tipo: {{ paymentMethodTypes[form.type] }}</h3>
            
            <!-- Bank Type Fields -->
            <div v-if="form.type === 'bank'" class="space-y-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <InputLabel for="bank_name" value="Nombre del Banco *" />
                        <TextInput id="bank_name" type="text" class="block w-full mt-1" v-model="form.bank_name" />
                        <InputError class="mt-2" :message="form.errors.bank_name" />
                    </div>
                    <div>
                        <InputLabel for="account_holder_name_bank" value="Titular de la Cuenta Bancaria *" />
                        <TextInput id="account_holder_name_bank" type="text" class="block w-full mt-1" v-model="form.account_holder_name" />
                        <InputError class="mt-2" :message="form.errors.account_holder_name" />
                    </div>
                    <div>
                        <InputLabel for="account_number_bank" value="Número de Cuenta Bancaria *" />
                        <TextInput id="account_number_bank" type="text" class="block w-full mt-1" v-model="form.account_number" />
                        <InputError class="mt-2" :message="form.errors.account_number" />
                    </div>
                    <div>
                        <InputLabel for="identification_number" value="Cédula/RIF del Titular (Opcional)" />
                        <TextInput id="identification_number" type="text" class="block w-full mt-1" v-model="form.identification_number" />
                        <InputError class="mt-2" :message="form.errors.identification_number" />
                    </div>
                    <div>
                        <InputLabel for="branch_name" value="Sucursal (Opcional)" />
                        <TextInput id="branch_name" type="text" class="block w-full mt-1" v-model="form.branch_name" />
                        <InputError class="mt-2" :message="form.errors.branch_name" />
                    </div>
                    <div>
                        <InputLabel for="swift_code" value="SWIFT/BIC (Opcional)" />
                        <TextInput id="swift_code" type="text" class="block w-full mt-1" v-model="form.swift_code" />
                        <InputError class="mt-2" :message="form.errors.swift_code" />
                    </div>
                    <div>
                        <InputLabel for="iban" value="IBAN (Opcional)" />
                        <TextInput id="iban" type="text" class="block w-full mt-1" v-model="form.iban" />
                        <InputError class="mt-2" :message="form.errors.iban" />
                    </div>
                </div>
            </div>

            <!-- Wallet Type Fields -->
            <div v-if="form.type === 'wallet' || form.type === 'paypal_manual'" class="space-y-6">
                 <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <InputLabel for="platform_name_wallet" value="Nombre de la Plataforma/Billetera *" />
                        <TextInput id="platform_name_wallet" type="text" class="block w-full mt-1" v-model="form.platform_name" />
                        <InputError class="mt-2" :message="form.errors.platform_name" />
                    </div>
                    <div>
                        <InputLabel for="account_holder_name_wallet" value="Nombre/ID del Titular en la Billetera *" />
                        <TextInput id="account_holder_name_wallet" type="text" class="block w-full mt-1" v-model="form.account_holder_name" />
                        <InputError class="mt-2" :message="form.errors.account_holder_name" />
                    </div>
                    <div>
                        <InputLabel for="email_address_wallet" value="Email Asociado (Opcional)" />
                        <TextInput id="email_address_wallet" type="email" class="block w-full mt-1" v-model="form.email_address" />
                        <InputError class="mt-2" :message="form.errors.email_address" />
                    </div>
                    <div>
                        <InputLabel for="payment_link_wallet" value="Enlace de Pago (ej: PayPal.me) (Opcional)" />
                        <TextInput id="payment_link_wallet" type="url" class="block w-full mt-1" v-model="form.payment_link" />
                        <InputError class="mt-2" :message="form.errors.payment_link" />
                    </div>
                </div>
            </div>

            <!-- Crypto Wallet Type Fields -->
            <div v-if="form.type === 'crypto_wallet'" class="space-y-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <InputLabel for="platform_name_crypto" value="Nombre de la Criptomoneda/Red *" />
                        <TextInput id="platform_name_crypto" type="text" class="block w-full mt-1" v-model="form.platform_name" />
                        <InputError class="mt-2" :message="form.errors.platform_name" />
                    </div>
                    <div>
                        <InputLabel for="account_number_crypto" value="Dirección de la Billetera *" />
                        <TextInput id="account_number_crypto" type="text" class="block w-full mt-1" v-model="form.account_number" />
                        <InputError class="mt-2" :message="form.errors.account_number" />
                    </div>
                     <div>
                        <InputLabel for="account_holder_name_crypto" value="Nombre del Titular (Opcional)" />
                        <TextInput id="account_holder_name_crypto" type="text" class="block w-full mt-1" v-model="form.account_holder_name" />
                        <InputError class="mt-2" :message="form.errors.account_holder_name" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Common Fields Continued -->
        <div>
            <InputLabel for="logo_url" value="URL del Logo (Opcional)" />
            <TextInput id="logo_url" type="url" class="block w-full mt-1" v-model="form.logo_url" placeholder="https://example.com/logo.png"/>
            <InputError class="mt-2" :message="form.errors.logo_url" />
        </div>

        <div>
            <InputLabel for="instructions" value="Instrucciones Adicionales (Opcional)" />
            <TextareaInput id="instructions" class="block w-full mt-1" v-model="form.instructions" :rows="4" 
                           placeholder="Ej: Por favor, incluya el número de factura en la referencia del pago."/>
            <InputError class="mt-2" :message="form.errors.instructions" />
        </div>

        <div class="block mt-4">
            <label class="flex items-center">
                <Checkbox name="is_active" v-model:checked="form.is_active" />
                <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">Activo</span>
            </label>
            <InputError class="mt-2" :message="form.errors.is_active" />
        </div>

        <div class="flex items-center justify-end mt-6 space-x-3">
            <Link :href="route('admin.payment-methods.index')"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <XMarkIcon class="w-5 h-5 mr-1 -ml-1" />
                Cancelar
            </Link>
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="inline-flex items-center">
                <ArrowDownTrayIcon class="w-5 h-5 mr-2 -ml-1" />
                {{ isEdit ? 'Actualizar Método de Pago' : 'Crear Método de Pago' }}
            </PrimaryButton>
        </div>
    </form>
</template>
