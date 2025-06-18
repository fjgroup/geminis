<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import PaymentMethodForm from './_Form.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/Forms/Buttons/PrimaryButton.vue';

const props = defineProps({
    paymentMethod: Object,
    paymentMethodTypes: Object, // Passed from controller
});

const form = useForm({
    _method: 'PUT',
    name: props.paymentMethod.name,
    type: props.paymentMethod.type || 'bank', // Ensure type is initialized
    account_holder_name: props.paymentMethod.account_holder_name || '',
    identification_number: props.paymentMethod.identification_number || '',
    platform_name: props.paymentMethod.platform_name || '',
    email_address: props.paymentMethod.email_address || '',
    payment_link: props.paymentMethod.payment_link || '',
    account_number: props.paymentMethod.account_number || '',
    bank_name: props.paymentMethod.bank_name || '',
    branch_name: props.paymentMethod.branch_name || '',
    swift_code: props.paymentMethod.swift_code || '',
    iban: props.paymentMethod.iban || '',
    instructions: props.paymentMethod.instructions || '',
    is_active: props.paymentMethod.is_active, // Booleans usually have a default in DB or model
    logo_url: props.paymentMethod.logo_url || '',
});

const submit = () => {
    form.post(route('admin.payment-methods.update', { payment_method: props.paymentMethod.id }), {
        // onSuccess: () => { /* Optional: handle success */ },
    });
};
</script>

<template>
    <AdminLayout :title="`Editar Método de Pago: ${paymentMethod.name}`">
        <Head :title="`Editar Método de Pago: ${paymentMethod.name}`" />

        <template #header>
             <div class="flex items-center">
                <PrimaryButton @click="$inertia.visit(route('admin.payment-methods.index'))" class="mr-4">
                    <ArrowLeftIcon class="w-5 h-5" />
                </PrimaryButton>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Editar Método de Pago: <span class="italic">{{ paymentMethod.name }}</span>
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8"> {/* Adjusted max-width */}
                <div class="p-6 overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <PaymentMethodForm
                        :form="form"
                        @submit="submit"
                        :is-edit="true"
                        :paymentMethodTypes="props.paymentMethodTypes"
                        :paymentMethod="props.paymentMethod"
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
