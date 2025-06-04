<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { computed } from "vue"; // Importamos computed
import { Head, Link, router, usePage } from "@inertiajs/vue3"; // Added router, usePage
import PrimaryButton from "@/Components/Forms/Buttons/PrimaryButton.vue";
import SecondaryButton from "@/Components/Forms/Buttons/SecondaryButton.vue";

const props = defineProps({
  invoice: {
    type: Object,
    required: true,
  },
  // auth: Object, // auth is available via usePage().props.auth
});

const page = usePage();
const user = computed(() => page.props.auth.user);

// Helper for currency formatting
const formatCurrency = (amount, currencyCode = "USD") => {
  if (amount === null || amount === undefined) return "";
  // Ensure props.invoice.currency_code is used if available, otherwise fallback
  const displayCurrency =
    props.invoice && props.invoice.currency_code
      ? props.invoice.currency_code
      : currencyCode;
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: displayCurrency,
  }).format(amount);
};

// Helper to determine payment button state
const canPayWithBalance = computed(() => {
      // Ensure user.value and user.value.balance are available
      if (!user.value || typeof user.value.balance === 'undefined') return false;
      // Ensure invoice.total_amount is available
      if (typeof props.invoice.total_amount === 'undefined') return false;

      return (
        props.invoice.status === "unpaid" &&
        parseFloat(user.value.balance) >= parseFloat(props.invoice.total_amount)
      );
    });

const hasSomeBalance = computed(() => {
      // Ensure user.value and user.value.balance are available
      if (!user.value || typeof user.value.balance === 'undefined') return false;
      // Ensure invoice.total_amount is available
      if (typeof props.invoice.total_amount === 'undefined') return false;

      const userBalance = parseFloat(user.value.balance);
      const invoiceTotal = parseFloat(props.invoice.total_amount);

      return (
        props.invoice.status === "unpaid" &&
        userBalance > 0 &&
        userBalance < invoiceTotal
      );
    });

const payInvoiceWithBalance = (invoiceId) => {
    if (confirm('¿Confirmas que deseas pagar esta factura utilizando tu saldo disponible?')) {
        router.post(route('client.invoices.payment.store', { invoice: invoiceId }), {
            payment_method: 'account_credit'
        }, {
            preserveScroll: true,
        });
    }
};

const formatDate = (datetime) => {
    if (!datetime) return 'N/A';
    // Basic date formatting, can be enhanced with a library like date-fns or moment if needed
    const date = new Date(datetime);
    return date.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
  <Head :title="`Factura #${invoice.invoice_number || invoice.number}`" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Detalles de Factura
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <h3 class="mb-4 text-lg font-semibold">
              Factura #{{ invoice.invoice_number || invoice.number }}
            </h3>

            <!-- Flash Messages -->
            <div
              v-if="$page.props.flash && $page.props.flash.success"
              class="mb-4 p-4 bg-green-100 text-green-700 rounded"
            >
              {{ $page.props.flash.success }}
            </div>
            <div
              v-if="$page.props.flash && $page.props.flash.error"
              class="mb-4 p-4 bg-red-100 text-red-700 rounded"
            >
              {{ $page.props.flash.error }}
            </div>

            <!-- Display User Balance -->
            <div
              v-if="user && user.formatted_balance"
              class="p-4 mb-6 text-lg text-center text-blue-700 bg-blue-100 rounded-md dark:bg-blue-900 dark:text-blue-200"
            >
              Tu crédito disponible:
              <span class="font-semibold">{{ user.formatted_balance }}</span>
            </div>

            <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2">
              <div>
                <strong>Fecha de Emisión:</strong>
                {{ new Date(invoice.issue_date).toLocaleDateString() }}
              </div>
              <div>
                <strong>Fecha de Vencimiento:</strong>
                {{ new Date(invoice.due_date).toLocaleDateString() }}
              </div>
              <div>
                <strong>Estado:</strong>
                <span
                  :class="{
                    'text-green-600 font-semibold': invoice.status === 'paid',
                    'text-red-600 font-semibold': invoice.status === 'overdue',
                    'text-yellow-600 font-semibold': invoice.status === 'unpaid',
                  }"
                  >{{ invoice.status }}</span
                >
              </div>
              <div>
                <strong>Total:</strong>
                {{
                  new Intl.NumberFormat("en-US", {
                    style: "currency",
                    currency: invoice.currency_code || "USD",
                  }).format(invoice.total_amount || invoice.total)
                }}
              </div>
              <div v-if="invoice.client">
                <strong>Cliente:</strong> {{ invoice.client.name }}
              </div>
              <div v-if="invoice.reseller">
                <strong>Revendedor:</strong> {{ invoice.reseller.name }}
              </div>
              <div v-if="invoice.paid_date">
                <strong>Fecha de Pago:</strong>
                {{ new Date(invoice.paid_date).toLocaleDateString() }}
              </div>
            </div>

            <h4 class="mb-3 font-semibold text-md">Ítems de la Factura:</h4>
            <table class="min-w-full divide-y divide-gray-200 mb-6">
              <thead class="bg-gray-50">
                <tr>
                  <th
                    scope="col"
                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                  >
                    Descripción
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                  >
                    Cantidad
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                  >
                    Precio Unitario
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                  >
                    Total
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200 dark:divide-gray-700">
    <template v-if="invoice.items && invoice.items.length > 0">
        <template v-for="item in invoice.items" :key="item.id">
            <tr v-if="item.order_item && item.order_item.setup_fee && parseFloat(item.order_item.setup_fee) > 0">
                <!-- Row for main product/service (when setup fee exists) -->
                <td class="px-6 py-4 whitespace-nowrap">
                    {{ item.order_item.product ? item.order_item.product.name : item.description }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">{{ item.quantity }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                    {{ formatCurrency(item.order_item.unit_price, invoice.currency_code) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                    {{ formatCurrency(parseFloat(item.order_item.unit_price) * item.quantity, invoice.currency_code) }}
                </td>
            </tr>
            <tr v-if="item.order_item && item.order_item.setup_fee && parseFloat(item.order_item.setup_fee) > 0" class="bg-gray-50 dark:bg-gray-700/30">
                <!-- Row for setup fee -->
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 pl-10">
                    Tarifa de Configuración <span v-if="item.order_item.product">para {{ item.order_item.product.name }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-400">{{ item.quantity }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">
                    {{ formatCurrency(item.order_item.setup_fee, invoice.currency_code) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">
                    {{ formatCurrency(parseFloat(item.order_item.setup_fee) * item.quantity, invoice.currency_code) }}
                </td>
            </tr>
            <tr v-if="!item.order_item || !item.order_item.setup_fee || parseFloat(item.order_item.setup_fee) <= 0">
                <!-- Original row for items without setup fee or if order_item is not available -->
                <td class="px-6 py-4 whitespace-nowrap">{{ item.description }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">{{ item.quantity }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                    {{ formatCurrency(item.unit_price, invoice.currency_code) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                    {{ formatCurrency(item.total_price, invoice.currency_code) }}
                </td>
            </tr>
        </template>
    </template>
    <tr v-else>
        <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No hay ítems en esta factura.</td>
    </tr>
</tbody>
            </table>

            <!-- Payment Details if Paid -->
            <div v-if="invoice.status === 'paid' && invoice.transactions && invoice.transactions.length > 0 && invoice.transactions[0].payment_method"
                 class="mt-6 p-4 border border-gray-200 dark:border-gray-700 rounded bg-gray-50 dark:bg-gray-700/50">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Detalles del Pago Realizado</h3>
                <div class="text-sm text-gray-700 dark:text-gray-300 space-y-2">
                    <p><strong>Fecha de Transacción (Cliente):</strong> {{ formatDate(invoice.transactions[0].transaction_date) }}</p>
                    <p><strong>Referencia del Cliente:</strong> {{ invoice.transactions[0].gateway_transaction_id }}</p>

                    <div v-if="invoice.transactions[0].payment_method.formatted_details" class="mt-2">
                        <h4 class="font-semibold text-gray-800 dark:text-gray-200">Método de Pago: {{ invoice.transactions[0].payment_method.formatted_details.name }}</h4>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1 space-y-1">
                             <p v-if="invoice.transactions[0].payment_method.formatted_details.type === 'bank'">
                                <strong>Banco:</strong> {{ invoice.transactions[0].payment_method.formatted_details.bank_name }}<br>
                                <strong>Nro. Cuenta:</strong> {{ invoice.transactions[0].payment_method.formatted_details.account_number }}<br>
                                <strong>Titular:</strong> {{ invoice.transactions[0].payment_method.formatted_details.account_holder_name }}<br>
                                <span v-if="invoice.transactions[0].payment_method.formatted_details.identification_number"><strong>Cédula/RIF:</strong> {{ invoice.transactions[0].payment_method.formatted_details.identification_number }}<br></span>
                            </p>
                            <p v-else-if="invoice.transactions[0].payment_method.formatted_details.type === 'wallet' || invoice.transactions[0].payment_method.formatted_details.type === 'paypal_manual'">
                                <strong>Plataforma:</strong> {{ invoice.transactions[0].payment_method.formatted_details.platform_name }}<br>
                                <span v-if="invoice.transactions[0].payment_method.formatted_details.email_address"><strong>Email:</strong> {{ invoice.transactions[0].payment_method.formatted_details.email_address }}<br></span>
                                <span v-if="invoice.transactions[0].payment_method.formatted_details.account_holder_name"><strong>Titular/Usuario:</strong> {{ invoice.transactions[0].payment_method.formatted_details.account_holder_name }}<br></span>
                                <span v-if="invoice.transactions[0].payment_method.formatted_details.payment_link"><strong>Enlace de Pago:</strong> <a :href="invoice.transactions[0].payment_method.formatted_details.payment_link" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ invoice.transactions[0].payment_method.formatted_details.payment_link }}</a></span>
                            </p>
                            <p v-else-if="invoice.transactions[0].payment_method.formatted_details.type === 'crypto_wallet'">
                                <strong>Red/Moneda:</strong> {{ invoice.transactions[0].payment_method.formatted_details.platform_name }}<br>
                                <strong>Dirección:</strong> {{ invoice.transactions[0].payment_method.formatted_details.wallet_address }} <span v-if="invoice.transactions[0].payment_method.formatted_details.crypto_network"> (Red: {{ invoice.transactions[0].payment_method.formatted_details.crypto_network }})</span><br>
                                <span v-if="invoice.transactions[0].payment_method.formatted_details.account_holder_name"><strong>Referencia/Titular:</strong> {{ invoice.transactions[0].payment_method.formatted_details.account_holder_name }}</span>
                            </p>
                            <p v-else-if="invoice.transactions[0].payment_method.formatted_details.type === 'balance'">
                                <strong>Método:</strong> Saldo de la Cuenta
                            </p>
                            <p v-if="invoice.transactions[0].payment_method.formatted_details.instructions" class="mt-2 whitespace-pre-wrap border-t border-gray-300 dark:border-gray-600 pt-2">
                                <strong>Instrucciones Adicionales:</strong><br>{{ invoice.transactions[0].payment_method.formatted_details.instructions }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Payment Options -->
            <div class="mt-6 space-y-4 text-center">
                <div v-if="invoice.status === 'unpaid' && user && user.balance > 0">
                    <PrimaryButton
                        @click="payInvoiceWithBalance(invoice.id)"
                        :disabled="!canPayWithBalance"
                        class="px-6 py-3 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-opacity-50 disabled:opacity-50"
                        :class="canPayWithBalance ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' : 'bg-gray-400 cursor-not-allowed'"
                    >
                        <span v-if="canPayWithBalance">Pagar con Saldo (Disponible: {{ user.formatted_balance }})</span>
                        <span v-else>Saldo Insuficiente (Disponible: {{ user.formatted_balance }})</span>
                    </PrimaryButton>
                    <p v-if="hasSomeBalance && !canPayWithBalance" class="mt-2 text-sm text-yellow-600 dark:text-yellow-400">
                        Tu saldo actual no es suficiente para cubrir el monto total de esta factura.
                         Necesitas {{ formatCurrency(parseFloat(props.invoice.total_amount) - parseFloat(user.value.balance), props.invoice.currency_code) }} más.
                    </p>
                </div>

                <!-- Manual Payment Registration Button -->
                <div v-if="invoice.status === 'unpaid'">
                    <hr class="my-4 dark:border-gray-700" v-if="user && user.balance > 0">
                    <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">¿O ya realizaste el pago por otro medio (transferencia, depósito)?</p>
                    <Link :href="route('client.invoices.manualPayment.create', { invoice: invoice.id })" class="mr-2">
                        <PrimaryButton class="bg-blue-600 hover:bg-blue-700 focus:ring-blue-500">Informar Pago Manual</PrimaryButton>
                    </Link>
                </div>
                 <!-- PayPal Payment Button -->
                <div v-if="invoice.status === 'unpaid'" class="mt-2">
                     <Link :href="route('client.paypal.checkout', { invoice: invoice.id })"
                          class="inline-flex items-center px-4 py-2 bg-paypal-blue border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-paypal-darkblue active:bg-paypal-darkerblue focus:outline-none focus:ring-2 focus:ring-paypal-blue focus:ring-offset-2 transition ease-in-out duration-150">
                        Pagar con PayPal
                    </Link>
                </div>

                <div v-if="invoice.status !== 'unpaid'" class="text-sm text-gray-600 dark:text-gray-400">
                    Esta factura no está pendiente de pago.
                </div>
            </div>

            <div v-if="invoice.order" class="mt-6">
              <Link
                :href="route('client.orders.index')"
                class="text-indigo-600 hover:text-indigo-900"
                >&laquo; Ver mis Órdenes</Link
              >
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
