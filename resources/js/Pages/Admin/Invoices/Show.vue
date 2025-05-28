<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import SelectInput from '@/Components/SelectInput.vue'; // Assumed to exist now

const props = defineProps({
    invoice: {
        type: Object,
        required: true,
    },
    // flash: Object, // For displaying success/error messages from session
});

const paymentForm = useForm({
    // invoice_id is part of the route, not the form data submitted
    transaction_date: new Date().toISOString().slice(0, 10), // Default to today
    amount: null,
    currency_code: props.invoice.currency_code || 'USD',
    gateway_slug: 'manual_payment',
    type: 'payment',
    status: 'completed',
    description: '',
    fees_amount: null,
    gateway_transaction_id: null,
});

const submitPayment = () => {
    paymentForm.post(route('admin.invoices.transactions.store', props.invoice.id), {
        preserveScroll: true,
        onSuccess: () => {
            paymentForm.reset();
            // Consider using Inertia's shared flash messages for success notifications
            // if not automatically handled by AdminLayout or similar.
            // For example, if `props.flash.success` is set by the backend:
            // if (props.flash && props.flash.success) {
            //  alert(props.flash.success); // Or a more sophisticated notification
            // }
        },
        // onError: (errors) => { /* Errors are typically handled by InputError components */ }
    });
};

const transactionTypes = [
    { value: 'payment', label: 'Payment' },
    { value: 'refund', label: 'Refund' },
    { value: 'credit_added', label: 'Credit Added' },
];
const transactionStatuses = [
    { value: 'completed', label: 'Completed' },
    { value: 'pending', label: 'Pending' },
    { value: 'failed', label: 'Failed' },
];

</script>

<template>
    <AdminLayout title="Detalles de Factura">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h2 class="mb-4 text-lg font-semibold">Detalles de Factura #{{ invoice.invoice_number }}</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div><strong>Cliente:</strong> {{ invoice.client?.name || 'N/A' }}</div>
                            <div><strong>Fecha de Emisión:</strong> {{ invoice.issue_date }}</div>
                            <div><strong>Fecha de Vencimiento:</strong> {{ invoice.due_date }}</div>
                            <div><strong>Estado:</strong> <span :class="{
                                'text-green-600': invoice.status === 'paid',
                                'text-red-600': invoice.status === 'overdue' || invoice.status === 'unpaid',
                                'text-yellow-600': invoice.status === 'pending_payment', // Assuming this status from orders might appear
                                'text-gray-600': invoice.status === 'cancelled' || invoice.status === 'refunded'
                            }">{{ invoice.status }}</span></div>
                            <div><strong>Subtotal:</strong> {{ invoice.subtotal }} {{ invoice.currency_code }}</div>
                            <div><strong>Total:</strong> {{ invoice.total_amount }} {{ invoice.currency_code }}</div>
                            <div v-if="invoice.paid_date"><strong>Fecha de Pago:</strong> {{ invoice.paid_date }}</div>
                        </div>

                        <h3 class="mt-6 mb-2 text-md font-semibold">Items de Factura:</h3>
                        <ul v-if="invoice.items && invoice.items.length > 0" class="list-disc pl-5">
                            <li v-for="item in invoice.items" :key="item.id">
                                {{ item.description }} - {{ item.quantity }} x {{ item.unit_price }} {{ invoice.currency_code }} = {{ item.total_price }} {{ invoice.currency_code }}
                            </li>
                        </ul>
                        <p v-else>No hay ítems en esta factura.</p>
                        
                        <!-- Display flash messages if any -->
                        <div v-if="$page.props.flash && $page.props.flash.success" class="mt-4 p-4 bg-green-100 text-green-700 rounded">
                            {{ $page.props.flash.success }}
                        </div>
                        <div v-if="$page.props.flash && $page.props.flash.error" class="mt-4 p-4 bg-red-100 text-red-700 rounded">
                            {{ $page.props.flash.error }}
                        </div>

                    </div>
                </div>

                <!-- Payment Registration Form -->
                <div class="mt-6 p-6 bg-white shadow-sm sm:rounded-lg" v-if="invoice.status !== 'paid' && invoice.status !== 'cancelled'">
                    <h3 class="text-lg font-medium text-gray-900">Registrar Transacción Manual</h3>
                    <form @submit.prevent="submitPayment" class="mt-6 space-y-6">
                        <div>
                            <InputLabel for="transaction_date" value="Fecha de Transacción" />
                            <TextInput id="transaction_date" type="date" class="mt-1 block w-full" v-model="paymentForm.transaction_date" required />
                            <InputError class="mt-2" :message="paymentForm.errors.transaction_date" />
                        </div>

                        <div>
                            <InputLabel for="amount" value="Monto" />
                            <TextInput id="amount" type="number" step="0.01" class="mt-1 block w-full" v-model="paymentForm.amount" required />
                            <InputError class="mt-2" :message="paymentForm.errors.amount" />
                        </div>
                        
                        <div>
                            <InputLabel for="currency_code" value="Moneda" />
                            <TextInput id="currency_code" type="text" class="mt-1 block w-full" v-model="paymentForm.currency_code" required />
                            <InputError class="mt-2" :message="paymentForm.errors.currency_code" />
                        </div>

                        <div>
                            <InputLabel for="gateway_slug" value="Pasarela de Pago" />
                            <TextInput id="gateway_slug" type="text" class="mt-1 block w-full" v-model="paymentForm.gateway_slug" required />
                            <InputError class="mt-2" :message="paymentForm.errors.gateway_slug" />
                        </div>
                        
                        <div>
                            <InputLabel for="gateway_transaction_id" value="ID Transacción Pasarela (Opcional)" />
                            <TextInput id="gateway_transaction_id" type="text" class="mt-1 block w-full" v-model="paymentForm.gateway_transaction_id" />
                            <InputError class="mt-2" :message="paymentForm.errors.gateway_transaction_id" />
                        </div>

                        <div>
                            <InputLabel for="type" value="Tipo" />
                            <SelectInput id="type" class="mt-1 block w-full" v-model="paymentForm.type" :options="transactionTypes" required />
                            <InputError class="mt-2" :message="paymentForm.errors.type" />
                        </div>

                        <div>
                            <InputLabel for="status" value="Estado" />
                            <SelectInput id="status" class="mt-1 block w-full" v-model="paymentForm.status" :options="transactionStatuses" required />
                            <InputError class="mt-2" :message="paymentForm.errors.status" />
                        </div>

                        <div>
                            <InputLabel for="description" value="Descripción (Opcional)" />
                            <TextInput id="description" type="text" class="mt-1 block w-full" v-model="paymentForm.description" />
                            <InputError class="mt-2" :message="paymentForm.errors.description" />
                        </div>

                        <div>
                            <InputLabel for="fees_amount" value="Comisiones (Opcional)" />
                            <TextInput id="fees_amount" type="number" step="0.01" class="mt-1 block w-full" v-model="paymentForm.fees_amount" />
                            <InputError class="mt-2" :message="paymentForm.errors.fees_amount" />
                        </div>

                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="paymentForm.processing">Registrar Transacción</PrimaryButton>
                            <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                                <p v-if="paymentForm.recentlySuccessful" class="text-sm text-gray-600">Transacción registrada.</p>
                            </Transition>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
