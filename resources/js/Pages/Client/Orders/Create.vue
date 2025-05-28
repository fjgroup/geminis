<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue'; // Importa 'computed' para el precio dinámico

const props = defineProps({
    product: Object,
});

const form = useForm({
    product_pricing_id: null,
    configurable_options: [], // Changed from configurable_options_ids to match request & controller
    notes: '', // Added notes field
});

const selectedPricing = computed(() => {
    if (!form.product_pricing_id) return null;
    return props.product.product_pricings.find(
        (pricing) => pricing.id === form.product_pricing_id
    );
});

// This computed property might need adjustment if configurable_options structure changes
// For example, if it becomes an object like { option_id: selected_value_id }
// or an array of { option_id: id, value_id: id }
// For now, assuming it's an array of selected configurable_option_ids as before,
// but the form field is now `form.configurable_options`.
const selectedConfigurableOptions = computed(() => {
    if (!props.product.configurable_option_groups) return [];
    return props.product.configurable_option_groups.flatMap(group =>
        group.configurable_options.filter(option =>
            form.configurable_options.includes(option.id) // Changed from configurable_options_ids
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
    // The product ID is not part of the route for placeOrder anymore
    // It's derived from product_pricing_id on the backend.
    form.post(route('client.order.placeOrder'), { // Corrected route name from previous task
        // The controller will redirect to the invoice page on success.
        // Inertia will automatically follow that redirect.
        // Error handling is typically automatic via Inertia's form helper.
    });
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
                                                v-model="form.configurable_options" 
                                            />
                                             <input
                                                v-else-if="group.type === 'radio'"
                                                type="radio"
                                                class="form-radio"
                                                :value="option.id"
                                                v-model="form.configurable_options" 
                                            />
                                            <!-- Displaying price might need adjustment if pricing is per option value, not option itself -->
                                            <span class="ml-2">{{ option.name }} 
                                                <span v-if="option.option_pricings && option.option_pricings.length > 0">
                                                    ({{ option.option_pricings[0].price }})
                                                </span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <InputError :message="form.errors.configurable_options" class="mt-2" />
                            </div>

                            <!-- Notes Field -->
                            <div class="mb-6">
                                <label for="notes" class="block mb-2 text-sm font-medium text-gray-700">Notas Adicionales (Opcional):</label>
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    rows="3"
                                    class="block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                ></textarea>
                                <InputError :message="form.errors.notes" class="mt-2" />
                            </div>
                            
                            <!-- Precio Total Preliminar -->
                            <div class="mb-6 text-lg font-bold">
                                Precio Total Preliminar: {{ totalCost.toFixed(2) }}
                            </div>

                            <!-- Botón de Envío -->
                            <div>
                                <PrimaryButton type="submit" :disabled="form.processing">
                                    Solicitar Orden y Generar Factura
                                </PrimaryButton>
                            </div>
                        </form>
                        <p class="mt-4 text-sm text-gray-600">
                            Al hacer clic en "Solicitar Orden y Generar Factura", se creará una orden y se generará una factura pendiente de pago.
                            Serás redirigido a la factura para completar el pago.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Estilos específicos del componente si son necesarios */
</style>
