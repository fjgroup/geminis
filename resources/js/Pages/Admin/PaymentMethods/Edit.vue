<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import PaymentMethodForm from './_Form.vue'; // Adjusted path if necessary
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/PrimaryButton.vue'; // For the back button, or use Link

const props = defineProps({
    paymentMethod: Object,
});

const form = useForm({
    _method: 'PUT', // Important for PUT requests with Inertia when not using router.put
    name: props.paymentMethod.name,
    account_holder_name: props.paymentMethod.account_holder_name,
    account_number: props.paymentMethod.account_number,
    bank_name: props.paymentMethod.bank_name,
    branch_name: props.paymentMethod.branch_name,
    swift_code: props.paymentMethod.swift_code,
    iban: props.paymentMethod.iban,
    instructions: props.paymentMethod.instructions,
    is_active: props.paymentMethod.is_active,
    logo_url: props.paymentMethod.logo_url,
});

const submit = () => {
    form.post(route('admin.payment-methods.update', { payment_method: props.paymentMethod.id }), {
        // Inertia automatically picks up the _method: 'PUT' for a POST request
        // Or you can use router.put:
        // router.put(route('admin.payment-methods.update', { payment_method: props.paymentMethod.id }), form.data())
        // onSuccess: () => { /* Optional: handle success */ },
    });
};
</script>

<template>
    <AdminLayout title="Editar Método de Pago">
        <Head :title="`Editar Método de Pago: ${paymentMethod.name}`" />

        <template #header>
             <div class="flex items-center">
                <PrimaryButton @click="$inertia.visit(route('admin.payment-methods.index'))" class="mr-4">
                    <ArrowLeftIcon class="w-5 h-5" />
                </PrimaryButton>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Editar Método de Pago: <span class="italic">{{ paymentMethod.name }}</span>
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="p-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <PaymentMethodForm :form="form" @submit="submit" :is-edit="true" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
