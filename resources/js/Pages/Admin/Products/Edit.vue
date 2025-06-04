<script setup>
// Mueve los console.log aquí, después de definir props, para que tengan acceso a ellas.
// Es mejor loguear las props directamente después de que el componente las recibe.
// El console.log original estaba antes de la definición de `props`, por lo que no funcionaría.



import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { ref, computed, toRaw } from "vue"; // Importar toRaw
import {
    ArrowDownTrayIcon, // Para guardar/actualizar
    XMarkIcon,         // Para cancelar
    PlusCircleIcon,    // Para agregar precio/opción
    PencilSquareIcon,
    TrashIcon,
    ListBulletIcon,    // Para listas o grupos
    CurrencyDollarIcon, // Para precios
} from '@heroicons/vue/24/outline';

import InputLabel from '@/Components/Forms/InputLabel.vue';
import SelectInput from '@/Components/Forms/SelectInput.vue';
import InputError from '@/Components/Forms/InputError.vue';

const props = defineProps({
    product: Object,
    resellers: Array, // Lista de revendedores para el select
    all_option_groups: Array, // Todos los grupos de opciones disponibles
    billingCycles: Array, // Nueva prop para ciclos de facturación
    productTypes: Array, // Added: Esperando array de { value: id, label: name }
});

// Paso de depuración: Imprimir los billingCycles que llegan como props
console.log('Props billingCycles en Edit.vue:', JSON.parse(JSON.stringify(props.billingCycles)));



const form = useForm({
    _method: "PUT",
    name: props.product.name,
    slug: props.product.slug, // Added slug
    description: props.product.description,
    product_type_id: props.product.product_type_id || null, // Changed from type
    module_name: props.product.module_name,
    owner_id: props.product.owner_id,
    status: props.product.status,
    is_publicly_available: props.product.is_publicly_available,
    is_resellable_by_default: props.product.is_resellable_by_default,
    configurable_option_groups: props.product.associated_option_groups || {}, // Objeto: { groupId: { display_order: X } }
});

// Estado para el modal de precios
const showPricingModal = ref(false);
const editingPricing = ref(null);

const pricingForm = useForm({
    product_id: props.product.id,
    billing_cycle_id: null, // Cambiado a billing_cycle_id
    price: "",
    setup_fee: 0.0,
    currency_code: "USD",
    is_active: true,
    id: null, // Para la actualización
});

const productStatusOptions = [
    { value: "active", label: "Active" },
    { value: "inactive", label: "Inactive" },
    { value: "hidden", label: "Hidden" },
];

const ownerOptions = [
    { value: null, label: "Producto de Plataforma (Administrador)" },
    ...(props.resellers
        ? props.resellers.map((reseller) => ({
              value: reseller.id,
              label: `${reseller.name} (${
                  reseller.company_name || "Sin compañía"
              })`,
          }))
        : []),
];

// Eliminado: const billingCycleOptions = [...]

const currencyOptions = [
    { value: "USD", label: "USD" },
    { value: "EUR", label: "EUR" },
];

// Nueva propiedad computada para ciclos de facturación dinámicos
const dynamicBillingCycleOptions = computed(() => {
    if (!props.billingCycles) return [];
    // Dado que props.billingCycles ya llega en el formato { value: ..., label: ... }
    // desde el controlador, podemos usarlo directamente.
    const options = props.billingCycles;
    // Paso de depuración: Imprimir las opciones generadas

    console.log('dynamicBillingCycleOptions generadas:', JSON.parse(JSON.stringify(options)));
    return options;
});



const submitProductForm = () => {

    // Reconstruir configurable_option_groups para asegurar el formato de objeto plano antes de enviar
    const formattedOptionGroups = {};
    for (const groupId in form.configurable_option_groups) {
        if (Object.hasOwnProperty.call(form.configurable_option_groups, groupId)) {
            const groupData = form.configurable_option_groups[groupId];
            // Asegurarse de que groupData es un objeto válido con display_order
            if (groupData !== null && typeof groupData === 'object' && groupData.hasOwnProperty('display_order')) {
                // Convertir la clave groupId a string explícitamente si es numérico y asegurarse de que display_order es numérico
                 formattedOptionGroups[String(groupId)] = { display_order: Number(groupData.display_order) };
            } else {
                 console.warn(`Unexpected data format for group ID ${groupId}:`, groupData);
            }
        }
    }

    // Reemplazar el objeto original en el formulario con el objeto formateado
    form.configurable_option_groups = formattedOptionGroups;


    form.put(route("admin.products.update", props.product.id));
};

const openAddPricingModal = () => {
    editingPricing.value = null;
    pricingForm.reset();
    pricingForm.product_id = props.product.id;
    pricingForm.billing_cycle_id = null; // Inicializar con null para el nuevo precio
    showPricingModal.value = true;
};

const openEditPricingModal = (pricingId) => {
    // Encontrar el objeto de precio completo con la relación billingCycle cargada
    const pricing = props.product.pricings.find(p => p.id === pricingId);

    if (!pricing) {
        console.error(`Pricing with ID ${pricingId} not found in product pricings.`);
        return; // Evitar errores si no se encuentra el precio
    }

    editingPricing.value = pricing;
    pricingForm.id = pricing.id;


    // Acceder directamente al ID de la relación de ciclo de facturación cargada
    pricingForm.billing_cycle_id = pricing.billing_cycle ? pricing.billing_cycle.id : null;



    pricingForm.price = pricing.price;
    pricingForm.setup_fee = pricing.setup_fee;
    pricingForm.currency_code = pricing.currency_code;
    pricingForm.is_active = Boolean(pricing.is_active);
    showPricingModal.value = true;
};

const submitPricingForm = () => {
    const url = editingPricing.value
        ? route("admin.products.pricing.update", {
              product: props.product.id,
              pricing: editingPricing.value.id,
          })
        : route("admin.products.pricing.store", props.product.id);
    const method = editingPricing.value ? "put" : "post";

    pricingForm.submit(method, url, {
        onSuccess: () => {
            showPricingModal.value = false;
            pricingForm.reset();
        },
        onError: (errors) => {
            console.error("Error en el formulario de precios:", errors);
            // Aquí podrías también mostrar los errores al usuario de forma más visible si es necesario.
        },
    });
};

const deletePricing = (pricingId) => {
    if (confirm("¿Estás seguro de que deseas eliminar este precio?")) {
        router.delete(
            route("admin.products.pricing.destroy", {
                product: props.product.id,
                pricing: pricingId,
            }),
            {
                preserveScroll: true,
            }
        );
    }
};

// Funciones para manejar la selección de grupos de opciones
const isGroupSelected = (groupId) => {
    return !!form.configurable_option_groups[groupId];
};

const toggleGroupSelection = (groupId) => {
    if (isGroupSelected(groupId)) {
        delete form.configurable_option_groups[groupId];
    } else {
        form.configurable_option_groups[groupId] = { display_order: 0 }; // Default order
    }
};

// Propiedad computada para filtrar los grupos de opciones
const filteredOptionGroups = computed(() => {
    if (!props.all_option_groups) return [];
    return props.all_option_groups.filter(group => {
        // El grupo ya está asociado a este producto
        const isAssociated = !!form.configurable_option_groups[group.id];
        // El grupo es global (no tiene un owner_product_id)
        const isGlobal = group.owner_product_id === null;
        // El grupo es específico de ESTE producto
        const isSpecificToThisProduct = group.owner_product_id === props.product.id;

        return isAssociated || isGlobal || isSpecificToThisProduct;
    });
});
// Método para obtener el nombre del ciclo de facturación de forma segura
const getBillingCycleName = (pricing) => {

  const rawPricing = toRaw(pricing); // Obtener el objeto crudo del Proxy

  // Acceder a la relación usando el nombre de columna de la base de datos (snake_case) con corchetes
  const billingCycle = rawPricing["billing_cycle"];
  return billingCycle && billingCycle.name ? billingCycle.name : 'N/A';
};

</script>

<template>
    <AdminLayout :title="'Editar Producto - ' + product.name">

        <Head :title="'Editar Producto - ' + product.name" />
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Editar Producto: {{ product.name }}
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="p-6 overflow-hidden bg-white shadow-sm sm:rounded-lg md:p-8 dark:bg-gray-800">
                    <form @submit.prevent="submitProductForm">
                        <!-- Name -->
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre
                                del Producto</label>
                            <input type="text" v-model="form.name" id="name"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                            <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
                            <textarea v-model="form.description" id="description" rows="4"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                            <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                                {{ form.errors.description }}
                            </div>
                        </div>

                        <!-- Product Type ID -->
                        <div class="mb-4">
                            <InputLabel for="product_type_id" value="Tipo de Producto *" />
                            <SelectInput
                                id="product_type_id"
                                class="block w-full mt-1"
                                v-model="form.product_type_id"
                                :options="props.productTypes"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.product_type_id" />
                        </div>

                        <!-- Module Name -->
                        <div class="mb-4">
                            <label for="module_name"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre del Módulo
                                (Opcional)</label>
                            <input type="text" v-model="form.module_name" id="module_name"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                            <div v-if="form.errors.module_name" class="mt-1 text-sm text-red-600">
                                {{ form.errors.module_name }}
                            </div>
                        </div>

                        <!-- Owner ID (Propietario) -->
                        <div class="mb-4">
                            <label for="owner_id"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Propietario del
                                Producto</label>
                            <select v-model="form.owner_id" id="owner_id"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option v-for="option in ownerOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                            <div v-if="form.errors.owner_id" class="mt-1 text-sm text-red-600">
                                {{ form.errors.owner_id }}
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label for="status"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado</label>
                            <select v-model="form.status" id="status"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option v-for="option in productStatusOptions" :key="option.value"
                                    :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                            <div v-if="form.errors.status" class="mt-1 text-sm text-red-600">
                                {{ form.errors.status }}
                            </div>
                        </div>

                        <!-- Is Publicly Available -->
                        <div class="block mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" v-model="form.is_publicly_available"
                                    class="text-indigo-600 border-gray-300 rounded shadow-sm focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 dark:focus:ring-indigo-600 dark:ring-offset-gray-800" />
                                <span class="text-sm text-gray-600 ms-2 dark:text-gray-400">Disponible
                                    Públicamente</span>
                            </label>
                            <div v-if="form.errors.is_publicly_available" class="mt-1 text-sm text-red-600">
                                {{ form.errors.is_publicly_available }}
                            </div>
                        </div>

                        <!-- Is Resellable By Default -->
                        <div class="block mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" v-model="form.is_resellable_by_default"
                                    class="text-indigo-600 border-gray-300 rounded shadow-sm focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 dark:focus:ring-indigo-600 dark:ring-offset-gray-800" />
                                <span class="text-sm text-gray-600 ms-2 dark:text-gray-400">Revendible por Defecto (para
                                    revendedores)</span>
                            </label>
                            <div v-if="form.errors.is_resellable_by_default" class="mt-1 text-sm text-red-600">
                                {{ form.errors.is_resellable_by_default }}
                            </div>
                        </div>

                        <!-- Botones -->
                        <div
                            class="flex items-center justify-end pt-4 mt-6 border-t border-gray-200 dark:border-gray-700">
                            <Link :href="route('admin.products.index')"
                                class="px-4 py-2 mr-4 text-sm text-gray-600 border border-gray-300 rounded-md dark:text-gray-400 dark:border-gray-500 hover:text-gray-900 hover:bg-gray-50 dark:hover:bg-gray-700">
                            Cancelar
                            </Link>
                            <button type="submit" :disabled="form.processing"
                                class="flex items-center px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                                {{
                                form.processing
                                ? "Actualizando..."
                                : "Actualizar Producto"
                                }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Sección de Precios -->
                <div class="p-6 mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg md:p-8 dark:bg-gray-800">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="flex items-center text-lg font-medium text-gray-900 dark:text-gray-100">
                            <CurrencyDollarIcon class="w-6 h-6 mr-2 text-green-500" />
                            Precios del Producto
                        </h3>
                        <button @click="openAddPricingModal"
                            class="flex items-center px-4 py-2 text-sm text-white bg-green-500 rounded-md hover:bg-green-600">
                            <PlusCircleIcon class="w-5 h-5 mr-2" />
                            Agregar Precio
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                        Ciclo Facturación
                                    </th>
                                    <th
                                        class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                        Precio
                                    </th>
                                    <th
                                        class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                        Config.
                                    </th>
                                    <th
                                        class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                        Moneda
                                    </th>
                                    <th
                                        class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                        Activo
                                    </th>
                                    <th
                                        class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                <tr v-if="
                                        !product.pricings ||
                                        product.pricings.length === 0
                                    ">
                                    <td colspan="6"
                                        class="px-4 py-3 text-sm text-center text-gray-500 dark:text-gray-400">
                                        No hay precios definidos para este
                                        producto.
                                    </td>
                                </tr>
                                <tr v-for="pricing in product.pricings" :key="pricing.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ getBillingCycleName(pricing) }} <!-- Usar el nuevo método -->
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ pricing.price }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ pricing.setup_fee }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ pricing.currency_code }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ pricing.is_active ? "Sí" : "No" }}
                                    </td>
                                    <td class="px-4 py-3 text-sm font-medium">
                                        <button @click="
                                            openEditPricingModal(pricing.id)"
                                            class="flex items-center mr-2 text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            <PencilSquareIcon class="w-4 h-4 mr-1" />
                                            Editar
                                        </button>
                                        <button @click="deletePricing(pricing.id)"
                                            class="flex items-center text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            <TrashIcon class="w-4 h-4 mr-1" />
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- ... cierre del div de la Sección de Precios ... -->

                <!-- Sección para Grupos de Opciones Configurables -->
                <div class="p-6 mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg md:p-8 dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
                        Grupos de Opciones Configurables Asociados
                    </h3>
                    <div v-if="
                            !filteredOptionGroups || filteredOptionGroups.length === 0
                        " class="text-sm text-gray-500 dark:text-gray-400">
                        No hay grupos de opciones configurables definidos en el
                        sistema o aplicables a este producto.
                    </div>
                    <div v-else class="space-y-3">
                        <div v-for="group_opt in filteredOptionGroups" :key="group_opt.id"
                            class="flex items-center justify-between p-3 border rounded-md dark:border-gray-600">
                            <label :for="'group-' + group_opt.id" class="flex items-center cursor-pointer">
                                <input type="checkbox" :id="'group-' + group_opt.id"
                                    :checked="isGroupSelected(group_opt.id)"
                                    @change="toggleGroupSelection(group_opt.id)"
                                    class="w-5 h-5 text-indigo-600 border-gray-300 rounded dark:bg-gray-900 dark:border-gray-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-800" />
                                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">{{ group_opt.name }}</span>
                            </label>
                            <div v-if="isGroupSelected(group_opt.id)" class="flex items-center">
                                <label :for="'group-order-' + group_opt.id"
                                    class="mr-2 text-sm text-gray-500 dark:text-gray-400">Prioridad:</label>
                                <input type="number" v-model.number="
                                        form.configurable_option_groups[
                                            group_opt.id
                                        ].display_order
                                    " :id="'group-order-' + group_opt.id"
                                    class="w-20 p-1 text-sm border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500" />
                            </div>
                        </div>
                    </div>
                    <div v-if="form.errors.configurable_option_groups" class="mt-1 text-sm text-red-600">
                        {{ form.errors.configurable_option_groups }}
                    </div>
                </div>
                <!-- Fin de la Sección para Grupos de Opciones Configurables -->
            </div>
        </div>
        <!-- ... resto del template ... -->
        <!-- Modal para Agregar/Editar Precio -->
        <div v-if="showPricingModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="w-full max-w-lg p-6 bg-white rounded-lg shadow-xl dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{
                    editingPricing
                    ? "Editar Precio"
                    : "Agregar Nuevo Precio"
                    }}
                </h2>
                <form @submit.prevent="submitPricingForm">
                    <div class="mb-4">
                        <label for="billing_cycle"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ciclo de
                            Facturación</label>
                        <select v-model="pricingForm.billing_cycle_id" id="billing_cycle"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option v-for="option in dynamicBillingCycleOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                        <div v-if="pricingForm.errors.billing_cycle_id" class="mt-1 text-sm text-red-600">
                            {{ pricingForm.errors.billing_cycle_id }}
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="price"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Precio</label>
                            <input type="number" step="0.01" v-model="pricingForm.price" id="price"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                            <div v-if="pricingForm.errors.price" class="mt-1 text-sm text-red-600">
                                {{ pricingForm.errors.price }}
                            </div>
                        </div>
                        <div>
                            <label for="setup_fee"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tarifa de
                                Configuración</label>
                            <input type="number" step="0.01" v-model="pricingForm.setup_fee" id="setup_fee"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                            <div v-if="pricingForm.errors.setup_fee" class="mt-1 text-sm text-red-600">
                                {{ pricingForm.errors.setup_fee }}
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="currency_code"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Moneda</label>
                        <select v-model="pricingForm.currency_code" id="currency_code"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option v-for="option in currencyOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                        <div v-if="pricingForm.errors.currency_code" class="mt-1 text-sm text-red-600">
                            {{ pricingForm.errors.currency_code }}
                        </div>
                    </div>
                    <div class="block mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" v-model="pricingForm.is_active"
                                class="text-indigo-600 border-gray-300 rounded shadow-sm focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 dark:focus:ring-indigo-600 dark:ring-offset-gray-800" />
                            <span class="text-sm text-gray-600 ms-2 dark:text-gray-400">Activo</span>
                        </label>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" @click="showPricingModal = false"
                            class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-md dark:text-gray-400 dark:border-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="pricingForm.processing"
                            class="px-4 py-2 text-sm text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50">
                            {{
                            pricingForm.processing
                            ? editingPricing
                            ? "Actualizando..."
                            : "Guardando..."
                            : editingPricing
                            ? "Actualizar Precio"
                            : "Guardar Precio"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
