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
              <label for="newPassword" class="block text-sm font-medium text-gray-700">Nueva Contraseña</label>
              <input type="password" id="newPassword" v-model="newPassword" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
            </div>
            <div class="mb-3">
              <label for="confirmPassword" class="block text-sm font-medium text-gray-700">Confirmar Nueva Contraseña</label>
              <input type="password" id="confirmPassword" v-model="confirmPassword" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
            </div>

            <!-- Password Requirements -->
            <div class="mb-4 text-xs text-gray-600">
              <p class="font-semibold mb-1">Requisitos de la contraseña:</p>
              <ul class="list-disc list-inside ml-2 space-y-0.5">
                <li v-for="req in Object.values(passwordRequirements)" :key="req.text" :class="{'text-green-500': req.met && newPassword.length > 0, 'text-red-500': !req.met && newPassword.length > 0, 'text-gray-500': newPassword.length === 0}">
                  {{ req.text }}
                  <span v-if="req.met && newPassword.length > 0"> ✓</span>
                  <span v-else-if="!req.met && newPassword.length > 0"> ✗</span>
                </li>
              </ul>
               <p v-if="newPassword && confirmPassword && newPassword !== confirmPassword" class="text-red-500 mt-1">Las contraseñas no coinciden. ✗</p>
               <p v-else-if="newPassword && confirmPassword && newPassword === confirmPassword && newPassword.length > 0" class="text-green-500 mt-1">Las contraseñas coinciden. ✓</p>
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
                :disabled="!isPasswordFormValid"
              >
                Guardar Contraseña
              </button>
            </div>
             <p v-if="passwordChangeMessage" :class="passwordChangeError ? 'text-red-500' : 'text-green-500'" class="mt-3 text-sm">{{ passwordChangeMessage }}</p>
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
import { ref, watch } from 'vue';
import { Inertia } from '@inertiajs/inertia'; // Corrected import for Inertia

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
const newPassword = ref('');
const confirmPassword = ref('');
const passwordChangeMessage = ref('');
const passwordChangeError = ref(false);

// Password requirements state
const passwordRequirements = ref({
    length: { text: 'Más de 12 caracteres', met: false, regex: /.{13,}/ },
    uppercase: { text: 'Mayúscula', met: false, regex: /[A-Z]/ },
    lowercase: { text: 'Minúscula', met: false, regex: /[a-z]/ },
    number: { text: 'Número', met: false, regex: /[0-9]/ },
    symbol: { text: 'Símbolo', met: false, regex: /[\W_]/ }, // \W is non-word, _ is often included
});

const isPasswordFormValid = computed(() => {
    if (!newPassword.value || !confirmPassword.value) return false;
    if (newPassword.value !== confirmPassword.value) return false;
    return Object.values(passwordRequirements.value).every(req => req.met);
});

watch(newPassword, (value) => {
    for (const key in passwordRequirements.value) {
        passwordRequirements.value[key].met = passwordRequirements.value[key].regex.test(value);
    }
    // Trigger reactivity for confirmPassword validation message if needed
    if (confirmPassword.value) {
      confirmPassword.value = confirmPassword.value + ''; // or any other way to explicitly trigger watcher
    }
});

watch(confirmPassword, (value) => {
    // This watcher is mainly for the password match message, actual validation is in isPasswordFormValid
});

const toggleChangePassword = () => {
  isChangingPassword.value = !isChangingPassword.value;
  resetPasswordForm();
};

const cancelChangePassword = ()_=> {
  isChangingPassword.value = false;
  resetPasswordForm();
};

const resetPasswordForm = () => {
  newPassword.value = '';
  confirmPassword.value = '';
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
  passwordChangeMessage.value = '';
  passwordChangeError.value = false;

  if (!isPasswordFormValid.value) {
    passwordChangeMessage.value = 'Por favor, cumple todos los requisitos y asegúrate de que las contraseñas coincidan.';
    passwordChangeError.value = true;
    return;
  }

  if (!props.service || !props.service.id) {
    passwordChangeMessage.value = 'Error: ID de servicio no encontrado.';
    passwordChangeError.value = true;
    return;
  }

  Inertia.post(route('client.services.updatePassword', { service: props.service.id }), {
    new_password: newPassword.value,
    new_password_confirmation: confirmPassword.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      passwordChangeMessage.value = 'Contraseña actualizada con éxito.';
      passwordChangeError.value = false;
      isChangingPassword.value = false;
      resetPasswordForm();
      // Optionally, emit an event or refresh service data if password shown/used directly
    },
    onError: (errors) => {
      if (errors.new_password) {
        passwordChangeMessage.value = errors.new_password;
      } else if (errors.message) {
         passwordChangeMessage.value = errors.message;
      } else {
        passwordChangeMessage.value = 'Error al actualizar la contraseña. Inténtalo de nuevo.';
      }
      passwordChangeError.value = true;
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
