<script setup lang="ts">
import { defineProps } from 'vue';
import Icon from '@/Components/UI/Icon.vue'; // Importar el componente Icon

interface FeatureHighlightCardProps {
    iconName: string; // Nombre del icono a mostrar
    text: string; // Texto de la característica
    // Nuevas propiedades para colores, opcionales con valores por defecto
    backgroundColor?: string; // Clase de Tailwind para el fondo del card
    textColor?: string; // Clase de Tailwind para el color del texto
    iconColor?: string; // Clase de Tailwind para el color del icono
 // Añadido para el color, aunque no se usa en este ejemplo
}

const props = withDefaults(defineProps<FeatureHighlightCardProps>(), {
    // Valores por defecto si no se proporcionan desde services.json
    backgroundColor: 'bg-slate-50', // Fondo claro por defecto
    textColor: 'text-slate-800', // Texto oscuro por defecto
    iconColor: 'text-gray-600' // Color del icono por defecto (asumiendo que 'brand-blue' está definido en tu config de Tailwind)
});

</script>

<template>
    <div
        :class="['flex flex-col items-center p-6 text-center rounded-lg shadow-lg feature-highlight-card animate-zoom-in', props.backgroundColor, props.textColor]">
        <Icon :name="props.iconName" :class="['w-[4rem] h-[4rem] mb-9', props.iconColor]" />
        <h3 class="text-xl font-semibold">{{ props.text }}</h3>
    </div>
</template>

<style scoped>
/* No hay estilos específicos en el componente React, pero puedes añadir si es necesario */
/* .feature-highlight-card { ... } */
</style>
