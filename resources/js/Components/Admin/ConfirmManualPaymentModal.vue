<template>
  <!-- Modal para confirmar pago manual -->
  <TransitionRoot as="template" :show="showModal">
    <Dialog as="div" class="relative z-10" @close="closeModal">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div
          class="flex items-end justify-center min-h-full p-4 text-center sm:items-center sm:p-0"
        >
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel
              class="relative px-4 pt-5 pb-4 overflow-hidden text-left transition-all transform bg-white rounded-lg shadow-xl dark:bg-gray-700 sm:my-8 sm:w-full sm:max-w-sm sm:p-6"
            >
              <div>
                <div
                  class="flex items-center justify-center w-12 h-12 mx-auto bg-green-100 rounded-full dark:bg-green-700"
                >
                  <!-- Icono de pago (ej: cheque, tarjeta, etc.) -->
                  <svg
                    class="w-6 h-6 text-green-600 dark:text-green-300"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    aria-hidden="true"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                </div>
                <div class="mt-3 text-center sm:mt-5">
                  <DialogTitle
                    as="h3"
                    class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100"
                  >
                    Confirmar Pago Manual
                  </DialogTitle>
                  <div class="mt-2">
                    <!-- Formulario para ingresar detalles del pago -->
                    <div class="space-y-4">
                      <div>
                        <label
                          for="amount"
                          class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                          Monto
                        </label>
                        <input
                          type="number"
                          name="amount"
                          id="amount"
                          class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-50 dark:placeholder-gray-400"
                          v-model="form.amount"
                          required
                        />
                      </div>

                      <div>
                        <label
                          for="transaction_date"
                          class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                          Fecha de Transacción
                        </label>
                        <input
                          type="date"
                          name="transaction_date"
                          id="transaction_date"
                          class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-50 dark:placeholder-gray-400"
                          v-model="form.transaction_date"
                          required
                        />
                      </div>

                      <div>
                        <label
                          for="payment_method_id"
                          class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                          Método de Pago
                        </label>
                        <select
                          id="payment_method_id"
                          name="payment_method_id"
                          class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-50 dark:placeholder-gray-400"
                          v-model="form.payment_method_id"
                          required
                        >
                          <option
                            v-for="method in paymentMethods"
                            :key="method.value"
                            :value="method.value"
                          >
                            {{ method.label }}
                          </option>
                        </select>
                      </div>

                      <div>
                        <label
                          for="reference_number"
                          class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                          Número de Referencia
                        </label>
                        <input
                          type="text"
                          name="reference_number"
                          id="reference_number"
                          class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-50 dark:placeholder-gray-400"
                          v-model="form.reference_number"
                        />
                      </div>

                      <div>
                        <label
                          for="notes"
                          class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                          Notas
                        </label>
                        <textarea
                          id="notes"
                          name="notes"
                          rows="3"
                          class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-50 dark:placeholder-gray-400"
                          v-model="form.notes"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="mt-5 sm:mt-6">
                <button
                  type="button"
                  class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:text-sm"
                  @click="confirmPayment"
                  :disabled="form.processing"
                >
                  <span v-if="form.processing">Procesando...</span>
                  <span v-else>Confirmar Pago</span>
                </button>
                <button
                  type="button"
                  class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600"
                  @click="closeModal"
                >
                  Cancelar
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, defineEmits } from "vue";
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import { useForm } from "@inertiajs/vue3";
import { route } from "@/Utils"; // Asegúrate de que el helper 'route' esté disponible

const props = defineProps({
  invoiceId: {
    type: Number,
    required: true,
  },
  paymentMethods: {
    type: Array,
    required: true,
  },
  showModal: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits(["close", "paymentConfirmed"]);

const form = useForm({
  invoice_id: props.invoiceId,
  amount: null,
  transaction_date: null,
  payment_method_id: null,
  reference_number: null,
  notes: null,
});

const closeModal = () => {
  emit("close");
  form.reset(); // Reset the form when closing
};

const confirmPayment = () => {
  form.post(route("admin.transactions.confirmManualPayment"), {
    onSuccess: (response) => {
      // Optionally, refresh data or show a success message
      closeModal();
      emit("paymentConfirmed");
    },
    onError: (errors) => {
      // Handle validation errors from the backend
      console.error("Payment Confirmation Errors:", errors);
      // You might want to display these errors to the user
    },
    onFinish: () => {
      // Optional: Perform actions when the request is finished (success or error)
      // form.reset(); // Reset the form if needed
    },
  });
};
</script>
