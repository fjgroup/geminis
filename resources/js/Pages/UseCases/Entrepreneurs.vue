<template>
    <div class="min-h-screen bg-white">
        <!-- Hero Section for Entrepreneurs -->
        <section class="relative bg-gradient-to-br from-orange-50 via-amber-50 to-yellow-50 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-orange-600/5 to-amber-600/5"></div>

            <!-- Navigation -->
            <nav class="relative z-10 px-4 sm:px-6 py-4">
                <div class="max-w-7xl mx-auto flex justify-between items-center">
                    <div class="flex items-center">
                        <img src="/images/logo.png" alt="Fj Group CA" class="h-6 sm:h-8 w-auto mr-2 sm:mr-3">
                        <span class="text-lg sm:text-2xl font-bold text-gray-900">Fj Group CA</span>
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-1 sm:space-y-0 sm:space-x-4 text-right">
                        <Link :href="route('sales.home')"
                            class="text-gray-600 hover:text-gray-900 font-medium text-sm sm:text-base">
                        ← Volver al inicio
                        </Link>
                        <Link v-if="canLogin" :href="route('login')"
                            class="text-gray-600 hover:text-gray-900 font-medium text-sm sm:text-base">
                        Iniciar Sesión
                        </Link>
                    </div>
                </div>
            </nav>

            <!-- Hero Content -->
            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 py-12 sm:py-20">
                <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">
                    <div>
                        <div
                            class="inline-flex items-center px-4 py-2 bg-orange-100 text-orange-800 rounded-full text-sm font-medium mb-6">
                            🚀 Para Emprendedores
                        </div>

                        <h1
                            class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-4 sm:mb-6 leading-tight">
                            Lanza Tu Startup al Mundo Digital
                        </h1>

                        <p class="text-lg sm:text-xl md:text-2xl text-gray-600 mb-6 sm:mb-8">
                            Convierte tu idea en un negocio online exitoso. Hosting escalable que crece contigo.
                            <strong>De MVP a unicornio.</strong>
                        </p>

                        <!-- Key Benefits -->
                        <div class="space-y-4 mb-8">
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-orange-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-lg text-gray-700">Hosting escalable desde $10/mes</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-orange-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-lg text-gray-700">SSL gratis para generar confianza</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-orange-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-lg text-gray-700">Soporte 24/7 para no perder ventas</span>
                            </div>
                        </div>

                        <button @click="startPurchase"
                            class="bg-orange-600 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-orange-700 transform hover:scale-105 transition-all duration-200 shadow-lg">
                            Lanzar Mi Startup Ahora
                        </button>
                    </div>

                    <div class="relative">
                        <!-- Startup growth chart mockup -->
                        <div class="bg-gradient-to-br from-orange-100 to-amber-100 rounded-2xl p-8 shadow-2xl">
                            <div class="text-center">
                                <div
                                    class="w-24 h-24 bg-orange-600 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">Crecimiento Escalable</h3>
                                <p class="text-gray-600">Infraestructura que se adapta a tu éxito</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-12 sm:py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <div class="text-center mb-8 sm:mb-16">
                    <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-3 sm:mb-4 px-4">
                        Todo lo que Necesitas para Emprender Online
                    </h2>
                    <p class="text-lg sm:text-xl text-gray-600 max-w-2xl mx-auto px-4">
                        Herramientas profesionales para convertir tu idea en un negocio exitoso
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Lanzamiento Rápido</h3>
                        <p class="text-gray-600">De idea a MVP en línea en menos de 24 horas</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Escalabilidad Automática</h3>
                        <p class="text-gray-600">Recursos que crecen automáticamente con tu tráfico</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Seguridad Empresarial</h3>
                        <p class="text-gray-600">SSL, backups automáticos y protección DDoS incluidos</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Analytics Avanzados</h3>
                        <p class="text-gray-600">Métricas detalladas para optimizar tu crecimiento</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V8l8 5 8-5v10zm0-12l-8 5L4 6h16z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">API Ready</h3>
                        <p class="text-gray-600">Integra con cualquier servicio o aplicación externa</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Soporte Startup</h3>
                        <p class="text-gray-600">Asesoría técnica especializada para emprendedores</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-teal-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">SSL Gratuito</h3>
                        <p class="text-gray-600">Certificados de seguridad incluidos para generar confianza</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-pink-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">CDN Global</h3>
                        <p class="text-gray-600">Red de distribución mundial para máxima velocidad</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-cyan-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Migración Gratuita</h3>
                        <p class="text-gray-600">Transferimos tu sitio actual sin costo adicional</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Success Stories -->
        <section class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        Startups que Confiaron en Nosotros
                    </h2>
                    <p class="text-xl text-gray-600">
                        Desde MVP hasta millones de usuarios
                    </p>
                </div>

                <div class="grid md:grid-cols-2 gap-8">
                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <div class="text-center mb-4">
                            <div
                                class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-2xl">🏥</span>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Priority Health and Development</h3>
                            <p class="text-gray-600">Centro de Rehabilitación</p>
                            <a href="https://www.priorityhealthanddevelopment.com/" target="_blank"
                                class="text-blue-600 hover:text-blue-800 text-sm">
                                Ver sitio web →
                            </a>
                        </div>
                        <p class="text-gray-700 text-center">
                            "Nuestro centro de rehabilitación ahora tiene presencia digital profesional que genera
                            confianza en nuestros pacientes"
                        </p>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <div class="text-center mb-4">
                            <div
                                class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-2xl">⭐</span>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">CenasVE</h3>
                            <p class="text-gray-600">Academia de Astrología Online</p>
                            <a href="https://www.centroastrologicovenezolano.net/" target="_blank"
                                class="text-blue-600 hover:text-blue-800 text-sm">
                                Ver sitio web →
                            </a>
                        </div>
                        <p class="text-gray-700 text-center">
                            "Nuestra academia de astrología online ha crecido significativamente gracias a la plataforma
                            estable y confiable"
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-orange-600">
            <div class="max-w-4xl mx-auto px-6 text-center">
                <h2 class="text-4xl font-bold text-white mb-6">
                    ¿Listo para Convertir Tu Idea en Realidad?
                </h2>
                <p class="text-xl text-orange-100 mb-8">
                    Únete a miles de emprendedores que ya están construyendo el futuro
                </p>
                <button @click="startPurchase"
                    class="bg-white text-orange-600 px-8 py-4 rounded-xl text-lg font-semibold hover:bg-gray-100 transform hover:scale-105 transition-all duration-200 shadow-lg">
                    Lanzar Mi Startup
                </button>
                <p class="text-orange-200 mt-4">
                    ✅ Setup en 24h • ✅ Escalabilidad automática • ✅ Soporte especializado
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
    useCaseInput.value = 'entrepreneurs'
    form.appendChild(useCaseInput)

    // Add plan
    const planInput = document.createElement('input')
    planInput.type = 'hidden'
    planInput.name = 'plan'
    planInput.value = 'startup'
    form.appendChild(planInput)

    document.body.appendChild(form)
    form.submit()
}
</script>
