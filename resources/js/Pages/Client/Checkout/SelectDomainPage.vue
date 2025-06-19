<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CartSummary from '@/Components/Client/CartSummary.vue'; // Ajusta la ruta si es necesario

// Estado reactivo
const domainNameInput = ref('');
const availabilityResult = ref(null); // Ej: { available: true, domain_name: 'example.com', is_new: true, message: 'Disponible!' } o { available: false, ... }
const domainProducts = ref([]); // Para TLDs si el dominio es nuevo y está disponible
const selectedDomainProductPricingId = ref(null); // ID del pricing del TLD seleccionado
const isLoadingAvailability = ref(false);

// Formulario para enviar la selección de dominio al carrito
const cartForm = useForm({
    domain_name: '',
    product_id: null, // Para el producto de registro de dominio (TLD)
    pricing_id: null,  // Para el pricing del producto de registro de dominio
});

// Simulación de la comprobación de disponibilidad de dominio
const checkAvailability = async () => {
    if (!domainNameInput.value.trim()) {
        availabilityResult.value = { available: false, message: 'Por favor, introduce un nombre de dominio.' };
        return;
    }
    isLoadingAvailability.value = true;
    availabilityResult.value = null;
    domainProducts.value = [];
    selectedDomainProductPricingId.value = null;

    // Simulación de llamada API
    await new Promise(resolve => setTimeout(resolve, 1000)); // Simular delay

    const randomOutcome = Math.random() > 0.4; // Simular disponibilidad
    if (randomOutcome) {
        availabilityResult.value = {
            available: true,
            domain_name: domainNameInput.value.trim(),
            is_new: true, // Asumir que siempre es nuevo por ahora para mostrar TLDs
            message: `¡El dominio ${domainNameInput.value.trim()} está disponible!`
        };
        // Simular obtención de TLDs para registro
        // En una implementación real, estos datos vendrían del backend
        // y filtrarías por product_type_id = 3 (Registro de Dominio)
        domainProducts.value = [
            {
                id: 101, name: '.com',
                pricings: [{id: 1001, term: '1 Año', price: 10.99, currency_code: 'USD', billing_cycle: { name: 'Anual' } }]
            },
            {
                id: 102, name: '.net',
                pricings: [{id: 1002, term: '1 Año', price: 12.99, currency_code: 'USD', billing_cycle: { name: 'Anual' } }]
            },
            {
                id: 103, name: '.org',
                pricings: [{id: 1003, term: '1 Año', price: 11.99, currency_code: 'USD', billing_cycle: { name: 'Anual' } }]
            },
        ];
        // Seleccionar el primer TLD por defecto si hay alguno
        if (domainProducts.value.length > 0 && domainProducts.value[0].pricings.length > 0) {
            selectedDomainProductPricingId.value = domainProducts.value[0].pricings[0].id;
        }
    } else {
        availabilityResult.value = {
            available: false,
            domain_name: domainNameInput.value.trim(),
            message: `El dominio ${domainNameInput.value.trim()} no está disponible o no es válido.`
        };
    }
    isLoadingAvailability.value = false;
};

// Enviar el dominio seleccionado al carrito
const submitDomainSelection = () => {
    if (!availabilityResult.value?.available) {
        alert('Por favor, verifica un dominio disponible primero.');
        return;
    }

    cartForm.domain_name = availabilityResult.value.domain_name;

    if (availabilityResult.value.is_new) {
        if (!selectedDomainProductPricingId.value) {
            alert('Por favor, selecciona una extensión (TLD) para tu nuevo dominio.');
            return;
        }
        // Encontrar el product_id y pricing_id del TLD seleccionado
        let foundPricing = null;
        let foundProduct = null;
        for (const product of domainProducts.value) {
            foundPricing = product.pricings.find(p => p.id === selectedDomainProductPricingId.value);
            if (foundPricing) {
                foundProduct = product;
                break;
            }
        }
        if (foundProduct && foundPricing) {
            cartForm.product_id = foundProduct.id;
            cartForm.pricing_id = foundPricing.id;
        } else {
            alert('La extensión seleccionada no es válida. Por favor, recarga la página e intenta de nuevo.');
            return;
        }
    } else {
        // Lógica para dominios existentes (transferencia/usar mi propio dominio) - no implementado en este paso
        cartForm.product_id = null;
        cartForm.pricing_id = null;
    }

    // console.log('Submitting to cart:', cartForm.data());

    cartForm.post(route('client.cart.account.setDomain'), {
        preserveScroll: true,
        preserveState: true, // Preservar estado para no recargar todo y perder el sumario del carrito
        onSuccess: () => {
            // Idealmente, el backend devolvería el active_account_id o todo el carrito
            // para que CartSummary se actualice si está escuchando eventos o props.
            // O forzar un refresh del CartSummary.
            // Por ahora, asumimos que CartSummary se refrescará en la siguiente página.
            window.dispatchEvent(new CustomEvent('cart-updated'));
            alert('Dominio añadido al carrito. Redirigiendo al siguiente paso...'); // Placeholder
            // Inertia.visit(route('client.checkout.selectServices')); // Descomentar cuando la ruta exista
        },
        onError: (errors) => {
            console.error('Error setting domain in cart:', errors);
            // Mostrar errores al usuario si es necesario
            if (errors.message) { // Asumiendo que el backend devuelve un error.message
                alert(`Error: ${errors.message}`);
            } else {
                alert('Ocurrió un error al añadir el dominio al carrito.');
            }
        }
    });
};

const canConfirmDomain = computed(() => {
    return availabilityResult.value?.available &&
           (availabilityResult.value.is_new ? selectedDomainProductPricingId.value !== null : true);
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
                    <!-- Columna Principal (Selección de Dominio) -->
                    <div class="md:col-span-2 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Busca tu Dominio Perfecto</h3>

                        <form @submit.prevent="checkAvailability" class="space-y-4">
                            <div>
                                <label for="domain_name_input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre de Dominio</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input type="text" v-model="domainNameInput" id="domain_name_input"
                                           class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-none rounded-l-md sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                           placeholder="ejemplo.com">
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

                        <div v-if="availabilityResult" class="mt-6 p-4 rounded-md"
                             :class="{'bg-green-50 border-green-300': availabilityResult.available, 'bg-red-50 border-red-300': !availabilityResult.available}">
                            <p class="font-medium" :class="{'text-green-700': availabilityResult.available, 'text-red-700': !availabilityResult.available}">
                                {{ availabilityResult.message }}
                            </p>
                        </div>

                        <div v-if="availabilityResult?.available && availabilityResult?.is_new && domainProducts.length > 0" class="mt-6">
                            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-2">Selecciona una extensión (TLD):</h4>
                            <div class="space-y-2">
                                <label v-for="product in domainProducts" :key="product.id"
                                       class="flex items-center p-3 rounded-md border hover:border-indigo-500 cursor-pointer"
                                       :class="{'bg-indigo-50 border-indigo-500 ring-2 ring-indigo-500': selectedDomainProductPricingId === product.pricings[0].id,
                                                'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700': selectedDomainProductPricingId !== product.pricings[0].id }">
                                    <input type="radio" :value="product.pricings[0].id" v-model="selectedDomainProductPricingId" class="form-radio h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                    <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ product.name }}</span>
                                    <span class="ml-auto text-sm text-gray-700 dark:text-gray-300">
                                        {{ product.pricings[0].price }} {{ product.pricings[0].currency_code }} ({{ product.pricings[0].billing_cycle.name }})
                                    </span>
                                </label>
                            </div>
                             <p v-if="cartForm.errors.product_id" class="mt-2 text-sm text-red-600">{{ cartForm.errors.product_id }}</p>
                             <p v-if="cartForm.errors.pricing_id" class="mt-2 text-sm text-red-600">{{ cartForm.errors.pricing_id }}</p>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button @click="submitDomainSelection"
                                    :disabled="!canConfirmDomain || cartForm.processing"
                                    class="px-6 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50">
                                <span v-if="cartForm.processing">Procesando...</span>
                                <span v-else>Confirmar Dominio y Continuar</span>
                            </button>
                        </div>

                    </div>

                    <!-- Columna Lateral (Resumen del Carrito) -->
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
