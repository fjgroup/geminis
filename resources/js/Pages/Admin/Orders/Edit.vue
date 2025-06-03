<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/Forms/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextareaInput from '@/Components/TextareaInput.vue'; // Assuming this exists

const props = defineProps({
    order: Object,
    possibleStatuses: Array, // Passed from OrderController@edit
    // errors: Object, // Inertia automatically provides errors
});

const form = useForm({
    status: props.order.status,
    notes: '', // For adding new notes, existing notes are part of props.order.notes
});

const statusOptions = props.possibleStatuses.map(status => ({ 
    value: status, 
    label: status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) 
}));

const submit = () => {
    form.put(route('admin.orders.update', props.order.id), {
        // preserveScroll: true, // Optional: preserve scroll on validation errors
        // onSuccess: () => { /* Optional: handle success if needed, often handled by controller redirect */ }
    });
};
</script>

<template>
    <Head :title="'Edit Order #' + order.order_number" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Order: {{ order.order_number }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Order Details</h3>
                            <p><strong>Client:</strong> {{ order.client?.name || 'N/A' }}</p>
                            <p><strong>Order Date:</strong> {{ new Date(order.order_date).toLocaleDateString() }}</p>
                            <p><strong>Current Status:</strong> {{ order.status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}</p>
                            <p><strong>Total Amount:</strong> {{ new Intl.NumberFormat('en-US', { style: 'currency', currency: order.currency_code || 'USD' }).format(order.total_amount) }}</p>
                            <div v-if="order.notes" class="mt-2">
                                <h4 class="font-medium">Existing Notes:</h4>
                                <pre class="text-sm bg-gray-100 p-2 rounded whitespace-pre-wrap">{{ order.notes }}</pre>
                            </div>
                        </div>

                        <form @submit.prevent="submit">
                            <div class="mt-4">
                                <InputLabel for="status" value="Order Status" />
                                <SelectInput 
                                    id="status" 
                                    class="mt-1 block w-full" 
                                    v-model="form.status" 
                                    :options="statusOptions" 
                                    required />
                                <InputError class="mt-2" :message="form.errors.status" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="notes" value="Add Admin Notes (optional, will be appended)" />
                                <TextareaInput 
                                    id="notes" 
                                    class="mt-1 block w-full" 
                                    v-model="form.notes" 
                                    rows="4" />
                                <InputError class="mt-2" :message="form.errors.notes" />
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('admin.orders.show', order.id)" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                    Cancel
                                </Link>
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Update Order
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
