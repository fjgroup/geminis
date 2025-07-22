<template>
  <div :class="cardClasses">
    <!-- Header -->
    <div v-if="hasHeader" :class="headerClasses">
      <slot name="header">
        <div class="flex items-center justify-between">
          <h3 v-if="title" :class="titleClasses">{{ title }}</h3>
          <div v-if="hasHeaderActions" class="flex items-center space-x-2">
            <slot name="header-actions" />
          </div>
        </div>
      </slot>
    </div>

    <!-- Content -->
    <div :class="contentClasses">
      <slot />
    </div>

    <!-- Footer -->
    <div v-if="hasFooter" :class="footerClasses">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
import { computed, useSlots } from 'vue'

const props = defineProps({
  // Variantes de estilo
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'bordered', 'elevated', 'flat'].includes(value)
  },
  
  // Tamaños
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  
  // Contenido
  title: String,
  
  // Modificadores
  hoverable: Boolean,
  clickable: Boolean,
  loading: Boolean,
})

const emit = defineEmits(['click'])

const slots = useSlots()

// Verificar si hay slots
const hasHeader = computed(() => !!slots.header || !!props.title || hasHeaderActions.value)
const hasHeaderActions = computed(() => !!slots['header-actions'])
const hasFooter = computed(() => !!slots.footer)

// Clases principales de la card
const cardClasses = computed(() => {
  const classes = [
    // Clases base
    'bg-white overflow-hidden transition-all duration-200',
    
    // Variante
    variantClasses.value,
    
    // Modificadores
    props.hoverable ? 'hover:shadow-lg transform hover:-translate-y-1' : '',
    props.clickable ? 'cursor-pointer' : '',
    props.loading ? 'opacity-50 pointer-events-none' : '',
  ]
  
  return classes.filter(Boolean).join(' ')
})

// Clases de variante
const variantClasses = computed(() => {
  const variants = {
    default: 'border border-gray-200 rounded-lg shadow-sm',
    bordered: 'border-2 border-gray-200 rounded-lg',
    elevated: 'rounded-lg shadow-md',
    flat: 'rounded-lg'
  }
  
  return variants[props.variant] || variants.default
})

// Clases del header
const headerClasses = computed(() => {
  const classes = [
    'px-6 py-4 border-b border-gray-200 bg-gray-50'
  ]
  
  return classes.join(' ')
})

// Clases del título
const titleClasses = computed(() => {
  const sizes = {
    sm: 'text-lg font-medium text-gray-900',
    md: 'text-xl font-semibold text-gray-900',
    lg: 'text-2xl font-semibold text-gray-900'
  }
  
  return sizes[props.size] || sizes.md
})

// Clases del contenido
const contentClasses = computed(() => {
  const sizes = {
    sm: 'p-4',
    md: 'p-6',
    lg: 'p-8'
  }
  
  return sizes[props.size] || sizes.md
})

// Clases del footer
const footerClasses = computed(() => {
  const classes = [
    'px-6 py-4 border-t border-gray-200 bg-gray-50'
  ]
  
  return classes.join(' ')
})

// Manejar click
const handleClick = (event) => {
  if (props.clickable && !props.loading) {
    emit('click', event)
  }
}
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
