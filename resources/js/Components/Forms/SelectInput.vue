<template>
    <select :value="modelValue" @change="$emit('update:modelValue', $event.target.value)"
        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
        :disabled="disabled">
        <option v-if="placeholder" :value="null" disabled>{{ placeholder }}</option>
        <option v-for="option in options" :key="option.value" :value="option.value">
            {{ option.label }}
        </option>
    </select>
</template>

<script setup>
defineProps({
    modelValue: [String, Number, null],
    options: {
        type: Array,
        required: true, // [{ value: 'foo', label: 'Foo' }, ...]
    },
    placeholder: String,
    disabled: {
        type: Boolean,
        default: false,
    }
});

defineEmits(['update:modelValue']);
</script>
