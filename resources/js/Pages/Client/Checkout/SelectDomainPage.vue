<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CartSummary from '@/Components/Client/CartSummary.vue';
import axios from 'axios';

const props = defineProps({
    genericDomainProductId: Number,
    genericDomainPricingId: Number,
});

// Estado reactivo
const sldInput = ref(''); // Solo el SLD, ej. "midominio"
const tldListFromApi = ref([]);
const selectedTldObject = ref(null); // Objeto completo del TLD seleccionado de tldListFromApi
const availabilityResult = ref(null);
const nameSiloRegistrationPrice = ref(null);
const isLoadingAvailability = ref(false);
const isLoadingTlds = ref(false);
const availabilityError = ref(null);
const tldError = ref(null);

// Nuevo estado para manejar dominios existentes
const domainType = ref('new'); // 'new' o 'existing'
const existingDomainInput = ref(''); // Para dominios existentes completos

const cartForm = useForm({
    domain_name: '',        // FQDN final
    override_price: null,
    tld_extension: null,    // ej. "com" (solo la extensión)
    product_id: props.genericDomainProductId,
    pricing_id: props.genericDomainPricingId
});

const formatCurrency = (value, currencyCode = 'USD') => {
    if (typeof value !== 'number' || isNaN(value)) return '';
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: currencyCode }).format(value);
};

const constructedFqdn = computed(() => {
    if (sldInput.value.trim() && selectedTldObject.value?.tld) {
        return sldInput.value.trim().toLowerCase() + '.' + selectedTldObject.value.tld;
    }
    return null;
});

// Función para determinar si un TLD está en promoción
const isPromotionalTld = (tldData) => {
    if (!tldData?.name_silo_info) return false;
    const { registration, renewal } = tldData.name_silo_info;
    if (typeof registration !== 'number' || typeof renewal !== 'number') return false;

    // Consideramos promocional si el precio de registro es significativamente menor al de renovación
    const discountThreshold = 0.7; // 30% o más de descuento
    return registration < (renewal * discountThreshold);
};

// Separar TLDs en promocionales y regulares
const promotionalTlds = computed(() => {
    return tldListFromApi.value.filter(tld => isPromotionalTld(tld));
});

const regularTlds = computed(() => {
    return tldListFromApi.value.filter(tld => !isPromotionalTld(tld));
});

const fetchTldProducts = async () => {
    isLoadingTlds.value = true;
    tldError.value = null;
    tldListFromApi.value = [];
    try {
        const response = await axios.get(route('api.domain.tldPricingInfo'));
        if (response.data && response.data.status === 'success' && Array.isArray(response.data.data)) {
            tldListFromApi.value = response.data.data; // Estructura: [{ tld: 'com', name: '.com', name_silo_info: { registration: X,... } }, ...]
        } else {
            throw new Error('Respuesta de precios de TLD no válida desde la API.');
        }
    } catch (error) {
        console.error("Error fetching TLD products:", error);
        tldError.value = error.response?.data?.message || error.message || 'Error al cargar extensiones de dominio.';
    } finally {
        isLoadingTlds.value = false;
    }
};

onMounted(() => {
    if (!props.genericDomainProductId || !props.genericDomainPricingId) {
        console.error("Error de Configuración: IDs de producto/precio de dominio genérico no proporcionados a SelectDomainPage.");
        tldError.value = "La página no está configurada correctamente para registrar dominios. Contacte a soporte.";
    }
    fetchTldProducts();
});

const checkAvailability = async () => {
    if (!constructedFqdn.value) { // Usa la propiedad computada
        availabilityResult.value = { available: false, message: 'Por favor, introduce un nombre de dominio y selecciona una extensión (TLD).' };
        return;
    }
    isLoadingAvailability.value = true;
    availabilityResult.value = null;
    availabilityError.value = null;
    nameSiloRegistrationPrice.value = null;

    try {
        const response = await axios.get(route('api.domain.checkAvailability'), {
            params: { domain: constructedFqdn.value } // Envía el FQDN construido
        });
        if (response.data && response.data.status === 'success') {
            availabilityResult.value = response.data.data;
            if (availabilityResult.value.available && availabilityResult.value.price !== null) {
                nameSiloRegistrationPrice.value = parseFloat(availabilityResult.value.price);
            }
        } else {
            throw new Error(response.data.message || 'Respuesta de disponibilidad no válida desde la API.');
        }
    } catch (error) {
        console.error("Error checking domain availability:", error);
        const message = error.response?.data?.message || error.message || 'Error al verificar disponibilidad del dominio.';
        // Usar el FQDN construido en el mensaje de error si availabilityResult.value.domain_name no está disponible
        availabilityResult.value = { available: false, message: message, domain_name: constructedFqdn.value };
        availabilityError.value = message;
    } finally {
        isLoadingAvailability.value = false;
    }
};

const submitDomainSelection = () => {
    // Validación para dominios nuevos
    if (domainType.value === 'new') {
        if (!availabilityResult.value?.available || !selectedTldObject.value || nameSiloRegistrationPrice.value === null) {
            alert('Por favor, verifica un dominio disponible, selecciona una extensión y asegúrate de que se haya obtenido un precio de registro.');
            return;
        }
        cartForm.domain_name = availabilityResult.value.domain_name;
        cartForm.override_price = nameSiloRegistrationPrice.value;
        cartForm.tld_extension = selectedTldObject.value.tld;
    }
    // Validación para dominios existentes
    else if (domainType.value === 'existing') {
        if (!existingDomainInput.value.trim()) {
            alert('Por favor, introduce el nombre de tu dominio existente.');
            return;
        }

        // Validar formato básico del dominio
        const domainRegex = /^[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9]?\.[a-zA-Z]{2,}$/;
        if (!domainRegex.test(existingDomainInput.value.trim())) {
            alert('Por favor, introduce un dominio válido (ej: midominio.com).');
            return;
        }

        cartForm.domain_name = existingDomainInput.value.trim().toLowerCase();
        cartForm.override_price = null; // No hay precio para dominios existentes
        cartForm.tld_extension = cartForm.domain_name.split('.').pop(); // Extraer TLD
    }

    if (!cartForm.product_id || !cartForm.pricing_id) {
        alert("Error de Configuración: IDs de producto/precio de dominio genérico no disponibles. No se puede continuar.");
        return;
    }

    cartForm.post(route('client.cart.account.setDomain'), {
        preserveScroll: true,
        preserveState: true, // Para que el CartSummary no se pierda si hay errores de validación del form
        onSuccess: () => {
            window.dispatchEvent(new CustomEvent('cart-updated'));
            router.visit(route('client.checkout.selectServices'));
        },
        onError: (errors) => {
            console.error('Error al configurar el dominio en el carrito:', errors);
            let errorMessages = [];
            if (typeof errors === 'string') {
                errorMessages.push(errors);
            } else if (typeof errors === 'object') {
                for (const key in errors) {
                    if (Array.isArray(errors[key])) {
                        errors[key].forEach(msg => errorMessages.push(msg));
                    } else {
                        errorMessages.push(errors[key]);
                    }
                }
            }
            alert(errorMessages.join("\n") || 'Ocurrió un error al añadir el dominio al carrito.');
        }
    });
};

const canConfirmDomain = computed(() => {
    if (domainType.value === 'new') {
        return availabilityResult.value?.available &&
            selectedTldObject.value !== null && // Solo necesitamos que un TLD esté seleccionado
            nameSiloRegistrationPrice.value !== null && // Y que tengamos un precio de NameSilo para el FQDN
            !isLoadingAvailability.value && !isLoadingTlds.value;
    } else if (domainType.value === 'existing') {
        return existingDomainInput.value.trim() !== '' &&
            /^[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9]?\.[a-zA-Z]{2,}$/.test(existingDomainInput.value.trim());
    }
    return false;
});

</script>

<template>

    <Head title="Seleccionar Dominio" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Paso 1: Elige tu Dominio
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                    <div class="p-6 bg-white shadow-sm md:col-span-2 dark:bg-gray-800 sm:rounded-lg">
                        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Configura tu Dominio</h3>

                        <!-- Selector de tipo de dominio -->
                        <div class="mb-6">
                            <label class="block mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">¿Qué tipo de
                                dominio
                                necesitas?</label>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="relative">
                                    <input type="radio" v-model="domainType" value="new" id="domain-new"
                                        class="sr-only peer">
                                    <label for="domain-new"
                                        class="flex items-center justify-center p-4 transition-all border-2 border-gray-300 rounded-lg cursor-pointer dark:border-gray-600 hover:border-indigo-500 dark:hover:border-indigo-400 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900/20">
                                        <div class="text-center">
                                            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                Registrar Nuevo
                                                Dominio</div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400">Buscar y registrar un
                                                dominio
                                                disponible</div>
                                        </div>
                                    </label>
                                </div>
                                <div class="relative">
                                    <input type="radio" v-model="domainType" value="existing" id="domain-existing"
                                        class="sr-only peer">
                                    <label for="domain-existing"
                                        class="flex items-center justify-center p-4 transition-all border-2 border-gray-300 rounded-lg cursor-pointer dark:border-gray-600 hover:border-indigo-500 dark:hover:border-indigo-400 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900/20">
                                        <div class="text-center">
                                            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">Usar
                                                Dominio
                                                Existente</div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400">Ya tengo un dominio
                                                registrado
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Sección para dominio existente -->
                        <div v-if="domainType === 'existing'" class="space-y-6">
                            <div>
                                <label for="existing_domain_input"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre de tu
                                    Dominio
                                    Existente</label>
                                <input type="text" v-model="existingDomainInput" id="existing_domain_input"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                    placeholder="midominio.com">
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    Introduce el nombre completo de tu dominio (ej: midominio.com)
                                </p>
                            </div>
                        </div>

                        <!-- Sección para dominio nuevo -->
                        <div v-if="domainType === 'new'" class="space-y-6">
                            <div>
                                <label for="sld_input"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre
                                    de Dominio (ej. "midominio")</label>
                                <input type="text" v-model="sldInput" id="sld_input"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                    placeholder="midominio">
                            </div>

                            <div>
                                <label for="tld_selection"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selecciona una
                                    Extensión (TLD)</label>
                                <select v-model="selectedTldObject" id="tld_selection"
                                    :disabled="isLoadingTlds || tldListFromApi.length === 0"
                                    class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option :value="null" disabled>
                                        <span v-if="isLoadingTlds">Cargando TLDs...</span>
                                        <span v-else-if="tldError">Error al cargar TLDs</span>
                                        <span v-else-if="tldListFromApi.length === 0">No hay TLDs disponibles</span>
                                        <span v-else>-- Selecciona un TLD --</span>
                                    </option>

                                    <!-- TLDs en Promoción -->
                                    <optgroup v-if="promotionalTlds.length > 0" label="🔥 Ofertas Especiales">
                                        <option v-for="tldOpt in promotionalTlds" :key="'promo-' + tldOpt.tld"
                                            :value="tldOpt">
                                            {{ tldOpt.name }} {{ formatCurrency(tldOpt.name_silo_info.registration,
                                            tldOpt.name_silo_info.currency || 'USD') }} (¡Ahorra {{ Math.round((1 -
                                                tldOpt.name_silo_info.registration / tldOpt.name_silo_info.renewal) * 100)
                                            }}%!)
                                            renovación {{ formatCurrency(tldOpt.name_silo_info.renewal,
                                            tldOpt.name_silo_info.currency || 'USD') }}
                                        </option>
                                    </optgroup>

                                    <!-- TLDs Regulares -->
                                    <optgroup v-if="regularTlds.length > 0" label="Extensiones Regulares">
                                        <option v-for="tldOpt in regularTlds" :key="'regular-' + tldOpt.tld"
                                            :value="tldOpt">
                                            {{ tldOpt.name }}
                                            <span
                                                v-if="tldOpt.name_silo_info && typeof tldOpt.name_silo_info.registration === 'number'">
                                                ({{ formatCurrency(tldOpt.name_silo_info.registration,
                                                    tldOpt.name_silo_info.currency || 'USD') }})
                                            </span>
                                        </option>
                                    </optgroup>
                                </select>
                                <p v-if="tldError" class="mt-2 text-sm text-red-600">{{ tldError }}</p>

                                <!-- Información adicional sobre promociones -->
                                <div v-if="promotionalTlds.length > 0"
                                    class="mt-3 p-3 bg-orange-50 border border-orange-200 rounded-md dark:bg-orange-900/20 dark:border-orange-800">
                                    <div class="flex items-center">
                                        <span class="text-orange-600 dark:text-orange-400 mr-2">🔥</span>
                                        <p class="text-sm text-orange-800 dark:text-orange-200">
                                            <strong>{{ promotionalTlds.length }}</strong> extensiones en oferta
                                            especial.
                                            Los precios mostrados son para el primer año, la renovación será al precio
                                            regular.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <button @click="checkAvailability"
                                :disabled="isLoadingAvailability || !sldInput.trim() || !selectedTldObject"
                                class="inline-flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                <span v-if="isLoadingAvailability">Verificando {{ constructedFqdn }}...</span>
                                <span v-else>Verificar Disponibilidad de {{ constructedFqdn || '...' }}</span>
                            </button>
                            <p v-if="cartForm.errors.domain_name" class="mt-2 text-sm text-red-600">{{
                                cartForm.errors.domain_name }}</p>

                            <!-- Resultados de disponibilidad solo para dominios nuevos -->
                            <div v-if="availabilityError && !availabilityResult"
                                class="p-4 mt-6 text-red-700 border-red-300 rounded-md bg-red-50">
                                <p>{{ availabilityError }}</p>
                            </div>
                            <div v-if="availabilityResult" class="p-4 mt-6 rounded-md" :class="{
                                'bg-green-50 border-green-300 text-green-700': availabilityResult.available,
                                'bg-red-50 border-red-300 text-red-700': !availabilityResult.available
                            }">
                                <p class="font-medium">
                                    {{ availabilityResult.message }}
                                </p>
                                <p v-if="availabilityResult.available && nameSiloRegistrationPrice !== null"
                                    class="mt-1 text-sm">
                                    Precio de Registro para <strong class="font-semibold">{{
                                        availabilityResult.domain_name
                                        }}</strong>:
                                    {{ formatCurrency(nameSiloRegistrationPrice, availabilityResult.currency_code ||
                                        'USD')
                                    }}
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-end mt-8">
                            <button @click="submitDomainSelection" :disabled="!canConfirmDomain || cartForm.processing"
                                class="px-6 py-2 text-base font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50">
                                <span v-if="cartForm.processing">Procesando...</span>
                                <span v-else-if="domainType === 'new'">Confirmar Dominio y Continuar</span>
                                <span v-else>Usar Dominio Existente y Continuar</span>
                            </button>
                        </div>
                        <p v-if="cartForm.errors.product_id" class="mt-2 text-sm text-red-600">{{
                            cartForm.errors.product_id }}
                        </p>
                        <p v-if="cartForm.errors.pricing_id" class="mt-2 text-sm text-red-600">{{
                            cartForm.errors.pricing_id }}
                        </p>
                        <p v-if="cartForm.errors.override_price" class="mt-2 text-sm text-red-600">{{
                            cartForm.errors.override_price }}</p>
                        <p v-if="cartForm.errors.tld_extension" class="mt-2 text-sm text-red-600">{{
                            cartForm.errors.tld_extension }}</p>
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
