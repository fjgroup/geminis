<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'; // Import necessary Vue reactivity and lifecycle functions
import { Head, Link, usePage, router } from '@inertiajs/vue3'; // Import Head, Link, usePage, and router from Inertia

// Importar los componentes Landing traducidos
import Header from '@/Components/Landing/Header.vue';
import HeroSection from '@/Components/Landing/HeroSection.vue';
import ServiceCategoryDetail from '@/Components/Landing/ServiceCategoryDetail.vue';
import HighlightedPlansSection from '@/Components/Landing/HighlightedPlansSection.vue';
import AllCategoriesList from '@/Components/Landing/AllCategoriesList.vue';
import Footer from '@/Components/Landing/Footer.vue';
import ContactModal from '@/Components/Landing/ContactModal.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue'; // Asumir que LoadingSpinner ya existe en Components
import Icon from '@/Components/Icon.vue'; // Importar el componente Icon
import WhatIsHostingSection from '@/Components/Landing/WhatIsHostingSection.vue';
import GeneralFeaturesExplainedSection from '@/Components/Landing/GeneralFeaturesExplainedSection.vue';
import FeatureHighlightCard from '@/Components/FeatureHighlightCard.vue'; // Importar el nuevo componente FeatureHighlightCard

// Definiciones de tipos (reutilizadas de otros componentes)
interface TrustpilotData { // Reutilizar si es necesario para props
    ratingText: string;
    reviewsText: string;
    reviewUrl: string;
}

interface FeatureHighlight { // Reutilizar si es necesario para props
    icon: string;
    text: string;
    backgroundColor: string; // Clase de Tailwind para el fondo del card
    textColor: string; // Clase de Tailwind para el color del texto
    iconColor: string; // Clase de Tailwind para el color del icono
}

interface HeroSectionData { // Reutilizar si es necesario para props
    title: string;
    subtitle: string;
    ctaButtonText: string;
    backgroundImageUrl: string;
    trustpilot: TrustpilotData;
    featureHighlights: FeatureHighlight[];
}

interface PlanFeatureObject { // Reutilizar si es necesario para props
    text: string;
    explanation?: string;
}
type PlanFeature = string | PlanFeatureObject;

interface PlanData { // Reutilizar si es necesario para props
    id: string;
    planIcon?: string;
    name: string;
    description: string;
    accounts_limit?: string | number;
    accounts_limit_text?: string;
    storage_gb?: number;
    storage_type?: string;
    storage_gb_info?: string;
    cpu_cores?: number;
    cpu_cores_info?: string;
    ram_gb?: number;
    ram_gb_info?: string;
    domains_limit?: number;
    domains_limit_text?: string;
    features: PlanFeature[];
    price_introductory: number;
    price_renewal: number;
    billing_cycle: string;
    notes?: string;
}

interface ServiceCategoryData { // Reutilizar si es necesario para props
    categoryId: string;
    categoryName: string;
    categoryIcon: string;
    categoryDescription: string;
    categoryDescriptionFontSize?: string;
    advantagesTitle?: string;
    advantagesList?: string[];
    imageUrl: string;
    plans: PlanData[];
}

interface ContactInfo { // Reutilizar si es necesario para props
    phone: string;
    email: string;
    message: string;
    footerDescription?: string;
}

interface PaymentMethod { // Reutilizar si es necesario para props
    name: string;
    logoIcon: string;
}

interface HighlightedPlansSectionData { // Reutilizar si es necesario para props
    title: string;
    subtitle: string;
    plansToHighlight: string[];
}

interface WhatIsHostingInfoData { // Reutilizar si es necesario para props
    title: string;
    content: string;
}

interface GeneralFeatureExplainedData { // Reutilizar si es necesario para props
    name: string;
    icon: string;
    explanation: string;
}

interface ServiceConfig { // Reutilizar si es necesario para props
    appName: string;
    currencySymbol: string;
    markupPercentage: number;
    heroSection: HeroSectionData;
    whatIsHostingInfo?: WhatIsHostingInfoData;
    generalFeaturesExplained?: GeneralFeatureExplainedData[];
    serviceCategoriesTitle: string;
    highlightedPlansSection?: HighlightedPlansSectionData;
    contactInfo: ContactInfo;
    paymentMethods: PaymentMethod[];
    planButtonText: string;
    serviceCategories: ServiceCategoryData[];
}

// Definición de tipos para la navegación de página
type PageView = 'landing' | 'categoryDetail';

// Define props (mantener las props de Inertia si son necesarias en esta página)
defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String, // No se usa en la landing view final, puede ser eliminado
    phpVersion: String, // No se usa en la landing view final, puede ser eliminado
});


// Define reactive state
const serviceData = ref<ServiceConfig | null>(null); // Tipado añadido
const isLoading = ref(true);
const error = ref<string | null>(null); // Tipado añadido
const isModalOpen = ref(false);
const contactedPlanName = ref<string | undefined>(undefined); // Tipado añadido

const currentPage = ref<PageView>('landing'); // Estado para controlar la vista actual
const selectedCategoryId = ref<string | null>(null); // Estado para la categoría seleccionada

const appMainRef = ref<HTMLElement | null>(null); // Referencia al elemento main para el scroll

// Para verificar los datos de autenticación
const page = usePage();



// Fetch data on mounted (adaptado de React useEffect)
onMounted(async () => {
    try {
        const response = await fetch('/data/services.json'); // Ajusta la ruta si es necesario
        // TEMPORALMENTE COMENTADO PARA EVITAR ERROR DE RUTA ZIGGY
        // // Redirección si el usuario ya está logueado
        // if (page.props.auth && page.props.auth.user) {
        //     const userRole = page.props.auth.user.role; // Asume que el objeto user tiene una propiedad 'role'
        //     if (userRole === 'client') {
        //         router.replace(route('client.dashboard'));
        //         return; // Detener la ejecución adicional si se redirige
        //     } else if (userRole === 'admin') { // O cualquier otro rol de administrador
        //         router.replace(route('admin.dashboard'));
        //         return; // Detener la ejecución adicional si se redirige
        //     }
        // }

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data: ServiceConfig = await response.json();
        serviceData.value = data;
    } catch (e: any) { // Captura el error como 'any' o con un tipo más específico si es posible
        console.error("Failed to fetch service data:", e);
        error.value = "No se pudo cargar la información de los servicios. Por favor, inténtalo de nuevo más tarde.";
    } finally {
        isLoading.value = false;
    }
});

// Funciones de manejo (adaptadas de React)

const handleOpenModal = (planName?: string) => {
    contactedPlanName.value = planName;
    isModalOpen.value = true;
};

const handleCloseModal = () => {
    isModalOpen.value = false;
    contactedPlanName.value = undefined;
};

const handleNavigation = (page: PageView, categoryId?: string) => {
    currentPage.value = page;
    selectedCategoryId.value = categoryId || null;
    if (appMainRef.value) {
        appMainRef.value.scrollTop = 0;
    }
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const handleHeroCtaClick = () => {
    if (serviceData.value) {
        const targetId = serviceData.value.whatIsHostingInfo ? "what-is-hosting-section"
            : serviceData.value.generalFeaturesExplained ? "general-features-section"
                : serviceData.value.highlightedPlansSection ? "highlighted-plans-section"
                    : "all-categories-section"; // Asegúrate de que estos IDs coincidan con los de tus componentes Vue
        const element = document.getElementById(targetId);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            const firstCategory = serviceData.value.serviceCategories[0];
            if (firstCategory) {
                handleNavigation('categoryDetail', firstCategory.categoryId);
            }
        }
    }
};

// Computed property para la categoría seleccionada
const selectedCategoryData = computed(() => {
    if (!serviceData.value || !selectedCategoryId.value) return null;
    return serviceData.value.serviceCategories.find(cat => cat.categoryId === selectedCategoryId.value);
});


</script>

<template>

    <Head title="Servicios" /> <!-- Título de la página -->

    <!-- Usar LoadingSpinner si está cargando -->
    <LoadingSpinner v-if="isLoading" />

    <div v-else-if="error"
        class="flex flex-col items-center justify-center min-h-screen p-8 bg-slate-900 text-slate-100">
        <!-- Reutilizar Icon si tienes un componente Icon global en Vue -->
        <Icon name="x-mark" class="w-16 h-16 mb-4 text-red-500" />
        <!-- <Icon name="x-mark" class="w-16 h-16 mb-4 text-red-500" /> -->
        <h1 class="mb-2 text-2xl font-bold">Error</h1>
        <p class="text-lg text-center">{{ error }}</p>
    </div>

    <div v-else-if="serviceData" class="flex flex-col min-h-screen bg-slate-900">
        <!-- Renderizar los componentes traducidos -->
        <Header :appName="serviceData.appName" :serviceCategories="serviceData.serviceCategories" :canLogin="canLogin"
            :canRegister="canRegister" :auth="$page.props.auth" @contactClick="handleOpenModal()"
            @navigate="handleNavigation" />

        <main ref="appMainRef" class="flex-grow overflow-y-auto">
            <div v-if="currentPage === 'landing'">
                <HeroSection :heroData="serviceData.heroSection" @ctaClick="handleHeroCtaClick" />


                <WhatIsHostingSection :info="serviceData.whatIsHostingInfo" />
                <GeneralFeaturesExplainedSection :features="serviceData.generalFeaturesExplained" />
                <HighlightedPlansSection :sectionData="serviceData.highlightedPlansSection"
                    :allCategories="serviceData.serviceCategories" :currencySymbol="serviceData.currencySymbol"
                    :markupPercentage="serviceData.markupPercentage" :planButtonText="serviceData.planButtonText"
                    @planContact="handleOpenModal" />
                <AllCategoriesList :title="serviceData.serviceCategoriesTitle"
                    :categories="serviceData.serviceCategories" @navigate="handleNavigation" />
            </div>

            <div v-else-if="currentPage === 'categoryDetail' && selectedCategoryData">
                <ServiceCategoryDetail :category="selectedCategoryData" :currencySymbol="serviceData.currencySymbol"
                    :markupPercentage="serviceData.markupPercentage" :planButtonText="serviceData.planButtonText"
                    @planContact="handleOpenModal" />
            </div>
        </main>

        <Footer :appName="serviceData.appName" :paymentMethods="serviceData.paymentMethods"
            :contactInfo="serviceData.contactInfo" :serviceCategories="serviceData.serviceCategories"
            @navigate="handleNavigation" />

        <ContactModal :isOpen="isModalOpen" :contactInfo="serviceData.contactInfo" :contactedPlan="contactedPlanName"
            @close="handleCloseModal" />
    </div>

    <!-- Opcional: Mantener enlaces de auth si se necesitan en esta página específica, ajustando estilos -->
    <!--
    <div v-if="canLogin" class="fixed top-0 right-0 px-6 py-4 sm:block">
        <Link v-if="$page.props.auth.user" :href="route('admin.dashboard')" class="text-sm text-gray-700 underline">Dashboard</Link>
        <template v-else>
            <Link :href="route('login')" class="text-sm text-gray-700 underline">Log in</Link>
            <Link v-if="canRegister" :href="route('register')" class="ml-4 text-sm text-gray-700 underline">Register</Link>
        </template>
</div>
-->

</template>

<style scoped>
/* Estilos específicos de la página de aterrizaje */
</style>
