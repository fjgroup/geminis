<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'; // Importar AdminLayout
import { Head, Link, useForm } from '@inertiajs/vue3';
import Checkbox from '@/Components/Forms/Checkbox.vue';
import {
    ArrowDownTrayIcon, // Para guardar/actualizar
    XMarkIcon,         // Para cancelar
    PlusCircleIcon,    // Para agregar precio/opción
    PencilSquareIcon,
    TrashIcon,
    ListBulletIcon,    // Para listas o grupos
    CurrencyDollarIcon // Para precios
} from '@heroicons/vue/24/outline';


const props = defineProps({
  user: Object,
  resellers: Array, // Lista de revendedores para el select
});

const form = useForm({
  _method: 'PUT',
  name: props.user.name,
  email: props.user.email,
  password: '',
  password_confirmation: '',
  role: props.user.role,
  reseller_id: props.user.reseller_id || null, // Asegurar que sea null si no está definido
  company_name: props.user.company_name,
  phone_number: props.user.phone_number,
  address_line1: props.user.address_line1,
  address_line2: props.user.address_line2,
  city: props.user.city,
  state_province: props.user.state_province,
  postal_code: props.user.postal_code,
  country: props.user.country,
  status: props.user.status,
  language_code: props.user.language_code,
  currency_code: props.user.currency_code,
  // Campos para ResellerProfile, inicializar si existen
  reseller_profile: {
    brand_name: props.user.reseller_profile?.brand_name || '',
    custom_domain: props.user.reseller_profile?.custom_domain || '',
    logo_url: props.user.reseller_profile?.logo_url || '',
    support_email: props.user.reseller_profile?.support_email || '',
    terms_url: props.user.reseller_profile?.terms_url || '',
    allow_custom_products: props.user.reseller_profile?.allow_custom_products || false,
  }
});

const submit = () => {
  form.post(route('admin.users.update', props.user.id), {
    onSuccess: () => {
        form.reset('password', 'password_confirmation');
    }
  });
};

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
    // Puedes añadir más monedas aquí si es necesario
];
</script>

<template>
    <AdminLayout :title="'Editar Usuario - ' + user.name">

        <Head :title="'Editar Usuario - ' + user.name" />
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Editar Usuario: {{
                user.name }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200 md:p-8">
                        <!-- <h1 class="mb-6 text-2xl font-semibold">Editar Usuario: {{ user.name }}</h1> -->
                        <form @submit.prevent="submit">

                            <div class="mb-4">
                                <label for="name" class="block mb-1 text-sm font-medium text-gray-700">Nombre <span
                                        class="text-red-500">*</span></label>
                                <input type="text" v-model="form.name" id="name"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                                <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}
                                </div>
                            </div>


                            <div class="mb-4">
                                <label for="email" class="block mb-1 text-sm font-medium text-gray-700">Correo
                                    Electrónico <span class="text-red-500">*</span></label>
                                <input type="email" v-model="form.email" id="email"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                                <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}
                                </div>
                            </div>


                            <div class="grid grid-cols-1 gap-6 mb-4 md:grid-cols-2">
                                <div>
                                    <label for="password" class="block mb-1 text-sm font-medium text-gray-700">Nueva
                                        Contraseña (dejar en blanco para mantener actual)</label>
                                    <input type="password" v-model="form.password" id="password"
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        autocomplete="new-password" />
                                    <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{
                                        form.errors.password }}</div>
                                </div>
                                <div>
                                    <label for="password_confirmation"
                                        class="block mb-1 text-sm font-medium text-gray-700">Confirmar Nueva
                                        Contraseña</label>
                                    <input type="password" v-model="form.password_confirmation"
                                        id="password_confirmation" autocomplete="new-password"
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                                </div>
                            </div>


                            <div class="mb-4">
                                <label for="role" class="block mb-1 text-sm font-medium text-gray-700">Rol <span
                                        class="text-red-500">*</span></label>
                                <select v-model="form.role" id="role"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option v-for="option in roleOptions" :key="option.value" :value="option.value">{{
                                        option.label }}</option>
                                </select>
                                <div v-if="form.errors.role" class="mt-1 text-sm text-red-600">{{ form.errors.role }}
                                </div>
                            </div>


                            <div class="mb-4" v-if="form.role === 'client'">
                                <label for="reseller_id" class="block mb-1 text-sm font-medium text-gray-700">Asignado
                                    al Revendedor</label>
                                <select v-model="form.reseller_id" id="reseller_id"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option :value="null">Cliente directo de la plataforma (Admin)</option>
                                    <!-- Asumiendo que el admin principal también puede ser un "revendedor" para sus propios clientes -->
                                    <option v-for="reseller in props.resellers" :key="reseller.value"
                                        :value="reseller.value">
                                        {{ reseller.label }}
                                    </option>
                                </select>
                                <div v-if="form.errors.reseller_id" class="mt-1 text-sm text-red-600">{{
                                    form.errors.reseller_id }}</div>
                            </div>

                            <!-- Sección para Reseller Profile -->
                            <div v-if="form.role === 'reseller'" class="pt-6 mt-6 border-t">
                                <h3 class="mb-4 text-lg font-medium text-gray-900">Perfil de Revendedor</h3>

                                <div class="mb-4">
                                    <label for="reseller_brand_name"
                                        class="block text-sm font-medium text-gray-700">Nombre de Marca</label>
                                    <input type="text" v-model="form.reseller_profile.brand_name"
                                        id="reseller_brand_name"
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                                    <div v-if="form.errors['reseller_profile.brand_name']"
                                        class="mt-1 text-sm text-red-600">{{ form.errors['reseller_profile.brand_name']
                                        }}</div>
                                </div>

                                <div class="mb-4">
                                    <label for="reseller_custom_domain"
                                        class="block text-sm font-medium text-gray-700">Dominio Personalizado</label>
                                    <input type="text" v-model="form.reseller_profile.custom_domain"
                                        id="reseller_custom_domain"
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                                    <div v-if="form.errors['reseller_profile.custom_domain']"
                                        class="mt-1 text-sm text-red-600">{{
                                        form.errors['reseller_profile.custom_domain'] }}</div>
                                </div>

                                <div class="mb-4">
                                    <label for="reseller_logo_url" class="block text-sm font-medium text-gray-700">URL
                                        del Logo</label>
                                    <input type="url" v-model="form.reseller_profile.logo_url" id="reseller_logo_url"
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                                    <div v-if="form.errors['reseller_profile.logo_url']"
                                        class="mt-1 text-sm text-red-600">{{ form.errors['reseller_profile.logo_url'] }}
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="reseller_support_email"
                                        class="block text-sm font-medium text-gray-700">Email de Soporte</label>
                                    <input type="email" v-model="form.reseller_profile.support_email"
                                        id="reseller_support_email"
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                                    <div v-if="form.errors['reseller_profile.support_email']"
                                        class="mt-1 text-sm text-red-600">{{
                                        form.errors['reseller_profile.support_email'] }}</div>
                                </div>

                                <div class="mb-4">
                                    <label for="reseller_terms_url" class="block text-sm font-medium text-gray-700">URL
                                        de Términos y Condiciones</label>
                                    <input type="url" v-model="form.reseller_profile.terms_url" id="reseller_terms_url"
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                                    <div v-if="form.errors['reseller_profile.terms_url']"
                                        class="mt-1 text-sm text-red-600">{{ form.errors['reseller_profile.terms_url']
                                        }}</div>
                                </div>

                                <div class="block mb-4">
                                    <label class="flex items-center">
                                        <Checkbox v-model:checked="form.reseller_profile.allow_custom_products" />
                                        <span class="ml-2 text-sm text-gray-600">Permitir productos
                                            personalizados</span>
                                    </label>
                                    <div v-if="form.errors['reseller_profile.allow_custom_products']"
                                        class="mt-1 text-sm text-red-600">{{
                                        form.errors['reseller_profile.allow_custom_products'] }}</div>
                                </div>
                            </div>


                            <div class="mb-4">
                                <label for="company_name" class="block mb-1 text-sm font-medium text-gray-700">Nombre de
                                    la Compañía</label>
                                <input type="text" v-model="form.company_name" id="company_name"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                                <div v-if="form.errors.company_name" class="mt-1 text-sm text-red-600">{{
                                    form.errors.company_name }}</div>
                            </div>


                            <div class="mb-4">
                                <label for="phone_number"
                                    class="block text-sm font-medium text-gray-700">Teléfono</label>
                                <input type="text" v-model="form.phone_number" id="phone_number"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                                <div v-if="form.errors.phone_number" class="mt-1 text-sm text-red-600">{{
                                    form.errors.phone_number }}</div>
                            </div>


                            <div class="mb-4">
                                <label for="address_line1" class="block text-sm font-medium text-gray-700">Dirección
                                    Línea 1</label>
                                <input type="text" v-model="form.address_line1" id="address_line1"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                                <div v-if="form.errors.address_line1" class="mt-1 text-sm text-red-600">{{
                                    form.errors.address_line1 }}</div>
                            </div>


                            <div class="mb-4">
                                <label for="address_line2" class="block text-sm font-medium text-gray-700">Dirección
                                    Línea 2</label>
                                <input type="text" v-model="form.address_line2" id="address_line2"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                                <div v-if="form.errors.address_line2" class="mt-1 text-sm text-red-600">{{
                                    form.errors.address_line2 }}</div>
                            </div>


                            <div class="mb-4">
                                <label for="city" class="block text-sm font-medium text-gray-700">Ciudad</label>
                                <input type="text" v-model="form.city" id="city"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                                <div v-if="form.errors.city" class="mt-1 text-sm text-red-600">{{ form.errors.city }}
                                </div>
                            </div>


                            <div class="mb-4">
                                <label for="state_province" class="block text-sm font-medium text-gray-700">Estado /
                                    Provincia</label>
                                <input type="text" v-model="form.state_province" id="state_province"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                                <div v-if="form.errors.state_province" class="mt-1 text-sm text-red-600">{{
                                    form.errors.state_province }}</div>
                            </div>


                            <div class="mb-4">
                                <label for="postal_code" class="block text-sm font-medium text-gray-700">Código
                                    Postal</label>
                                <input type="text" v-model="form.postal_code" id="postal_code"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                                <div v-if="form.errors.postal_code" class="mt-1 text-sm text-red-600">{{
                                    form.errors.postal_code }}</div>
                            </div>


                            <div class="mb-4">
                                <label for="country" class="block text-sm font-medium text-gray-700">País</label>
                                <select v-model="form.country" id="country"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option v-for="option in countryOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                                <div v-if="form.errors.country" class="mt-1 text-sm text-red-600">{{ form.errors.country
                                    }}</div>

                            </div>


                            <div class="mb-4">
                                <label for="status" class="block text-sm font-medium text-gray-700">Estado <span
                                        class="text-red-500">*</span></label>
                                <select v-model="form.status" id="status"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option v-for="option in statusOptions" :key="option.value" :value="option.value">{{
                                        option.label }}</option>
                                </select>
                                <div v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status
                                    }}</div>
                            </div>


                            <div class="mb-4">
                                <label for="language_code"
                                    class="block text-sm font-medium text-gray-700">Idioma</label>
                                <select v-model="form.language_code" id="language_code"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option v-for="option in languageOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                                <div v-if="form.errors.language_code" class="mt-1 text-sm text-red-600">{{
                                    form.errors.language_code }}</div>
                            </div>


                            <div class="mb-4">
                                <label for="currency_code"
                                    class="block text-sm font-medium text-gray-700">Moneda</label>
                                <select v-model="form.currency_code" id="currency_code"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option v-for="option in currencyOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                                <div v-if="form.errors.currency_code" class="mt-1 text-sm text-red-600">{{
                                    form.errors.currency_code }}</div>
                            </div>

                            <!-- Client Balance Display -->
                            <div class="pt-6 mt-6 border-t" v-if="props.user.role === 'client' || props.user.role === 'reseller'">
                                <h3 class="mb-4 text-lg font-medium text-gray-900">Información Financiera</h3>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Saldo Actual</label>
                                    <p class="mt-1 text-lg font-semibold text-gray-900">
                                        {{ props.user.formatted_balance }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center justify-end pt-4 mt-6 border-t border-gray-200">
                                <Link :href="route('admin.users.index')"
                                    class="px-4 py-2 mr-4 text-sm text-gray-600 border border-gray-300 rounded-md hover:text-gray-900 hover:bg-gray-50">
                                Cancelar</Link>
                                <button type="submit" :disabled="form.processing"
                                    class="flex items-center px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                                    {{ form.processing ? 'Updating...' : 'Actualizar' }} </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
