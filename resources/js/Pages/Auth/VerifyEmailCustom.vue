<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-purple-50 flex items-center justify-center px-4">
        <Head title="Verificar Email - Fj Group CA" />
        
        <div class="max-w-md w-full">
            <!-- Logo -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Fj Group CA</h1>
                <p class="text-gray-600 mt-2">Tu aliado comercial</p>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <!-- Icon -->
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Verifica tu Email</h2>
                    <p class="text-gray-600 mt-2">Para acceder a tu panel de cliente</p>
                </div>

                <!-- Message -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">
                                Verificación Requerida
                            </h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>Hemos enviado un enlace de verificación a tu email.</p>
                                <p class="text-xs text-blue-600 mt-1">Revisa tu bandeja de entrada y spam.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Success Message -->
                <div v-if="verificationLinkSent" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                                ¡Email enviado exitosamente!
                            </p>
                            <p class="text-sm text-green-700 mt-1">
                                Revisa tu bandeja de entrada y spam.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <form @submit.prevent="submit" class="space-y-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <span v-if="form.processing">Enviando...</span>
                        <span v-else>Reenviar Email de Verificación</span>
                    </button>
                </form>

                <!-- Alternative Actions -->
                <div class="border-t pt-4 mt-6">
                    <div class="flex justify-between items-center text-sm">
                        <Link :href="route('sales.home')" class="text-gray-600 hover:text-gray-800">
                            ← Volver al inicio
                        </Link>
                        
                        <Link :href="route('logout')" method="post" as="button" 
                              class="text-gray-600 hover:text-gray-800">
                            Cerrar sesión
                        </Link>
                    </div>
                </div>

                <!-- Help -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <h4 class="font-semibold text-gray-900 mb-2">¿Necesitas ayuda?</h4>
                    <div class="text-sm text-gray-600 space-y-1">
                        <p>📧 soporte@fjgroupca.com</p>
                        <p>📱 WhatsApp: +58 412 8172337</p>
                        <p class="text-xs text-gray-500 mt-2">
                            <strong>Para desarrollo:</strong> Puedes verificar manualmente desde la base de datos actualizando el campo <code>email_verified_at</code>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>
