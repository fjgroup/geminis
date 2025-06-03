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

        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100">
                <!-- Primary Navigation Menu -->
                <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex items-center shrink-0">
                                <Link :href="route('admin.dashboard')">
                                <!-- <ApplicationLogo class="block w-auto text-gray-800 fill-current h-9" /> -->
                                <span class="text-xl font-semibold text-gray-800">Dashboard</span>
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <!-- <NavLink :href="route('admin.dashboard')" :active="route().current('admin.dashboard')">
                                    Dashboard
                                    <HomeIcon class="inline-block w-5 h-5 ml-2" />
                                </NavLink> -->
                                <NavLink :href="route('admin.users.index')" :active="route().current('admin.users.*')">
                                    Usuarios
                                    <UsersIcon class="inline-block w-5 h-5 ml-2" />
                                </NavLink>
                                <NavLink :href="route('admin.products.index')"
                                    :active="route().current('admin.products.*')">
                                    Productos
                                    <CubeIcon class="inline-block w-5 h-5 ml-2" />
                                </NavLink>
                                <NavLink :href="route('admin.product-types.index')" :active="route().current('admin.product-types.*')">
                                    Tipos de Producto
                                    <TagIcon class="inline-block w-5 h-5 ml-2" />
                                </NavLink>
                                <NavLink :href="route('admin.configurable-option-groups.index')"
                                    :active="route().current('admin.configurable-option-groups.*')">
                                    Grupos Opciones
                                    <CogIcon class="inline-block w-5 h-5 ml-2" />
                                </NavLink>
                                <NavLink :href="route('admin.client-services.index')"
                                    :active="route().current('admin.client-services.*')">
                                    Servicios Clientes
                                    <BriefcaseIcon class="inline-block w-5 h-5 ml-2" />
                                </NavLink>
                                <NavLink :href="route('admin.orders.index')" :active="route().current('admin.orders.*')">
                                    Órdenes
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="inline-block w-5 h-5 ml-2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0Zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0Z" />
                                        </svg>

                                    </NavLink>
                                    <NavLink :href="route('admin.invoices.index')" :active="route().current('admin.invoices.*')">
                                        Facturas
                                        <CurrencyDollarIcon class="inline-block w-5 h-5 ml-2" />
                                    </NavLink>
                                    <NavLink :href="route('admin.payment-methods.index')" :active="route().current('admin.payment-methods.*')">
                                        Métodos de Pago
                                        <CreditCardIcon class="inline-block w-5 h-5 ml-2" />
                                    </NavLink>
                                </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
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

                        <!-- Hamburger -->
                        <div class="flex items-center -me-2 sm:hidden">
                            <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 text-gray-400 transition duration-150 ease-in-out rounded-md hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500">
                                <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                    class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <!-- <ResponsiveNavLink :href="route('admin.dashboard')"
                            :active="route().current('admin.dashboard')">
                            Dashboard
                        </ResponsiveNavLink> -->
                        <ResponsiveNavLink :href="route('admin.users.index')"
                            :active="route().current('admin.users.*')">
                            Usuarios
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('admin.products.index')"
                            :active="route().current('admin.products.*')">
                            Productos
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('admin.product-types.index')" :active="route().current('admin.product-types.*')">
                            Tipos de Producto
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('admin.client-services.index')"
                            :active="route().current('admin.client-services.*')">
                            Servicios Clientes
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('admin.invoices.index')"
                            :active="route().current('admin.invoices.*')">
                            Facturas
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('admin.payment-methods.index')"
                            :active="route().current('admin.payment-methods.*')">
                            Métodos de Pago
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200" v-if="$page.props.auth.user">
                        <div class="px-4">
                            <div class="text-base font-medium text-gray-800">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">{{ $page.props.auth.user.email }}</div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')"> Perfil </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                Cerrar Sesión
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow" v-if="$slots.header">
                <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
