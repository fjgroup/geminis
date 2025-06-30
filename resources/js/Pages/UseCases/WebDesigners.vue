<template>
    <div class="min-h-screen bg-white">
        <!-- Hero Section for Web Designers -->
        <section class="relative bg-gradient-to-br from-purple-50 via-blue-50 to-indigo-50 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-purple-600/5 to-blue-600/5"></div>

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
                            class="inline-flex items-center px-4 py-2 bg-purple-100 text-purple-800 rounded-full text-sm font-medium mb-6">
                            🎨 Para Diseñadores Web
                        </div>

                        <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                            Ofrece Hosting Profesional a Tus Clientes
                        </h1>

                        <p class="text-xl md:text-2xl text-gray-600 mb-8">
                            Gana dinero recurrente cada mes mientras nosotros nos encargamos de toda la parte técnica.
                            <strong>Tú diseñas, nosotros mantenemos.</strong>
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
                                <span class="text-lg text-gray-700">Panel de gestión GRATIS (sin WHMCS)</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-lg text-gray-700">Gana $5-15 USD por cliente cada mes</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-lg text-gray-700">Soporte técnico 24/7 para tus clientes</span>
                            </div>
                        </div>

                        <button @click="startPurchase"
                            class="bg-purple-600 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-purple-700 transform hover:scale-105 transition-all duration-200 shadow-lg">
                            Comenzar a Ganar Dinero Recurrente
                        </button>
                    </div>

                    <div class="relative">
                        <!-- Placeholder for designer dashboard image -->
                        <div class="bg-gradient-to-br from-purple-100 to-blue-100 rounded-2xl p-8 shadow-2xl">
                            <div class="text-center">
                                <div
                                    class="w-24 h-24 bg-purple-600 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" />
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">Panel de Gestión</h3>
                                <p class="text-gray-600">Controla todos los hostings desde un solo lugar</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Revenue Calculator Section -->
        <section class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        Calcula Tus Ganancias Mensuales
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Descubre cuánto puedes ganar ofreciendo hosting a tus clientes
                    </p>
                </div>

                <div class="max-w-4xl mx-auto">
                    <div class="bg-white rounded-2xl shadow-xl p-8">
                        <div class="grid md:grid-cols-2 gap-8">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-6">Tus Números</h3>

                                <div class="space-y-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            ¿Cuántos clientes tienes actualmente?
                                        </label>
                                        <input v-model="currentClients" type="number" min="0" max="100"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-lg">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            ¿Cuántos clientes nuevos consigues por mes?
                                        </label>
                                        <input v-model="newClientsPerMonth" type="number" min="0" max="20"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-lg">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            ¿Cuánto cobras por diseñar un sitio web?
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-3 text-gray-500 text-lg">$</span>
                                            <input v-model="webDesignPrice" type="number" min="50" max="1000"
                                                class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-lg">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-6">Tus Ganancias Reales</h3>

                                <div class="space-y-4">
                                    <!-- Ingresos por Hosting -->
                                    <div class="bg-purple-50 rounded-lg p-4">
                                        <h4 class="font-semibold text-purple-800 mb-2">💰 Ingresos por Hosting
                                            (Recurrentes)</h4>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-700">Mes 1:</span>
                                            <span class="text-lg font-bold text-purple-600">${{ currentHostingRevenue
                                            }}/mes</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-700">Mes 12:</span>
                                            <span class="text-lg font-bold text-purple-600">${{ finalHostingRevenue
                                            }}/mes</span>
                                        </div>
                                        <div class="flex justify-between items-center border-t pt-2 mt-2">
                                            <span class="text-gray-700 font-medium">Total hosting en 12 meses:</span>
                                            <span class="text-xl font-bold text-purple-600">${{ totalHostingRevenue
                                            }}</span>
                                        </div>
                                    </div>

                                    <!-- Ingresos por Diseño Web -->
                                    <div class="bg-green-50 rounded-lg p-4">
                                        <h4 class="font-semibold text-green-800 mb-2">🎨 Ingresos por Diseño Web (Una
                                            vez)</h4>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-700">{{ totalNewClients }} clientes × ${{
                                                webDesignPrice }}:</span>
                                            <span class="text-xl font-bold text-green-600">${{ totalDesignRevenue
                                            }}</span>
                                        </div>
                                    </div>

                                    <!-- Total Real -->
                                    <div class="bg-blue-50 rounded-lg p-6 border-2 border-blue-200">
                                        <h4 class="font-semibold text-blue-800 mb-3 text-lg">🏆 Total Real que Ganarás
                                            en 12 Meses</h4>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-700">Hosting + Diseño Web:</span>
                                            <span class="text-3xl font-bold text-blue-600">${{ totalYearlyRevenue
                                            }}</span>
                                        </div>
                                        <div class="text-sm text-blue-700 mt-2">
                                            <p>• ${{ totalHostingRevenue }} por hosting recurrente</p>
                                            <p>• ${{ totalDesignRevenue }} por {{ totalNewClients }} diseños web</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6 text-sm text-gray-600">
                                    <p><strong>Ejemplo mes a mes:</strong></p>
                                    <p>• Enero: {{ currentClients }} clientes = ${{ currentHostingRevenue }} + {{
                                        newClientsPerMonth }} diseños = ${{ webDesignPrice * newClientsPerMonth }}</p>
                                    <p>• Diciembre: {{ finalHostingRevenue / 10 }} clientes = ${{ finalHostingRevenue
                                    }}/mes</p>
                                    <p>• <strong>Cálculo honesto</strong> - Sin trucos matemáticos</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        Todo lo que Necesitas para Ser Reseller
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Herramientas profesionales sin costo adicional
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Panel de Gestión GRATIS</h3>
                        <p class="text-gray-600">Sistema propio de facturación. No necesitas WHMCS ($18/mes ahorrados)
                        </p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Planes Personalizables</h3>
                        <p class="text-gray-600">CPU, RAM y almacenamiento ajustables según cada proyecto</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-5 14H4v-6h11v6zm0-8H4V8h11v2zm5 8h-4V8h4v10z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">DNS Privados</h3>
                        <p class="text-gray-600">ns1.tudominio.com - Marca blanca completa</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Soporte Técnico 24/7</h3>
                        <p class="text-gray-600">Nosotros resolvemos los problemas técnicos de tus clientes</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Respaldos Automáticos</h3>
                        <p class="text-gray-600">Copias de seguridad diarias. Tranquilidad total para tus clientes</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Migración Gratuita</h3>
                        <p class="text-gray-600">Movemos los sitios de tus clientes sin costo adicional</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-teal-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">SSL Gratuito</h3>
                        <p class="text-gray-600">Certificados de seguridad incluidos para todos los sitios</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-pink-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Velocidad Optimizada</h3>
                        <p class="text-gray-600">SSD NVMe + CDN. Sitios súper rápidos que encantan a Google</p>
                    </div>

                    <div class="p-6 border border-gray-200 rounded-xl hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">API Completa</h3>
                        <p class="text-gray-600">Integra con tus herramientas y automatiza procesos</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-purple-600">
            <div class="max-w-4xl mx-auto px-6 text-center">
                <h2 class="text-4xl font-bold text-white mb-6">
                    ¿Listo para Generar Ingresos Recurrentes?
                </h2>
                <p class="text-xl text-purple-100 mb-8">
                    Únete a cientos de diseñadores que ya están ganando dinero extra cada mes
                </p>
                <button @click="startPurchase"
                    class="bg-white text-purple-600 px-8 py-4 rounded-xl text-lg font-semibold hover:bg-gray-100 transform hover:scale-105 transition-all duration-200 shadow-lg">
                    Comenzar Ahora - Plan Reseller
                </button>
                <p class="text-purple-200 mt-4">
                    ✅ Panel GRATIS • ✅ Soporte 24/7 • ✅ 15 años de experiencia
                </p>
            </div>
        </section>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
})

// Variables reactivas con valores por defecto realistas
const currentClients = ref(1) // Clientes actuales
const newClientsPerMonth = ref(3) // Nuevos clientes por mes
const webDesignPrice = ref(120) // Precio por diseño web

// Cálculos de hosting (recurrentes)
const currentHostingRevenue = computed(() => {
    // Mes 1: clientes actuales + nuevos del primer mes
    return (currentClients.value + newClientsPerMonth.value) * 10
})

const finalHostingRevenue = computed(() => {
    return (currentClients.value + (newClientsPerMonth.value * 12)) * 10
})

const totalHostingRevenue = computed(() => {
    let totalClients = currentClients.value
    let totalRevenue = 0

    // Calcular ingresos acumulados mes a mes (como en Excel)
    for (let month = 1; month <= 12; month++) {
        // PRIMERO agregar nuevos clientes del mes
        totalClients += newClientsPerMonth.value
        // DESPUÉS calcular ingresos del mes
        totalRevenue += totalClients * 10 // $10 por cliente por mes
    }

    return totalRevenue
})

// Cálculos de diseño web (una sola vez)
const totalNewClients = computed(() => {
    return newClientsPerMonth.value * 12
})

const totalDesignRevenue = computed(() => {
    return totalNewClients.value * webDesignPrice.value
})

// Total real anual
const totalYearlyRevenue = computed(() => {
    return totalHostingRevenue.value + totalDesignRevenue.value
})

const startPurchase = () => {
    // Create form to submit use case and plan for reseller
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
    useCaseInput.value = 'web-designers'
    form.appendChild(useCaseInput)

    // Add plan (reseller plan)
    const planInput = document.createElement('input')
    planInput.type = 'hidden'
    planInput.name = 'plan'
    planInput.value = 'reseller'
    form.appendChild(planInput)

    document.body.appendChild(form)
    form.submit()
}
</script>
