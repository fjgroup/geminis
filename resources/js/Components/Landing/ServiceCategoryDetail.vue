<script setup lang="ts">
import { computed } from 'vue';
import Icon from "../UI/Icon.vue";
import PlanCard from './PlanCard.vue'; // Importar el componente PlanCard

// Definiciones de tipos (reutilizadas de PlanCard o definidas localmente si es la primera vez)
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
  imageUrl: string; // Aunque no se usa en este componente, se incluye para consistencia
  plans: PlanData[];
}

interface ServiceCategoryDetailProps {
  category: ServiceCategoryData;
  currencySymbol: string;
  markupPercentage: number;
  planButtonText: string;
  // onPlanContact ya no es una prop en Vue, será un evento emitido
}

// Definición de Props
const props = defineProps<ServiceCategoryDetailProps>();

// Definición de Emits
const emit = defineEmits<{
  (e: 'planContact', planName: string): void;
}>();

// Lógica para manejar el evento de contacto del plan
const handlePlanContact = (planName: string) => {
  emit('planContact', planName);
};

// Computed property para el HTML seguro de la descripción (maneja el markdown)
const categoryDescriptionHtml = computed(() => {
  return props.category.categoryDescription.replace(/\*\*(.*?)\*\*/g, '<strong class="text-slate-100">$1</strong>');
});

</script>

<template>
  <section :id="`category-${category.categoryId}`" class="py-12 md:py-16 bg-slate-900 text-slate-100">
    <div class="container px-4 mx-auto">
      <div class="mb-10 text-center md:mb-16">
        <Icon :name="category.categoryIcon" class="w-20 h-20 mx-auto mb-6 text-brand-blue" />
        <h1 class="mb-4 text-4xl font-bold md:text-5xl text-slate-50">{{ category.categoryName }}</h1>
        <p
           :class="`${category.categoryDescriptionFontSize || 'text-lg'} text-slate-300 max-w-3xl mx-auto font-inter mb-6`"
           v-html="categoryDescriptionHtml">
        </p>

        <div v-if="category.advantagesTitle && category.advantagesList && category.advantagesList.length > 0"
             class="max-w-2xl p-6 mx-auto mt-8 text-left rounded-lg shadow-xl bg-slate-800">
          <h3 class="mb-4 text-2xl font-semibold text-center text-brand-blue">{{ category.advantagesTitle }}</h3>
          <ul class="space-y-3">
            <li v-for="(advantage, idx) in category.advantagesList" :key="idx" class="flex items-start">
              <Icon name="check-circle" class="flex-shrink-0 w-6 h-6 mt-1 mr-3 text-green-400" />
              <span class="text-slate-300 text-md">{{ advantage }}</span>
            </li>
          </ul>
        </div>
      </div>
      <div v-if="category.plans && category.plans.length > 0" class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
        <PlanCard
          v-for="plan in category.plans"
          :key="plan.id"
          :plan="plan"
          :categoryIcon="category.categoryIcon"
          :currencySymbol="currencySymbol"
          :markupPercentage="markupPercentage"
          :buttonText="planButtonText"
          @contact="handlePlanContact" /> <!-- Escuchar el evento contact del PlanCard -->
      </div>
      <p v-else class="text-lg text-center text-slate-400">No hay planes disponibles en esta categoría por el momento.</p>
    </div>
  </section>
</template>

<style scoped>
/* Estilos específicos aquí si son necesarios */
</style>
