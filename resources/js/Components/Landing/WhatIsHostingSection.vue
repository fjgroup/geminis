<script setup lang="ts">
// No se requieren importaciones de componentes específicos de Landing, solo si se usaran globalmente (Icon no se usa directamente aquí).

// Definición de tipos para la información "What is Hosting"
interface WhatIsHostingInfoData {
  title: string;
  content: string;
}

interface WhatIsHostingSectionProps {
    info?: WhatIsHostingInfoData;
}

// Definición de Props
const props = defineProps<WhatIsHostingSectionProps>();

</script>

<template>
    <section v-if="info" id="what-is-hosting-section" class="py-16 bg-slate-800 text-slate-200">
        <div class="container px-4 mx-auto">
            <div class="max-w-3xl mx-auto mb-12 text-center">
                <h2 class="mb-4 text-3xl font-bold text-white md:text-4xl animate-fade-in-up">{{ info.title }}</h2>
                <p class="text-lg text-slate-300 animate-fade-in-up font-inter" v-html="info.content"></p>
            </div>
        </div>
    </section>
</template>

<style scoped>
/* Estilos específicos aquí si son necesarios */
</style>
