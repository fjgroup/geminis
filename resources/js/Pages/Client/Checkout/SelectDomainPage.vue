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
    if (!availabilityResult.value?.available || !selectedTldObject.value || nameSiloRegistrationPrice.value === null) {
        alert('Por favor, verifica un dominio disponible, selecciona una extensión y asegúrate de que se haya obtenido un precio de registro.');
        return;
    }

    cartForm.domain_name = availabilityResult.value.domain_name;
    cartForm.override_price = nameSiloRegistrationPrice.value;
    cartForm.tld_extension = selectedTldObject.value.tld;
    // product_id y pricing_id ya están seteados en useForm con los props genéricos.
    // No es necesario volver a asignarlos aquí si no cambian.

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
    return availabilityResult.value?.available &&
           selectedTldObject.value !== null && // Solo necesitamos que un TLD esté seleccionado
           nameSiloRegistrationPrice.value !== null && // Y que tengamos un precio de NameSilo para el FQDN
           !isLoadingAvailability.value && !isLoadingTlds.value;
});

</script>

<template>
    <Head title="Seleccionar Dominio" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Paso 1: Elige tu Dominio</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="md:col-span-2 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Busca tu Dominio Perfecto</h3>

                        <div class="space-y-6">
                            <div>
                                <label for="sld_input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre de Dominio (ej. "midominio")</label>
                                <input type="text" v-model="sldInput" id="sld_input"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md"
                                       placeholder="midominio">
                            </div>

                            <div>
                                <label for="tld_selection" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selecciona una Extensión (TLD)</label>
                                <select v-model="selectedTldObject" id="tld_selection" :disabled="isLoadingTlds || tldListFromApi.length === 0"
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option :value="null" disabled>
                                        <span v-if="isLoadingTlds">Cargando TLDs...</span>
                                        <span v-else-if="tldError">Error al cargar TLDs</span>
                                        <span v-else-if="tldListFromApi.length === 0">No hay TLDs disponibles</span>
                                        <span v-else>-- Selecciona un TLD --</span>
                                    </option>
                                    <option v-for="tldOpt in tldListFromApi" :key="tldOpt.tld" :value="tldOpt">
                                        {{ tldOpt.name }}
                                        <span v-if="tldOpt.name_silo_info && typeof tldOpt.name_silo_info.registration === 'number'">
                                            (Registro aprox. {{ formatCurrency(tldOpt.name_silo_info.registration, tldOpt.name_silo_info.currency || 'USD') }})
                                        </span>
                                    </option>
                                </select>
                                <p v-if="tldError" class="mt-2 text-sm text-red-600">{{ tldError }}</p>
                            </div>

                            <button @click="checkAvailability"
                                    :disabled="isLoadingAvailability || !sldInput.trim() || !selectedTldObject"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                <span v-if="isLoadingAvailability">Verificando {{ constructedFqdn }}...</span>
                                <span v-else>Verificar Disponibilidad de {{ constructedFqdn || '...' }}</span>
                            </button>
                             <p v-if="cartForm.errors.domain_name" class="mt-2 text-sm text-red-600">{{ cartForm.errors.domain_name }}</p>
                        </div>

                        <div v-if="availabilityError && !availabilityResult" class="mt-6 p-4 rounded-md bg-red-50 border-red-300 text-red-700">
                            <p>{{ availabilityError }}</p>
                        </div>
                        <div v-if="availabilityResult" class="mt-6 p-4 rounded-md"
                             :class="{'bg-green-50 border-green-300 text-green-700': availabilityResult.available,
                                      'bg-red-50 border-red-300 text-red-700': !availabilityResult.available}">
                            <p class="font-medium">
                                {{ availabilityResult.message }}
                            </p>
                            <p v-if="availabilityResult.available && nameSiloRegistrationPrice !== null" class="text-sm mt-1">
                                Precio de Registro para <strong class="font-semibold">{{ availabilityResult.domain_name }}</strong>:
                                {{ formatCurrency(nameSiloRegistrationPrice, availabilityResult.currency_code || 'USD') }}
                            </p>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button @click="submitDomainSelection"
                                    :disabled="!canConfirmDomain || cartForm.processing"
                                    class="px-6 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50">
                                <span v-if="cartForm.processing">Procesando...</span>
                                <span v-else>Confirmar Dominio y Continuar</span>
                            </button>
                        </div>
                         <p v-if="cartForm.errors.product_id" class="mt-2 text-sm text-red-600">{{ cartForm.errors.product_id }}</p>
                         <p v-if="cartForm.errors.pricing_id" class="mt-2 text-sm text-red-600">{{ cartForm.errors.pricing_id }}</p>
                         <p v-if="cartForm.errors.override_price" class="mt-2 text-sm text-red-600">{{ cartForm.errors.override_price }}</p>
                         <p v-if="cartForm.errors.tld_extension" class="mt-2 text-sm text-red-600">{{ cartForm.errors.tld_extension }}</p>
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
