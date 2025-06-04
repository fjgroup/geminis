<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Pagination from '@/Components/UI/Pagination.vue';
import PrimaryButton from '@/Components/Forms/Buttons/PrimaryButton.vue';

const props = defineProps({
    products: {
        type: Object,
        required: true,
    },
});

const formatCurrency = (amount, currencyCode = 'USD') => {
    if (amount === null || amount === undefined) return 'N/A';
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: currencyCode }).format(amount);
};

// Helper to find the lowest price for a product if multiple pricings exist
const getLowestPrice = (productPricings) => {
    if (!productPricings || productPricings.length === 0) {
        return { amount: null, cycle: 'N/A' };
    }
    // For simplicity, just taking the first one or you can implement logic to find "starting at" price
    const firstPricing = productPricings[0];
    return {
        amount: firstPricing.price,
        currency: firstPricing.currency_code,
        cycle: firstPricing.billing_cycle ? firstPricing.billing_cycle.name : 'One Time'
    };
};
</script>

<template>
    <Head title="Browse Products" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Our Products & Services
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="products.data.length === 0" class="p-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <p class="text-center text-gray-500 dark:text-gray-400">
                        There are no products available at the moment. Please check back later.
                    </p>
                </div>

                <div v-else class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="product in products.data" :key="product.id"
                         class="flex flex-col justify-between overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ product.name }}</h3>
                            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400 min-h-[60px]">
                                {{ product.description || 'No description available.' }}
                            </p>

                            <div v-if="product.pricings && product.pricings.length > 0">
                                <p class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">Precio:</p>
                                <div v-if="product.pricings.length === 1">
                                    <p class="text-lg font-bold text-gray-700 dark:text-gray-200">
                                        {{ formatCurrency(product.pricings[0].price, product.pricings[0].currency_code) }}
                                        <span v-if="product.pricings[0].billing_cycle"> ({{ product.pricings[0].billing_cycle.name }})</span>
                                        <span v-else> (Pago Único)</span>
                                    </p>
                                </div>
                                <div v-else>
                                    <p class="text-lg font-bold text-gray-700 dark:text-gray-200">
                                        Desde {{ formatCurrency(getLowestPrice(product.pricings).amount, getLowestPrice(product.pricings).currency) }}
                                        <span class="text-xs font-normal">/ {{ getLowestPrice(product.pricings).cycle }}</span>
                                    </p>
                                </div>
                                <!-- Opcional: mostrar todas las opciones de precio si es necesario -->
                                <!-- <ul class="mb-4 ml-4 text-xs text-gray-500 list-disc dark:text-gray-400">
                                   <li v-for="pricing in product.pricings" :key="pricing.id">
                                        {{ formatCurrency(pricing.price, pricing.currency_code) }}
                                        <span v-if="pricing.billing_cycle"> ({{ pricing.billing_cycle.name }})</span>
                                        <span v-else> (Pago Único)</span>
                                    </li>
                                </ul> -->
                            </div>
                            <div v-else>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Precio no disponible.</p>
                            </div>
                        </div>
                        <div class="p-6 bg-gray-50 dark:bg-gray-700/50">
                             <Link :href="route('client.order.showOrderForm', { product: product.id })"
                                  class="w-full">
                                <PrimaryButton class="justify-center w-full">
                                    Order Now
                                </PrimaryButton>
                            </Link>
                        </div>
                    </div>
                </div>

                <Pagination v-if="products.links.length > 3" :links="products.links" class="mt-6" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
