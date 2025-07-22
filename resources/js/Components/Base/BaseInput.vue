<template>
  <div :class="wrapperClasses">
    <!-- Label -->
    <label v-if="label" :for="inputId" :class="labelClasses">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>

    <!-- Input wrapper -->
    <div class="relative">
      <!-- Prefix icon -->
      <div v-if="prefixIcon" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <Icon :name="prefixIcon" :size="iconSize" class="text-gray-400" />
      </div>

      <!-- Input element -->
      <component
        :is="inputComponent"
        :id="inputId"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :class="inputClasses"
        :rows="type === 'textarea' ? rows : undefined"
        :min="min"
        :max="max"
        :step="step"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
        @keydown="handleKeydown"
      />

      <!-- Suffix icon -->
      <div v-if="suffixIcon || loading" class="absolute inset-y-0 right-0 pr-3 flex items-center">
        <LoadingSpinner v-if="loading" :size="iconSize" />
        <Icon v-else-if="suffixIcon" :name="suffixIcon" :size="iconSize" class="text-gray-400" />
      </div>

      <!-- Clear button -->
      <button
        v-if="clearable && modelValue && !disabled && !readonly"
        type="button"
        class="absolute inset-y-0 right-0 pr-3 flex items-center"
        @click="clearInput"
      >
        <Icon name="x" :size="iconSize" class="text-gray-400 hover:text-gray-600" />
      </button>
    </div>

    <!-- Help text -->
    <p v-if="helpText && !hasError" :class="helpTextClasses">
      {{ helpText }}
    </p>

    <!-- Error message -->
    <p v-if="hasError" :class="errorTextClasses">
      {{ errorMessage }}
    </p>
  </div>
</template>

<script setup>
import { computed, ref, useId } from 'vue'
import Icon from '@/Components/UI/Icon.vue'
import LoadingSpinner from '@/Components/UI/LoadingSpinner.vue'

const props = defineProps({
  // Valor del input
  modelValue: {
    type: [String, Number],
    default: ''
  },
  
  // Tipo de input
  type: {
    type: String,
    default: 'text',
    validator: (value) => [
      'text', 'email', 'password', 'number', 'tel', 'url', 'search', 'textarea'
    ].includes(value)
  },
  
  // Etiqueta y ayuda
  label: String,
  placeholder: String,
  helpText: String,
  
  // Validación y errores
  required: Boolean,
  error: [String, Array, Boolean],
  
  // Estados
  disabled: Boolean,
  readonly: Boolean,
  loading: Boolean,
  
  // Iconos
  prefixIcon: String,
  suffixIcon: String,
  clearable: Boolean,
  
  // Tamaños
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  
  // Propiedades específicas
  rows: {
    type: Number,
    default: 3
  },
  min: [String, Number],
  max: [String, Number],
  step: [String, Number],
})

const emit = defineEmits(['update:modelValue', 'blur', 'focus', 'keydown', 'clear'])

// ID único para el input
const inputId = useId()

// Estado interno
const isFocused = ref(false)

// Componente del input
const inputComponent = computed(() => {
  return props.type === 'textarea' ? 'textarea' : 'input'
})

// Verificar si hay error
const hasError = computed(() => {
  if (typeof props.error === 'boolean') return props.error
  if (typeof props.error === 'string') return props.error.length > 0
  if (Array.isArray(props.error)) return props.error.length > 0
  return false
})

// Mensaje de error
const errorMessage = computed(() => {
  if (typeof props.error === 'string') return props.error
  if (Array.isArray(props.error)) return props.error[0]
  return ''
})

// Clases del wrapper
const wrapperClasses = computed(() => {
  return 'w-full'
})

// Clases del label
const labelClasses = computed(() => {
  const classes = [
    'block text-sm font-medium mb-1',
    hasError.value ? 'text-red-700' : 'text-gray-700'
  ]
  
  return classes.join(' ')
})

// Clases del input
const inputClasses = computed(() => {
  const baseClasses = [
    'block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset',
    'placeholder:text-gray-400 focus:ring-2 focus:ring-inset',
    'disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500',
    'transition-colors duration-200'
  ]
  
  // Tamaño
  const sizeClasses = {
    sm: 'px-2.5 py-1.5 text-sm',
    md: 'px-3 py-2 text-sm', 
    lg: 'px-4 py-3 text-base'
  }
  
  // Estados
  const stateClasses = hasError.value
    ? 'ring-red-300 focus:ring-red-500 text-red-900'
    : 'ring-gray-300 focus:ring-blue-500 text-gray-900'
  
  // Espaciado para iconos
  const spacingClasses = []
  if (props.prefixIcon) spacingClasses.push('pl-10')
  if (props.suffixIcon || props.clearable || props.loading) spacingClasses.push('pr-10')
  
  return [
    ...baseClasses,
    sizeClasses[props.size] || sizeClasses.md,
    stateClasses,
    ...spacingClasses
  ].join(' ')
})

// Clases del texto de ayuda
const helpTextClasses = computed(() => {
  return 'mt-1 text-sm text-gray-600'
})

// Clases del texto de error
const errorTextClasses = computed(() => {
  return 'mt-1 text-sm text-red-600'
})

// Tamaño del icono
const iconSize = computed(() => {
  const sizes = {
    sm: 'sm',
    md: 'sm',
    lg: 'md'
  }
  
  return sizes[props.size] || 'sm'
})

// Manejadores de eventos
const handleInput = (event) => {
  emit('update:modelValue', event.target.value)
}

const handleBlur = (event) => {
  isFocused.value = false
  emit('blur', event)
}

const handleFocus = (event) => {
  isFocused.value = true
  emit('focus', event)
}

const handleKeydown = (event) => {
  emit('keydown', event)
}

const clearInput = () => {
  emit('update:modelValue', '')
  emit('clear')
}
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
