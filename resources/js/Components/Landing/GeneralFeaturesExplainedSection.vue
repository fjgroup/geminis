<script setup lang="ts">
import { computed } from 'vue';
import Icon from '../Icon.vue';

// Definición de tipos para las características generales explicadas
interface GeneralFeatureExplainedData {
  name: string;
  icon: string;
  explanation: string;
}

interface GeneralFeaturesExplainedSectionProps {
    features?: GeneralFeatureExplainedData[];
}

// Definición de Props
const props = defineProps<GeneralFeaturesExplainedSectionProps>();

// Computed property para determinar si se debe renderizar la sección
const shouldRenderSection = computed(() => {
  return props.features !== undefined && props.features.length > 0;
});

</script>

<template>
    <section v-if="shouldRenderSection" id="general-features-section" class="py-16 bg-slate-900">
        <div class="container px-4 mx-auto">
            <h2 class="mb-12 text-3xl font-bold text-center md:text-4xl text-slate-100 animate-fade-in-up">
                Características Clave de Nuestro Servicio</h2>
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="(feature, index) in features" :key="index"
                    class="flex flex-col items-start p-6 transition-shadow duration-300 shadow-xl bg-slate-800 rounded-xl animate-zoom-in hover:shadow-brand-blue/20">
                    <Icon :name="feature.icon" class="w-[4rem] h-[4rem] mb-4 text-blue-400" />
                    <h3 class="mb-2 text-xl font-semibold text-slate-100">{{ feature.name }}</h3>
                    <p class="flex-grow text-sm text-slate-400 font-inter">{{ feature.explanation }}</p>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
/* Estilos específicos aquí si son necesarios */
</style>
