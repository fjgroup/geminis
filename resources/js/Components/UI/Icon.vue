<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue';

// Asume que icons.json está en public/data/icons.json

const props = defineProps({
    name: {
        type: String,
        default: "",
    },
    className: { // <-- AÑADIMOS ESTA PROPIEDAD
        type: String,
        default: "",
    }
});

const svgContent = ref<string>('');
const allIcons = ref<Record<string, string> | null>(null);

const loadIconsData = async () => {
    if (allIcons.value) return; // Evita recargar si ya están los datos
    try {
        // Ajusta la ruta a tu icons.json si es diferente
        const response = await fetch('/data/icons.json');
        if (!response.ok) {
            throw new Error(`Error cargando icons.json: ${response.statusText}`);
        }
        allIcons.value = await response.json();
    } catch (error) {
       // console.error("No se pudo cargar icons.json:", error);
       // allIcons.value = {}; // Previene errores si la carga falla
    }
};

const updateSvgContent = () => {
    if (allIcons.value && props.name && allIcons.value[props.name]) {
        svgContent.value = allIcons.value[props.name];
    } else if (props.name) {
        // Si el icono solicitado no se encuentra, pero se proporcionó un nombre, muestra el nombre.
        // Usamos un prefijo para que sea visualmente un poco distinto.
        // El color del texto será heredado por 'text-current' en el div padre.
        svgContent.value = `? ${props.name}`;
        console.warn(`Icono "${props.name}" no encontrado en icons.json.`);
    }
};

onMounted(async () => {
    await loadIconsData();
    updateSvgContent();
});

watch(() => props.name, () => {
    if (allIcons.value) {
        updateSvgContent();
    } else {
        loadIconsData().then(updateSvgContent); // Intenta cargar si aún no están listos
    }
});

const iconClasses = computed(() => [
    'inline-block w-5 h-5 text-current', // Tamaño base y alineación
    props.className
]);
</script>

<template>
    <div :class="iconClasses" aria-hidden="true" v-html="svgContent"></div>
</template>
