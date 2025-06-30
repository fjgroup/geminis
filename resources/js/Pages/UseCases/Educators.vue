<template>
    <div class="min-h-screen bg-white">
        <!-- Hero Section for Educators -->
        <section class="relative bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600/5 to-purple-600/5"></div>

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
                            class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-medium mb-6">
                            🎓 Para Educadores y Profesores
                        </div>

                        <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                            Crea tu Academia Online y Enseña desde Casa
                        </h1>

                        <p class="text-xl md:text-2xl text-gray-600 mb-8">
                            Convierte tu conocimiento en ingresos extra con una plataforma educativa profesional.
                            <strong>Sin conocimientos técnicos, sin complicaciones.</strong>
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
                                <span class="text-lg text-gray-700">Plataforma Moodle instalada automáticamente</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-lg text-gray-700">Cobra por tus cursos online</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-lg text-gray-700">Certificados automáticos para estudiantes</span>
                            </div>
                        </div>

                        <button @click="startPurchase"
                            class="bg-blue-600 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-blue-700 transform hover:scale-105 transition-all duration-200 shadow-lg">
                            Crear Mi Academia Online
                        </button>
                    </div>

                    <div class="relative">
                        <!-- Placeholder for academy image -->
                        <div class="bg-gradient-to-br from-blue-100 to-purple-100 rounded-2xl p-8 shadow-2xl">
                            <div class="text-center">
                                <div
                                    class="w-24 h-24 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72L12 15l5-2.73v3.72z" />
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">Tu Academia Virtual</h3>
                                <p class="text-gray-600">Lista en menos de 5 minutos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        ¿Cómo Funciona?
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        En 3 simples pasos tendrás tu academia online funcionando
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div
                            class="w-16 h-16 bg-blue-600 text-white rounded-full flex items-center justify-center mx-auto mb-6 text-2xl font-bold">
                            1
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Elige tu Dominio</h3>
                        <p class="text-gray-600">Selecciona el nombre que tus estudiantes recordarán fácilmente</p>
                    </div>

                    <div class="text-center">
                        <div
                            class="w-16 h-16 bg-blue-600 text-white rounded-full flex items-center justify-center mx-auto mb-6 text-2xl font-bold">
                            2
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Nosotros Instalamos Todo</h3>
                        <p class="text-gray-600">Moodle, certificados SSL, respaldos automáticos. Todo listo para usar.
                        </p>
                    </div>

                    <div class="text-center">
                        <div
                            class="w-16 h-16 bg-blue-600 text-white rounded-full flex items-center justify-center mx-auto mb-6 text-2xl font-bold">
                            3
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Comienza a Enseñar</h3>
                        <p class="text-gray-600">Sube tus cursos, invita estudiantes y comienza a generar ingresos.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        Todo lo que Necesitas para Enseñar Online
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Tu academia incluye todas las herramientas profesionales
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Gestión de Cursos</h3>
                        <p class="text-gray-600">Organiza tus lecciones, tareas y evaluaciones fácilmente</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm-1 16H9V7h9v14z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Certificados Automáticos</h3>
                        <p class="text-gray-600">Genera certificados profesionales al completar cursos</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Sistema de Calificaciones</h3>
                        <p class="text-gray-600">Evalúa y da seguimiento al progreso de tus estudiantes</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Foros de Discusión</h3>
                        <p class="text-gray-600">Crea comunidad entre tus estudiantes</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Reportes Detallados</h3>
                        <p class="text-gray-600">Analiza el rendimiento y participación</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm3.5 6L12 10.5 8.5 8 12 5.5 15.5 8zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Acceso Móvil</h3>
                        <p class="text-gray-600">Tus estudiantes pueden aprender desde cualquier dispositivo</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-teal-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Pagos Integrados</h3>
                        <p class="text-gray-600">Cobra por tus cursos directamente en la plataforma</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-pink-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Respaldos Automáticos</h3>
                        <p class="text-gray-600">Todo tu contenido educativo protegido y seguro</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Velocidad Optimizada</h3>
                        <p class="text-gray-600">Videos y contenido se cargan rápidamente</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-blue-600">
            <div class="max-w-4xl mx-auto px-6 text-center">
                <h2 class="text-4xl font-bold text-white mb-6">
                    ¿Listo para Comenzar tu Academia Online?
                </h2>
                <p class="text-xl text-blue-100 mb-8">
                    Únete a cientos de educadores que ya están generando ingresos extra enseñando online
                </p>
                <button @click="startPurchase"
                    class="bg-white text-blue-600 px-8 py-4 rounded-xl text-lg font-semibold hover:bg-gray-100 transform hover:scale-105 transition-all duration-200 shadow-lg">
                    Crear Mi Academia Ahora
                </button>
                <p class="text-blue-200 mt-4">
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
    useCaseInput.value = 'educators'
    form.appendChild(useCaseInput)

    // Add plan (default to professional for educators)
    const planInput = document.createElement('input')
    planInput.type = 'hidden'
    planInput.name = 'plan'
    planInput.value = 'professional'
    form.appendChild(planInput)

    document.body.appendChild(form)
    form.submit()
}
</script>
