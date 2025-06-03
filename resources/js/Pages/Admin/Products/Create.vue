<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/Forms/InputLabel.vue'; // Assuming correct path
import SelectInput from '@/Components/Forms/SelectInput.vue'; // Assuming correct path
import InputError from '@/Components/Forms/InputError.vue'; // Assuming correct path


const props = defineProps({
    productTypes: Array, // Expecting array of { value: id, label: name }
    // resellers: Array,
});

const form = useForm({
    name: '',
    slug: '', // Added slug as it's in the form/controller logic
    description: '',
    product_type_id: null, // Changed from type to product_type_id
    module_name: '',
    owner_id: null,
    status: 'active', // Valor por defecto
    is_publicly_available: true,
    is_resellable_by_default: true,
    // welcome_email_template_id: null,
    // display_order: 0,
});

const productTypeOptions = [
    { value: 'shared_hosting', label: 'Shared Hosting' },
    { value: 'vps', label: 'VPS' },
    { value: 'dedicated_server', label: 'Dedicated Server' },
    { value: 'domain_registration', label: 'Domain Registration' },
    { value: 'ssl_certificate', label: 'SSL Certificate' },
    { value: 'other', label: 'Other' },
];

const productStatusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
    { value: 'hidden', label: 'Hidden' },
];

// Ejemplo si pasaras revendedores desde el controlador:
// const ownerOptions = [
//     { value: null, label: 'Platform Product' },
//     ...(props.resellers ? props.resellers.map(r => ({ value: r.id, label: r.name })) : [])
// ];

const submit = () => {
    form.post(route('admin.products.store'), {
        // onFinish: () => form.reset('name', 'description', ...), // Opcional
    });
};
</script>

<template>
    <AdminLayout title="Crear Producto">
        <Head title="Crear Nuevo Producto" />
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Crear Nuevo Producto
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="p-6 overflow-hidden bg-white shadow-sm sm:rounded-lg md:p-8">
                    <form @submit.prevent="submit">
                        <!-- Name -->
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre del Producto</label>
                            <input type="text" v-model="form.name" id="name"
                                   class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                            <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
                            <textarea v-model="form.description" id="description" rows="4"
                                      class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                            <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
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
                            <label for="module_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre del Módulo (Opcional)</label>
                            <input type="text" v-model="form.module_name" id="module_name"
                                   class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                            <div v-if="form.errors.module_name" class="mt-1 text-sm text-red-600">{{ form.errors.module_name }}</div>
                        </div>

                        <!-- Owner ID (Propietario) -->
                        <div class="mb-4">
                            <label for="owner_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Propietario (ID Revendedor, vacío para plataforma)</label>
                            <input type="number" v-model="form.owner_id" id="owner_id" placeholder="Dejar vacío si es producto de plataforma"
                                   class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                            <!--
                            <select v-model="form.owner_id" id="owner_id" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option v-for="option in ownerOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                            -->
                            <div v-if="form.errors.owner_id" class="mt-1 text-sm text-red-600">{{ form.errors.owner_id }}</div>
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado</label>
                            <select v-model="form.status" id="status"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option v-for="option in productStatusOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                            <div v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</div>
                        </div>

                        <!-- Is Publicly Available -->
                        <div class="block mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" v-model="form.is_publicly_available" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 dark:focus:ring-indigo-600 dark:ring-offset-gray-800" />
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Disponible Públicamente</span>
                            </label>
                            <div v-if="form.errors.is_publicly_available" class="mt-1 text-sm text-red-600">{{ form.errors.is_publicly_available }}</div>
                        </div>

                        <!-- Is Resellable By Default -->
                        <div class="block mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" v-model="form.is_resellable_by_default" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 dark:focus:ring-indigo-600 dark:ring-offset-gray-800" />
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Revendible por Defecto (para revendedores)</span>
                            </label>
                            <div v-if="form.errors.is_resellable_by_default" class="mt-1 text-sm text-red-600">{{ form.errors.is_resellable_by_default }}</div>
                        </div>

                        <!-- Botones -->
                        <div class="flex items-center justify-end pt-4 mt-6 border-t border-gray-200 dark:border-gray-700">
                            <Link :href="route('admin.products.index')" class="px-4 py-2 mr-4 text-sm text-gray-600 border border-gray-300 rounded-md dark:text-gray-400 dark:border-gray-500 hover:text-gray-900 hover:bg-gray-50 dark:hover:bg-gray-700">
                                Cancelar
                            </Link>
                            <button type="submit" :disabled="form.processing"
                                    class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                                {{ form.processing ? 'Creando...' : 'Crear Producto' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>