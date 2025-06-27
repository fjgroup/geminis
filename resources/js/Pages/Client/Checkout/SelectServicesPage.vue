<script setup>
import { ref, computed, onMounted, watchEffect } from 'vue'; // Importar watchEffect
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CartSummary from '@/Components/Client/CartSummary.vue';

const props = defineProps({
    initialCart: Object,
    mainServiceProducts: Array,
    sslProducts: Array,
    licenseProducts: Array,
});

const formPrimaryService = useForm({
    product_id: null,
    pricing_id: null,
    configurable_options: {}
});
const formAdditionalService = useForm({ product_id: null, pricing_id: null });

const currentCart = ref(null); // Se inicializará con watchEffect
const currentSelectedMainProduct = ref(null);
const selectedConfigurableOptions = ref({});
const activeDomainName = ref(''); // Se actualizará con watchEffect

// Reaccionar a los cambios en la prop initialCart
watchEffect(() => {
    console.log('SelectServicesPage: watchEffect - initialCart prop cambió o se inicializó.');
    // Crear una copia profunda para evitar modificar la prop directamente si fuera necesario,
    // aunque para visualización y re-renderizado, usarla directamente o una copia superficial es común.
    // Para este caso, si currentCart solo se usa para leer y pasar a otros componentes, props.initialCart es suficiente.
    // Si se necesita modificar localmente (ej. antes de una actualización del backend), una copia profunda es más segura.
    currentCart.value = JSON.parse(JSON.stringify(props.initialCart));

    if (currentCart.value?.accounts && currentCart.value.active_account_id) {
        const activeAccount = currentCart.value.accounts.find(acc => acc.account_id === currentCart.value.active_account_id);
        if (activeAccount && activeAccount.domain_info) {
            activeDomainName.value = activeAccount.domain_info.domain_name;
            console.log('SelectServicesPage: watchEffect - activeDomainName actualizado:', activeDomainName.value);
        } else {
            activeDomainName.value = 'Cuenta Activa (sin dominio)'; // Mensaje más claro
            console.log('SelectServicesPage: watchEffect - Cuenta activa sin nombre de dominio.');
        }
    } else {
         activeDomainName.value = 'N/A (Carrito no disponible o sin cuenta activa)';
         console.log('SelectServicesPage: watchEffect - Carrito no disponible o sin cuenta activa.');
    }
});


const formatCurrency = (value, currencyCode = 'USD') => {
    if (typeof value !== 'number' || isNaN(value)) return '';
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: currencyCode }).format(value);
};

const selectMainProductForConfiguration = (product) => {
    console.log('--- selectMainProductForConfiguration ---');
    console.log('Producto seleccionado para configurar:', product.id, product.name);
    currentSelectedMainProduct.value = product;
    if (!selectedConfigurableOptions.value[product.id]) {
        selectedConfigurableOptions.value[product.id] = {};
    }
    // console.log('Grupos de opciones configurables para el producto:', product.configurable_option_groups);
};

const areAllConfigurableOptionsSelected = (product, selections) => {
    if (!product || !product.configurable_option_groups || product.configurable_option_groups.length === 0) {
        return true;
    }
    for (const group of product.configurable_option_groups) {
        if (group.options && group.options.length > 0) {
            // Asumir que todas las opciones son requeridas si el grupo existe y tiene opciones.
            // Esta es una simplificación.
            if (!selections || !selections[group.id]) {
                console.warn(`Validación de opciones: Opción faltante para el grupo ${group.name} (ID: ${group.id})`);
                return false;
            }
        }
    }
    return true;
};

const handleSelectPrimaryService = (productId, pricingId) => {
    console.log('--- handleSelectPrimaryService INVOCADO ---');
    console.log('Producto ID:', productId, 'Pricing ID:', pricingId);

    if (!pricingId) {
        console.error('ERROR CRÍTICO: pricingId es nulo/indefinido.');
        alert('Por favor, selecciona un ciclo de facturación válido.');
        return;
    }

    formPrimaryService.product_id = productId;
    formPrimaryService.pricing_id = pricingId;

    const product = props.mainServiceProducts.find(p => p.id === productId);
    if (currentSelectedMainProduct.value && currentSelectedMainProduct.value.id === productId &&
        product && product.configurable_option_groups && product.configurable_option_groups.length > 0) {

        const productOptionsSelections = selectedConfigurableOptions.value[productId] || {};
        // console.log(`Opciones seleccionadas para producto ${productId}:`, JSON.parse(JSON.stringify(productOptionsSelections)));

        if (!areAllConfigurableOptionsSelected(product, productOptionsSelections)) {
            alert('Por favor, completa todas las opciones configurables requeridas para este plan.');
            return;
        }
        formPrimaryService.configurable_options = productOptionsSelections;
    } else {
        formPrimaryService.configurable_options = {};
    }

    console.log('Datos formPrimaryService ANTES POST:', JSON.parse(JSON.stringify(formPrimaryService.data())));
    formPrimaryService.post(route('client.cart.account.setPrimaryService'), {
        preserveScroll: true, // Inertia intentará mantener el scroll
        preserveState: false, // Permitir que las props se recarguen y actualicen la página. True puede prevenirlo.
                              // Con redirect back(), preserveState: false o no ponerlo es usualmente lo que se quiere
                              // para que las props (como initialCart y mensajes flash) se actualicen.
        onSuccess: (page) => {
            console.log('POST a setPrimaryService ÉXITO.');
            // Ya no es necesario actualizar currentCart.value desde page.props aquí,
            // watchEffect se encargará cuando props.initialCart cambie.
            window.dispatchEvent(new CustomEvent('cart-updated')); // Mantener por si CartSummary no usa props
            if (page.props.flash && page.props.flash.success) {
                // alert(page.props.flash.success); // O usar un sistema de notificaciones más elegante
            }
        },
        onError: (errors) => {
            console.error('POST a setPrimaryService FALLÓ:', errors);
            let errorMessages = 'Ocurrió un error.';
            if (errors && typeof errors === 'object') {
                errorMessages = Object.values(errors).join(' ');
            } else if (typeof errors === 'string') {
                errorMessages = errors;
            }
            alert(`Error al añadir servicio principal: ${errorMessages}`);
        },
        onFinish: () => {
            console.log('POST a setPrimaryService FINALIZADO.');
        }
    });
};

const handleAddAdditionalService = (productId, pricingId) => {
    console.log('--- handleAddAdditionalService INVOCADO ---');
    console.log('Producto Adicional ID:', productId, 'Pricing ID:', pricingId);
    formAdditionalService.product_id = productId;
    formAdditionalService.pricing_id = pricingId;

    console.log('Datos formAdditionalService ANTES POST:', JSON.parse(JSON.stringify(formAdditionalService.data())));
    formAdditionalService.post(route('client.cart.add'), {
        preserveScroll: true,
        preserveState: false, // Similar a arriba
        onSuccess: (page) => {
            console.log('POST a addItem (adicional) ÉXITO.');
            window.dispatchEvent(new CustomEvent('cart-updated'));
            if (page.props.flash && page.props.flash.success) {
                // alert(page.props.flash.success);
            }
        },
        onError: (errors) => {
            console.error('POST a addItem (adicional) FALLÓ:', errors);
             let errorMessages = 'Ocurrió un error.';
            if (errors && typeof errors === 'object') {
                errorMessages = Object.values(errors).join(' ');
            } else if (typeof errors === 'string') {
                errorMessages = errors;
            }
            alert(`Error al añadir servicio adicional: ${errorMessages}`);
        },
         onFinish: () => {
            console.log('POST a addItem (adicional) FINALIZADO.');
        }
    });
};

const goToFinalCheckout = () => {
    console.log('--- goToFinalCheckout INVOCADO ---');
    router.visit(route('client.checkout.confirm'));
};

onMounted(() => {
    // Inicializar selectedConfigurableOptions para cada producto principal que tenga opciones
    // y asegurar que currentCart y activeDomainName se establezcan inicialmente desde props.
    // watchEffect se encargará de esto y de futuras actualizaciones.
    // La llamada inicial a watchEffect ocurrirá después del montaje si props.initialCart está disponible.

    // Si props.initialCart puede no estar disponible inmediatamente (aunque Inertia usualmente lo asegura),
    // se podría forzar una actualización inicial aquí si es necesario, pero watchEffect es preferible.
    // currentCart.value = JSON.parse(JSON.stringify(props.initialCart));
    // (Lógica de activeDomainName también se movería aquí o se dejaría en watchEffect)

    props.mainServiceProducts.forEach(product => {
        if (product.configurable_option_groups && product.configurable_option_groups.length > 0) {
            if (!selectedConfigurableOptions.value[product.id]) {
                 selectedConfigurableOptions.value[product.id] = {};
            }
        }
    });
});

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
                    <div class="md:col-span-2 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-8">

                        <div>
                            <p class="mb-4 text-lg text-gray-700 dark:text-gray-300">
                                Añadiendo servicios para: <strong class="text-indigo-600">{{ activeDomainName || 'Cuenta Activa' }}</strong>
                            </p>

                            <section>
                                <h3 class="text-xl font-medium text-gray-900 dark:text-gray-100 mb-3">Servicios Principales (Elige uno)</h3>
                                <div class="space-y-4">
                                    <div v-for="product in props.mainServiceProducts" :key="product.id"
                                         class="p-4 border rounded-lg dark:border-gray-700"
                                         :class="{'ring-2 ring-indigo-500': currentSelectedMainProduct && currentSelectedMainProduct.id === product.id}">

                                        <div @click="selectMainProductForConfiguration(product)" class="cursor-pointer">
                                            <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ product.name }}</h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ product.description }}</p>
                                        </div>

                                        <div v-if="currentSelectedMainProduct && currentSelectedMainProduct.id === product.id && product.configurable_option_groups && product.configurable_option_groups.length > 0" class="my-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-md space-y-3">
                                            <h5 class="text-md font-semibold text-gray-700 dark:text-gray-200">Configura tu servicio:</h5>
                                            <div v-for="group in product.configurable_option_groups" :key="group.id" class="py-2">
                                                <label :for="'group_'+product.id+'_'+group.id" class="text-sm font-medium text-gray-800 dark:text-gray-300">{{ group.name }}:</label>
                                                <p v-if="group.description" class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{group.description}}</p>
                                                <select v-if="group.options && group.options.length > 0"
                                                        :id="'group_'+product.id+'_'+group.id"
                                                        v-model="selectedConfigurableOptions[product.id][group.id]"
                                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                                    <option :value="undefined">-- Selecciona {{ group.name.toLowerCase() }} --</option>
                                                    <option v-for="option in group.options" :key="option.id" :value="option.id">
                                                        {{ option.name }}
                                                    </option>
                                                </select>
                                                <div v-else class="text-xs text-gray-400 italic">No hay opciones disponibles para este grupo.</div>
                                            </div>
                                        </div>

                                        <div class="space-y-2 mt-3">
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Elige un ciclo de facturación:</p>
                                            <button v-for="pricing in product.pricings" :key="pricing.id"
                                                    @click="console.log(`Clic en ciclo: ProdID=${product.id}, PricingID=${pricing.id}`); handleSelectPrimaryService(product.id, pricing.id)"
                                                    :disabled="formPrimaryService.processing"
                                                    class="w-full text-left p-3 rounded-md hover:bg-indigo-50 dark:hover:bg-gray-700 border dark:border-gray-600 flex justify-between items-center">
                                                <span>{{ pricing.billing_cycle.name }}</span>
                                                <span class="font-semibold">{{ formatCurrency(pricing.price, pricing.currency_code ) }}</span>
                                            </button>
                                        </div>
                                    </div>
                                    <p v-if="formPrimaryService.errors.product_id" class="text-sm text-red-500">{{formPrimaryService.errors.product_id}}</p>
                                    <p v-if="formPrimaryService.errors.pricing_id" class="text-sm text-red-500">{{formPrimaryService.errors.pricing_id}}</p>
                                     <p v-if="formPrimaryService.errors.configurable_options" class="text-sm text-red-500">{{formPrimaryService.errors.configurable_options}}</p>
                                     <p v-if="formPrimaryService.errors.general_error" class="text-sm text-red-500">{{formPrimaryService.errors.general_error}}</p>
                                </div>
                            </section>

                            <section>
                                <h3 class="text-xl font-medium text-gray-900 dark:text-gray-100 mb-3 mt-6">Certificados SSL (Opcional)</h3>
                                 <div class="space-y-4">
                                    <div v-for="product in props.sslProducts" :key="product.id" class="p-4 border rounded-lg dark:border-gray-700">
                                        <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ product.name }}</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ product.description }}</p>
                                        <div class="space-y-2">
                                             <button v-for="pricing in product.pricings" :key="pricing.id"
                                                    @click="console.log(`Clic en SSL: ProdID=${product.id}, PricingID=${pricing.id}`); handleAddAdditionalService(product.id, pricing.id)"
                                                    :disabled="formAdditionalService.processing"
                                                    class="w-full text-left p-3 rounded-md hover:bg-green-50 dark:hover:bg-gray-700 border dark:border-gray-600 flex justify-between items-center">
                                                <span>{{ pricing.billing_cycle.name }}</span>
                                                <span class="font-semibold">{{ formatCurrency(pricing.price, pricing.currency_code) }}</span>
                                            </button>
                                        </div>
                                    </div>
                                     <p v-if="formAdditionalService.errors.general_error_ssl" class="text-sm text-red-500">{{formAdditionalService.errors.general_error_ssl}}</p>
                                </div>
                            </section>

                             <section>
                                <h3 class="text-xl font-medium text-gray-900 dark:text-gray-100 mb-3 mt-6">Licencias Adicionales (Opcional)</h3>
                                 <div class="space-y-4">
                                    <div v-for="product in props.licenseProducts" :key="product.id" class="p-4 border rounded-lg dark:border-gray-700">
                                        <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ product.name }}</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ product.description }}</p>
                                        <div class="space-y-2">
                                             <button v-for="pricing in product.pricings" :key="pricing.id"
                                                    @click="console.log(`Clic en Licencia: ProdID=${product.id}, PricingID=${pricing.id}`); handleAddAdditionalService(product.id, pricing.id)"
                                                    :disabled="formAdditionalService.processing"
                                                    class="w-full text-left p-3 rounded-md hover:bg-yellow-50 dark:hover:bg-gray-700 border dark:border-gray-600 flex justify-between items-center">
                                                <span>{{ pricing.billing_cycle.name }}</span>
                                                <span class="font-semibold">{{ formatCurrency(pricing.price, pricing.currency_code) }}</span>
                                            </button>
                                        </div>
                                    </div>
                                     <p v-if="formAdditionalService.errors.product_id" class="text-sm text-red-500">{{formAdditionalService.errors.product_id}}</p>
                                     <p v-if="formAdditionalService.errors.pricing_id" class="text-sm text-red-500">{{formAdditionalService.errors.pricing_id}}</p>
                                     <p v-if="formAdditionalService.errors.general_error_license" class="text-sm text-red-500">{{formAdditionalService.errors.general_error_license}}</p>
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
