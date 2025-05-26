<script setup lang="ts">
import { computed } from 'vue';
import PlanCard from './PlanCard.vue'; // Importar el componente PlanCard

// Definiciones de tipos (reutilizadas de otros componentes ya migrados)
interface PlanFeatureObject {
  text: string;
  explanation?: string;
}
type PlanFeature = string | PlanFeatureObject;

interface PlanData {
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

interface ServiceCategoryData {
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

interface HighlightedPlansSectionData {
  title: string;
  subtitle: string;
  plansToHighlight: string[];
}

interface HighlightedPlansProps {
  sectionData?: HighlightedPlansSectionData;
  allCategories: ServiceCategoryData[];
  currencySymbol: string;
  markupPercentage: number;
  planButtonText: string;
  // onPlanContact ya no es una prop en Vue, será un evento emitido
}

// Definición de Props
const props = defineProps<HighlightedPlansProps>();

// Definición de Emits
const emit = defineEmits<{
  (e: 'planContact', planName: string): void;
}>();

// Lógica para manejar el evento de contacto del plan
const handlePlanContact = (planName: string) => {
  emit('planContact', planName);
};

// Computed property para obtener los detalles de los planes destacados
const highlightedPlansDetails = computed(() => {
  if (!props.sectionData) return [];

  const details: (PlanData & { categoryIcon: string })[] = [];
  for (const planId of props.sectionData.plansToHighlight) {
    for (const category of props.allCategories) {
      const plan = category.plans.find(p => p.id === planId);
      if (plan) {
        details.push({ ...plan, categoryIcon: category.categoryIcon });
        break; // Salir del bucle de categorías una vez encontrado el plan
      }
    }
  }
  return details;
});

// Computed property para determinar si se debe renderizar la sección
const shouldRenderSection = computed(() => {
  return props.sectionData !== undefined && highlightedPlansDetails.value.length > 0;
});

</script>

<template>
  <section v-if="shouldRenderSection" id="highlighted-plans-section" class="py-16 bg-slate-800">
    <div class="container px-4 mx-auto text-center">
      <h2 class="mb-4 text-3xl font-bold md:text-4xl text-slate-100 animate-fade-in-up">{{ sectionData?.title }}</h2>
      <p class="max-w-2xl mx-auto mb-12 text-lg text-slate-300 animate-fade-in-up font-inter">{{ sectionData?.subtitle }}</p>
      <div class="grid max-w-4xl grid-cols-1 gap-8 mx-auto md:grid-cols-2">
        <PlanCard
          v-for="plan in highlightedPlansDetails"
          :key="plan.id"
          :plan="plan"
          :categoryIcon="plan.categoryIcon"
          :currencySymbol="currencySymbol"
          :markupPercentage="markupPercentage"
          :buttonText="planButtonText"
          @contact="handlePlanContact" /> <!-- Escuchar el evento contact del PlanCard -->
      </div>
    </div>
  </section>
</template>

<style scoped>
/* Estilos específicos aquí si son necesarios */
</style>
