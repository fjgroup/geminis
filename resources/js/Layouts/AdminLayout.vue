<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3'; // router es necesario para logout
import NavLink from '@/Components/Shared/NavLink.vue';
import {
    HomeIcon,
    UsersIcon,
    CubeIcon,
    CogIcon, // O WrenchScrewdriverIcon si prefieres para "Configurable"
    BriefcaseIcon, // Para Client Services
    CurrencyDollarIcon, // Para Facturas
    CreditCardIcon, // Para Métodos de Pago
    TagIcon, // Para Tipos de Producto
} from '@heroicons/vue/24/outline';
import Dropdown from '@/Components/Shared/Dropdown.vue';
import DropdownLink from '@/Components/Shared/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/Shared/ResponsiveNavLink.vue';
// import ApplicationLogo from '@/Components/UI/ApplicationLogo.vue'; // Descomenta si tienes y quieres usar un logo SVG

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>

        <Head :title="title" />

        <div class="min-h-screen bg-gray-100 flex">
            <!-- Sidebar -->
            <div class="w-64 bg-white shadow-lg">
                <!-- Logo -->
                <div class="flex items-center justify-center h-16 border-b border-gray-200">
                    <Link :href="route('admin.dashboard')" class="flex items-center">
                    <span class="text-xl font-semibold text-gray-800">Admin Panel</span>
                    </Link>
                </div>

                <!-- Navigation Links -->
                <nav class="mt-8">
                    <div class="px-4 space-y-2">
                        <!-- Dashboard -->
                        <Link :href="route('admin.dashboard')" :class="[
                            'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200',
                            route().current('admin.dashboard')
                                ? 'bg-blue-100 text-blue-700 border-r-4 border-blue-700'
                                : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
                        ]">
                        <HomeIcon class="w-5 h-5 mr-3" />
                        Dashboard
                        </Link>

                        <!-- Usuarios -->
                        <Link :href="route('admin.users.index')" :class="[
                            'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200',
                            route().current('admin.users.*')
                                ? 'bg-blue-100 text-blue-700 border-r-4 border-blue-700'
                                : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
                        ]">
                        <UsersIcon class="w-5 h-5 mr-3" />
                        Usuarios
                        </Link>

                        <!-- Productos -->
                        <Link :href="route('admin.products.index')" :class="[
                            'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200',
                            route().current('admin.products.*')
                                ? 'bg-blue-100 text-blue-700 border-r-4 border-blue-700'
                                : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
                        ]">
                        <CubeIcon class="w-5 h-5 mr-3" />
                        Productos
                        </Link>

                        <!-- Tipos de Producto -->
                        <Link :href="route('admin.product-types.index')" :class="[
                            'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200',
                            route().current('admin.product-types.*')
                                ? 'bg-blue-100 text-blue-700 border-r-4 border-blue-700'
                                : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
                        ]">
                        <TagIcon class="w-5 h-5 mr-3" />
                        Tipos de Producto
                        </Link>

                        <!-- Grupos Opciones -->
                        <Link :href="route('admin.configurable-option-groups.index')" :class="[
                            'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200',
                            route().current('admin.configurable-option-groups.*')
                                ? 'bg-blue-100 text-blue-700 border-r-4 border-blue-700'
                                : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
                        ]">
                        <CogIcon class="w-5 h-5 mr-3" />
                        Grupos Opciones
                        </Link>

                        <!-- Servicios Clientes -->
                        <Link :href="route('admin.client-services.index')" :class="[
                            'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200',
                            route().current('admin.client-services.*')
                                ? 'bg-blue-100 text-blue-700 border-r-4 border-blue-700'
                                : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
                        ]">
                        <BriefcaseIcon class="w-5 h-5 mr-3" />
                        Servicios Clientes
                        </Link>

                        <!-- Facturas -->
                        <Link :href="route('admin.invoices.index')" :class="[
                            'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200',
                            route().current('admin.invoices.*')
                                ? 'bg-blue-100 text-blue-700 border-r-4 border-blue-700'
                                : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
                        ]">
                        <CurrencyDollarIcon class="w-5 h-5 mr-3" />
                        Facturas
                        </Link>

                        <!-- Métodos de Pago -->
                        <Link :href="route('admin.payment-methods.index')" :class="[
                            'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200',
                            route().current('admin.payment-methods.*')
                                ? 'bg-blue-100 text-blue-700 border-r-4 border-blue-700'
                                : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
                        ]">
                        <CreditCardIcon class="w-5 h-5 mr-3" />
                        Métodos de Pago
                        </Link>
                    </div>
                </nav>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col">
                <!-- Top Header -->
                <header class="bg-white shadow-sm border-b border-gray-200">
                    <div class="flex items-center justify-between px-6 py-4">
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900" v-if="$slots.header">
                                <slot name="header" />
                            </h1>
                        </div>

                        <!-- User Dropdown -->
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <Dropdown align="right" width="48" v-if="$page.props.auth.user">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button"
                                                class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md hover:text-gray-700 focus:outline-none">
                                                {{ $page.props.auth.user.name }}

                                                <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')"> Perfil </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Cerrar Sesión
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Main Content -->
                <main class="flex-1 overflow-y-auto bg-gray-50">
                    <div class="p-6">
                        <slot />
                    </div>
                </main>
            </div>
        </div>
    </div>

</template>
