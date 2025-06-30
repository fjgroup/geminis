<template>
    <div class="min-h-screen bg-white">
        <!-- Hero Section for Technical Resellers -->
        <section class="relative bg-gradient-to-br from-gray-900 via-blue-900 to-indigo-900 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600/10 to-indigo-600/10"></div>

            <!-- Navigation -->
            <nav class="relative z-10 px-4 sm:px-6 py-4">
                <div class="max-w-7xl mx-auto flex justify-between items-center">
                    <div class="text-lg sm:text-2xl font-bold text-white">
                        Fj Group CA
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-1 sm:space-y-0 sm:space-x-4 text-right">
                        <Link :href="route('sales.home')"
                            class="text-gray-300 hover:text-white font-medium text-sm sm:text-base">
                        ← Volver al inicio
                        </Link>
                        <Link v-if="canLogin" :href="route('login')"
                            class="text-gray-300 hover:text-white font-medium text-sm sm:text-base">
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
                            class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-medium mb-6">
                            🔧 Para Revendedores Técnicos
                        </div>

                        <h1 class="text-5xl md:text-6xl font-bold text-white mb-6 leading-tight">
                            Hosting Reseller Nivel Empresarial
                        </h1>

                        <p class="text-xl md:text-2xl text-gray-300 mb-8">
                            Soluciones de hosting marca blanca con cuentas ilimitadas, nameservers privados y
                            características empresariales.
                            <strong>Construido para profesionales que exigen lo mejor.</strong>
                        </p>

                        <!-- Key Technical Features -->
                        <div class="space-y-4 mb-8">
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-lg text-gray-300">Unlimited cPanel accounts</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-lg text-gray-300">Private nameservers (ns1.yourdomain.com)</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-lg text-gray-300">WHM/cPanel with full root access</span>
                            </div>
                        </div>

                        <button @click="startPurchase"
                            class="bg-blue-600 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-blue-700 transform hover:scale-105 transition-all duration-200 shadow-lg">
                            Start Reselling Now
                        </button>
                    </div>

                    <div class="relative">
                        <!-- Technical dashboard mockup -->
                        <div
                            class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl p-8 shadow-2xl border border-gray-700">
                            <div class="text-center">
                                <div
                                    class="w-24 h-24 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" />
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold text-white mb-2">WHM Control Panel</h3>
                                <p class="text-gray-400">Full server management capabilities</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Technical Specifications -->
        <section class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        Enterprise-Level Infrastructure
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Built on cutting-edge technology for maximum performance and reliability
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">NVMe SSD Storage</h3>
                        <p class="text-gray-600">Ultra-fast NVMe drives for lightning-quick load times</p>
                        <div class="mt-3 text-sm text-blue-600 font-medium">Up to 100x faster than HDD</div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">LiteSpeed Web Server</h3>
                        <p class="text-gray-600">Advanced caching and optimization for peak performance</p>
                        <div class="mt-3 text-sm text-green-600 font-medium">9x faster than Apache</div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Cloudflare CDN</h3>
                        <p class="text-gray-600">Global content delivery network with DDoS protection</p>
                        <div class="mt-3 text-sm text-purple-600 font-medium">200+ edge locations</div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">99.9% Uptime SLA</h3>
                        <p class="text-gray-600">Enterprise-grade reliability with monitoring</p>
                        <div class="mt-3 text-sm text-red-600 font-medium">Guaranteed uptime</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Reseller Features -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        Complete White-Label Solution
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Everything you need to run a professional hosting business
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Acceso WHM/cPanel</h3>
                        <p class="text-gray-600">Web Host Manager completo con creación ilimitada de cuentas cPanel</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V8l8 5 8-5v10zm0-12l-8 5L4 6h16z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Nameservers Privados</h3>
                        <p class="text-gray-600">ns1.tudominio.com y ns2.tudominio.com para marca blanca completa</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Integración WHMCS</h3>
                        <p class="text-gray-600">Integración perfecta con WHMCS para facturación automatizada</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Certificados SSL Gratuitos</h3>
                        <p class="text-gray-600">Certificados SSL Let's Encrypt para todas las cuentas de tus clientes
                        </p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Respaldos Diarios</h3>
                        <p class="text-gray-600">Copias de seguridad automáticas diarias con opciones de restauración
                            fácil</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Migraciones Gratuitas</h3>
                        <p class="text-gray-600">Servicio de migración profesional para tus clientes existentes</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-teal-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Soporte 24/7</h3>
                        <p class="text-gray-600">Soporte técnico especializado disponible las 24 horas del día</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-pink-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">API Completa</h3>
                        <p class="text-gray-600">API REST completa para automatizar todas las operaciones</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Alto Rendimiento</h3>
                        <p class="text-gray-600">Servidores optimizados con recursos garantizados para tus clientes</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-gray-900">
            <div class="max-w-4xl mx-auto px-6 text-center">
                <h2 class="text-4xl font-bold text-white mb-6">
                    Ready to Scale Your Hosting Business?
                </h2>
                <p class="text-xl text-gray-300 mb-8">
                    Join hundreds of successful hosting resellers worldwide
                </p>
                <button @click="startPurchase"
                    class="bg-blue-600 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-blue-700 transform hover:scale-105 transition-all duration-200 shadow-lg">
                    Start Your Reseller Business
                </button>
                <p class="text-gray-400 mt-4">
                    ✅ 15+ years experience • ✅ Enterprise infrastructure • ✅ 24/7 technical support
                </p>
            </div>
        </section>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
})

const startPurchase = () => {
    // Create form to submit use case and plan for technical resellers
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
    useCaseInput.value = 'technical-resellers'
    form.appendChild(useCaseInput)

    // Add plan (enterprise reseller plan)
    const planInput = document.createElement('input')
    planInput.type = 'hidden'
    planInput.name = 'plan'
    planInput.value = 'enterprise-reseller'
    form.appendChild(planInput)

    document.body.appendChild(form)
    form.submit()
}
</script>
