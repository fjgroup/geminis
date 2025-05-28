<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextareaInput from '@/Components/TextareaInput.vue';
import InputError from '@/Components/InputError.vue';
import DangerButton from '@/Components/DangerButton.vue';


const props = defineProps({
    clients: Array, // [{id, name, email}, ...]
    possibleStatuses: Array, // ['unpaid', 'paid', 'cancelled']
    defaultCurrency: String,
    currencies: Array, // ['USD', 'EUR', ...]
    // errors: Object, // Inertia provides this
});

const form = useForm({
    client_id: null,
    issue_date: new Date().toISOString().slice(0, 10),
    due_date: new Date(new Date().setDate(new Date().getDate() + 30)).toISOString().slice(0, 10), // Default 30 days
    status: 'unpaid',
    currency_code: props.defaultCurrency || 'USD',
    notes_to_client: '',
    admin_notes: '',
    items: [
        { description: '', quantity: 1, unit_price: 0.00, taxable: false }
    ],
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

const addItem = () => {
    form.items.push({ description: '', quantity: 1, unit_price: 0.00, taxable: false });
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    } else {
        // Or show a message that at least one item is required
        alert('At least one item is required for an invoice.');
    }
};

const subtotal = computed(() => {
    return form.items.reduce((acc, item) => acc + (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0), 0);
});

const totalAmount = computed(() => {
    // For now, total is same as subtotal as tax logic is deferred
    return subtotal.value;
});

const formatCurrencyDisplay = (amount) => {
    if (amount === null || amount === undefined) return 'N/A';
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: form.currency_code || 'USD' }).format(amount);
};

const submit = () => {
    // Ensure numbers are correctly formatted before submission if backend expects numbers not strings
    const dataToSubmit = {
        ...form.data(),
        items: form.items.map(item => ({
            ...item,
            quantity: parseInt(item.quantity, 10) || 0,
            unit_price: parseFloat(item.unit_price) || 0.00,
        })),
    };
    // Use Inertia post with the transformed data
    form.transform(() => dataToSubmit).post(route('admin.invoices.store'), {
        // onFinish: () => form.reset('items'), // Or reset fully
    });
};

</script>

<template>
    <Head title="Create Manual Invoice" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Manual Invoice</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel for="client_id" value="Client" />
                            <SelectInput id="client_id" class="mt-1 block w-full" v-model="form.client_id" :options="clientOptions" required />
                            <InputError class="mt-2" :message="form.errors.client_id" />
                        </div>
                        <div>
                            <InputLabel for="currency_code" value="Currency" />
                            <SelectInput id="currency_code" class="mt-1 block w-full" v-model="form.currency_code" :options="currencyOptions" required />
                            <InputError class="mt-2" :message="form.errors.currency_code" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <InputLabel for="issue_date" value="Issue Date" />
                            <TextInput id="issue_date" type="date" class="mt-1 block w-full" v-model="form.issue_date" required />
                            <InputError class="mt-2" :message="form.errors.issue_date" />
                        </div>
                        <div>
                            <InputLabel for="due_date" value="Due Date" />
                            <TextInput id="due_date" type="date" class="mt-1 block w-full" v-model="form.due_date" required />
                            <InputError class="mt-2" :message="form.errors.due_date" />
                        </div>
                        <div>
                            <InputLabel for="status" value="Status" />
                            <SelectInput id="status" class="mt-1 block w-full" v-model="form.status" :options="statusOptions" required />
                            <InputError class="mt-2" :message="form.errors.status" />
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mt-6 mb-2">Invoice Items</h3>
                        <div v-for="(item, index) in form.items" :key="index" class="space-y-3 p-3 border rounded-md mb-3 bg-gray-50 relative">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                                <div class="md:col-span-5">
                                    <InputLabel :for="'item_description_' + index" value="Description" />
                                    <TextInput :id="'item_description_' + index" type="text" class="mt-1 block w-full" v-model="item.description" required />
                                    <InputError class="mt-2" :message="form.errors[`items.${index}.description`]" />
                                </div>
                                <div class="md:col-span-2">
                                    <InputLabel :for="'item_quantity_' + index" value="Qty" />
                                    <TextInput :id="'item_quantity_' + index" type="number" min="1" class="mt-1 block w-full" v-model.number="item.quantity" required />
                                    <InputError class="mt-2" :message="form.errors[`items.${index}.quantity`]" />
                                </div>
                                <div class="md:col-span-3">
                                    <InputLabel :for="'item_unit_price_' + index" value="Unit Price" />
                                    <TextInput :id="'item_unit_price_' + index" type="number" step="0.01" min="0" class="mt-1 block w-full" v-model.number="item.unit_price" required />
                                    <InputError class="mt-2" :message="form.errors[`items.${index}.unit_price`]" />
                                </div>
                                <div class="md:col-span-1 flex items-end justify-center pt-6">
                                     <label class="flex items-center">
                                        <input type="checkbox" v-model="item.taxable" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                        <span class="ml-2 text-sm text-gray-600">Tax?</span>
                                    </label>
                                </div>
                                <div class="md:col-span-1 flex items-end justify-center pt-6">
                                    <DangerButton type="button" @click="removeItem(index)" v-if="form.items.length > 1" class="p-2 leading-none">
                                        Del
                                    </DangerButton>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 text-right">Line Total: {{ formatCurrencyDisplay((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0)) }}</p>
                        </div>
                        <SecondaryButton type="button" @click="addItem" class="mt-2">Add Item</SecondaryButton>
                        <InputError class="mt-2" :message="form.errors.items" />
                    </div>

                    <div class="text-right mt-6 space-y-1">
                        <p class="text-lg font-semibold">Subtotal: {{ formatCurrencyDisplay(subtotal) }}</p>
                        <!-- Taxes would go here -->
                        <p class="text-xl font-bold">Total: {{ formatCurrencyDisplay(totalAmount) }}</p>
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

                    <div class="flex items-center justify-end mt-6 space-x-4">
                        <Link :href="route('admin.invoices.index')" class="text-sm text-gray-600 hover:text-gray-900">Cancel</Link>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Create Invoice
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
