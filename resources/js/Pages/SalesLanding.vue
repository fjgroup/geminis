<template>
    <div class="min-h-screen bg-white">
        <!-- Hero Section -->
        <section class="relative bg-gradient-to-br from-blue-50 via-white to-purple-50 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600/5 to-purple-600/5"></div>

            <!-- Navigation -->
            <nav class="relative z-10 px-4 sm:px-6 py-4">
                <div class="max-w-7xl mx-auto flex justify-between items-center">
                    <div class="flex items-center">
                        <img src="/images/logo.png" alt="Fj Group CA" class="h-6 sm:h-8 w-auto mr-2 sm:mr-3">
                        <span class="text-lg sm:text-2xl font-bold text-gray-900">{{ salesData.appName }}</span>
                    </div>
                    <div class="flex space-x-2 sm:space-x-4">
                        <!-- Si está autenticado, mostrar Dashboard -->
                        <Link v-if="auth?.user" :href="getDashboardRoute()"
                            class="text-gray-600 hover:text-gray-900 font-medium text-sm sm:text-base">
                        Dashboard
                        </Link>
                        <!-- Si no está autenticado, mostrar Login -->
                        <Link v-else-if="canLogin" :href="route('login')"
                            class="text-gray-600 hover:text-gray-900 font-medium text-sm sm:text-base">
                        Iniciar Sesión
                        </Link>
                        <!-- Botón de registro solo si no está autenticado -->
                        <Link v-if="canRegister && !auth?.user" :href="route('register')"
                            class="bg-blue-600 text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-blue-700 font-medium text-sm sm:text-base">
                        Registrarse
                        </Link>
                    </div>
                </div>
            </nav>

            <!-- Hero Content -->
            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 py-12 sm:py-20">
                <div class="text-center">
                    <h1
                        class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-4 sm:mb-6 leading-tight px-2">
                        {{ salesData.heroSection.title }}
                    </h1>
                    <p class="text-lg sm:text-xl md:text-2xl text-gray-600 mb-6 sm:mb-8 max-w-3xl mx-auto px-4">
                        {{ salesData.heroSection.subtitle }}
                    </p>

                    <!-- Trust Indicators -->
                    <div class="flex flex-wrap justify-center gap-8 mb-10">
                        <div v-for="indicator in salesData.heroSection.trustIndicators" :key="indicator.text"
                            class="flex items-center space-x-2">
                            <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span :class="indicator.color" class="font-medium">{{ indicator.text }}</span>
                        </div>
                    </div>

                    <button @click="scrollToUseCases"
                        class="bg-blue-600 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-blue-700 transform hover:scale-105 transition-all duration-200 shadow-lg">
                        {{ salesData.heroSection.ctaButtonText }}
                    </button>
                </div>
            </div>
        </section>

        <!-- Use Cases Section -->
        <section id="use-cases" class="py-12 sm:py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <div class="text-center mb-8 sm:mb-16">
                    <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-3 sm:mb-4 px-4">
                        ¿Cuál es tu Objetivo?
                    </h2>
                    <p class="text-lg sm:text-xl text-gray-600 max-w-2xl mx-auto px-4">
                        Elige la opción que mejor describe lo que quieres lograr
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
                    <div v-for="useCase in salesData.useCases" :key="useCase.id" :class="useCase.color"
                        class="rounded-2xl p-4 sm:p-6 md:p-8 border border-gray-200 hover:shadow-xl transition-all duration-300 cursor-pointer transform hover:-translate-y-2">

                        <div class="flex items-center mb-4 sm:mb-6">
                            <div :class="useCase.accentColor"
                                class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg bg-white flex items-center justify-center mr-3 sm:mr-4">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <!-- Icon placeholder - you can add specific icons here -->
                                    <path d="M12 2L2 7v10c0 5.55 3.84 9.74 9 11 5.16-1.26 9-5.45 9-11V7l-10-5z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl sm:text-2xl font-bold text-gray-900">{{ useCase.title }}</h3>
                            </div>
                        </div>

                        <h4 :class="useCase.accentColor" class="text-lg sm:text-xl font-semibold mb-2 sm:mb-3">
                            {{ useCase.headline }}
                        </h4>

                        <p class="text-gray-600 mb-4 sm:mb-6 text-sm sm:text-base">
                            {{ useCase.description }}
                        </p>

                        <ul class="space-y-2 mb-6">
                            <li v-for="benefit in useCase.benefits" :key="benefit"
                                class="flex items-center text-gray-700">
                                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                {{ benefit }}
                            </li>
                        </ul>

                        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                            <button @click="selectUseCase(useCase.id)"
                                :class="useCase.accentColor.replace('text-', 'bg-')"
                                class="flex-1 text-white py-2 sm:py-3 rounded-lg font-semibold hover:opacity-90 transition-opacity text-sm sm:text-base">
                                {{ useCase.ctaText }}
                            </button>
                            <Link :href="getUseCasePageUrl(useCase.id)"
                                class="px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium text-center text-sm sm:text-base">
                            Ver más
                            </Link>
                        </div>

                        <!-- Testimonial -->
                        <div v-if="useCase.testimonial" class="mt-6 p-4 bg-white rounded-lg border-l-4"
                            :class="useCase.accentColor.replace('text-', 'border-')">
                            <p class="text-gray-600 italic mb-2">"{{ useCase.testimonial.text }}"</p>
                            <p class="text-sm font-medium text-gray-800">- {{ useCase.testimonial.author }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why Choose Us Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        {{ salesData.whyChooseUs.title }}
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        {{ salesData.whyChooseUs.subtitle }}
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div v-for="feature in salesData.whyChooseUs.features" :key="feature.title" class="text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <!-- Icon placeholder -->
                                <path d="M12 2L2 7v10c0 5.55 3.84 9.74 9 11 5.16-1.26 9-5.45 9-11V7l-10-5z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ feature.title }}</h3>
                        <p class="text-gray-600">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        {{ salesData.testimonials.title }}
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        {{ salesData.testimonials.subtitle }}
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="review in salesData.testimonials.reviews" :key="review.name"
                        class="bg-white border border-gray-200 rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow">

                        <!-- Rating Stars - Centered -->
                        <div class="flex justify-center mb-4">
                            <div class="flex">
                                <svg v-for="star in review.rating" :key="star" class="w-6 h-6 text-yellow-400"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                    </path>
                                </svg>
                            </div>
                        </div>

                        <!-- Review Text -->
                        <p class="text-gray-700 mb-4 italic">"{{ review.text }}"</p>

                        <!-- Reviewer Info -->
                        <div class="border-t pt-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ review.name }}</p>
                                    <p class="text-sm text-gray-600">{{ review.business_type }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">{{ review.country }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- For Designers Section -->
        <section class="py-20 bg-gradient-to-r from-purple-600 to-blue-600">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-white mb-4">
                        {{ salesData.forDesigners.title }}
                    </h2>
                    <p class="text-xl text-purple-100 max-w-2xl mx-auto">
                        {{ salesData.forDesigners.subtitle }}
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
                    <div v-for="benefit in salesData.forDesigners.benefits" :key="benefit.title" class="text-center">
                        <div
                            class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <!-- Icon placeholder -->
                                <path d="M12 2L2 7v10c0 5.55 3.84 9.74 9 11 5.16-1.26 9-5.45 9-11V7l-10-5z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2">{{ benefit.title }}</h3>
                        <p class="text-purple-100">{{ benefit.description }}</p>
                    </div>
                </div>

                <div class="text-center">
                    <button @click="selectUseCase('web-designers')"
                        class="bg-white text-purple-600 px-8 py-4 rounded-xl text-lg font-semibold hover:bg-gray-100 transform hover:scale-105 transition-all duration-200 shadow-lg">
                        {{ salesData.forDesigners.cta }}
                    </button>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        {{ salesData.pricing.title }}
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        {{ salesData.pricing.subtitle }}
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                    <div v-for="plan in salesData.pricing.plans" :key="plan.id"
                        :class="plan.popular ? 'ring-2 ring-blue-500 scale-105' : ''"
                        class="bg-white rounded-2xl p-8 shadow-lg relative">

                        <div v-if="plan.popular"
                            class="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-blue-500 text-white px-4 py-1 rounded-full text-sm font-medium">
                            Más Popular
                        </div>

                        <div class="text-center mb-6">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ plan.name }}</h3>
                            <p class="text-gray-600 mb-4">{{ plan.description }}</p>
                            <div class="flex items-center justify-center">
                                <span class="text-4xl font-bold text-gray-900">${{ plan.price }}</span>
                                <span class="text-gray-600 ml-2">/ {{ plan.period }}</span>
                            </div>
                        </div>

                        <ul class="space-y-3 mb-8">
                            <li v-for="feature in plan.features" :key="feature" class="flex items-center text-gray-700">
                                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                {{ feature }}
                            </li>
                        </ul>

                        <button @click="selectPlan(plan.id)"
                            :class="plan.popular ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-800 hover:bg-gray-900'"
                            class="w-full text-white py-3 rounded-lg font-semibold transition-colors">
                            {{ plan.ctaText }}
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Guarantee Section -->
        <section class="py-16 bg-blue-600">
            <div class="max-w-4xl mx-auto px-6 text-center">
                <div class="flex items-center justify-center mb-6">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7v10c0 5.55 3.84 9.74 9 11 5.16-1.26 9-5.45 9-11V7l-10-5z" />
                        </svg>
                    </div>
                </div>
                <h2 class="text-3xl font-bold text-white mb-4">{{ salesData.guarantee.title }}</h2>
                <p class="text-xl text-blue-100">{{ salesData.guarantee.description }}</p>
            </div>
        </section>

        <!-- Footer -->
        <Footer />

        <!-- WhatsApp Button -->
        <WhatsAppButton />

        <!-- Modal de Selección de Planes -->
        <div v-if="showPlanModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl max-w-5xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-8">
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-gray-900 mb-2">Planes Simples, Resultados Extraordinarios
                        </h2>
                        <p class="text-gray-600">Elige el plan perfecto para tu proyecto. Puedes cambiar cuando quieras.
                        </p>
                        <button @click="showPlanModal = false"
                            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="grid md:grid-cols-3 gap-6">
                        <!-- Plan Emprendedor -->
                        <div class="bg-white rounded-xl p-6 border border-gray-200 relative">
                            <div class="text-center mb-6">
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">Emprendedor</h3>
                                <p class="text-gray-600 mb-4">Perfecto para comenzar tu presencia online</p>
                                <div class="flex items-center justify-center">
                                    <span class="text-4xl font-bold text-gray-900">$10</span>
                                    <span class="text-gray-600 ml-2">/ mes</span>
                                </div>
                            </div>

                            <ul class="space-y-3 mb-8">
                                <li class="flex items-center text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Sitio web profesional incluido
                                </li>
                                <li class="flex items-center text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Dominio gratis el primer año
                                </li>
                                <li class="flex items-center text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Email profesional (tu@tunegocio.com)
                                </li>
                                <li class="flex items-center text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Certificado de seguridad SSL
                                </li>
                                <li class="flex items-center text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Soporte por WhatsApp
                                </li>
                            </ul>

                            <button @click="selectPlanFromModal('starter')"
                                class="w-full bg-gray-800 hover:bg-gray-900 text-white py-3 rounded-lg font-semibold transition-colors">
                                Comenzar Ahora
                            </button>
                        </div>

                        <!-- Plan Profesional -->
                        <div class="bg-white rounded-xl p-6 border-2 border-blue-500 relative scale-105">
                            <div
                                class="absolute -top-3 left-1/2 transform -translate-x-1/2 bg-blue-500 text-white px-4 py-1 rounded-full text-sm font-medium">
                                Más Popular
                            </div>

                            <div class="text-center mb-6">
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">Profesional</h3>
                                <p class="text-gray-600 mb-4">Para negocios que quieren crecer en serio</p>
                                <div class="flex items-center justify-center">
                                    <span class="text-4xl font-bold text-gray-900">$16</span>
                                    <span class="text-gray-600 ml-2">/ mes</span>
                                </div>
                            </div>

                            <ul class="space-y-3 mb-8">
                                <li class="flex items-center text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Todo lo del plan Emprendedor
                                </li>
                                <li class="flex items-center text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Tienda online básica incluida
                                </li>
                                <li class="flex items-center text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Hasta 5 páginas personalizadas
                                </li>
                                <li class="flex items-center text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Integración con redes sociales
                                </li>
                                <li class="flex items-center text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Estadísticas de visitantes
                                </li>
                                <li class="flex items-center text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Respaldos automáticos diarios
                                </li>
                            </ul>

                            <button @click="selectPlanFromModal('professional')"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold transition-colors">
                                Elegir Profesional
                            </button>
                        </div>

                        <!-- Plan Negocio -->
                        <div class="bg-white rounded-xl p-6 border border-gray-200 relative">
                            <div class="text-center mb-6">
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">Negocio</h3>
                                <p class="text-gray-600 mb-4">Para empresas que buscan resultados serios</p>
                                <div class="flex items-center justify-center">
                                    <span class="text-4xl font-bold text-gray-900">$22</span>
                                    <span class="text-gray-600 ml-2">/ mes</span>
                                </div>
                            </div>

                            <ul class="space-y-3 mb-8">
                                <li class="flex items-center text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Todo lo del plan Profesional
                                </li>
                                <li class="flex items-center text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Tienda online avanzada
                                </li>
                                <li class="flex items-center text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Academia virtual incluida
                                </li>
                                <li class="flex items-center text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Páginas ilimitadas
                                </li>
                                <li class="flex items-center text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Chat en vivo con clientes
                                </li>
                                <li class="flex items-center text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Soporte prioritario por teléfono
                                </li>
                            </ul>

                            <button @click="selectPlanFromModal('business')"
                                class="w-full bg-gray-800 hover:bg-gray-900 text-white py-3 rounded-lg font-semibold transition-colors">
                                Impulsar Mi Negocio
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Color beige/marrón personalizado para emprendedores */
.bg-brown-custom {
    background-color: #fef7ed;
    /* Color beige claro como en la imagen */
}

.text-brown-custom {
    color: #ea580c;
    /* Color naranja/marrón para el texto */
}

.border-brown-custom {
    border-color: #92400e;
}

.bg-brown-custom-dark {
    background-color: #ea580c;
}
</style>

<script setup>
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import WhatsAppButton from '@/Components/WhatsAppButton.vue'
import Footer from '@/Components/Footer.vue'

const props = defineProps({
    salesData: Object,
    focusedUseCase: Object,
    canLogin: Boolean,
    canRegister: Boolean,
})

const selectedUseCase = ref(null)
const selectedPlan = ref(null)
const showPlanModal = ref(false)
const showPlanModal = ref(false)

const scrollToUseCases = () => {
    document.getElementById('use-cases').scrollIntoView({
        behavior: 'smooth'
    })
}

const selectUseCase = (useCaseId) => {
    selectedUseCase.value = useCaseId
    showPlanModal.value = true
}

const getUseCasePageUrl = (useCaseId) => {
    const urls = {
        'educators': route('sales.educators'),
        'small-business': route('sales.small-business'),
        'entrepreneurs': route('sales.entrepreneurs'),
        'professionals': route('sales.professionals'),
        'web-designers': route('sales.web-designers'),
        'technical-resellers': route('sales.technical-resellers')
    }
    return urls[useCaseId] || route('sales.home')
}

const getDashboardRoute = () => {
    const user = props.auth?.user
    if (!user) return route('login')

    switch (user.role) {
        case 'admin':
            return route('admin.dashboard')
        case 'reseller':
            return route('reseller.dashboard')
        case 'client':
        default:
            // Ahora usar ruta normal
            return route('client.dashboard')
    }
}

const selectPlan = (planId) => {
    selectedPlan.value = planId

    if (!selectedUseCase.value) {
        // If no use case selected, show alert and scroll back to use cases
        alert('Por favor, primero selecciona para qué quieres usar tu sitio web en la sección anterior.')
        scrollToUseCases()
        return
    }

    // Start purchase flow
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
    useCaseInput.value = selectedUseCase.value
    form.appendChild(useCaseInput)

    // Add plan
    const planInput = document.createElement('input')
    planInput.type = 'hidden'
    planInput.name = 'plan'
    planInput.value = planId
    form.appendChild(planInput)

    document.body.appendChild(form)
    form.submit()
}

const selectPlanFromModal = (planId) => {
    selectedPlan.value = planId
    showPlanModal.value = false

    // Start purchase flow
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
    useCaseInput.value = selectedUseCase.value
    form.appendChild(useCaseInput)

    // Add plan
    const planInput = document.createElement('input')
    planInput.type = 'hidden'
    planInput.name = 'plan'
    planInput.value = planId
    form.appendChild(planInput)

    document.body.appendChild(form)
    form.submit()
}
</script>
