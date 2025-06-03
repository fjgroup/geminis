<template>
    <AuthenticatedLayout>
        <Head :title="'Edit Order #' + order.order_number" />

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h1 class="text-2xl font-semibold mb-6">Edit Order: #{{ order.order_number }}</h1>

                        <div v-if="form.hasErrors" class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                            <p v-for="(error, key) in form.errors" :key="key">{{ error }}</p>
                        </div>

                        <form @submit.prevent="submitUpdateOrder">
                            <div v-for="(item, index) in form.items" :key="item.id" class="mb-8 p-4 border rounded-md">
                                <h2 class="text-xl font-medium mb-3">{{ item.product_name }}</h2>

                                <!-- Current Billing Cycle -->
                                <p class="text-sm text-gray-600 mb-1">
                                    Current Cycle: {{ item.current_billing_cycle_name }} ({{ item.current_price_formatted }})
                                </p>

                                <!-- Billing Cycle Selection -->
                                <div class="mb-4">
                                    <InputLabel :for="'product_pricing_id-' + item.id" value="Billing Cycle" />
                                    <SelectInput
                                        :id="'product_pricing_id-' + item.id"
                                        class="mt-1 block w-full"
                                        v-model="item.product_pricing_id"
                                        :options="item.available_billing_cycles"
                                        option-value="id"
                                        option-label="name"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors[`items.${index}.product_pricing_id`]" />
                                </div>

                                <!-- Quantity Input -->
                                <div class="mb-4">
                                    <InputLabel :for="'quantity-' + item.id" value="Quantity" />
                                    <TextInput
                                        :id="'quantity-' + item.id"
                                        type="number"
                                        class="mt-1 block w-full"
                                        v-model.number="item.quantity"
                                        required
                                        min="1"
                                    />
                                    <InputError class="mt-2" :message="form.errors[`items.${index}.quantity`]" />
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end space-x-4">
                                <Link :href="route('client.orders.show', { order: order.id })" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/Forms/InputLabel.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import SelectInput from '@/Components/Forms/SelectInput.vue';
import PrimaryButton from '@/Components/Forms/Buttons/PrimaryButton.vue';
import InputError from '@/Components/Forms/InputError.vue';

const props = defineProps({
  order: Object, // Includes items.product.productPricings.billingCycle and items.productPricing.billingCycle
  errors: Object, // General errors if any from controller
});

const formatCurrency = (amount, currencyCode = 'USD') => {
    if (amount === null || amount === undefined) return 'N/A';
    // Attempt to use order's currency code first, then item's, then default.
    const displayCurrency = props.order.currency_code || currencyCode;
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: displayCurrency }).format(amount);
};

const form = useForm({
  items: props.order.items.map(item => {
    // item.product.productPricings should be available from controller eager loading
    const productPricings = item.product && item.product.product_pricings ? item.product.product_pricings : [];
    const available_billing_cycles = productPricings.map(pricing => ({
      id: pricing.id, // This is product_pricing_id
      name: `${pricing.billing_cycle.name} - ${formatCurrency(pricing.price, pricing.currency_code || props.order.currency_code)}`,
    }));

    // item.product_pricing also available from controller for current details
    const currentBillingCycleName = item.product_pricing?.billing_cycle?.name || 'N/A';
    const currentPriceFormatted = formatCurrency(item.unit_price, props.order.currency_code);


    return {
      id: item.id, // OrderItem ID
      product_name: item.product.name,
      quantity: item.quantity,
      product_pricing_id: item.product_pricing_id, // Current product_pricing_id
      available_billing_cycles: available_billing_cycles,
      current_billing_cycle_name: currentBillingCycleName,
      current_price_formatted: currentPriceFormatted,
      // Note: 'billing_cycle_id' in the task description for items.*.billing_cycle_id (validation)
      // refers to the ID of a ProductPricing record. So form field should be product_pricing_id.
    };
  })
});

function submitUpdateOrder() {
  // The 'items.*.billing_cycle_id' in the backend validation actually refers to 'product_pricing_id'
  // So, the form data structure is already correct.
  form.put(route('client.orders.updateOrder', { order: props.order.id }), {
    preserveScroll: true,
    // onSuccess: () => { /* Handled by controller redirect with flash message */ },
    // onError: (pageErrors) => { /* form.errors will be populated automatically */ }
  });
}
</script>
