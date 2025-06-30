<template>
    <div class="min-h-screen bg-white">
        <!-- Hero Section for Professionals -->
        <section class="relative bg-gradient-to-br from-indigo-50 via-blue-50 to-cyan-50 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-600/5 to-blue-600/5"></div>

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
                            class="inline-flex items-center px-4 py-2 bg-indigo-100 text-indigo-800 rounded-full text-sm font-medium mb-6">
                            👔 Para Profesionales
                        </div>

                        <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                            Tu Presencia Profesional Online
                        </h1>

                        <p class="text-xl md:text-2xl text-gray-600 mb-8">
                            Destaca en tu sector con una presencia web profesional. Atrae más clientes y proyecta
                            confianza.
                            <strong>Tu expertise merece una web de calidad.</strong>
                        </p>

                        <!-- Key Benefits -->
                        <div class="space-y-4 mb-8">
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-lg text-gray-700">Portafolio profesional online</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-lg text-gray-700">Sistema de citas online</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-lg text-gray-700">Email profesional @tudominio.com</span>
                            </div>
                        </div>

                        <button @click="startPurchase"
                            class="bg-indigo-600 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-indigo-700 transform hover:scale-105 transition-all duration-200 shadow-lg">
                            Crear Mi Presencia Profesional
                        </button>
                    </div>

                    <div class="relative">
                        <!-- Professional portfolio mockup -->
                        <div class="bg-gradient-to-br from-indigo-100 to-blue-100 rounded-2xl p-8 shadow-2xl">
                            <div class="text-center">
                                <div
                                    class="w-24 h-24 bg-indigo-600 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z" />
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">Portafolio Digital</h3>
                                <p class="text-gray-600">Muestra tu experiencia y atrae mejores clientes</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Professional Types Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        Perfecto para Todo Tipo de Profesionales
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Sin importar tu área de expertise, una presencia web profesional te diferencia
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="text-center p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl">⚖️</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Abogados</h3>
                        <p class="text-gray-600">Genera confianza con casos de éxito y testimonios</p>
                    </div>

                    <div class="text-center p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl">🏥</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Médicos</h3>
                        <p class="text-gray-600">Sistema de citas online y información de servicios</p>
                    </div>

                    <div class="text-center p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl">💼</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Consultores</h3>
                        <p class="text-gray-600">Muestra tu expertise y atrae clientes corporativos</p>
                    </div>

                    <div class="text-center p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl">🎨</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Creativos</h3>
                        <p class="text-gray-600">Portafolio visual que impresiona a clientes</p>
                    </div>

                    <div class="text-center p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl">🏗️</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Arquitectos</h3>
                        <p class="text-gray-600">Galería de proyectos y proceso de trabajo</p>
                    </div>

                    <div class="text-center p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl">💰</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Contadores</h3>
                        <p class="text-gray-600">Servicios fiscales y calculadoras online</p>
                    </div>

                    <div class="text-center p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-16 h-16 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl">💅</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Estilistas</h3>
                        <p class="text-gray-600">Antes/después y sistema de reservas online</p>
                    </div>

                    <div class="text-center p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl">🏃</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Entrenadores</h3>
                        <p class="text-gray-600">Planes de entrenamiento y transformaciones</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        Herramientas Profesionales Incluidas
                    </h2>
                    <p class="text-xl text-gray-600">
                        Todo lo que necesitas para proyectar profesionalismo
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Portafolio Digital</h3>
                        <p class="text-gray-600">Muestra tus mejores trabajos y casos de éxito de forma profesional</p>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Sistema de Citas</h3>
                        <p class="text-gray-600">Permite que tus clientes agenden citas online las 24 horas</p>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V8l8 5 8-5v10zm0-12l-8 5L4 6h16z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Email Profesional</h3>
                        <p class="text-gray-600">contacto@tudominio.com proyecta más confianza que gmail</p>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Testimonios</h3>
                        <p class="text-gray-600">Muestra reseñas de clientes satisfechos para generar confianza</p>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Certificaciones</h3>
                        <p class="text-gray-600">Muestra tus títulos, certificaciones y logros profesionales</p>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Blog Profesional</h3>
                        <p class="text-gray-600">Comparte tu conocimiento y posiciónate como experto</p>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-teal-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">SEO Optimizado</h3>
                        <p class="text-gray-600">Aparece en Google cuando buscan tu especialidad</p>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-pink-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Formularios de Contacto</h3>
                        <p class="text-gray-600">Recibe consultas directamente desde tu sitio web</p>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Integración Redes Sociales</h3>
                        <p class="text-gray-600">Conecta todas tus plataformas en un solo lugar</p>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-cyan-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Respaldos Automáticos</h3>
                        <p class="text-gray-600">Tu información profesional siempre protegida y segura</p>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Velocidad Optimizada</h3>
                        <p class="text-gray-600">Carga rápida para impresionar a tus clientes potenciales</p>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">SSL Incluido</h3>
                        <p class="text-gray-600">Certificado de seguridad para generar confianza profesional</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-indigo-600">
            <div class="max-w-4xl mx-auto px-6 text-center">
                <h2 class="text-4xl font-bold text-white mb-6">
                    ¿Listo para Proyectar Profesionalismo?
                </h2>
                <p class="text-xl text-indigo-100 mb-8">
                    Únete a miles de profesionales que ya destacan con su presencia web
                </p>
                <button @click="startPurchase"
                    class="bg-white text-indigo-600 px-8 py-4 rounded-xl text-lg font-semibold hover:bg-gray-100 transform hover:scale-105 transition-all duration-200 shadow-lg">
                    Crear Mi Presencia Profesional
                </button>
                <p class="text-indigo-200 mt-4">
                    ✅ Setup profesional • ✅ Soporte especializado • ✅ Resultados garantizados
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
    useCaseInput.value = 'professionals'
    form.appendChild(useCaseInput)

    // Add plan
    const planInput = document.createElement('input')
    planInput.type = 'hidden'
    planInput.name = 'plan'
    planInput.value = 'professional'
    form.appendChild(planInput)

    document.body.appendChild(form)
    form.submit()
}
</script>
