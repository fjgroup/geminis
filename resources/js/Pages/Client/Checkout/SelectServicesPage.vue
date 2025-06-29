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

// Computed para el nombre de la cuenta activa (evita problemas de sintaxis)
const displayAccountName = computed(() => {
    return activeDomainName.value || 'Cuenta Activa';
});

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

// Función para obtener el label del tipo de opción
const getOptionTypeLabel = (optionType) => {
    const types = {
        'dropdown': 'Lista desplegable',
        'radio': 'Selección única',
        'checkbox': 'Activar/Desactivar',
        'quantity': 'Cantidad',
        'text': 'Texto libre'
    };
    return types[optionType] || optionType;
};

// Función para obtener el precio de una opción para un producto específico
const getOptionPricing = (option, product) => {
    if (!option.pricings || !product.pricings) return null;

    // Buscar el pricing que coincida con el ciclo de facturación del producto
    // Por ahora, tomamos el primer pricing disponible
    return option.pricings.find(pricing => pricing.is_active) || option.pricings[0] || null;
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
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Paso 2: Selecciona tus
                Servicios</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                    <div class="p-6 space-y-8 bg-white shadow-sm md:col-span-2 dark:bg-gray-800 sm:rounded-lg">

                        <div>
                            <p class="mb-4 text-lg text-gray-700 dark:text-gray-300">
                                Añadiendo servicios para: <strong class="text-indigo-600">{{ displayAccountName
                                    }}</strong>
                            </p>

                            <section>
                                <h3 class="mb-3 text-xl font-medium text-gray-900 dark:text-gray-100">Servicios
                                    Principales
                                    (Elige uno)</h3>
                                <div class="space-y-4">
                                    <div v-for="product in props.mainServiceProducts" :key="product.id"
                                        class="p-4 border rounded-lg dark:border-gray-700"
                                        :class="{ 'ring-2 ring-indigo-500': currentSelectedMainProduct && currentSelectedMainProduct.id === product.id }">

                                        <div @click="selectMainProductForConfiguration(product)" class="cursor-pointer">
                                            <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{
                                                product.name
                                                }}</h4>
                                            <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">{{
                                                product.description }}
                                            </p>

                                            <!-- Recursos base incluidos -->
                                            <div v-if="product.base_disk_space_gb || product.base_vcpu_cores || product.base_ram_gb"
                                                class="mb-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                                <h5 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">
                                                    Recursos
                                                    incluidos:</h5>
                                                <div
                                                    class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs text-blue-700 dark:text-blue-300">
                                                    <div v-if="product.base_disk_space_gb">
                                                        <span class="font-medium">Disco:</span> {{
                                                            product.base_disk_space_gb
                                                        }}GB
                                                    </div>
                                                    <div v-if="product.base_vcpu_cores">
                                                        <span class="font-medium">vCPU:</span> {{
                                                            product.base_vcpu_cores }}
                                                        cores
                                                    </div>
                                                    <div v-if="product.base_ram_gb">
                                                        <span class="font-medium">RAM:</span> {{ product.base_ram_gb
                                                        }}GB
                                                    </div>
                                                    <div v-if="product.base_bandwidth_gb">
                                                        <span class="font-medium">Transferencia:</span> {{
                                                            product.base_bandwidth_gb }}GB
                                                    </div>
                                                    <div v-if="product.base_email_accounts">
                                                        <span class="font-medium">Emails:</span> {{
                                                            product.base_email_accounts
                                                        }}
                                                    </div>
                                                    <div v-if="product.base_databases">
                                                        <span class="font-medium">BD:</span> {{ product.base_databases
                                                        }}
                                                    </div>
                                                    <div v-if="product.base_domains">
                                                        <span class="font-medium">Dominios:</span> {{
                                                            product.base_domains }}
                                                    </div>
                                                    <div v-if="product.base_subdomains">
                                                        <span class="font-medium">Subdominios:</span> {{
                                                            product.base_subdomains
                                                        }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div v-if="currentSelectedMainProduct && currentSelectedMainProduct.id === product.id && product.configurable_option_groups && product.configurable_option_groups.length > 0"
                                            class="p-4 my-4 space-y-4 rounded-md bg-gray-50 dark:bg-gray-700">
                                            <h5 class="font-semibold text-gray-700 text-md dark:text-gray-200">Configura
                                                tu servicio:</h5>

                                            <div v-for="group in product.configurable_option_groups" :key="group.id"
                                                class="p-3 border border-gray-200 rounded-lg dark:border-gray-600">
                                                <div class="flex items-center justify-between mb-2">
                                                    <label class="text-sm font-medium text-gray-800 dark:text-gray-300">
                                                        {{ group.name }}
                                                        <span v-if="group.is_required" class="text-red-500">*</span>
                                                    </label>
                                                    <span v-if="group.is_required"
                                                        class="text-xs text-yellow-600 bg-yellow-100 px-2 py-1 rounded">
                                                        Obligatorio
                                                    </span>
                                                </div>

                                                <p v-if="group.description"
                                                    class="mb-3 text-xs text-gray-500 dark:text-gray-400">
                                                    {{ group.description }}
                                                </p>

                                                <div v-if="group.options && group.options.length > 0" class="space-y-3">
                                                    <div v-for="option in group.options" :key="option.id"
                                                        class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded border">
                                                        <div class="flex-1">
                                                            <div class="flex items-center space-x-3">
                                                                <!-- Checkbox para opciones no obligatorias -->
                                                                <input
                                                                    v-if="option.option_type === 'checkbox' || !group.is_required"
                                                                    :id="`option_${option.id}`" type="checkbox"
                                                                    v-model="selectedConfigurableOptions[product.id][option.id]"
                                                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">

                                                                <!-- Radio button para opciones obligatorias -->
                                                                <input v-else-if="group.is_required"
                                                                    :id="`option_${option.id}`" type="radio"
                                                                    :name="`group_${group.id}`" :value="option.id"
                                                                    v-model="selectedConfigurableOptions[product.id][group.id]"
                                                                    class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">

                                                                <label :for="`option_${option.id}`"
                                                                    class="flex-1 cursor-pointer">
                                                                    <div
                                                                        class="font-medium text-sm text-gray-900 dark:text-gray-100">
                                                                        {{ option.name }}
                                                                    </div>
                                                                    <div v-if="option.description"
                                                                        class="text-xs text-gray-500">
                                                                        {{ option.description }}
                                                                    </div>
                                                                    <div class="text-xs text-gray-400">
                                                                        Tipo: {{ getOptionTypeLabel(option.option_type)
                                                                        }}
                                                                        <span
                                                                            v-if="option.min_value || option.max_value">
                                                                            ({{ option.min_value || 0 }} - {{
                                                                            option.max_value
                                                                            || '∞' }})
                                                                        </span>
                                                                    </div>
                                                                    <!-- Mostrar precio si existe -->
                                                                    <div v-if="option.pricings && option.pricings.length > 0"
                                                                        class="text-xs text-green-600 dark:text-green-400 font-medium">
                                                                        {{ formatCurrency(option.pricings[0].price,
                                                                        option.pricings[0].currency_code) }}
                                                                        <span
                                                                            v-if="option.option_type === 'quantity'">por
                                                                            unidad</span>
                                                                        / {{ option.pricings[0].billing_cycle.name }}
                                                                    </div>
                                                                </label>
                                                            </div>

                                                            <!-- Input de cantidad para opciones de tipo quantity -->
                                                            <div v-if="option.option_type === 'quantity' && (selectedConfigurableOptions[product.id][option.id] || group.is_required)"
                                                                class="mt-2 ml-7">
                                                                <label
                                                                    class="block text-xs font-medium text-gray-700 mb-1">
                                                                    Cantidad:
                                                                </label>
                                                                <input type="number" :min="option.min_value || 1"
                                                                    :max="option.max_value || 999"
                                                                    v-model="selectedConfigurableOptions[product.id][`${option.id}_quantity`]"
                                                                    class="w-24 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                                            </div>
                                                        </div>

                                                        <!-- Precio de la opción -->
                                                        <div class="text-right ml-4">
                                                            <div v-if="getOptionPricing(option, product)"
                                                                class="text-sm">
                                                                <div
                                                                    class="font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ formatCurrency(getOptionPricing(option,
                                                                        product).price)
                                                                    }}
                                                                </div>
                                                                <div class="text-xs text-gray-500">
                                                                    {{ option.option_type === 'quantity' ? '/ unidad' :
                                                                        '' }}
                                                                </div>
                                                                <div v-if="getOptionPricing(option, product).setup_fee > 0"
                                                                    class="text-xs text-gray-400">
                                                                    Setup: {{ formatCurrency(getOptionPricing(option,
                                                                        product).setup_fee) }}
                                                                </div>
                                                            </div>
                                                            <div v-else class="text-xs text-gray-400">
                                                                Sin precio
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div v-else class="text-xs italic text-gray-400">
                                                    No hay opciones disponibles para este grupo.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Elige un ciclo de facturación:</p>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                                <button v-for="pricing in product.pricings" :key="pricing.id"
                                                    @click="console.log(`Clic en ciclo: ProdID=${product.id}, PricingID=${pricing.id}`); handleSelectPrimaryService(product.id, pricing.id)"
                                                    :disabled="formPrimaryService.processing"
                                                    class="flex flex-col items-center justify-center p-3 text-center border rounded-md hover:bg-indigo-50 dark:hover:bg-gray-700 dark:border-gray-600 transition-colors duration-200">
                                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ pricing.billing_cycle.name }}</span>
                                                    <div class="flex flex-col items-center mt-1">
                                                        <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                                                            {{ formatCurrency(pricing.price, pricing.currency_code) }}
                                                        </span>
                                                        <span v-if="pricing.billing_cycle.discount_percentage > 0"
                                                              class="text-xs text-green-600 dark:text-green-400 font-medium bg-green-100 dark:bg-green-900/30 px-2 py-1 rounded-full">
                                                            -{{ pricing.billing_cycle.discount_percentage }}% descuento
                                                        </span>
                                                        <span v-else class="text-xs text-gray-400">
                                                            Precio regular
                                                        </span>
                                                    </div>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <p v-if="formPrimaryService.errors.product_id" class="text-sm text-red-500">
                                        {{ formPrimaryService.errors.product_id }}</p>
                                    <p v-if="formPrimaryService.errors.pricing_id" class="text-sm text-red-500">
                                        {{ formPrimaryService.errors.pricing_id }}</p>
                                    <p v-if="formPrimaryService.errors.configurable_options"
                                        class="text-sm text-red-500">
                                        {{ formPrimaryService.errors.configurable_options }}</p>
                                    <p v-if="formPrimaryService.errors.general_error" class="text-sm text-red-500">
                                        {{ formPrimaryService.errors.general_error }}</p>
                                </div>
                            </section>

                            <section>
                                <h3 class="mt-6 mb-3 text-xl font-medium text-gray-900 dark:text-gray-100">Certificados
                                    SSL
                                    (Opcional)</h3>
                                <div class="space-y-4">
                                    <div v-for="product in props.sslProducts" :key="product.id"
                                        class="p-4 border rounded-lg dark:border-gray-700">
                                        <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{
                                            product.name }}
                                        </h4>
                                        <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">{{ product.description
                                            }}</p>
                                        <div class="space-y-2">
                                            <button v-for="pricing in product.pricings" :key="pricing.id"
                                                @click="console.log(`Clic en SSL: ProdID=${product.id}, PricingID=${pricing.id}`); handleAddAdditionalService(product.id, pricing.id)"
                                                :disabled="formAdditionalService.processing"
                                                class="flex items-center justify-between w-full p-3 text-left border rounded-md hover:bg-green-50 dark:hover:bg-gray-700 dark:border-gray-600">
                                                <span>{{ pricing.billing_cycle.name }}</span>
                                                <span class="font-semibold">{{ formatCurrency(pricing.price,
                                                    pricing.currency_code) }}</span>
                                            </button>
                                        </div>
                                    </div>
                                    <p v-if="formAdditionalService.errors.general_error_ssl"
                                        class="text-sm text-red-500">
                                        {{ formAdditionalService.errors.general_error_ssl }}</p>
                                </div>
                            </section>

                            <section>
                                <h3 class="mt-6 mb-3 text-xl font-medium text-gray-900 dark:text-gray-100">Licencias
                                    Adicionales
                                    (Opcional)</h3>
                                <div class="space-y-4">
                                    <div v-for="product in props.licenseProducts" :key="product.id"
                                        class="p-4 border rounded-lg dark:border-gray-700">
                                        <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{
                                            product.name }}
                                        </h4>
                                        <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">{{ product.description
                                            }}</p>
                                        <div class="space-y-2">
                                            <button v-for="pricing in product.pricings" :key="pricing.id"
                                                @click="console.log(`Clic en Licencia: ProdID=${product.id}, PricingID=${pricing.id}`); handleAddAdditionalService(product.id, pricing.id)"
                                                :disabled="formAdditionalService.processing"
                                                class="flex items-center justify-between w-full p-3 text-left border rounded-md hover:bg-yellow-50 dark:hover:bg-gray-700 dark:border-gray-600">
                                                <span>{{ pricing.billing_cycle.name }}</span>
                                                <span class="font-semibold">{{ formatCurrency(pricing.price,
                                                    pricing.currency_code) }}</span>
                                            </button>
                                        </div>
                                    </div>
                                    <p v-if="formAdditionalService.errors.product_id" class="text-sm text-red-500">
                                        {{ formAdditionalService.errors.product_id }}</p>
                                    <p v-if="formAdditionalService.errors.pricing_id" class="text-sm text-red-500">
                                        {{ formAdditionalService.errors.pricing_id }}</p>
                                    <p v-if="formAdditionalService.errors.general_error_license"
                                        class="text-sm text-red-500">
                                        {{ formAdditionalService.errors.general_error_license }}</p>
                                </div>
                            </section>
                        </div>

                        <div class="flex justify-between mt-8">
                            <button @click="router.visit(route('client.checkout.selectDomain'))"
                                :disabled="formPrimaryService.processing || formAdditionalService.processing"
                                class="px-6 py-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                                ← Regresar a Dominios
                            </button>
                            <button @click="goToFinalCheckout"
                                :disabled="formPrimaryService.processing || formAdditionalService.processing"
                                class="px-6 py-3 text-base font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Continuar al Pago
                            </button>
                        </div>
                    </div>

                    <div class="md:col-span-1">
                        <div class="sticky top-6">
                            <CartSummary ref="cartSummaryComp" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Estilos específicos si son necesarios */
</style>
