<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // Usar el layout de cliente
import { Head, useForm } from '@inertiajs/vue3'; // Added useForm
import PrimaryButton from '@/Components/PrimaryButton.vue'; // Added PrimaryButton

const props = defineProps({
    invoice: {
        type: Object,
        required: true,
    },
});

const paymentForm = useForm({});

const markAsPaid = () => {
    if (confirm('This is a simulated payment. Mark this invoice as paid?')) {
        paymentForm.post(route('client.invoices.payment.store', props.invoice.id), {
            preserveScroll: true,
            // onSuccess: () => { /* Controller handles redirect and flash message */ }
        });
    }
};
</script>

<template>
    <Head :title="`Factura #${invoice.invoice_number || invoice.number}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Detalles de Factura</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="mb-4 text-lg font-semibold">Factura #{{ invoice.invoice_number || invoice.number }}</h3>

                        <!-- Flash Messages -->
                        <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                            {{ $page.props.flash.success }}
                        </div>
                        <div v-if="$page.props.flash && $page.props.flash.error" class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                            {{ $page.props.flash.error }}
                        </div>

                        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2">
                            <div><strong>Fecha de Emisión:</strong> {{ new Date(invoice.issue_date).toLocaleDateString() }}</div>
                            <div><strong>Fecha de Vencimiento:</strong> {{ new Date(invoice.due_date).toLocaleDateString() }}</div>
                            <div><strong>Estado:</strong> 
                                <span :class="{
                                    'text-green-600 font-semibold': invoice.status === 'paid',
                                    'text-red-600 font-semibold': invoice.status === 'overdue',
                                    'text-yellow-600 font-semibold': invoice.status === 'unpaid',
                                }">{{ invoice.status }}</span>
                            </div>
                            <div><strong>Total:</strong> {{ new Intl.NumberFormat('en-US', { style: 'currency', currency: invoice.currency_code || 'USD' }).format(invoice.total_amount || invoice.total) }}</div>
                            <div v-if="invoice.client"><strong>Cliente:</strong> {{ invoice.client.name }}</div>
                            <div v-if="invoice.reseller"><strong>Revendedor:</strong> {{ invoice.reseller.name }}</div>
                            <div v-if="invoice.paid_date"><strong>Fecha de Pago:</strong> {{ new Date(invoice.paid_date).toLocaleDateString() }}</div>
                        </div>

                        <h4 class="mb-3 font-semibold text-md">Ítems de la Factura:</h4>
                        <table class="min-w-full divide-y divide-gray-200 mb-6">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Descripción</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Cantidad</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Precio Unitario</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in invoice.items" :key="item.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ item.description }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">{{ item.quantity }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">{{ new Intl.NumberFormat('en-US', { style: 'currency', currency: invoice.currency_code || 'USD' }).format(item.unit_price) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">{{ new Intl.NumberFormat('en-US', { style: 'currency', currency: invoice.currency_code || 'USD' }).format(item.total_price || item.subtotal) }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-if="invoice.status === 'unpaid'" class="mt-6 text-center">
                            <PrimaryButton @click="markAsPaid" :disabled="paymentForm.processing">
                                Mark as Paid (Simulated)
                            </PrimaryButton>
                            <p v-if="paymentForm.processing" class="text-sm text-gray-600 mt-2">Processing payment...</p>
                        </div>

                        <div v-if="invoice.order" class="mt-6">
                             <Link :href="route('client.orders.index')" class="text-indigo-600 hover:text-indigo-900">&laquo; Ver mis Órdenes</Link>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
