<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click="handleBackdropClick"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" />
        
        <!-- Modal container -->
        <div class="flex min-h-full items-center justify-center p-4">
          <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <div
              v-if="show"
              :class="modalClasses"
              @click.stop
            >
              <!-- Header -->
              <div v-if="hasHeader" :class="headerClasses">
                <slot name="header">
                  <div class="flex items-center justify-between">
                    <h3 v-if="title" :class="titleClasses">{{ title }}</h3>
                    <button
                      v-if="closable"
                      type="button"
                      class="text-gray-400 hover:text-gray-600 transition-colors"
                      @click="close"
                    >
                      <Icon name="x" size="lg" />
                    </button>
                  </div>
                </slot>
              </div>

              <!-- Content -->
              <div :class="contentClasses">
                <slot />
              </div>

              <!-- Footer -->
              <div v-if="hasFooter" :class="footerClasses">
                <slot name="footer">
                  <div class="flex justify-end space-x-3">
                    <BaseButton
                      v-if="showCancelButton"
                      variant="outline"
                      @click="cancel"
                    >
                      {{ cancelText }}
                    </BaseButton>
                    <BaseButton
                      v-if="showConfirmButton"
                      :variant="confirmVariant"
                      :loading="loading"
                      @click="confirm"
                    >
                      {{ confirmText }}
                    </BaseButton>
                  </div>
                </slot>
              </div>
            </div>
          </Transition>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, useSlots, watch, nextTick } from 'vue'
import Icon from '@/Components/UI/Icon.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'

const props = defineProps({
  // Control de visibilidad
  show: Boolean,
  
  // Contenido
  title: String,
  
  // Tamaños
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl', 'full'].includes(value)
  },
  
  // Comportamiento
  closable: {
    type: Boolean,
    default: true
  },
  closeOnBackdrop: {
    type: Boolean,
    default: true
  },
  closeOnEscape: {
    type: Boolean,
    default: true
  },
  
  // Botones del footer
  showCancelButton: Boolean,
  showConfirmButton: Boolean,
  cancelText: {
    type: String,
    default: 'Cancelar'
  },
  confirmText: {
    type: String,
    default: 'Confirmar'
  },
  confirmVariant: {
    type: String,
    default: 'primary'
  },
  
  // Estados
  loading: Boolean,
})

const emit = defineEmits(['close', 'cancel', 'confirm'])

const slots = useSlots()

// Verificar si hay slots
const hasHeader = computed(() => !!slots.header || !!props.title)
const hasFooter = computed(() => !!slots.footer || props.showCancelButton || props.showConfirmButton)

// Clases del modal
const modalClasses = computed(() => {
  const baseClasses = [
    'relative bg-white rounded-lg shadow-xl transform transition-all',
    'w-full max-h-[90vh] overflow-hidden flex flex-col'
  ]
  
  // Tamaños
  const sizeClasses = {
    xs: 'max-w-xs',
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    full: 'max-w-full mx-4'
  }
  
  return [
    ...baseClasses,
    sizeClasses[props.size] || sizeClasses.md
  ].join(' ')
})

// Clases del header
const headerClasses = computed(() => {
  return 'px-6 py-4 border-b border-gray-200 flex-shrink-0'
})

// Clases del título
const titleClasses = computed(() => {
  return 'text-lg font-semibold text-gray-900'
})

// Clases del contenido
const contentClasses = computed(() => {
  return 'px-6 py-4 flex-1 overflow-y-auto'
})

// Clases del footer
const footerClasses = computed(() => {
  return 'px-6 py-4 border-t border-gray-200 flex-shrink-0'
})

// Manejadores de eventos
const close = () => {
  emit('close')
}

const cancel = () => {
  emit('cancel')
  close()
}

const confirm = () => {
  emit('confirm')
}

const handleBackdropClick = () => {
  if (props.closeOnBackdrop) {
    close()
  }
}

const handleEscapeKey = (event) => {
  if (event.key === 'Escape' && props.closeOnEscape && props.show) {
    close()
  }
}

// Manejar tecla Escape
watch(() => props.show, (newValue) => {
  if (newValue) {
    nextTick(() => {
      document.addEventListener('keydown', handleEscapeKey)
    })
  } else {
    document.removeEventListener('keydown', handleEscapeKey)
  }
})

// Limpiar event listeners al desmontar
import { onUnmounted } from 'vue'
onUnmounted(() => {
  document.removeEventListener('keydown', handleEscapeKey)
})
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
