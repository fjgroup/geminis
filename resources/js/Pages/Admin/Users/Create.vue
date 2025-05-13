<template>
    <AdminLayout title="Crear Usuario">

        <Head title="Crear Nuevo Usuario" />
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Crear Nuevo Usuario
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="p-6 overflow-hidden bg-white shadow-sm sm:rounded-lg md:p-8">
                    <form @submit.prevent="submit">
                        <!-- Nombre -->
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                            <input type="text" v-model="form.name" id="name"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                            <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
                        </div>

                        <!-- Correo Electrónico -->
                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700">Correo
                                Electrónico</label>
                            <input type="email" v-model="form.email" id="email"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                            <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}
                            </div>
                        </div>

                        <!-- Contraseña -->
                        <div class="mb-4">
                            <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                            <input type="password" v-model="form.password" id="password"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" autocomplete="new-password" />
                            <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password
                                }}</div>
                        </div>

                        <!-- Confirmar Contraseña -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar
                                Contraseña</label>
                            <input type="password" v-model="form.password_confirmation" id="password_confirmation"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" autocomplete="new-password" />
                            <div v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600">{{
                                form.errors.password_confirmation }}</div>
                        </div>

                        <!-- Rol -->
                        <div class="mb-4">
                            <label for="role" class="block text-sm font-medium text-gray-700">Rol</label>
                            <select v-model="form.role" id="role"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option v-for="option in roleOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                            <div v-if="form.errors.role" class="mt-1 text-sm text-red-600">{{ form.errors.role }}</div>
                        </div>

                        <!-- Reseller ID -->
                        <div class="mb-4" v-if="form.role === 'client'">
                            <label for="reseller_id" class="block text-sm font-medium text-gray-700">ID del
                                Revendedor</label>
                            <input type="number" v-model="form.reseller_id" id="reseller_id"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                            <div v-if="form.errors.reseller_id" class="mt-1 text-sm text-red-600">{{
                                form.errors.reseller_id }}
                            </div>
                        </div>

                        <!-- Company Name -->
                        <div class="mb-4">
                            <label for="company_name" class="block text-sm font-medium text-gray-700">Nombre de la
                                Compañía
                            </label>
                            <input type="text" v-model="form.company_name" id="company_name"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                            <div v-if="form.errors.company_name" class="mt-1 text-sm text-red-600">{{
                                form.errors.company_name
                                }}
                            </div>
                        </div>

                        <!-- Teléfono -->
                        <div class="mb-4">
                            <label for="phone_number" class="block text-sm font-medium text-gray-700">Teléfono</label>
                            <input type="text" v-model="form.phone_number" id="phone_number"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                            <div v-if="form.errors.phone_number" class="mt-1 text-sm text-red-600">{{
                                form.errors.phone_number
                                }}
                            </div>
                        </div>

                        <!-- Dirección Línea 1 -->
                        <div class="mb-4">
                            <label for="address_line1" class="block text-sm font-medium text-gray-700">Dirección Línea
                                1</label>
                            <input type="text" v-model="form.address_line1" id="address_line1"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                            <div v-if="form.errors.address_line1" class="mt-1 text-sm text-red-600">{{
                                form.errors.address_line1
                                }}
                            </div>
                        </div>

                        <!-- Dirección Línea 2 -->
                        <div class="mb-4">
                            <label for="address_line2" class="block text-sm font-medium text-gray-700">Dirección Línea
                                2</label>
                            <input type="text" v-model="form.address_line2" id="address_line2"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                            <div v-if="form.errors.address_line2" class="mt-1 text-sm text-red-600">{{
                                form.errors.address_line2
                                }}
                            </div>
                        </div>

                        <!-- Ciudad -->
                        <div class="mb-4">
                            <label for="city" class="block text-sm font-medium text-gray-700">Ciudad</label>
                            <input type="text" v-model="form.city" id="city"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                            <div v-if="form.errors.city" class="mt-1 text-sm text-red-600">{{ form.errors.city }}</div>
                        </div>

                        <!-- Estado/Provincia -->
                        <div class="mb-4">
                            <label for="state_province"
                                class="block text-sm font-medium text-gray-700">Estado/Provincia</label>
                            <input type="text" v-model="form.state_province" id="state_province"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                            <div v-if="form.errors.state_province" class="mt-1 text-sm text-red-600">{{
                                form.errors.state_province
                                }}</div>
                        </div>

                        <!-- Código Postal -->
                        <div class="mb-4">
                            <label for="postal_code" class="block text-sm font-medium text-gray-700">Código
                                Postal</label>
                            <input type="text" v-model="form.postal_code" id="postal_code"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                            <div v-if="form.errors.postal_code" class="mt-1 text-sm text-red-600">{{
                                form.errors.postal_code }}
                            </div>
                        </div>

                        <!-- País -->
                        <div class="mb-4">
                            <label for="country" class="block text-sm font-medium text-gray-700">País</label>
                            <select v-model="form.country" id="country"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option v-for="option in countryOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                            <div v-if="form.errors.country" class="mt-1 text-sm text-red-600">{{ form.errors.country }}
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-gray-700">Estado</label>
                            <select v-model="form.status" id="status"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                            <div v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}
                            </div>
                        </div>

                        <!-- Código de Idioma -->
                        <div class="mb-4">
                            <label for="language_code" class="block text-sm font-medium text-gray-700">Idioma</label>
                            <select v-model="form.language_code" id="language_code"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option v-for="option in languageOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                            <div v-if="form.errors.language_code" class="mt-1 text-sm text-red-600">{{
                                form.errors.language_code
                                }}
                            </div>
                        </div>

                        <!-- Código de Moneda -->
                        <div class="mb-4">
                            <label for="currency_code" class="block text-sm font-medium text-gray-700">Moneda</label>
                            <select v-model="form.currency_code" id="currency_code"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option v-for="option in currencyOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                            <div v-if="form.errors.currency_code" class="mt-1 text-sm text-red-600">{{
                                form.errors.currency_code
                                }}
                            </div>
                        </div>

                        <div class="flex items-center justify-end pt-4 mt-6 border-t border-gray-200">
                            <Link :href="route('admin.users.index')"
                                class="px-4 py-2 mr-4 text-sm text-gray-600 border border-gray-300 rounded-md hover:text-gray-900 hover:bg-gray-50">
                            Cancelar
                            </Link>
                            <button type="submit" :disabled="form.processing"
                                class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                {{ form.processing ? 'Creando...' : 'Crear Usuario' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';


const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'client',
    reseller_id: null,
    company_name: '',
    phone_number: '',
    address_line1: '',
    address_line2: '',
    city: '',
    state_province: '',
    postal_code: '',
    country: 'VE',
    status: 'active',
    language_code: 'es',
    currency_code: 'USD',
});

const roleOptions = [
    { value: 'admin', label: 'Admin' },
    { value: 'client', label: 'Client' },
    { value: 'reseller', label: 'Reseller' },
];

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
    { value: 'suspended', label: 'Suspended' },
];

const countryOptions = [
    { value: '', label: 'Seleccione un país...' },
    { value: 'AR', label: 'Argentina' },
    { value: 'BO', label: 'Bolivia' },
    { value: 'BR', label: 'Brasil' },
    { value: 'CL', label: 'Chile' },
    { value: 'CO', label: 'Colombia' },
    { value: 'CR', label: 'Costa Rica' },
    { value: 'CU', label: 'Cuba' },
    { value: 'DO', label: 'República Dominicana' },
    { value: 'EC', label: 'Ecuador' },
    { value: 'SV', label: 'El Salvador' },
    { value: 'ES', label: 'España' },
    { value: 'US', label: 'Estados Unidos' },
    { value: 'GT', label: 'Guatemala' },
    { value: 'HT', label: 'Haití' },
    { value: 'HN', label: 'Honduras' },
    { value: 'MX', label: 'México' },
    { value: 'NI', label: 'Nicaragua' },
    { value: 'PA', label: 'Panamá' },
    { value: 'PY', label: 'Paraguay' },
    { value: 'PE', label: 'Perú' },
    { value: 'UY', label: 'Uruguay' },
    { value: 'VE', label: 'Venezuela' },
];

const languageOptions = [
    { value: 'es', label: 'Español' },
    { value: 'en', label: 'Inglés' },
];

const currencyOptions = [
    { value: 'USD', label: 'USD - Dólar estadounidense' },
    { value: 'EUR', label: 'EUR - Euro' },
];

const submit = () => {
    form.post(route('admin.users.store'), {
        onFinish: () => {
        },
        onSuccess: () => {
            form.reset('password', 'password_confirmation');
        }
    });
};

</script>

<style scoped>
/* Puedes añadir estilos específicos aquí si es necesario,
   pero Tailwind debería cubrir la mayoría */
</style>
