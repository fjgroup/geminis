<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3'; // router importado
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CartSummary from '@/Components/Client/CartSummary.vue';
import axios from 'axios';

// Estado reactivo
const domainNameInput = ref('');
const availabilityResult = ref(null);
const domainProducts = ref([]);
const selectedDomainProductPricingId = ref(null); // Este será el ID de TU ProductPricing interno
const isLoadingAvailability = ref(false);
const isLoadingTlds = ref(false);
const availabilityError = ref(null);
const tldError = ref(null);
const nameSiloRegistrationPrice = ref(null); // Para almacenar el precio específico de NameSilo

const cartForm = useForm({
    domain_name: '',
    product_id: null,       // Tu internal_product_id para el TLD
    pricing_id: null,       // Tu internal_pricing_id para el TLD y ciclo (ej. 1 año)
    override_price: null    // El precio de NameSilo para el registro inicial
});

// Helper para formatear moneda (si no lo tienes global)
const formatCurrency = (value, currencyCode = 'USD') => {
    if (typeof value !== 'number') return value;
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: currencyCode }).format(value);
};

const fetchTldProducts = async () => {
    isLoadingTlds.value = true;
    tldError.value = null;
    domainProducts.value = [];
    try {
        const response = await axios.get(route('api.domain.tldPricingInfo'));
        if (response.data && response.data.status === 'success' && Array.isArray(response.data.data)) {
            domainProducts.value = response.data.data.map(tldData => ({
                id: tldData.internal_product_id,
                tld: tldData.tld,
                name: `.${tldData.tld}`,
                internal_product_id: tldData.internal_product_id,
                pricings: tldData.pricings.map(p => ({
                    id: p.id,
                    term: p.term,
                    price: p.price, // Este es TU precio de venta para renovación/transferencia, etc.
                    currency_code: p.currency_code,
                    billing_cycle: { name: p.term }
                }))
            }));
            if (domainProducts.value.length > 0 && domainProducts.value[0].pricings.length > 0) {
                selectedDomainProductPricingId.value = domainProducts.value[0].pricings[0].id;
            }
        } else {
            throw new Error('Respuesta de precios de TLD no válida.');
        }
    } catch (error) {
        console.error("Error fetching TLD products:", error);
        tldError.value = error.response?.data?.message || error.message || 'Error al cargar extensiones de dominio.';
    } finally {
        isLoadingTlds.value = false;
    }
};

onMounted(fetchTldProducts);

const checkAvailability = async () => {
    if (!domainNameInput.value.trim()) {
        availabilityResult.value = { available: false, message: 'Por favor, introduce un nombre de dominio.' };
        return;
    }
    isLoadingAvailability.value = true;
    availabilityResult.value = null;
    availabilityError.value = null;
    nameSiloRegistrationPrice.value = null; // Resetear precio de NameSilo

    try {
        const response = await axios.get(route('api.domain.checkAvailability'), {
            params: { domain: domainNameInput.value.trim() }
        });
        if (response.data && response.data.status === 'success') {
            availabilityResult.value = response.data.data;
            if (availabilityResult.value.available && availabilityResult.value.price !== null) {
                nameSiloRegistrationPrice.value = parseFloat(availabilityResult.value.price);
            }
        } else {
            throw new Error(response.data.message || 'Respuesta de disponibilidad no válida.');
        }
    } catch (error) {
        console.error("Error checking domain availability:", error);
        const message = error.response?.data?.message || error.message || 'Error al verificar disponibilidad del dominio.';
        availabilityResult.value = { available: false, message: message };
        availabilityError.value = message;
    } finally {
        isLoadingAvailability.value = false;
    }
};

const submitDomainSelection = () => {
    if (!availabilityResult.value?.available) {
        alert('Por favor, verifica un dominio disponible primero.');
        return;
    }

    cartForm.domain_name = availabilityResult.value.domain_name; // Usar el nombre verificado (puede incluir TLD)

    if (availabilityResult.value.is_new) {
        if (!selectedDomainProductPricingId.value) {
            alert('Por favor, selecciona una extensión (TLD) para tu nuevo dominio.');
            return;
        }

        const selectedPricingId = selectedDomainProductPricingId.value;
        let productIdToSubmit = null;

        for (const tldProduct of domainProducts.value) {
            const foundPricing = tldProduct.pricings.find(p => p.id === selectedPricingId);
            if (foundPricing) {
                productIdToSubmit = tldProduct.internal_product_id;
                break;
            }
        }

        if (productIdToSubmit && selectedPricingId) {
            cartForm.product_id = productIdToSubmit;
            cartForm.pricing_id = selectedPricingId; // Tu ProductPricing ID interno
            cartForm.override_price = nameSiloRegistrationPrice.value; // Precio de NameSilo para el registro
        } else {
            alert('La extensión seleccionada no es válida. Por favor, recarga la página e intenta de nuevo.');
            return;
        }
    } else {
        cartForm.product_id = null;
        cartForm.pricing_id = null;
        cartForm.override_price = null; // No override price para transferencias u otros casos por ahora
    }

    cartForm.post(route('client.cart.account.setDomain'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            window.dispatchEvent(new CustomEvent('cart-updated'));
            // alert('Dominio añadido al carrito. Redirigiendo al siguiente paso...'); // Ya no es necesario con la redirección
            router.visit(route('client.checkout.selectServices'));
        },
        onError: (errors) => {
            console.error('Error al configurar el dominio en el carrito:', errors);
            if (errors.message) {
                alert(`Error: ${errors.message}`);
            } else if (Object.keys(errors).length > 0) {
                alert(`Error: ${JSON.stringify(errors)}`);
            } else {
                alert('Ocurrió un error al añadir el dominio al carrito.');
            }
        }
    });
};

const canConfirmDomain = computed(() => {
    if (!availabilityResult.value?.available) return false;
    if (availabilityResult.value.is_new) {
        // Solo se requiere seleccionar un TLD (ProductPricing interno).
        // El nameSiloRegistrationPrice es opcional; si no está, se usará el precio interno.
        return selectedDomainProductPricingId.value !== null;
    }
    // Para transferencias u otros (no 'is_new'), podríamos necesitar lógica diferente aquí.
    // Por ahora, si está disponible y no es nuevo, se permite continuar (lógica de transferencia no implementada).
    return true;
});

</script>

<template>
    <Head title="Seleccionar Dominio" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Paso 1: Selecciona tu Dominio</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="md:col-span-2 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Busca tu Dominio Perfecto</h3>

                        <form @submit.prevent="checkAvailability" class="space-y-4">
                            <div>
                                <label for="domain_name_input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre de Dominio</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input type="text" v-model="domainNameInput" id="domain_name_input"
                                           class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-none rounded-l-md sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                           placeholder="ejemplo">
                                    <button type="submit"
                                            :disabled="isLoadingAvailability || !domainNameInput.trim()"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-r-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                        <span v-if="isLoadingAvailability">Verificando...</span>
                                        <span v-else>Verificar</span>
                                    </button>
                                </div>
                                <p v-if="cartForm.errors.domain_name" class="mt-2 text-sm text-red-600">{{ cartForm.errors.domain_name }}</p>
                            </div>
                        </form>

                        <div v-if="availabilityError" class="mt-6 p-4 rounded-md bg-red-50 border-red-300 text-red-700">
                            <p>{{ availabilityError }}</p>
                        </div>
                        <div v-if="availabilityResult" class="mt-6 p-4 rounded-md"
                             :class="{'bg-green-50 border-green-300': availabilityResult.available, 'bg-red-50 border-red-300': !availabilityResult.available && !availabilityError}">
                            <p class="font-medium" :class="{'text-green-700': availabilityResult.available, 'text-red-700': !availabilityResult.available}">
                                {{ availabilityResult.message }}
                            </p>
                            <p v-if="availabilityResult.available && nameSiloRegistrationPrice !== null" class="text-sm"
                               :class="{'text-green-700': availabilityResult.available, 'text-red-700': !availabilityResult.available}">
                                Precio de Registro: {{ formatCurrency(nameSiloRegistrationPrice, availabilityResult.currency_code || 'USD') }}
                            </p>
                        </div>

                        <div v-if="isLoadingTlds" class="mt-6 text-center text-gray-500">
                            <p>Cargando extensiones de dominio...</p>
                        </div>
                        <div v-if="tldError" class="mt-6 p-4 rounded-md bg-red-50 border-red-300 text-red-700">
                            <p>{{ tldError }}</p>
                        </div>

                        <div v-if="availabilityResult?.available && availabilityResult?.is_new && domainProducts.length > 0 && !isLoadingTlds && !tldError" class="mt-6">
                            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-2">
                                Tu dominio <strong class="text-indigo-600">{{ availabilityResult.domain_name }}</strong> está disponible.
                                <span v-if="nameSiloRegistrationPrice !== null">Precio de registro: <strong>{{ formatCurrency(nameSiloRegistrationPrice, availabilityResult.currency_code || 'USD') }}</strong>.</span>
                                <br>Selecciona la extensión (TLD) que deseas registrar:
                            </h4>
                            <div class="space-y-2">
                                <label v-for="tldProduct in domainProducts" :key="tldProduct.internal_product_id"
                                       class="flex items-center p-3 rounded-md border hover:border-indigo-500 cursor-pointer"
                                       :class="{'bg-indigo-50 border-indigo-500 ring-2 ring-indigo-500': selectedDomainProductPricingId === tldProduct.pricings[0].id,
                                                'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700': selectedDomainProductPricingId !== tldProduct.pricings[0].id }">
                                    <input type="radio" :value="tldProduct.pricings[0].id" v-model="selectedDomainProductPricingId" class="form-radio h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                    <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ tldProduct.name }}</span>
                                    <span class="ml-auto text-sm text-gray-700 dark:text-gray-300" v-if="tldProduct.pricings.length > 0">
                                        <!-- Mostrar precio de renovación (tu precio interno) -->
                                        Renovación: {{ formatCurrency(tldProduct.pricings[0].price, tldProduct.pricings[0].currency_code) }} ({{ tldProduct.pricings[0].term }})
                                    </span>
                                    <span v-else class="ml-auto text-sm text-gray-500">Precio no disponible</span>
                                </label>
                            </div>
                             <p v-if="cartForm.errors.product_id" class="mt-2 text-sm text-red-600">{{ cartForm.errors.product_id }}</p>
                             <p v-if="cartForm.errors.pricing_id" class="mt-2 text-sm text-red-600">{{ cartForm.errors.pricing_id }}</p>
                             <p v-if="cartForm.errors.override_price" class="mt-2 text-sm text-red-600">{{ cartForm.errors.override_price }}</p>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button @click="submitDomainSelection"
                                    :disabled="!canConfirmDomain || cartForm.processing || isLoadingAvailability || isLoadingTlds"
                                    class="px-6 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50">
                                <span v-if="cartForm.processing">Procesando...</span>
                                <span v-else>Confirmar Dominio y Continuar</span>
                            </button>
                        </div>
                    </div>

                    <div class="md:col-span-1">
                        <CartSummary />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Puedes añadir estilos específicos si Tailwind no es suficiente */
</style>
