<script setup lang="ts">
import Icon from '../Icon.vue';

// Definiciones de tipos (reutilizadas de otros componentes o definidas localmente)
interface ContactInfo {
  phone: string;
  email: string;
  message: string;
  footerDescription?: string; // No se usa en este componente
}

interface ContactModalProps {
  isOpen: boolean;
  // onClose ya no es una prop en Vue, será un evento emitido
  contactInfo: ContactInfo;
  contactedPlan?: string;
}

// Definición de Props
const props = defineProps<ContactModalProps>();

// Definición de Emits
const emit = defineEmits<{
  (e: 'close'): void;
}>();

// Lógica para manejar el evento de cierre
const handleClose = () => {
  emit('close');
};

</script>

<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-75 backdrop-blur-sm flex items-center justify-center z-[60] p-4 animate-fade-in" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="w-full max-w-md p-6 rounded-lg shadow-2xl bg-slate-100 sm:p-8 text-slate-800 animate-zoom-in">
      <div class="flex items-center justify-between mb-6">
        <h2 id="modal-title" class="text-2xl font-bold text-slate-800">Información de Contacto</h2>
        <button @click="handleClose" type="button" class="text-slate-400 hover:text-slate-600" aria-label="Cerrar modal">
          <Icon name="x-mark" class="w-7 h-7" />
        </button>
      </div>
      <div class="space-y-4 text-slate-700 font-inter">
        <p v-if="contactedPlan" class="font-semibold text-md text-brand-blue">Consultando por el plan: {{ contactedPlan }}</p>
        <p class="text-md">{{ contactInfo.message }}</p>
        <div class="space-y-3">
          <a :href="`tel:${contactInfo.phone.replace(/\s/g, '')}`" class="flex items-center group">
            <Icon name="phone" class="w-5 h-5 mr-3 text-brand-blue"/>
            <span class="transition-colors group-hover:text-brand-blue">{{ contactInfo.phone }}</span>
          </a>
          <a :href="`mailto:${contactInfo.email}`" class="flex items-center group">
             <Icon name="envelope" class="w-5 h-5 mr-3 text-brand-blue"/>
             <span class="transition-colors group-hover:text-brand-blue">{{ contactInfo.email }}</span>
          </a>
        </div>
      </div>
      <button
        @click="handleClose"
        type="button"
        class="mt-8 w-full bg-brand-blue hover:bg-brand-blue-dark text-white font-semibold py-2.5 px-4 rounded-md transition-colors"
      >
        Cerrar
      </button>
    </div>
  </div>
</template>

<style scoped>
/* Estilos específicos aquí si son necesarios */
</style>
