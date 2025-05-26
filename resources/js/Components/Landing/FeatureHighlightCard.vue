<script setup lang="ts">
import { defineProps } from 'vue';
import Icon from '@/Components/Icon.vue'; // Importar el componente Icon

interface FeatureHighlightCardProps {
  iconName: string;
  text: string;
}

const props = defineProps<FeatureHighlightCardProps>();

</script>

<template>
  <div class="flex flex-col items-center p-6 text-center rounded-lg shadow-lg feature-highlight-card bg-slate-50 text-slate-800 animate-zoom-in">
    <Icon :name="props.iconName" class="w-10 h-10 mb-3 text-brand-blue" />
    <h3 class="font-semibold text-md">{{ props.text }}</h3>
  </div>
</template>

<style scoped>
/* No hay estilos específicos en el componente React, pero puedes añadir si es necesario */
/* .feature-highlight-card { ... } */
</style>
