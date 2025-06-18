<script setup lang="ts">
import Icon from "../UI/Icon.vue";
import { Link } from '@inertiajs/vue3'; // Added Link

// Definiciones de tipos (reutilizadas de otros componentes ya migrados)
interface PlanData { // Definición completa no es necesaria aquí, solo para ServiceCategoryData
  id: string;
  // ... otras propiedades si fueran necesarias
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

// Definición de tipos para la navegación
type PageView = 'landing' | 'categoryDetail';

interface AllCategoriesListProps {
    title: string;
    categories: ServiceCategoryData[];
    // onNavigate ya no es una prop en Vue, será un evento emitido
}

// Definición de Props
const props = defineProps<AllCategoriesListProps>();

// Emits and handleNavigate are no longer needed
// const emit = defineEmits<{
//   (e: 'navigate', page: PageView, categoryId?: string): void;
// }>();

// const handleNavigate = (page: PageView, categoryId?: string) => {
//   emit('navigate', page, categoryId);
// };

// Función para limpiar el markdown (negritas) de la descripción
const cleanDescription = (description: string) => {
    return description.replace(/\*\*.*?\*\*/g, '');
};

</script>

<template>
    <section id="all-categories-section" class="py-12 text-center md:py-16 bg-slate-900">
        <div class="container px-4 mx-auto">
            <h2 class="mb-12 text-3xl font-bold md:text-4xl text-slate-100 animate-fade-in-up">
                {{ title }}
            </h2>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <Link v-for="category in categories" :key="category.categoryId"
                    :href="route('landing.category', { categorySlug: category.categoryId })"
                    class="flex flex-col items-center p-6 text-center transition-all duration-300 rounded-lg shadow-lg bg-slate-800 hover:bg-slate-700 hover:shadow-sky-600/30 animate-zoom-in group">
                    <Icon :name="category.categoryIcon"
                        class="w-[4rem] h-[4rem] mb-4 transition-transform text-sky-500 group-hover:scale-110" />
                    <h3 class="mb-2 text-xl font-semibold text-slate-100">{{ category.categoryName }}</h3>
                    <p class="flex-grow text-sm text-slate-400 line-clamp-4">{{
                        cleanDescription(category.categoryDescription) }}</p>
                    <span
                        class="inline-flex items-center mt-4 text-sm font-semibold text-sky-500 group-hover:underline">Ver
                        Planes
                        <Icon name="arrow-right" class="w-3 h-3 ml-1" />
                    </span>
                </Link>
            </div>
        </div>
    </section>
</template>

<style scoped>
/* Estilos específicos aquí si son necesarios */
</style>
