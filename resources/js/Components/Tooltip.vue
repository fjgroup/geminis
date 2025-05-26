<script setup>
import { defineProps } from 'vue';

const props = defineProps({
  text: {
    type: String,
    required: true,
  },
});
</script>

<template>
  <span class="relative group">
    <slot></slot> <!-- Slot para el contenido hijo -->
    <span class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 w-max max-w-xs
                   bg-slate-700 text-white text-xs rounded-md py-1.5 px-2.5 z-10 whitespace-pre-line
                   opacity-0 group-hover:opacity-100 transition-opacity duration-200
                   pointer-events-none shadow-lg">
      {{ text }}
      <svg class="absolute left-0 w-full h-2 text-slate-700 top-full" x="0px" y="0px" viewBox="0 0 255 255">
        <polygon class="fill-current" points="0,0 127.5,127.5 255,0"/>
      </svg>
    </span>
  </span>
</template>

<style scoped>
/* Estilos específicos si fueran necesarios, pero Tailwind ya maneja la mayoría */
</style>
