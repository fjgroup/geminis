<script setup lang="ts">
import { ref, computed, watchEffect } from 'vue'; // Added watchEffect, removed onMounted
import { Head, Link, usePage, router } from '@inertiajs/vue3'; // Import Head, Link, usePage, and router from Inertia

// Importar los componentes Landing traducidos
import Header from '@/Components/Landing/Header.vue';
import HeroSection from '@/Components/Landing/HeroSection.vue';
import ServiceCategoryDetail from '@/Components/Landing/ServiceCategoryDetail.vue';
import HighlightedPlansSection from '@/Components/Landing/HighlightedPlansSection.vue';
import AllCategoriesList from '@/Components/Landing/AllCategoriesList.vue';
import Footer from '@/Components/Landing/Footer.vue';
import ContactModal from '@/Components/Landing/ContactModal.vue';
import LoadingSpinner from '@/Components/UI/LoadingSpinner.vue'; // Asumir que LoadingSpinner ya existe en Components
import Icon from '@/Components/UI/Icon.vue'; // Importar el componente Icon
import WhatIsHostingSection from '@/Components/Landing/WhatIsHostingSection.vue';
import GeneralFeaturesExplainedSection from '@/Components/Landing/GeneralFeaturesExplainedSection.vue';
import FeatureHighlightCard from '@/Components/Shared/FeatureHighlightCard.vue'; // Importar el nuevo componente FeatureHighlightCard

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

// Define props
const props = defineProps({
    serviceData: Object, // Data now comes from controller
    activeCategorySlug: {
        type: String,
        default: null,
    },
    // canLogin, canRegister are available via $page.props if needed by Header/Footer directly
});

// Define reactive state
const serviceConfig = computed(() => props.serviceData as ServiceConfig | null); // Use props
const isLoading = ref(false); // Data is pre-loaded
const error = computed(() => { // Error based on prop
    if (!props.serviceData) {
        return "No se pudo cargar la información de los servicios desde el servidor.";
    }
    // Could also check for a specific error structure within props.serviceData if controller sends it
    return null;
});
const isModalOpen = ref(false);
const contactedPlanName = ref<string | undefined>(undefined);

const currentPage = ref<PageView>('landing');
const selectedCategoryId = ref<string | null>(null);

const appMainRef = ref<HTMLElement | null>(null);

// Para verificar los datos de autenticación (canLogin, canRegister are in $page.props)
const page = usePage();


// Watch for activeCategorySlug changes to set current page and selected category
watchEffect(() => {
    if (props.activeCategorySlug && serviceConfig.value && serviceConfig.value.serviceCategories) {
        const categoryExists = serviceConfig.value.serviceCategories.some(
            // Assuming categoryId is used as slug. Adjust if a 'slug' field exists.
            cat => cat.categoryId === props.activeCategorySlug
        );
        if (categoryExists) {
            currentPage.value = 'categoryDetail';
            selectedCategoryId.value = props.activeCategorySlug;
        } else {
            // Slug doesn't match any category, show main landing content
            // This could happen if user navigates to a non-existent category slug
            currentPage.value = 'landing';
            selectedCategoryId.value = null;
            // Optional: Redirect to home if slug is invalid and not already on home
            // This check might be complex if '/' also uses a controller that sets activeCategorySlug to null.
            // For now, just show landing. A 404 might be better handled by Laravel routes/controller.
            // if (route().current() !== 'landing.home' && props.activeCategorySlug) {
            //    router.replace(route('landing.home'));
            // }
        }
    } else {
        // No slug, or no service data, so default to landing page view
        currentPage.value = 'landing';
        selectedCategoryId.value = null;
    }
    // Scroll to top when page/category changes
    if (appMainRef.value) appMainRef.value.scrollTop = 0;
    window.scrollTo({ top: 0, behavior: 'smooth' });
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

// REMOVED handleNavigation function as navigation is now via Inertia links

const handleHeroCtaClick = () => {
    if (serviceConfig.value) { // Use serviceConfig computed prop
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
            :canRegister="canRegister" :auth="$page.props.auth" @contactClick="handleOpenModal()" />

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
                    :categories="serviceData.serviceCategories" />
            </div>

            <div v-else-if="currentPage === 'categoryDetail' && selectedCategoryData">
                <ServiceCategoryDetail :category="selectedCategoryData" :currencySymbol="serviceData.currencySymbol"
                    :markupPercentage="serviceData.markupPercentage" :planButtonText="serviceData.planButtonText"
                    @planContact="handleOpenModal" />
            </div>
        </main>

        <Footer :appName="serviceData.appName" :paymentMethods="serviceData.paymentMethods"
            :contactInfo="serviceData.contactInfo" :serviceCategories="serviceData.serviceCategories" />

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
