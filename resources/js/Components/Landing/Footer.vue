<script setup lang="ts">
import { computed } from 'vue';
import Icon from '../Icon.vue';
import { Link } from '@inertiajs/vue3'; // Added Link

// Definiciones de tipos (reutilizadas de otros componentes o definidas localmente)
interface ContactInfo {
  phone: string;
  email: string;
  message: string; // No se usa en este componente, pero se incluye para consistencia
  footerDescription?: string;
}

interface PaymentMethod {
  name: string;
  logoIcon: string;
}

interface PlanData { // Definición completa no es necesaria aquí, solo para ServiceCategoryData
  id: string;
  // ... otras propiedades si fueran necesarias
}

interface ServiceCategoryData {
  categoryId: string;
  categoryName: string;
  categoryIcon: string; // No se usa en este componente
  categoryDescription: string; // Se usa una versión limpiada para el enlace
  categoryDescriptionFontSize?: string;
  advantagesTitle?: string;
  advantagesList?: string[];
  imageUrl: string;
  plans: PlanData[];
}

// Definición de tipos para la navegación
type PageView = 'landing' | 'categoryDetail';

interface FooterProps {
  appName: string;
  contactInfo: ContactInfo;
  paymentMethods: PaymentMethod[];
  serviceCategories: ServiceCategoryData[];
  // onNavigate ya no es una prop en Vue, será un evento emitido
}

// Definición de Props
const props = defineProps<FooterProps>();

// Emits and handleNavigate are no longer needed
// const emit = defineEmits<{
//   (e: 'navigate', page: PageView, categoryId?: string): void;
// }>();

// const handleNavigate = (page: PageView, categoryId?: string) => {
//   emit('navigate', page, categoryId);
// };

// Lógica para manejar el markdown en la descripción del footer
const footerDescriptionHtml = computed(() => {
  return (props.contactInfo.footerDescription || 'Tu socio confiable en soluciones de hosting web. Potenciamos tu presencia en línea.').replace(/\*\*(.*?)\*\*/g, '<strong class="text-slate-200">$1</strong>');
});

// Función para limpiar el markdown (negritas) de la descripción de la categoría para el enlace
const cleanCategoryDescription = (description: string) => {
    // En el componente React original, la descripción del enlace rápido en el footer NO elimina las negritas.
    // Solo se elimina para la lista completa de categorías.
    // Reviso el código React original (línea 600) y no aplica replace. Mantendré el texto tal cual.
    return description; // Mantener el texto original de la descripción de la categoría
};


</script>

<template>
  <footer class="px-4 py-12 bg-slate-800 text-slate-400 font-inter">
    <div class="container mx-auto">
      <div class="grid grid-cols-1 gap-8 mb-8 md:grid-cols-3">
        <div>
          <h3 class="mb-3 text-xl font-semibold text-slate-200">{{ appName }}</h3>
          <p
            class="text-sm text-justify"
            v-html="footerDescriptionHtml"
          ></p>
        </div>
        <div>
          <h3 class="mb-3 text-lg font-semibold text-slate-200">Enlaces Rápidos</h3>
          <ul class="space-y-2 text-sm">
            <li v-for="cat in serviceCategories" :key="cat.categoryId">
              <Link :href="route('landing.category', { categorySlug: cat.categoryId })" class="transition-colors hover:text-brand-blue">
                {{ cat.categoryName }}
              </Link>
            </li>
          </ul>
        </div>
        <div>
          <h3 class="mb-3 text-lg font-semibold text-slate-200">Métodos de Pago</h3>
          <div class="space-y-3">
            <div v-for="method in paymentMethods" :key="method.name" class="flex items-center p-2 rounded-md bg-slate-700 w-max" :title="method.name">
              <Icon :name="method.logoIcon" class="w-6 h-6 mr-2 text-slate-300"/>
              <span class="text-sm">{{ method.name }}</span>
            </div>
          </div>
        </div>
      </div>
      <div class="pt-8 text-sm text-center border-t border-slate-700">
        <p>&copy; {{ new Date().getFullYear() }} {{ appName }}. Todos los derechos reservados.</p>
      </div>
    </div>
  </footer>
</template>

<style scoped>
/* Estilos específicos aquí si son necesarios */
</style>
