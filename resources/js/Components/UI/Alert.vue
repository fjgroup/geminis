<script setup>
import { computed } from 'vue';
import { CheckCircleIcon, XCircleIcon, ExclamationTriangleIcon, InformationCircleIcon } from '@heroicons/vue/24/solid'; // Opcional, para iconos

const props = defineProps({
    message: String,
    type: {
        type: String,
        default: 'info', // success, error, warning, info
    },
});

const alertClasses = computed(() => {
    switch (props.type) {
        case 'success':
            return 'bg-green-100 dark:bg-green-700 border-green-400 dark:border-green-600 text-green-700 dark:text-green-100';
        case 'error':
            return 'bg-red-100 dark:bg-red-700 border-red-400 dark:border-red-600 text-red-700 dark:text-red-100';
        case 'warning':
            return 'bg-yellow-100 dark:bg-yellow-700 border-yellow-400 dark:border-yellow-600 text-yellow-700 dark:text-yellow-100';
        case 'info':
        default:
            return 'bg-blue-100 dark:bg-blue-700 border-blue-400 dark:border-blue-600 text-blue-700 dark:text-blue-100';
    }
});

// Opcional: Iconos
const IconComponent = computed(() => {
    if (props.type === 'success') return CheckCircleIcon;
    if (props.type === 'error') return XCircleIcon;
    if (props.type === 'warning') return ExclamationTriangleIcon;
    return InformationCircleIcon; // Para info y default
});
</script>

<template>
    <div v-if="message" :class="['border-l-4 p-4', alertClasses]" role="alert">
        <div class="flex items-center">
            <!-- <IconComponent class="h-5 w-5 mr-2" /> Opcional: Icono -->
            <p class="font-bold" v-if="type === 'error'">Error</p>
            <p class="font-bold" v-if="type === 'success'">Éxito</p>
            <p class="font-bold" v-if="type === 'warning'">Advertencia</p>
            <p class="font-bold" v-if="type === 'info'">Información</p>
        </div>
        <p class="text-sm">{{ message }}</p>
    </div>
</template>
