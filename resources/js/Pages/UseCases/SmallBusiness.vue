<template>
    <div class="min-h-screen bg-white">
        <!-- Hero Section for Small Business -->
        <section class="relative bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-green-600/5 to-emerald-600/5"></div>

            <!-- Navigation -->
            <nav class="relative z-10 px-6 py-4">
                <div class="max-w-7xl mx-auto flex justify-between items-center">
                    <div class="flex items-center">
                        <img src="/images/logo.png" alt="Fj Group CA" class="h-8 w-auto mr-3">
                        <span class="text-2xl font-bold text-gray-900">Fj Group CA</span>
                    </div>
                    <div class="flex space-x-4">
                        <Link :href="route('sales.home')" class="text-gray-600 hover:text-gray-900 font-medium">
                        ← Volver al inicio
                        </Link>
                        <Link v-if="canLogin" :href="route('login')"
                            class="text-gray-600 hover:text-gray-900 font-medium">
                        Iniciar Sesión
                        </Link>
                    </div>
                </div>
            </nav>

            <!-- Hero Content -->
            <div class="relative z-10 max-w-7xl mx-auto px-6 py-20">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <div
                            class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-medium mb-6">
                            🏢 Para Pequeños Negocios
                        </div>

                        <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                            Tu Negocio Online Profesional
                        </h1>

                        <p class="text-xl md:text-2xl text-gray-600 mb-8">
                            Lleva tu pequeño negocio al mundo digital con una presencia web profesional.
                            <strong>Más clientes, más ventas, más crecimiento.</strong>
                        </p>

                        <!-- Key Benefits -->
                        <div class="space-y-4 mb-8">
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-lg text-gray-700">Sitio web profesional desde $15/mes</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-lg text-gray-700">Email profesional @tunegocio.com</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-lg text-gray-700">Tienda online para vender 24/7</span>
                            </div>
                        </div>

                        <button @click="startPurchase"
                            class="bg-green-600 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-green-700 transform hover:scale-105 transition-all duration-200 shadow-lg">
                            Crear Mi Sitio Web Ahora
                        </button>
                    </div>

                    <div class="relative">
                        <!-- Business website mockup -->
                        <div class="bg-gradient-to-br from-green-100 to-emerald-100 rounded-2xl p-8 shadow-2xl">
                            <div class="text-center">
                                <div
                                    class="w-24 h-24 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">Tu Negocio Online</h3>
                                <p class="text-gray-600">Presencia profesional que genera confianza y ventas</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Benefits Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        ¿Por Qué Tu Negocio Necesita Estar Online?
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        En 2024, el 87% de los clientes buscan negocios online antes de comprar
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Credibilidad Profesional</h3>
                        <p class="text-gray-600">Un sitio web profesional genera confianza y te diferencia de la
                            competencia</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Ventas 24/7</h3>
                        <p class="text-gray-600">Tu negocio trabaja mientras duermes. Recibe pedidos y consultas las 24
                            horas</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Más Clientes</h3>
                        <p class="text-gray-600">Aparece en Google cuando buscan tu tipo de negocio en tu ciudad</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V8l8 5 8-5v10zm0-12l-8 5L4 6h16z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Email Profesional</h3>
                        <p class="text-gray-600">contacto@tunegocio.com se ve más profesional que gmail.com</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Control Total</h3>
                        <p class="text-gray-600">No dependas de redes sociales. Tu sitio web es tuyo para siempre</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Fácil de Usar</h3>
                        <p class="text-gray-600">Actualiza tu contenido fácilmente sin conocimientos técnicos</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-teal-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">SSL Incluido</h3>
                        <p class="text-gray-600">Certificado de seguridad gratuito para proteger a tus clientes</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-pink-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Respaldos Automáticos</h3>
                        <p class="text-gray-600">Copias de seguridad diarias para proteger tu información</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Velocidad Optimizada</h3>
                        <p class="text-gray-600">Servidores de alta velocidad para cargas rápidas</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-green-600">
            <div class="max-w-4xl mx-auto px-6 text-center">
                <h2 class="text-4xl font-bold text-white mb-6">
                    ¿Listo para Hacer Crecer Tu Negocio?
                </h2>
                <p class="text-xl text-green-100 mb-8">
                    Únete a miles de pequeños negocios que ya están vendiendo online
                </p>
                <button @click="startPurchase"
                    class="bg-white text-green-600 px-8 py-4 rounded-xl text-lg font-semibold hover:bg-gray-100 transform hover:scale-105 transition-all duration-200 shadow-lg">
                    Comenzar Mi Sitio Web
                </button>
                <p class="text-green-200 mt-4">
                    ✅ Garantía de 45 días • ✅ Soporte en español • ✅ Sin conocimientos técnicos
                </p>
            </div>
        </section>

        <!-- Footer -->
        <Footer />

        <!-- WhatsApp Button -->
        <WhatsAppButton />
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import WhatsAppButton from '@/Components/WhatsAppButton.vue'
import Footer from '@/Components/Footer.vue'

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
})

const startPurchase = () => {
    // Create form to submit use case and plan
    const form = document.createElement('form')
    form.method = 'POST'
    form.action = route('sales.start-purchase')

    // Add CSRF token
    const csrfInput = document.createElement('input')
    csrfInput.type = 'hidden'
    csrfInput.name = '_token'
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    form.appendChild(csrfInput)

    // Add use case
    const useCaseInput = document.createElement('input')
    useCaseInput.type = 'hidden'
    useCaseInput.name = 'use_case'
    useCaseInput.value = 'small-business'
    form.appendChild(useCaseInput)

    // Add plan
    const planInput = document.createElement('input')
    planInput.type = 'hidden'
    planInput.name = 'plan'
    planInput.value = 'business'
    form.appendChild(planInput)

    document.body.appendChild(form)
    form.submit()
}
</script>
