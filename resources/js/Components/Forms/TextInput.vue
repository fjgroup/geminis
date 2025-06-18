<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({ // Cambiar a la forma más flexible si la advertencia persiste
    type: [String, Number], // Permitir String o Number
    required: false, // Opcional, ajustar según necesidad
}); // Si se hace este cambio, la propiedad computada displayOrderModel en _Form.vue se vuelve menos crítica para la conversión de tipo.


const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        v-model="model"
        ref="input"
    />
</template>
