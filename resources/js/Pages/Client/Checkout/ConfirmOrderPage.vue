<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CartSummary from '@/Components/Client/CartSummary.vue'; // Asegúrate que la ruta sea correcta

const props = defineProps({
    initialCart: Object,
});

const form = useForm({
    notes_to_client: '',
});

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
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Paso 3: Confirmar Pedido
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="mb-6 text-2xl font-semibold text-gray-900 dark:text-gray-100">Revisa tu Pedido</h3>

                        <!-- CartSummary: Idealmente, modificar para aceptar initialCart como prop -->
                        <!-- <CartSummary :initial-cart-data="props.initialCart" /> -->
                        <CartSummary />
                        <!-- Si CartSummary no acepta props, se basará en su propia carga o evento 'cart-updated' -->
                    </div>

                    <form @submit.prevent="submitOrder">
                        <div class="p-6 space-y-6">
                            <div>
                                <label for="notes_to_client"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Notas Adicionales (Opcional)
                                </label>
                                <textarea v-model="form.notes_to_client" id="notes_to_client" rows="3"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                <p v-if="form.errors.notes_to_client" class="mt-1 text-sm text-red-500">{{
                                    form.errors.notes_to_client }}</p>
                            </div>

                            <!-- Información sobre el proceso de pago -->
                            <div
                                class="p-4 bg-blue-50 border border-blue-200 rounded-md dark:bg-blue-900/20 dark:border-blue-800">
                                <div class="flex items-center">
                                    <span class="text-blue-600 dark:text-blue-400 mr-2">ℹ️</span>
                                    <div>
                                        <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200">Proceso de Pago
                                        </h4>
                                        <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                                            Después de confirmar tu pedido, se generará una factura con las opciones de
                                            pago
                                            disponibles
                                            (PayPal, transferencia bancaria, etc.).
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-between px-6 py-4 bg-gray-50 dark:bg-gray-750 sm:rounded-b-lg">
                            <button type="button" @click="$inertia.visit(route('client.checkout.selectServices'))"
                                :disabled="form.processing"
                                class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                                ← Regresar a Servicios
                            </button>
                            <button type="submit" :disabled="form.processing"
                                class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50">
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
