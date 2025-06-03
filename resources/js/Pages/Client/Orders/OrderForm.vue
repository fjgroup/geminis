<template>
    <AuthenticatedLayout>
        <Head :title="'Order ' + product.name" />

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h1 class="mb-6 text-2xl font-semibold">Order: {{ product.name }}</h1>

                        <div v-if="product.description" class="mb-4">
                            <p class="text-gray-700">{{ product.description }}</p>
                        </div>

                        <form @submit.prevent="submitOrder">
                            <!-- Billing Cycle Selection -->
                            <div class="mb-4">
                                <InputLabel for="billing_cycle_id" value="Billing Cycle" />
                                <SelectInput
                                    id="billing_cycle_id"
                                    class="block w-full mt-1"
                                    v-model="form.billing_cycle_id"
                                    :options="billingCycleOptions"
                                    option-value="id"
                                    option-label="name"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.billing_cycle_id" />
                            </div>

                            <!-- Quantity Input -->
                            <div class="mb-4">
                                <InputLabel for="quantity" value="Quantity" />
                                <TextInput
                                    id="quantity"
                                    type="number"
                                    class="block w-full mt-1"
                                    v-model="form.quantity"
                                    required
                                    min="1"
                                />
                                <InputError class="mt-2" :message="form.errors.quantity" />
                            </div>

                            <!-- Domain Name Inputs (Conditional) -->
                            <div v-if="props.product.product_type && props.product.product_type.requires_domain && form.quantity > 0 && form.domainNames.length > 0" class="mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-md space-y-3">
                                <h3 class="text-md font-medium text-gray-900 dark:text-gray-100">Nombres de Dominio Asociados:</h3>
                                <div v-for="(domain, index) in form.domainNames" :key="index" class="space-y-1">
                                    <InputLabel :for="'domain_name_' + index">Nombre de Dominio {{ index + 1 }} <span class="text-red-500">*</span></InputLabel>
                                    <TextInput
                                        :id="'domain_name_' + index"
                                        type="text"
                                        class="block w-full"
                                        v-model="form.domainNames[index]"
                                        placeholder="ej: sudominio.com"
                                        required
                                    />
                                    <!-- Adjust error message key based on backend validation structure for arrays -->
                                    <InputError class="mt-1" :message="form.errors['domainNames.' + index] || form.errors.domainNames" />
                                </div>
                            </div>

                            <!-- Notes to Client (Optional) -->
                            <div class="mb-4">
                                <InputLabel for="notes_to_client" value="Notes (Optional)" />
                                <textarea
                                    id="notes_to_client"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    v-model="form.notes_to_client"
                                    rows="3"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.notes_to_client" />
                            </div>

                            <!-- TODO: Add Configurable Options Selection Here -->
                            <!-- Example for configurable options:
                            <div v-for="group in product.configurable_option_groups" :key="group.id" class="mb-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ group.name }}</h3>
                                <p v-if="group.description" class="mb-2 text-sm text-gray-500">{{ group.description }}</p>
                                <div v-for="option in group.options" :key="option.id">
                                     Display option based on its type (e.g., dropdown, radio, checkbox)
                                     Example for a dropdown:
                                    <InputLabel :for="'option_' + option.id" :value="option.name" />
                                    <SelectInput
                                        :id="'option_' + option.id"
                                        class="block w-full mt-1"
                                        v-model="form.configurable_options[group.id][option.id]"
                                        :options="option.values" <!- Assuming option values are structured for SelectInput ->
                                    />
                                </div>
                            </div>
                            -->

                            <div class="mt-6">
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Place Order
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
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/Forms/InputLabel.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import SelectInput from '@/Components/Forms/SelectInput.vue'; // Assuming you have or will create this generic component
import InputError from '@/Components/Forms/InputError.vue';
import PrimaryButton from '@/Components/Forms/Buttons/PrimaryButton.vue';
import { computed, watch } from 'vue'; // Added watch

const props = defineProps({
  product: Object,
  errors: Object, // For displaying general errors if any
});

const form = useForm({
  billing_cycle_id: props.product.pricings && props.product.pricings.length > 0 ? props.product.pricings[0].id : null, // Usar 'pricings'
  quantity: 1,
  notes_to_client: '',
  domainNames: [''], // Added
  // configurable_options: {}, // Initialize as an empty object for future use
});

watch(() => form.quantity, (newQuantity, oldQuantity) => {
    const currentLength = form.domainNames.length;
    const targetQuantity = Math.max(1, Number(newQuantity) || 1); // Ensure quantity is at least 1

    // Use ProductType property
    if (props.product.product_type && props.product.product_type.requires_domain) {
        if (targetQuantity > currentLength) {
            // Add new empty strings
            for (let i = 0; i < targetQuantity - currentLength; i++) {
                form.domainNames.push('');
            }
        } else if (targetQuantity < currentLength) {
            // Remove excess domain names
            form.domainNames.splice(targetQuantity);
        }
        // Ensure at least one input if quantity is 1 or more
        if (targetQuantity >= 1 && form.domainNames.length === 0) {
            form.domainNames.push('');
        }
    } else {
        // If not a hosting product, clear domain names
        form.domainNames = [];
    }
}, { immediate: true });


// Prepare billing cycle options for the SelectInput component
const billingCycleOptions = computed(() => {
  if (!props.product.pricings) { // Usar 'pricings'
    return [];
  }
  return props.product.pricings.map(pricing => ({
    value: pricing.id, // Cambiar a 'value'
    label: `${pricing.billing_cycle.name} (${pricing.price} ${pricing.currency_code})`, // Cambiar a 'label' y ajustar el formato
  }));
});

// Initialize configurable_options in the form based on product data
// This is a basic setup; actual structure might depend on how options are selected and submitted
/*
if (props.product.configurable_option_groups) {
  props.product.configurable_option_groups.forEach(group => {
    form.configurable_options[group.id] = {};
    group.options.forEach(option => {
      // Set a default value if necessary, e.g., first value or null
      form.configurable_options[group.id][option.id] = null;
    });
  });
}
*/

function submitOrder() {
  form.post(route('client.order.placeOrder', { product: props.product.id }), {
    onError: (pageErrors) => {
      // This callback is for when the form submission itself fails at the network level
      // or if Laravel returns a general error response (e.g., 500).
      // Validation errors (422) are automatically handled and populated in form.errors.
      console.error('Order submission error:', pageErrors);
    },
    onFinish: () => {
      // This callback executes after the request is finished, regardless of success or failure.
      // form.reset('quantity', 'notes_to_client'); // Optionally reset fields
    },
  });
}
</script>
