<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @click.self="$emit('close')">
    <div class="bg-white p-6 rounded-lg shadow-xl max-w-md w-full mx-4">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Detalles del Servicio</h3>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
      </div>
      <div v-if="service">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Producto:</label>
          <p class="mt-1 text-sm text-gray-900">{{ service.product?.name || 'N/A' }}</p>
        </div>
        <div class="mb-4" v-if="service.domain_name">
          <label class="block text-sm font-medium text-gray-700">Dominio:</label>
          <p class="mt-1 text-sm text-gray-900">{{ service.domain_name }}</p>
        </div>
        <div class="mb-4" v-if="service.service_username || (service.config && service.config.username)">
          <label class="block text-sm font-medium text-gray-700">Nombre de Usuario:</label>
          <p class="mt-1 text-sm text-gray-900">{{ service.service_username || service.config?.username || 'N/A' }}</p>
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Contraseña:</label>
          <p v-if="service.service_password || (service.config && service.config.password)" class="mt-1 text-sm text-gray-900 italic">******** (No se muestra por seguridad)</p>
          <p v-else class="mt-1 text-sm text-gray-500 italic">No disponible o no establecida.</p>
        </div>

        <!-- Change Password Section -->
        <div class="mt-4">
          <button
            v-if="!isChangingPassword"
            @click="toggleChangePassword"
            class="w-full px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
          >
            Cambiar Contraseña
          </button>

          <div v-if="isChangingPassword" class="mt-4 p-4 border border-gray-200 rounded-md">
            <h4 class="text-md font-semibold mb-3">Establecer Nueva Contraseña</h4>
            <div class="mb-3">
                <label for="currentPassword" class="block text-sm font-medium text-gray-700">Contraseña Actual</label>
                <input type="password" id="currentPassword" v-model="form.currentPassword" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" :class="{'border-red-500': form.errors.currentPassword}" />
                <p v-if="form.errors.currentPassword" class="mt-1 text-xs text-red-500">{{ form.errors.currentPassword }}</p>
            </div>
            <div class="mb-3">
              <label for="new_password" class="block text-sm font-medium text-gray-700">Nueva Contraseña</label>
              <input type="password" id="new_password" v-model="form.new_password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" :class="{'border-red-500': form.errors.new_password}" />
               <p v-if="form.errors.new_password && form.errors.new_password !== passwordChangeMessage" class="mt-1 text-xs text-red-500">{{ form.errors.new_password }}</p>
            </div>
            <div class="mb-3">
              <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Nueva Contraseña</label>
              <input type="password" id="new_password_confirmation" v-model="form.new_password_confirmation" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" :class="{'border-red-500': form.errors.new_password_confirmation}" />
               <p v-if="form.errors.new_password_confirmation" class="mt-1 text-xs text-red-500">{{ form.errors.new_password_confirmation }}</p>
            </div>

            <!-- Password Requirements -->
            <div class="mb-4 text-xs text-gray-600">
              <p class="font-semibold mb-1">Requisitos de la contraseña nueva:</p>
              <ul class="list-disc list-inside ml-2 space-y-0.5">
                <li v-for="req in Object.values(passwordRequirements)" :key="req.text" :class="{'text-green-500': req.met && form.new_password.length > 0, 'text-red-500': !req.met && form.new_password.length > 0, 'text-gray-500': form.new_password.length === 0}">
                  {{ req.text }}
                  <span v-if="req.met && form.new_password.length > 0"> ✓</span>
                  <span v-else-if="!req.met && form.new_password.length > 0"> ✗</span>
                </li>
              </ul>
               <p v-if="form.new_password && form.new_password_confirmation && form.new_password !== form.new_password_confirmation" class="text-red-500 mt-1">Las contraseñas nuevas no coinciden. ✗</p>
               <p v-else-if="form.new_password && form.new_password_confirmation && form.new_password === form.new_password_confirmation && form.new_password.length > 0" class="text-green-500 mt-1">Las contraseñas nuevas coinciden. ✓</p>
            </div>

            <div class="flex justify-end space-x-2">
              <button
                @click="cancelChangePassword"
                class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300"
              >
                Cancelar
              </button>
              <button
                @click="submitNewPassword"
                class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 disabled:opacity-50"
                :disabled="isSaveDisabled"
              >
                Guardar Contraseña
              </button>
            </div>
            <!-- Display general (non-field) errors or success messages not handled by session flash -->
             <p v-if="passwordChangeMessage && (!form.hasErrors || (form.errors.current_password === passwordChangeMessage || form.errors.new_password === passwordChangeMessage) )"
                :class="passwordChangeError ? 'text-red-500' : 'text-green-500'"
                class="mt-3 text-sm">
                {{ passwordChangeMessage }}
             </p>
             <!-- Fallback for other errors if not caught by specific field messages -->
             <p v-if="form.hasErrors && !form.errors.currentPassword && !form.errors.new_password && !form.errors.new_password_confirmation && !passwordChangeMessage" class="mt-3 text-sm text-red-500">
                Se encontraron errores de validación. Por favor, revisa los campos.
             </p>
          </div>
        </div>
      </div>
      <div v-else>
        <p class="text-gray-700">No hay detalles de servicio para mostrar.</p>
      </div>
      <div class="mt-6 text-right">
        <button @click="handleCloseModal" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">Cerrar</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3'; // Import useForm

const props = defineProps({
  show: {
    type: Boolean,
    required: true,
  },
  service: {
    type: Object,
    default: () => null,
  },
});

const emit = defineEmits(['close']);

const isChangingPassword = ref(false);
const passwordChangeMessage = ref(''); // For general messages not tied to form errors
const passwordChangeError = ref(false); // For general messages not tied to form errors

const form = useForm({
    currentPassword: '',
    new_password: '',
    new_password_confirmation: '', // Field name must match for 'confirmed' rule in Laravel
});

// Password requirements state
const passwordRequirements = ref({
    length: { text: 'Más de 12 caracteres', met: false, regex: /.{13,}/ },
    uppercase: { text: 'Mayúscula', met: false, regex: /[A-Z]/ },
    lowercase: { text: 'Minúscula', met: false, regex: /[a-z]/ },
    number: { text: 'Número', met: false, regex: /[0-9]/ },
    symbol: { text: 'Símbolo', met: false, regex: /[\W_]/ },
});

const isSaveDisabled = computed(() => { // Renamed from isPasswordFormValid for clarity with useForm
    if (!form.currentPassword || !form.new_password || !form.new_password_confirmation) return true;
    if (form.new_password !== form.new_password_confirmation) return true;
    if (form.processing) return true;
    return !Object.values(passwordRequirements.value).every(req => req.met);
});

watch(() => form.new_password, (value) => {
    for (const key in passwordRequirements.value) {
        passwordRequirements.value[key].met = passwordRequirements.value[key].regex.test(value);
    }
});

// Watch for form errors from backend to display them, or use form.hasErrors and form.errors object directly in template
// This watcher can be simplified if template directly uses form.errors.
// General non-field error messages can be handled by onError callback of form.post/put
watch(() => form.errors, (newErrors) => {
    // Check if there are general, non-field-specific errors or if specific field errors should populate passwordChangeMessage
    // For now, this will be simplified as the template will show field-specific errors.
    // General error messages are better handled in form.post's onError.
    if (form.wasSuccessful) {
        passwordChangeMessage.value = ''; // Clear message on new success
        passwordChangeError.value = false;
    }
}, { deep: true });

const toggleChangePassword = () => {
  isChangingPassword.value = !isChangingPassword.value;
  resetPasswordForm();
};

const cancelChangePassword = () => {
  isChangingPassword.value = false;
  resetPasswordForm();
};

const resetPasswordForm = () => {
    form.reset(); // Resets form fields to initial values
    form.clearErrors(); // Clears validation errors
    passwordChangeMessage.value = '';
    passwordChangeError.value = false;
    for (const key in passwordRequirements.value) {
        passwordRequirements.value[key].met = false;
    }
};

const handleCloseModal = () => {
  if (isChangingPassword.value) {
    cancelChangePassword();
  }
  emit('close');
};

const submitNewPassword = async () => {
  // Clear previous non-field specific messages
  passwordChangeMessage.value = '';
  passwordChangeError.value = false;

  if (!props.service || !props.service.id) {
      passwordChangeMessage.value = 'Error: ID de servicio no encontrado.';
      passwordChangeError.value = true;
      return;
  }

  // Now form.data is implicitly sent, which includes currentPassword, new_password, new_password_confirmation
  form.post(route('client.services.updatePassword', { service: props.service.id }), {
      preserveScroll: true,
      onSuccess: () => {
          // Success messages are typically handled by session flash & component displaying it (e.g. AuthenticatedLayout)
          // passwordChangeMessage.value = 'Contraseña actualizada con éxito.'; // Can be removed if relying on session flash
          // passwordChangeError.value = false;
          isChangingPassword.value = false; // Hide form
          resetPasswordForm(); // Reset form fields and errors
      },
      onError: (pageErrors) => {
          // Errors are automatically handled and available in form.errors.
          // This block can be used for additional side effects or general non-field error messages.
          if (pageErrors && Object.keys(pageErrors).length === 0) { // Check if pageErrors is empty but error occurred
               passwordChangeMessage.value = 'Error al actualizar la contraseña. Inténtalo de nuevo.';
               passwordChangeError.value = true;
          } else if (pageErrors && pageErrors.message) { // Example if backend sends a general 'message' in error response that's not a validation error
              passwordChangeMessage.value = pageErrors.message;
              passwordChangeError.value = true;
          }
          // Field-specific errors are in form.errors and should be displayed near the fields.
      }
  });
};

// Watch for modal visibility to reset form state
watch(() => props.show, (newValue) => {
  if (!newValue) {
    cancelChangePassword(); // Reset form when modal is closed
  }
});

</script>

<style scoped>
/* Basic modal styling, can be enhanced */
</style>
