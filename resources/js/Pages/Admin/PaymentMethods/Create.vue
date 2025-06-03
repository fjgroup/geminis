<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import PaymentMethodForm from './_Form.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/Forms/Buttons/PrimaryButton.vue';

const props = defineProps({
    paymentMethodTypes: Object, // Passed from controller
});

const defaultType = Object.keys(props.paymentMethodTypes)[0] || 'bank'; // Default to first type or 'bank'

const form = useForm({
    name: '',
    type: defaultType, // Initialize type
    account_holder_name: '', // Common, but might be more specific per type
    identification_number: '', // Bank-specific
    platform_name: '',       // Wallet/Crypto-specific
    email_address: '',       // Wallet-specific
    payment_link: '',        // Wallet-specific
    account_number: '',      // Bank/Crypto-specific (wallet address for crypto)
    bank_name: '',           // Bank-specific
    branch_name: null,       // Bank-specific
    swift_code: null,        // Bank-specific
    iban: null,              // Bank-specific
    instructions: null,
    is_active: true,
    logo_url: null,
});

const submit = () => {
    form.post(route('admin.payment-methods.store'), {
        // onSuccess: () => { /* Optional: handle success */ },
    });
};
</script>

<template>
    <AdminLayout title="Crear Método de Pago">
        <Head title="Crear Nuevo Método de Pago" />

        <template #header>
            <div class="flex items-center">
                 <PrimaryButton @click="$inertia.visit(route('admin.payment-methods.index'))" class="mr-4">
                    <ArrowLeftIcon class="w-5 h-5" />
                </PrimaryButton>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Crear Nuevo Método de Pago
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8"> {/* Adjusted max-width for better form layout */}
                <div class="p-6 overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <PaymentMethodForm
                        :form="form"
                        @submit="submit"
                        :is-edit="false"
                        :paymentMethodTypes="props.paymentMethodTypes"
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
