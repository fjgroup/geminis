<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue'; // Importa 'computed' para el precio dinámico

const props = defineProps({
    product: Object,
});

const form = useForm({
    product_pricing_id: null,
    configurable_options_ids: [],
});

const selectedPricing = computed(() => {
    return props.product.product_pricings.find(
        (pricing) => pricing.id === form.product_pricing_id
    );
});

const selectedConfigurableOptions = computed(() => {
    return props.product.configurable_option_groups.flatMap(group =>
        group.configurable_options.filter(option =>
            form.configurable_options_ids.includes(option.id)
        )
    );
});

const totalCost = computed(() => {
    let baseCost = selectedPricing.value ? parseFloat(selectedPricing.value.price) : 0;
    let optionsCost = selectedConfigurableOptions.value.reduce(
        (sum, option) => sum + parseFloat(option.pricing.price),
        0
    );
    return baseCost + optionsCost;
});


const submit = () => {
    form.post(route('client.orders.placeOrder', { product: props.product.id }));
};
</script>

<template>
    <Head :title="`Crear Orden para ${product.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Crear Orden para {{ product.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Detalles del Producto -->
                        <h3 class="mb-4 text-lg font-semibold">{{ product.name }}</h3>
                        <p class="mb-6">{{ product.description }}</p>

                        <!-- Formulario de Orden -->
                        <form @submit.prevent="submit">
                            <!-- Selección de Ciclo de Facturación -->
                            <div class="mb-6">
                                <label class="block mb-2 text-sm font-medium text-gray-700">Ciclo de Facturación:</label>
                                <div v-for="pricing in product.product_pricings" :key="pricing.id" class="mb-2">
                                    <label class="inline-flex items-center">
                                        <input
                                            type="radio"
                                            class="form-radio"
                                            :value="pricing.id"
                                            v-model="form.product_pricing_id"
                                            required
                                        />
                                        <span class="ml-2">{{ pricing.billing_cycle.name }} - {{ pricing.price }}</span>
                                    </label>
                                </div>
                                <InputError :message="form.errors.product_pricing_id" class="mt-2" />
                            </div>

                            <!-- Opciones Configurables -->
                            <div v-if="product.configurable_option_groups && product.configurable_option_groups.length > 0" class="mb-6">
                                <h4 class="mb-3 font-semibold text-md">Opciones Configurables:</h4>
                                <div v-for="group in product.configurable_option_groups" :key="group.id" class="p-4 mb-4 border rounded-md">
                                    <p class="mb-2 font-medium">{{ group.name }}</p>
                                    <div v-for="option in group.configurable_options" :key="option.id" class="mb-1">
                                        <label class="inline-flex items-center">
                                            <input
                                                v-if="group.type === 'quantity' || group.type === 'checkbox'"
                                                type="checkbox"
                                                class="form-checkbox"
                                                :value="option.id"
                                                v-model="form.configurable_options_ids"
                                            />
                                             <input
                                                v-else-if="group.type === 'radio'"
                                                type="radio"
                                                class="form-radio"
                                                :value="option.id"
                                                v-model="form.configurable_options_ids"
                                            />
                                            <span class="ml-2">{{ option.name }} ({{ option.pricing.price }})</span>
                                        </label>
                                    </div>
                                </div>
                                <InputError :message="form.errors.configurable_options_ids" class="mt-2" />
                            </div>

                            <!-- Precio Total Preliminar -->
                            <div class="mb-6 text-lg font-bold">
                                Precio Total Preliminar: {{ totalCost.toFixed(2) }}
                            </div>

                            <!-- Botón de Envío -->
                            <div>
                                <PrimaryButton type="submit" :disabled="form.processing">
                                    Realizar Orden
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Estilos específicos del componente si son necesarios */
</style>
