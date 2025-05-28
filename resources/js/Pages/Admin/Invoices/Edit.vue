<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextareaInput from '@/Components/TextareaInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    invoice: Object, // The invoice to edit
    clients: Array, // [{id, name, email}, ...]
    possibleStatuses: Array, // ['unpaid', 'paid', ...]
    currencies: Array, // ['USD', 'EUR', ...]
    // errors: Object, // Inertia provides this
});

const form = useForm({
    _method: 'PUT', // Important for PUT requests with Inertia forms
    client_id: props.invoice.client_id,
    issue_date: props.invoice.issue_date ? new Date(props.invoice.issue_date).toISOString().slice(0, 10) : null,
    due_date: props.invoice.due_date ? new Date(props.invoice.due_date).toISOString().slice(0, 10) : null,
    paid_date: props.invoice.paid_date ? new Date(props.invoice.paid_date).toISOString().slice(0, 10) : null,
    status: props.invoice.status,
    currency_code: props.invoice.currency_code,
    notes_to_client: props.invoice.notes_to_client || '',
    admin_notes: props.invoice.admin_notes || '',
    // Line items are not editable in this form
});

const clientOptions = props.clients.map(client => ({
    value: client.id,
    label: `${client.name} (${client.email})`
}));

const statusOptions = props.possibleStatuses.map(status => ({
    value: status,
    label: status.charAt(0).toUpperCase() + status.slice(1).replace(/_/g, ' ')
}));

const currencyOptions = props.currencies.map(currency => ({
    value: currency,
    label: currency
}));

// Helper for currency formatting
const formatCurrencyDisplay = (amount, currencyCode) => {
    if (amount === null || amount === undefined) return 'N/A';
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: currencyCode || 'USD' }).format(amount);
};

const submit = () => {
    form.post(route('admin.invoices.update', props.invoice.id), { // .post because _method: 'PUT'
        // preserveScroll: true,
        // onSuccess: () => { /* Controller redirects */ }
    });
};
</script>

<template>
    <Head :title="'Edit Invoice #' + invoice.invoice_number" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Invoice: {{ invoice.invoice_number }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel for="client_id" value="Client" />
                            <SelectInput id="client_id" class="mt-1 block w-full" v-model="form.client_id" :options="clientOptions" />
                            <InputError class="mt-2" :message="form.errors.client_id" />
                            <p class="text-xs text-gray-500 mt-1">Caution: Changing client on an existing invoice can have side effects.</p>
                        </div>
                        <div>
                            <InputLabel for="currency_code" value="Currency" />
                            <SelectInput id="currency_code" class="mt-1 block w-full" v-model="form.currency_code" :options="currencyOptions" />
                            <InputError class="mt-2" :message="form.errors.currency_code" />
                            <p class="text-xs text-gray-500 mt-1">Caution: Changing currency on an invoice with transactions is not advised.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <InputLabel for="issue_date" value="Issue Date" />
                            <TextInput id="issue_date" type="date" class="mt-1 block w-full" v-model="form.issue_date" />
                            <InputError class="mt-2" :message="form.errors.issue_date" />
                        </div>
                        <div>
                            <InputLabel for="due_date" value="Due Date" />
                            <TextInput id="due_date" type="date" class="mt-1 block w-full" v-model="form.due_date" />
                            <InputError class="mt-2" :message="form.errors.due_date" />
                        </div>
                         <div>
                            <InputLabel for="paid_date" value="Paid Date (if status is 'Paid')" />
                            <TextInput id="paid_date" type="date" class="mt-1 block w-full" v-model="form.paid_date" :disabled="form.status !== 'paid'" />
                            <InputError class="mt-2" :message="form.errors.paid_date" />
                        </div>
                    </div>
                     <div>
                        <InputLabel for="status" value="Status" />
                        <SelectInput id="status" class="mt-1 block w-full" v-model="form.status" :options="statusOptions" />
                        <InputError class="mt-2" :message="form.errors.status" />
                    </div>

                    <div>
                        <InputLabel for="notes_to_client" value="Notes to Client (Optional)" />
                        <TextareaInput id="notes_to_client" class="mt-1 block w-full" v-model="form.notes_to_client" rows="3" />
                        <InputError class="mt-2" :message="form.errors.notes_to_client" />
                    </div>
                    <div>
                        <InputLabel for="admin_notes" value="Admin Notes (Optional, internal)" />
                        <TextareaInput id="admin_notes" class="mt-1 block w-full" v-model="form.admin_notes" rows="3" />
                        <InputError class="mt-2" :message="form.errors.admin_notes" />
                    </div>

                    <div class="mt-6 border-t pt-6">
                        <h4 class="text-md font-medium text-gray-700 mb-2">Invoice Items (Read-only for now)</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="item in invoice.items" :key="item.id">
                                        <td class="px-4 py-3 text-sm">{{ item.description }}</td>
                                        <td class="px-4 py-3 text-sm">{{ item.quantity }}</td>
                                        <td class="px-4 py-3 text-sm">{{ formatCurrencyDisplay(item.unit_price, invoice.currency_code) }}</td>
                                        <td class="px-4 py-3 text-sm">{{ formatCurrencyDisplay(item.total_price, invoice.currency_code) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-6 space-x-4">
                        <Link :href="route('admin.invoices.show', invoice.id)" class="text-sm text-gray-600 hover:text-gray-900">Cancel</Link>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Update Invoice
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
