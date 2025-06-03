<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: String,
    rows: {
        type: [String, Number],
        default: 3,
    },
    disabled: {
        type: Boolean,
        default: false,
    }
});

defineEmits(['update:modelValue']);

const textarea = ref(null);

onMounted(() => {
    // Puedes añadir lógica aquí si necesitas, por ejemplo, auto-focus
});
</script>

<template>
    <textarea
        ref="textarea"
        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:focus:ring-indigo-600 dark:focus:border-indigo-600 sm:text-sm"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        :rows="rows"
        :disabled="disabled"
    ></textarea>
</template>
