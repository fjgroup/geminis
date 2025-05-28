<script setup lang="ts">


import { ref, PropType } from 'vue';
import Icon from '@/Components/Icon.vue'; // Asumiendo el alias @/Components apunta a ../geminis/resources/js/Components
import { Link, usePage } from '@inertiajs/vue3'; // Importar Link y usePage

interface ServiceCategoryData {
    categoryId: string; // O el tipo correcto según la implementación
    categoryName: string;
}

type PageView = 'landing' | 'categoryDetail' | 'contact'; // Definir los tipos de vista posibles

const props = defineProps({
    appName: {
        type: String,
        required: true
    },
    onContactClick: {
        type: Function as PropType<() => void>,
        required: true
    },
    onNavigate: {
        type: Function as PropType<(page: PageView, categoryId?: string) => void>,
        required: true
    },
    serviceCategories: {
        type: Array as PropType<ServiceCategoryData[]>,
        required: true
    },
    // Props para autenticación (añadidas)
    canLogin: {
        type: Boolean,
        default: false,
    },
    canRegister: {
        type: Boolean,
        default: false,
    },
    auth: { // Para acceder a $page.props.auth.user o props.auth.user
        type: Object,
        required: false, // Puede ser opcional si el header puede existir sin info de auth
    }
});

const page = usePage(); // Para acceder a $page.props si auth no se pasa directamente como user

const isMobileMenuOpen = ref(false);

const handleNavigate = (page: PageView, categoryId?: string) => {
    props.onNavigate(page, categoryId);
    isMobileMenuOpen.value = false;
};

const handleContactClick = () => {
    props.onContactClick();
    isMobileMenuOpen.value = false;
};
</script>

<template>
    <nav class="sticky top-0 z-50 shadow-lg bg-slate-800 font-inter">
        <div class="container px-4 mx-auto sm:px-6 lg:px-8">
            <div class="md:py-3">
                <div class="flex items-center justify-between h-16 md:h-auto">
                    <div class="flex-shrink-0">
                        <button @click="handleNavigate('landing')" class="flex-shrink-0 text-xl font-bold text-white">
                            {{ appName }}
                        </button>
                    </div>
                    <div class="items-center ml-auto space-x-2 md:flex">
                        <template v-if="!props.auth || !props.auth.user">
                            <Link :href="route('login')" title="Login"
                                class="flex items-center p-2 transition-colors rounded-md text-slate-300 hover:bg-slate-700 hover:text-white">
                            <Icon name="login" className="w-5 h-5 mr-2 text-green-400" />
                            Login
                            </Link>
                            <Link :href="route('register')" title="Register"
                                class="flex items-center p-2 transition-colors rounded-md text-slate-300 hover:bg-slate-700 hover:text-white">
                            <Icon name="person-add" className="w-5 h-5 mr-2 text-green-400" />
                            Registrarse
                            </Link>
                        </template>
                        <template v-else>
                            <Link v-if="props.auth.user.role === 'admin'" :href="route('admin.dashboard')"
                                title="Admin Dashboard"
                                class="flex items-center p-2 transition-colors rounded-md text-slate-300 hover:bg-slate-700 hover:text-white">
                            <Icon name="dashboard" className="w-5 h-5 mr-2 text-green-400" />

                            Dashboard
                            </Link>
                            <Link v-else-if="props.auth.user.role === 'client'" href="/client/services"
                                title="Client Dashboard"
                                class="flex items-center p-2 transition-colors rounded-md text-slate-300 hover:bg-slate-700 hover:text-white">
                            <Icon name="dashboard" className="w-5 h-5 mr-2 text-green-400" />

                            Dashboard
                            </Link>
                            <Link :href="route('logout')" method="post" as="button" type="button" title="Salir"
                                class="flex items-center p-2 transition-colors rounded-md text-slate-300 hover:bg-slate-700 hover:text-white">
                            <Icon name="logout" className="w-5 h-5 mr-2 text-green-400" />

                            Salir
                            </Link>
                        </template>
                    </div>
                </div>
                <div class="hidden space-x-1 md:flex md:flex-wrap md:items-baseline md:justify-center md:mt-2">
                    <button v-for="item in serviceCategories" :key="item.categoryId"
                        @click="handleNavigate('categoryDetail', item.categoryId)"
                        :class="`px-3 py-2 rounded-md text-sm font-medium text-slate-300 hover:bg-slate-700 hover:text-white transition-colors text-left`">
                        {{ item.categoryName }}
                    </button>
                </div>
            </div>
        </div>
        <div v-if="isMobileMenuOpen"
            class="absolute inset-x-0 z-40 p-2 space-y-1 shadow-xl md:hidden top-16 bg-slate-800 sm:px-3 animate-fade-in-down"
            id="mobile-menu">
            <button v-for="item in serviceCategories" :key="item.categoryId"
                @click="handleNavigate('categoryDetail', item.categoryId)"
                :class="`block w-full px-3 py-2 rounded-md text-sm font-medium text-slate-300 hover:bg-slate-700 hover:text-white transition-colors text-left`">
                {{ item.categoryName }}
            </button>
            <button @click="handleContactClick"
                class="block w-full px-3 py-2 mt-2 text-sm font-medium text-left text-white transition-colors rounded-md bg-brand-blue hover:bg-brand-blue-dark">
                Contacto
            </button>
            <!-- Menú móvil para usuarios no autenticados -->
            <template v-if="!props.auth || !props.auth.user">
                <Link v-if="props.canLogin" :href="route('login')"
                    class="flex items-center w-full px-3 py-2 mt-1 text-sm font-medium text-left transition-colors rounded-md text-slate-300 hover:bg-slate-700 hover:text-white">
                <Icon name="login" className="w-5 h-5 mr-2 text-green-400" />
                Login
                </Link>
                <Link v-if="props.canRegister" :href="route('register')"
                    class="flex items-center w-full px-3 py-2 mt-1 text-sm font-medium text-left transition-colors rounded-md text-slate-300 hover:bg-slate-700 hover:text-white">
                <Icon name="person-add" className="w-5 h-5 mr-2 text-green-400" />
                Registrarse
                </Link>
            </template>
            <!-- Menú móvil para usuarios autenticados -->
            <template v-if="props.auth && props.auth.user">
                <Link v-if="props.auth.user.role === 'admin'" :href="route('admin.dashboard')"
                    class="flex items-center w-full px-3 py-2 mt-1 text-sm font-medium text-left transition-colors rounded-md text-slate-300 hover:bg-slate-700 hover:text-white">
                <Icon name="dashboard" className="w-5 h-5 mr-2 text-green-400" />

                Admin Panel
                </Link>
                <Link v-else-if="props.auth.user.role === 'client'" :href="route('client.dashboard')"
                    class="flex items-center w-full px-3 py-2 mt-1 text-sm font-medium text-left transition-colors rounded-md text-slate-300 hover:bg-slate-700 hover:text-white">
                <Icon name="dashboard" className="w-5 h-5 mr-2 text-green-400" />

                Mi Panel
                </Link>
                <Link :href="route('logout')" method="post" as="button" type="button"
                    class="flex items-center w-full px-3 py-2 mt-1 text-sm font-medium text-left transition-colors rounded-md text-slate-300 hover:bg-slate-700 hover:text-white">
                <Icon name="logout" className="w-5 h-5 mr-2 text-green-400" />

                Salir
                </Link>
            </template>
        </div>
    </nav>
</template>

<style scoped>
/* Puedes añadir estilos adicionales aquí si es necesario */
.animate-fade-in-down {
    animation: fadeInDown 0.5s ease-out forwards;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
