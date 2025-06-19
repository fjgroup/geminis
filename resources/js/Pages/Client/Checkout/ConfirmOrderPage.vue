<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CartSummary from '@/Components/Client/CartSummary.vue'; // Asegúrate que la ruta sea correcta

const props = defineProps({
    initialCart: Object,
    // paymentMethods: Array, // Descomentar si se pasan métodos de pago
});

const form = useForm({
    notes_to_client: '',
    payment_method_slug: null, // O un valor por defecto si hay métodos de pago
});

// Si se pasan métodos de pago como prop:
// onMounted(() => {
//     if (props.paymentMethods && props.paymentMethods.length > 0) {
//         form.payment_method_slug = props.paymentMethods[0].slug; // Seleccionar el primero por defecto
//     }
// });

const submitOrder = () => {
    form.post(route('client.checkout.submit'), {
        onSuccess: () => {
            // La redirección a la factura ya la maneja el controlador.
            // Aquí podrías limpiar algún estado local si fuera necesario,
            // o mostrar un mensaje intermedio antes de la redirección de Inertia.
            // No es común necesitar hacer algo aquí si el backend redirige correctamente.
        },
        onError: (errors) => {
            console.error('Error al enviar el pedido:', errors);
            // Los errores se mostrarán automáticamente por Inertia si se configuran en el form helper.
            // O puedes manejarlos manualmente aquí con notificaciones.
            if (errors.message) { // Para errores generales devueltos por el backend
                alert(`Error: ${errors.message}`);
            }
        }
    });
};

// Para evitar que CartSummary haga su propia llamada fetch si ya tenemos los datos
// Se pasa initialCart a CartSummary. CartSummary necesitaría ser adaptado para aceptar esta prop.
// Por ahora, se asume que CartSummary se actualiza por evento 'cart-updated' o carga por sí mismo.
// Para una mejor UX, CartSummary debería poder tomar `initial-cart-data`.
// Si CartSummary NO toma props y SIEMPRE hace fetch, y queremos evitar doble carga:
// const shouldRenderCartSummary = ref(false);
// onMounted(() => { shouldRenderCartSummary.value = true; });
// Y en el template: <CartSummary v-if="shouldRenderCartSummary" />
// Pero es mejor modificar CartSummary para que acepte una prop.

</script>

<template>
    <Head title="Confirmar Pedido" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Paso 3: Confirmar Pedido</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-6">Revisa tu Pedido</h3>

                        <!-- CartSummary: Idealmente, modificar para aceptar initialCart como prop -->
                        <!-- <CartSummary :initial-cart-data="props.initialCart" /> -->
                        <CartSummary />
                        <!-- Si CartSummary no acepta props, se basará en su propia carga o evento 'cart-updated' -->
                    </div>

                    <form @submit.prevent="submitOrder">
                        <div class="p-6 space-y-6">
                            <div>
                                <label for="notes_to_client" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Notas Adicionales (Opcional)
                                </label>
                                <textarea v-model="form.notes_to_client" id="notes_to_client" rows="3"
                                          class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                <p v-if="form.errors.notes_to_client" class="mt-1 text-sm text-red-500">{{ form.errors.notes_to_client }}</p>
                            </div>

                            <!-- Sección de Métodos de Pago (Simplificado) -->
                            <div>
                                <label for="payment_method_slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Método de Pago (Provisional)
                                </label>
                                <select v-model="form.payment_method_slug" id="payment_method_slug"
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option :value="null">Seleccionar método de pago (opcional por ahora)</option>
                                    <option value="bank_transfer">Transferencia Bancaria</option>
                                    <option value="paypal">PayPal</option>
                                    <!-- <option v-for="method in props.paymentMethods" :key="method.slug" :value="method.slug">
                                        {{ method.name }}
                                    </option> -->
                                </select>
                                <p v-if="form.errors.payment_method_slug" class="mt-1 text-sm text-red-500">{{ form.errors.payment_method_slug }}</p>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-750 text-right sm:rounded-b-lg">
                            <button type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50">
                                <span v-if="form.processing">Procesando...</span>
                                <span v-else>Realizar Pedido y Pagar</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Estilos específicos para ConfirmOrderPage */
</style>
