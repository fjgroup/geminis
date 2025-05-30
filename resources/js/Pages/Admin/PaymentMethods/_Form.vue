<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextareaInput from '@/Components/TextareaInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { Link } from '@inertiajs/vue3';
import { ArrowDownTrayIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    form: Object, // El objeto useForm de Inertia
    isEdit: {
        type: Boolean,
        default: false,
    }
});

const emit = defineEmits(['submit']);

const submitForm = () => {
    emit('submit');
};
</script>

<template>
    <form @submit.prevent="submitForm" class="space-y-6">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <InputLabel for="name" value="Nombre del Método" />
                <TextInput id="name" type="text" class="block w-full mt-1" v-model="form.name" required autofocus />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="bank_name" value="Nombre del Banco" />
                <TextInput id="bank_name" type="text" class="block w-full mt-1" v-model="form.bank_name" required />
                <InputError class="mt-2" :message="form.errors.bank_name" />
            </div>

            <div>
                <InputLabel for="account_holder_name" value="Titular de la Cuenta" />
                <TextInput id="account_holder_name" type="text" class="block w-full mt-1" v-model="form.account_holder_name" required />
                <InputError class="mt-2" :message="form.errors.account_holder_name" />
            </div>

            <div>
                <InputLabel for="account_number" value="Número de Cuenta" />
                <TextInput id="account_number" type="text" class="block w-full mt-1" v-model="form.account_number" required />
                <InputError class="mt-2" :message="form.errors.account_number" />
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
            
            <div>
                <InputLabel for="logo_url" value="URL del Logo (Opcional)" />
                <TextInput id="logo_url" type="url" class="block w-full mt-1" v-model="form.logo_url" />
                <InputError class="mt-2" :message="form.errors.logo_url" />
            </div>
        </div>

        <div>
            <InputLabel for="instructions" value="Instrucciones de Pago (Opcional)" />
            <TextareaInput id="instructions" class="block w-full mt-1" v-model="form.instructions" :rows="4" />
            <InputError class="mt-2" :message="form.errors.instructions" />
        </div>

        <div class="block mt-4">
            <label class="flex items-center">
                <Checkbox name="is_active" v-model:checked="form.is_active" />
                <span class="ml-2 text-sm text-gray-600">Activo</span>
            </label>
            <InputError class="mt-2" :message="form.errors.is_active" />
        </div>

        <div class="flex items-center justify-end mt-6 space-x-3">
            <Link :href="route('admin.payment-methods.index')"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
