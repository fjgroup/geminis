<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3'; // router para Inertia.visit
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CartSummary from '@/Components/Client/CartSummary.vue'; // Ajusta la ruta si es necesario

// Props definidas por el controlador
const props = defineProps({
    initialCart: Object,
    mainServiceProducts: Array,
    sslProducts: Array,
    licenseProducts: Array,
});

// Forms para añadir servicios al carrito
const formPrimaryService = useForm({ product_id: null, pricing_id: null });
const formAdditionalService = useForm({ product_id: null, pricing_id: null });

// Estado reactivo
const currentCart = ref(props.initialCart); // Usar el carrito inicial pasado como prop
const isLoadingProducts = ref(false); // Ya no se cargan aquí, vienen como props

const currentSelectedMainProduct = ref(null); // Para mostrar opciones del producto seleccionado
const selectedConfigurableOptions = ref({}); // { groupId: optionId }

// Dominio activo de la cuenta activa en el carrito
const activeDomainName = computed(() => {
    if (currentCart.value && currentCart.value.accounts && currentCart.value.active_account_id) {
        const activeAccount = currentCart.value.accounts.find(acc => acc.account_id === currentCart.value.active_account_id);
        return activeAccount?.domain_info?.domain_name || 'N/A (Selecciona un dominio)';
    }
    return 'N/A (Carrito no disponible o sin cuenta activa)';
});

// No se necesita fetchProducts, los productos vienen como props.
// onMounted(() => { ... }); // Ya no es necesario para cargar productos y carrito inicial


const selectMainProductForConfiguration = (product) => {
    currentSelectedMainProduct.value = product;
    selectedConfigurableOptions.value = {}; // Resetear opciones al cambiar de producto base
    // Pre-seleccionar la primera opción de cada grupo si es un select/radio (opcional)
    if (product && product.configurable_option_groups) {
        product.configurable_option_groups.forEach(group => {
            if (group.options && group.options.length > 0 && (group.option_type === 'select' || group.option_type === 'radio')) {
                // Esta es una simplificación. La opción por defecto podría venir del backend.
                // selectedConfigurableOptions.value[group.id] = group.options[0].id;
            }
        });
    }
};

// Seleccionar/Añadir servicio principal
const handleSelectPrimaryService = (productId, pricingId) => {
    // Encuentra el producto para verificar si tiene opciones configurables que requieran selección
    const product = props.mainServiceProducts.find(p => p.id === productId);
    if (product && product.configurable_option_groups && product.configurable_option_groups.length > 0) {
        // Validar que todas las opciones configurables requeridas estén seleccionadas
        for (const group of product.configurable_option_groups) {
            // Asumiendo que todas las opciones son requeridas por ahora si el grupo existe
            if (!selectedConfigurableOptions.value[group.id] && group.options.length > 0) {
                 alert(`Por favor, selecciona una opción para "${group.name}".`);
                 currentSelectedMainProduct.value = product; // Asegurarse que se muestran las opciones
                 return;
            }
        }
    }

    formPrimaryService.product_id = productId;
    formPrimaryService.pricing_id = pricingId;
    formPrimaryService.configurable_options = selectedConfigurableOptions.value; // Enviar opciones seleccionadas

    formPrimaryService.post(route('client.cart.account.setPrimaryService'), {
        preserveScroll: true,
        onSuccess: () => {
            alert('Servicio principal añadido/actualizado.');
            // Forzar actualización de CartSummary o emitir evento
            window.dispatchEvent(new CustomEvent('cart-updated'));
            // Actualizar el currentCart localmente para reflejar cambios podría ser complejo
            // sin una respuesta completa del carrito. Es mejor depender del evento y que CartSummary se actualice.
            // Si el backend devolviera el carrito actualizado en la respuesta del POST, podríamos usarlo:
            // currentCart.value = response.data.cart; (ejemplo)
        },
        onError: (errors) => {
            handleFormError(errors, 'servicio principal');
        }
    });
};

// Añadir servicio adicional (SSL, Licencia)
const handleAddAdditionalService = (productId, pricingId) => {
    formAdditionalService.product_id = productId;
    formAdditionalService.pricing_id = pricingId;
    formAdditionalService.post(route('client.cart.add'), {
        preserveScroll: true,
        onSuccess: () => {
            alert('Servicio adicional añadido.');
            window.dispatchEvent(new CustomEvent('cart-updated'));
        },
        onError: (errors) => {
            handleFormError(errors, 'servicio adicional');
        }
    });
};

const handleFormError = (errors, serviceType) => {
    console.error(`Error añadiendo ${serviceType}:`, errors);
    if (errors.message) {
        alert(`Error: ${errors.message}`);
    } else if (Object.keys(errors).length > 0) {
        alert(`Error al añadir ${serviceType}: ${JSON.stringify(errors)}`);
    } else {
        alert(`Ocurrió un error desconocido al añadir ${serviceType}.`);
    }
};

// Ir al siguiente paso del checkout
const goToFinalCheckout = () => {
    // router.visit(route('client.checkout.confirm')); // Descomentar cuando la ruta exista
    alert('Redirigiendo a la página de confirmación de pedido...'); // Placeholder
};

// Idealmente, para saber qué deshabilitar, necesitaríamos leer el estado actual del carrito.
// Esto se puede hacer si CartSummary emite el `cartData` o si usamos un store global.
// Por ahora, esta lógica de deshabilitación no está implementada.

</script>

<template>
    <Head title="Seleccionar Servicios" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Paso 2: Selecciona tus Servicios</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Columna Principal (Selección de Servicios) -->
                    <div class="md:col-span-2 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-8">

                        <!-- <div v-if="isLoadingProducts" class="text-center text-gray-500"> // Ya no se usa
                            Cargando productos...
                        </div> -->
                        <div>
                            <p class="mb-4 text-lg text-gray-700 dark:text-gray-300">
                                Añadiendo servicios para: <strong class="text-indigo-600">{{ activeDomainName || 'Cuenta Activa' }}</strong>
                            </p>

                            <!-- Sección Servicios Principales -->
                            <section>
                                <h3 class="text-xl font-medium text-gray-900 dark:text-gray-100 mb-3">Servicios Principales (Elige uno)</h3>
                                <div class="space-y-4">
                                    <div v-for="product in props.mainServiceProducts" :key="product.id"
                                         class="p-4 border rounded-lg dark:border-gray-700"
                                         :class="{'ring-2 ring-indigo-500': currentSelectedMainProduct && currentSelectedMainProduct.id === product.id}">

                                        <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ product.name }}</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ product.description }}</p>

                                        <!-- Opciones Configurables (Se muestran si este producto está seleccionado para configurar) -->
                                        <div v-if="currentSelectedMainProduct && currentSelectedMainProduct.id === product.id && product.configurable_option_groups && product.configurable_option_groups.length > 0" class="my-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-md space-y-3">
                                            <h5 class="text-md font-semibold text-gray-700 dark:text-gray-200">Configura tu servicio:</h5>
                                            <div v-for="group in product.configurable_option_groups" :key="group.id" class="py-2">
                                                <label :for="'group_'+group.id" class="text-sm font-medium text-gray-800 dark:text-gray-300">{{ group.name }}:</label>
                                                <p v-if="group.description" class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{group.description}}</p>
                                                <select v-if="group.options && group.options.length > 0"
                                                        :id="'group_'+group.id"
                                                        v-model="selectedConfigurableOptions[group.id]"
                                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                                    <option :value="undefined">-- Selecciona {{ group.name.toLowerCase() }} --</option>
                                                    <option v-for="option in group.options" :key="option.id" :value="option.id">
                                                        {{ option.name }}
                                                        <!-- Asumir que la opción no tiene precio propio por ahora, o se manejaría más complejo -->
                                                    </option>
                                                </select>
                                                <div v-else class="text-xs text-gray-400 italic">No hay opciones disponibles para este grupo.</div>
                                            </div>
                                        </div>

                                        <!-- Botones de Selección de Ciclo de Facturación / Precio -->
                                        <div class="space-y-2 mt-3">
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Elige un ciclo de facturación:</p>
                                            <button v-for="pricing in product.pricings" :key="pricing.id"
                                                    @click="currentSelectedMainProduct && currentSelectedMainProduct.id === product.id ? handleSelectPrimaryService(product.id, pricing.id) : selectMainProductForConfiguration(product)"
                                                    :disabled="formPrimaryService.processing"
                                                    class="w-full text-left p-3 rounded-md hover:bg-indigo-50 dark:hover:bg-gray-700 border dark:border-gray-600 flex justify-between items-center">
                                                <span>{{ pricing.billing_cycle.name }}</span>
                                                <span class="font-semibold">{{ pricing.price }} {{ pricing.currency_code }}</span>
                                            </button>
                                        </div>
                                    </div>
                                    <p v-if="formPrimaryService.errors.product_id" class="text-sm text-red-500">{{formPrimaryService.errors.product_id}}</p>
                                    <p v-if="formPrimaryService.errors.pricing_id" class="text-sm text-red-500">{{formPrimaryService.errors.pricing_id}}</p>
                                </div>
                            </section>

                            <!-- Sección Certificados SSL -->
                            <section>
                                <h3 class="text-xl font-medium text-gray-900 dark:text-gray-100 mb-3 mt-6">Certificados SSL (Opcional)</h3>
                                 <div class="space-y-4">
                                    <div v-for="product in props.sslProducts" :key="product.id" class="p-4 border rounded-lg dark:border-gray-700">
                                        <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ product.name }}</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ product.description }}</p>
                                        <div class="space-y-2">
                                             <button v-for="pricing in product.pricings" :key="pricing.id"
                                                    @click="handleAddAdditionalService(product.id, pricing.id)"
                                                    :disabled="formAdditionalService.processing"
                                                    class="w-full text-left p-3 rounded-md hover:bg-green-50 dark:hover:bg-gray-700 border dark:border-gray-600 flex justify-between items-center">
                                                <span>{{ pricing.billing_cycle.name }}</span>
                                                <span class="font-semibold">{{ pricing.price }} {{ pricing.currency_code }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Sección Licencias -->
                             <section>
                                <h3 class="text-xl font-medium text-gray-900 dark:text-gray-100 mb-3 mt-6">Licencias Adicionales (Opcional)</h3>
                                 <div class="space-y-4">
                                    <div v-for="product in props.licenseProducts" :key="product.id" class="p-4 border rounded-lg dark:border-gray-700">
                                        <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ product.name }}</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ product.description }}</p>
                                        <div class="space-y-2">
                                             <button v-for="pricing in product.pricings" :key="pricing.id"
                                                    @click="handleAddAdditionalService(product.id, pricing.id)"
                                                    :disabled="formAdditionalService.processing"
                                                    class="w-full text-left p-3 rounded-md hover:bg-yellow-50 dark:hover:bg-gray-700 border dark:border-gray-600 flex justify-between items-center">
                                                <span>{{ pricing.billing_cycle.name }}</span>
                                                <span class="font-semibold">{{ pricing.price }} {{ pricing.currency_code }}</span>
                                            </button>
                                        </div>
                                    </div>
                                     <p v-if="formAdditionalService.errors.product_id" class="text-sm text-red-500">{{formAdditionalService.errors.product_id}}</p>
                                     <p v-if="formAdditionalService.errors.pricing_id" class="text-sm text-red-500">{{formAdditionalService.errors.pricing_id}}</p>
                                </div>
                            </section>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button @click="goToFinalCheckout"
                                    :disabled="formPrimaryService.processing || formAdditionalService.processing"
                                    class="px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Continuar al Pago
                            </button>
                        </div>
                    </div>

                    <!-- Columna Lateral (Resumen del Carrito) -->
                    <div class="md:col-span-1">
                        <CartSummary ref="cartSummaryComp" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Estilos específicos si son necesarios */
</style>
