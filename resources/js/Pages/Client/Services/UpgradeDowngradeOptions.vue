<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/Forms/Buttons/PrimaryButton.vue';

const props = defineProps({
    service: {
        type: Object,
        required: true,
    },
    availableOptions: {
        type: Array,
        default: () => [],
    },
});

// Helper function for formatting currency
const formatCurrency = (amount, currencyCode = 'USD') => {
    const number = parseFloat(amount);
    if (isNaN(number)) {
        return 'N/A';
    }
    try {
        return new Intl.NumberFormat(undefined, { style: 'currency', currency: currencyCode }).format(number);
    } catch (e) {
        return `${currencyCode} ${number.toFixed(2)}`;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    // Assuming date-fns is not explicitly imported here, use basic JS Date
    // For more robust formatting, consider importing 'date-fns' or similar
    const date = new Date(dateString);
    return date.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
};

import { router, useForm } from '@inertiajs/vue3'; // Import router and useForm

const confirmPlanChange = (event, newPricingId, optionBillingCycleName) => {
    event.preventDefault();
    if (confirm(`Are you sure you want to change your plan to "${props.service.product?.name} - ${optionBillingCycleName}"? The new price will be ${formatCurrency(props.availableOptions.find(o => o.id === newPricingId)?.price, props.availableOptions.find(o => o.id === newPricingId)?.currency_code)}. Changes will apply on your next renewal date.`)) {
        router.post(route('client.services.processUpgradeDowngrade', { service: props.service.id }), {
            new_product_pricing_id: newPricingId,
        }, {
            preserveScroll: true,
            // onSuccess: page => { /* Optional: Handle success from page props */ },
            // onError: errors => { /* Optional: Handle errors */ },
        });
    }
};
</script>

<template>
    <Head :title="`Change Plan for ${service.product?.name || 'Service'}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Change Plan: {{ service.product?.name }}
                <span v-if="service.domain_name" class="text-sm text-gray-500 dark:text-gray-400">({{ service.domain_name }})</span>
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Current Plan Details -->
                <div class="mb-8 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Your Current Plan</h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Product/Service:</p>
                                <p class="font-semibold text-gray-800 dark:text-gray-200">{{ service.product?.name || 'N/A' }}</p>
                            </div>
                            <div v-if="service.domain_name">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Domain:</p>
                                <p class="font-semibold text-gray-800 dark:text-gray-200">{{ service.domain_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Current Billing Cycle:</p>
                                <p class="font-semibold text-gray-800 dark:text-gray-200">{{ service.product_pricing?.billing_cycle?.name || 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Current Price:</p>
                                <p class="font-semibold text-gray-800 dark:text-gray-200">{{ formatCurrency(service.billing_amount, service.product_pricing?.currency_code) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Status:</p>
                                <p class="font-semibold text-gray-800 dark:text-gray-200">{{ service.status }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Next Due Date:</p>
                                <p class="font-semibold text-gray-800 dark:text-gray-200">{{ formatDate(service.next_due_date) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available Upgrade/Downgrade Options -->
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-6 text-lg font-semibold text-gray-900 dark:text-gray-100">Available Plans</h3>
                        
                        <div v-if="availableOptions.length > 0" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div v-for="option in availableOptions" :key="option.id"
                                 class="flex flex-col justify-between p-6 border border-gray-200 rounded-lg dark:border-gray-700 hover:shadow-lg transition-shadow duration-200">
                                <div>
                                    <h4 class="mb-2 text-md font-semibold text-gray-800 dark:text-gray-200">
                                        {{ service.product?.name }} - {{ option.billing_cycle?.name || 'N/A' }}
                                    </h4>
                                    <!-- Placeholder for features - this would come from product or pricing details -->
                                    <ul class="mb-4 text-xs text-gray-600 list-disc list-inside dark:text-gray-400">
                                        <li>Feature A for {{ option.billing_cycle?.name }}</li>
                                        <li>Feature B for {{ option.billing_cycle?.name }}</li>
                                    </ul>
                                    <p class="mb-4 text-xl font-bold text-gray-900 dark:text-gray-100">
                                        {{ formatCurrency(option.price, option.currency_code) }}
                                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">/ {{ option.billing_cycle?.name || 'N/A' }}</span>
                                    </p>
                                </div>
                                <PrimaryButton 
                                    as="button" 
                                    @click.prevent="confirmPlanChange($event, option.id, option.billing_cycle?.name || 'N/A')" 
                                    class="w-full justify-center"
                                    :disabled="false">  <!-- Replace false with a 'form.processing' if using useForm -->
                                    Select this plan
                                </PrimaryButton>
                            </div>
                        </div>
                        <div v-else>
                            <p class="text-center text-gray-500 dark:text-gray-400">
                                No other billing options are currently available for this product.
                            </p>
                        </div>
                         <div class="mt-8 text-center">
                            <Link :href="route('client.services.index')" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                                &laquo; Back to My Services
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
