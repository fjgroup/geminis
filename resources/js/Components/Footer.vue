<template>
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-8">
                <!-- Company Info -->
                <div class="lg:col-span-2 xl:col-span-2">
                    <div class="flex items-center mb-4">
                        <img src="/images/logo.png" alt="Fj Group CA" class="h-8 w-auto mr-3">
                        <span class="text-2xl font-bold">Fj Group CA</span>
                    </div>
                    <p class="text-gray-400 mb-6 max-w-md">
                        Desde el 2012 ofreciendo soluciones de hosting confiables y profesionales.
                        Tu éxito online es nuestra prioridad.
                    </p>

                    <!-- Contact Info -->
                    <div class="space-y-3">
                        <div class="flex items-center text-gray-300">
                            <svg class="w-5 h-5 mr-3 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                            </svg>
                            <span class="text-sm">
                                Calle Blanco Fombona CC Nohelia Blohm Piso 1<br>
                                Cumaná - Venezuela
                            </span>
                        </div>

                        <div class="flex items-center text-gray-300">
                            <svg class="w-5 h-5 mr-3 text-green-400" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z" />
                            </svg>
                            <span class="text-sm">+58 0412 817.23.37</span>
                        </div>

                        <div class="flex items-center text-gray-300">
                            <svg class="w-5 h-5 mr-3 text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                            </svg>
                            <span class="text-sm">RIF: J-403247242</span>
                        </div>
                    </div>
                </div>

                <!-- Hosting Solutions -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Hosting</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="/para-educadores" class="hover:text-white transition-colors">Para Educadores</a>
                        </li>
                        <li><a href="/para-emprendedores" class="hover:text-white transition-colors">Para
                                Emprendedores</a></li>
                        <li><a href="/para-profesionales" class="hover:text-white transition-colors">Para
                                Profesionales</a></li>
                        <li><a href="/para-negocios" class="hover:text-white transition-colors">Para Pequeños
                                Negocios</a></li>
                        <li><a href="/para-diseñadores-web" class="hover:text-white transition-colors">Para Diseñadores
                                Web</a></li>
                        <li><a href="/technical-resellers" class="hover:text-white transition-colors">Para Revendedores
                                Técnicos</a></li>
                    </ul>
                </div>

                <!-- Services -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Servicios</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">Dominios</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">SSL Certificados</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Email Profesional</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Migración Gratuita</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Soporte 24/7</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Soporte</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">Centro de Ayuda</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Contacto</a></li>
                        <li>
                            <a :href="whatsappUrl" target="_blank"
                                class="hover:text-white transition-colors flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.893 3.488" />
                                </svg>
                                WhatsApp
                            </a>
                        </li>
                        <li><a href="#" class="hover:text-white transition-colors">Estado del Servicio</a></li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="border-t border-gray-800 mt-8 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="text-gray-400 text-sm mb-4 md:mb-0">
                        © {{ currentYear }} Fj Group CA. Todos los derechos reservados.
                    </div>

                    <div class="flex space-x-6 text-sm text-gray-400">
                        <a href="#" class="hover:text-white transition-colors">Términos de Servicio</a>
                        <a href="#" class="hover:text-white transition-colors">Política de Privacidad</a>
                        <a href="#" class="hover:text-white transition-colors">Política de Reembolso</a>
                    </div>
                </div>

                <!-- Trust Badges -->
                <div class="flex justify-center items-center mt-6 space-x-8">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-400">2012</div>
                        <div class="text-xs text-gray-500">Desde</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-400">99.9%</div>
                        <div class="text-xs text-gray-500">Uptime Garantizado</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-400">24/7</div>
                        <div class="text-xs text-gray-500">Soporte Técnico</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-400">45</div>
                        <div class="text-xs text-gray-500">Días de Garantía</div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { computed } from 'vue'

const currentYear = computed(() => new Date().getFullYear())

const whatsappUrl = computed(() => {
    const message = encodeURIComponent('¡Hola! Me interesa conocer más sobre sus servicios de hosting.')
    return `https://wa.me/584128172337?text=${message}`
})
</script>
