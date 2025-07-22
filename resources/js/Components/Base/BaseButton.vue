<template>
  <component
    :is="tag"
    :type="tag === 'button' ? type : undefined"
    :href="tag === 'a' ? href : undefined"
    :to="tag === 'router-link' ? to : undefined"
    :disabled="disabled || loading"
    :class="buttonClasses"
    @click="handleClick"
  >
    <LoadingSpinner v-if="loading" :size="spinnerSize" class="mr-2" />
    <Icon v-if="icon && !loading" :name="icon" :size="iconSize" class="mr-2" />
    <slot />
  </component>
</template>

<script setup>
import { computed } from 'vue'
import { designTokens } from '@/design-tokens.js'
import LoadingSpinner from '@/Components/UI/LoadingSpinner.vue'
import Icon from '@/Components/UI/Icon.vue'

const props = defineProps({
  // Variantes de estilo
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'success', 'warning', 'error', 'ghost', 'outline'].includes(value)
  },
  
  // Tamaños
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  
  // Tipo de elemento
  tag: {
    type: String,
    default: 'button',
    validator: (value) => ['button', 'a', 'router-link'].includes(value)
  },
  
  // Propiedades específicas
  type: {
    type: String,
    default: 'button'
  },
  
  href: String,
  to: [String, Object],
  
  // Estados
  disabled: Boolean,
  loading: Boolean,
  
  // Iconos
  icon: String,
  
  // Estilos adicionales
  fullWidth: Boolean,
  rounded: Boolean,
})

const emit = defineEmits(['click'])

// Clases computadas para el botón
const buttonClasses = computed(() => {
  const classes = [
    // Clases base
    'inline-flex items-center justify-center font-medium transition-all duration-150',
    'focus:outline-none focus:ring-2 focus:ring-offset-2',
    'disabled:opacity-50 disabled:cursor-not-allowed',
    
    // Tamaño
    sizeClasses.value,
    
    // Variante
    variantClasses.value,
    
    // Modificadores
    props.fullWidth ? 'w-full' : '',
    props.rounded ? 'rounded-full' : 'rounded-md',
  ]
  
  return classes.filter(Boolean).join(' ')
})

// Clases de tamaño
const sizeClasses = computed(() => {
  const sizes = {
    xs: 'px-2.5 py-1.5 text-xs',
    sm: 'px-3 py-2 text-sm',
    md: 'px-4 py-2 text-sm',
    lg: 'px-4 py-2 text-base',
    xl: 'px-6 py-3 text-base'
  }
  
  return sizes[props.size] || sizes.md
})

// Clases de variante
const variantClasses = computed(() => {
  const variants = {
    primary: 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
    secondary: 'bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500',
    success: 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
    warning: 'bg-yellow-600 text-white hover:bg-yellow-700 focus:ring-yellow-500',
    error: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
    ghost: 'bg-transparent text-gray-700 hover:bg-gray-100 focus:ring-gray-500',
    outline: 'bg-transparent border border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-gray-500'
  }
  
  return variants[props.variant] || variants.primary
})

// Tamaño del spinner
const spinnerSize = computed(() => {
  const sizes = {
    xs: 'xs',
    sm: 'sm', 
    md: 'sm',
    lg: 'md',
    xl: 'md'
  }
  
  return sizes[props.size] || 'sm'
})

// Tamaño del icono
const iconSize = computed(() => {
  const sizes = {
    xs: 'xs',
    sm: 'sm',
    md: 'sm', 
    lg: 'md',
    xl: 'md'
  }
  
  return sizes[props.size] || 'sm'
})

// Manejar click
const handleClick = (event) => {
  if (!props.disabled && !props.loading) {
    emit('click', event)
  }
}
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
