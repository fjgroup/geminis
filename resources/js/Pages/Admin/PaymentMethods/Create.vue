<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import PaymentMethodForm from './_Form.vue'; // Adjusted path if necessary
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/PrimaryButton.vue'; // For the back button, or use Link

const form = useForm({
    name: '',
    account_holder_name: '',
    account_number: '',
    bank_name: '',
    branch_name: null,
    swift_code: null,
    iban: null,
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
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Crear Nuevo Método de Pago
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="p-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <PaymentMethodForm :form="form" @submit="submit" :is-edit="false" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
