<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-purple-50">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-4xl mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">Fj Group CA</h1>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <div
                                class="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-sm">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="text-sm text-gray-600">Dominio</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div
                                class="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-sm">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="text-sm text-gray-600">Registro</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div
                                class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm">
                                3</div>
                            <span class="text-sm font-medium text-blue-600">Pago</span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-4xl mx-auto px-6 py-12">
            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Payment Form -->
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Finalizar Compra</h2>

                    <!-- Order Summary -->
                    <div class="bg-gray-50 rounded-lg p-6 mb-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Resumen del Pedido</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span>{{ product.name }}</span>
                                <span>${{ subtotal.toFixed(2) }}</span>
                            </div>
                            <div v-if="domainPrice > 0" class="flex justify-between">
                                <span>Dominio: {{ purchaseContext.domain }}</span>
                                <span>${{ domainPrice.toFixed(2) }}</span>
                            </div>
                            <div class="border-t pt-2 flex justify-between font-semibold">
                                <span>Total</span>
                                <span>${{ total.toFixed(2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="space-y-4">
                        <h3 class="font-semibold text-gray-900">Método de Pago</h3>

                        <div class="space-y-3">
                            <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="radio" name="payment_method" value="paypal" v-model="selectedPaymentMethod"
                                    class="mr-3">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-600 rounded flex items-center justify-center mr-3">
                                        <span class="text-white text-xs font-bold">PP</span>
                                    </div>
                                    <span class="font-medium">PayPal</span>
                                </div>
                            </label>

                            <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="radio" name="payment_method" value="other_methods" v-model="selectedPaymentMethod"
                                    class="mr-3">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gray-600 rounded flex items-center justify-center mr-3">
                                        <span class="text-white text-xs font-bold">💰</span>
                                    </div>
                                    <span class="font-medium">Otros Medios</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-between items-center pt-6 mt-6 border-t">
                        <Link :href="route('public.checkout.register')"
                            class="text-gray-600 hover:text-gray-800 font-medium">
                        ← Volver
                        </Link>

                        <button @click="processPayment" :disabled="!selectedPaymentMethod || processing"
                            class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <span v-if="processing">Procesando...</span>
                            <span v-else>Pagar ${{ total.toFixed(2) }}</span>
                        </button>
                    </div>
                </div>

                <!-- Order Details -->
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Detalles del Servicio</h3>

                    <div class="space-y-4">
                        <div>
                            <h4 class="font-semibold text-gray-900">Plan Seleccionado</h4>
                            <p class="text-gray-600">{{ product.name }}</p>
                        </div>

                        <div>
                            <h4 class="font-semibold text-gray-900">Dominio</h4>
                            <p class="text-gray-600">{{ purchaseContext.domain }}</p>
                            <p class="text-sm text-gray-500">
                                {{ purchaseContext.domain_action === 'register' ? 'Nuevo registro' : 'Dominio existente'
                                }}
                            </p>
                        </div>

                        <div>
                            <h4 class="font-semibold text-gray-900">Caso de Uso</h4>
                            <p class="text-gray-600">{{ getUseCaseLabel(purchaseContext.use_case) }}</p>
                        </div>
                    </div>

                    <!-- Support Info -->
                    <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                        <h4 class="font-semibold text-blue-900 mb-2">¿Necesitas ayuda?</h4>
                        <p class="text-blue-700 text-sm mb-2">Nuestro equipo está aquí para ayudarte</p>
                        <div class="space-y-1 text-sm text-blue-600">
                            <p>📧 soporte@fjgroupca.com</p>
                            <p>📱 +58 412 8172337</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
    purchaseContext: Object,
    product: Object,
    pricing: Object,
    subtotal: Number,
    domainPrice: Number,
    total: Number,
    useCaseMessages: Object,
})

const selectedPaymentMethod = ref('')
const processing = ref(false)

const getUseCaseLabel = (useCase) => {
    const labels = {
        'educators': 'Para Educadores',
        'entrepreneurs': 'Para Emprendedores',
        'professionals': 'Para Profesionales',
        'small-business': 'Para Pequeños Negocios'
    }
    return labels[useCase] || useCase
}

const form = useForm({
    payment_method: ''
})

const processPayment = () => {
    if (!selectedPaymentMethod.value) return

    processing.value = true
    form.payment_method = selectedPaymentMethod.value

    form.post(route('public.checkout.payment.process'), {
        onSuccess: () => {
            processing.value = false
        },
        onError: () => {
            processing.value = false
        }
    })
}
</script>
