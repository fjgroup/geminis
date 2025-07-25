<script setup>
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/UI/ApplicationLogo.vue';
import Dropdown from '@/Components/Shared/Dropdown.vue';
import DropdownLink from '@/Components/Shared/DropdownLink.vue';
import NavLink from '@/Components/Shared/NavLink.vue';
import ResponsiveNavLink from '@/Components/Shared/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const page = usePage();

const dashboardRoute = computed(() => {
    const userRole = page.props.auth.user?.role;
    if (userRole === "client") {
        return route("client.dashboard");
    } else if (userRole === "reseller") {
        return route("reseller.dashboard");
    }
    return route("admin.dashboard");
});

const isActiveDashboard = computed(() => {
    const userRole = page.props.auth.user?.role;
    if (userRole === "client") {
        return route().current("client.dashboard");
    } else if (userRole === "reseller") {
        return route().current("reseller.dashboard");
    }
    return route().current("admin.dashboard");
});
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100">
                <!-- Primary Navigation Menu -->
                <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex items-center shrink-0">
                                <Link :href="dashboardRoute">
                                <ApplicationLogo class="block w-auto text-gray-800 fill-current h-9" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="dashboardRoute" :active="isActiveDashboard">
                                    Panel Principal
                                </NavLink>

                                <template v-if="$page.props.auth.user?.role === 'client'">
                                    <NavLink :href="route('client.services.index')"
                                        :active="route().current('client.services.index')">
                                        Mis Servicios
                                    </NavLink>
                                    <NavLink :href="route('client.invoices.index')"
                                        :active="route().current('client.invoices.index') || route().current('client.invoices.show')">
                                        Facturas
                                    </NavLink>
                                    <NavLink :href="route('client.transactions.index')"
                                        :active="route().current('client.transactions.index')">
                                        Transacciones
                                    </NavLink>
                                    <NavLink :href="route('client.checkout.selectDomain')"
                                        :active="route().current('client.checkout.selectDomain')">
                                        Tienda
                                    </NavLink>
                                    <NavLink :href="route('client.funds.create')"
                                        :active="route().current('client.funds.create')">
                                        Agregar Fondos
                                    </NavLink>
                                </template>

                                <template v-if="$page.props.auth.user?.role === 'reseller'">
                                    <NavLink :href="route('reseller.clients.index')"
                                        :active="route().current('reseller.clients.index') || route().current('reseller.clients.create') || route().current('reseller.clients.edit') || route().current('reseller.clients.show')">
                                        My Clients
                                    </NavLink>
                                </template>

                                <template v-if="$page.props.auth.user?.role === 'admin'">
                                    <NavLink :href="route('admin.users.index')"
                                        :active="route().current('admin.users.index') || route().current('admin.users.show') || route().current('admin.users.edit')">
                                        Manage Users
                                    </NavLink>
                                    <NavLink :href="route('admin.products.index')"
                                        :active="route().current('admin.products.index') || route().current('admin.products.show')">
                                        Manage Products
                                    </NavLink>
                                    <!-- TODO: Implementar rutas de órdenes -->
                                    <!-- <NavLink :href="route('admin.orders.index')"
                                        :active="route().current('admin.orders.index') || route().current('admin.orders.show')">
                                        Manage Orders
                                    </NavLink> -->
                                    <NavLink :href="route('admin.invoices.index')"
                                        :active="route().current('admin.invoices.index') || route().current('admin.invoices.show')">
                                        Manage Invoices
                                    </NavLink>
                                    <NavLink :href="route('admin.configurable-option-groups.index')"
                                        :active="route().current('admin.configurable-option-groups.index')">
                                        Configurable Groups
                                    </NavLink>
                                    <NavLink :href="route('admin.client-services.index')"
                                        :active="route().current('admin.client-services.index') || route().current('admin.client-services.show')">
                                        Client Services
                                    </NavLink>
                                </template>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Botón Volver al Admin (solo visible durante impersonation) -->
                            <div v-if="$page.props.impersonating_admin" class="mr-4">
                                <form @submit.prevent="$inertia.post(route('admin.stop-impersonation'))" class="inline">
                                    <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        🔙 Volver al Admin
                                    </button>
                                </form>
                            </div>

                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button"
                                                class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md hover:text-gray-700 focus:outline-none">
                                                <span class="mr-2">{{ $page.props.auth.user?.name }}</span>
                                                <span v-if="$page.props.impersonating_admin"
                                                    class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full mr-2">
                                                    Modo Admin
                                                </span>
                                                <span
                                                    v-if="$page.props.auth.user?.role === 'client' && typeof $page.props.auth.user?.balance !== 'undefined' && $page.props.auth.user?.formatted_balance"
                                                    class="text-xs text-green-600">
                                                    ({{ $page.props.auth.user.formatted_balance }})
                                                </span>
                                                <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
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
                                class="inline-flex items-center justify-center p-2 text-gray-400 transition duration-150 ease-in-out rounded-md hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none">
                                <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                    <path
                                        :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{
                    block: showingNavigationDropdown,
                    hidden: !showingNavigationDropdown,
                }" class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="dashboardRoute" :active="isActiveDashboard">
                            Panel Principal
                        </ResponsiveNavLink>
                        <template v-if="$page.props.auth.user?.role === 'client'">
                            <ResponsiveNavLink :href="route('client.services.index')"
                                :active="route().current('client.services.index')">
                                Mis Servicios
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('client.invoices.index')"
                                :active="route().current('client.invoices.index') || route().current('client.invoices.show')">
                                Facturas
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('client.transactions.index')"
                                :active="route().current('client.transactions.index')">
                                Transacciones
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('client.checkout.selectDomain')"
                                :active="route().current('client.checkout.selectDomain')">
                                Tienda
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('client.funds.create')"
                                :active="route().current('client.funds.create')">
                                Agregar Fondos
                            </ResponsiveNavLink>
                        </template>
                        <template v-if="$page.props.auth.user?.role === 'reseller'">
                            <ResponsiveNavLink :href="route('reseller.clients.index')"
                                :active="route().current('reseller.clients.index') || route().current('reseller.clients.create') || route().current('reseller.clients.edit') || route().current('reseller.clients.show')">
                                My Clients
                            </ResponsiveNavLink>
                        </template>
                        <template v-if="$page.props.auth.user?.role === 'admin'">
                            <ResponsiveNavLink :href="route('admin.users.index')"
                                :active="route().current('admin.users.index') || route().current('admin.users.show') || route().current('admin.users.edit')">
                                Manage Users
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.products.index')"
                                :active="route().current('admin.products.index') || route().current('admin.products.show')">
                                Manage Products
                            </ResponsiveNavLink>
                            <!-- TODO: Implementar rutas de órdenes -->
                            <!-- <ResponsiveNavLink :href="route('admin.orders.index')"
                                :active="route().current('admin.orders.index') || route().current('admin.orders.show')">
                                Manage Orders
                            </ResponsiveNavLink> -->
                            <ResponsiveNavLink :href="route('admin.invoices.index')"
                                :active="route().current('admin.invoices.index') || route().current('admin.invoices.show')">
                                Manage Invoices
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.configurable-option-groups.index')"
                                :active="route().current('admin.configurable-option-groups.index')">
                                Configurable Groups
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.client-services.index')"
                                :active="route().current('admin.client-services.index') || route().current('admin.client-services.show')">
                                Client Services
                            </ResponsiveNavLink>
                        </template>
                    </div>

                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="px-4">
                            <div class="text-base font-medium text-gray-800">
                                {{ $page.props.auth.user?.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user?.email }}
                            </div>
                            <div v-if="$page.props.auth.user?.role === 'client' && typeof $page.props.auth.user?.balance !== 'undefined' && $page.props.auth.user?.formatted_balance"
                                class="mt-1 text-sm font-medium text-green-600">
                                Saldo: {{ $page.props.auth.user.formatted_balance }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Perfil
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                Cerrar Sesión
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <header class="bg-white shadow" v-if="$slots.header">
                <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main>

                <div v-if="$page.props.flash && $page.props.flash.success"
                    class="p-4 mb-4 text-green-700 bg-green-100 rounded dark:bg-green-800 dark:text-green-200">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash && $page.props.flash.error"
                    class="p-4 mb-4 text-red-700 bg-red-100 rounded dark:bg-red-800 dark:text-red-200">
                    {{ $page.props.flash.error }}
                </div>
                <div v-if="$page.props.flash && $page.props.flash.info"
                    class="p-4 mb-4 text-blue-700 bg-blue-100 rounded dark:bg-blue-800 dark:text-blue-200">
                    {{ $page.props.flash.info }}
                </div>
                <div v-if="$page.props.flash && $page.props.flash.warning"
                    class="p-4 mb-4 text-yellow-700 bg-yellow-100 rounded dark:bg-yellow-800 dark:text-yellow-200">
                    {{ $page.props.flash.warning }}
                </div>

                <slot />
            </main>
        </div>
    </div>
</template>
