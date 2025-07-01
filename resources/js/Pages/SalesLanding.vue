<template>
    <div class="min-h-screen bg-white">
        <!-- Hero Section -->
        <section class="relative overflow-hidden bg-gradient-to-br from-blue-50 via-white to-purple-50">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600/5 to-purple-600/5"></div>

            <!-- Navigation -->
            <nav class="relative z-10 px-4 py-4 sm:px-6">
                <div class="flex items-center justify-between mx-auto max-w-7xl">
                    <Link :href="route('sales.home')" class="flex items-center transition-opacity hover:opacity-80">
                    <img src="/images/logo.png" alt="Fj Group CA" class="w-auto h-6 mr-2 sm:h-8 sm:mr-3">
                    <span class="text-lg font-bold text-gray-900 sm:text-2xl">{{ salesData.appName }}</span>
                    </Link>
                    <div class="flex space-x-2 sm:space-x-4">
                        <!-- Si está autenticado, mostrar Dashboard -->
                        <Link v-if="auth?.user" :href="getDashboardRoute()"
                            class="text-sm font-medium text-gray-600 hover:text-gray-900 sm:text-base">
                        Dashboard
                        </Link>
                        <!-- Si no está autenticado, mostrar Login -->
                        <Link v-else-if="canLogin" :href="route('login')"
                            class="text-sm font-medium text-gray-600 hover:text-gray-900 sm:text-base">
                        Iniciar Sesión
                        </Link>

                    </div>
                </div>
            </nav>

            <!-- Hero Content -->
            <div class="relative z-10 px-4 py-12 mx-auto max-w-7xl sm:px-6 sm:py-20">
                <div class="text-center">
                    <h1
                        class="px-2 mb-4 text-3xl font-bold leading-tight text-gray-900 sm:text-4xl md:text-5xl lg:text-6xl sm:mb-6">
                        {{ salesData.heroSection.title }}
                    </h1>
                    <p class="max-w-3xl px-4 mx-auto mb-6 text-lg text-gray-600 sm:text-xl md:text-2xl sm:mb-8">
                        {{ salesData.heroSection.subtitle }}
                    </p>

                    <!-- Trust Indicators -->
                    <div class="flex flex-wrap justify-center gap-8 mb-10">
                        <div v-for="indicator in salesData.heroSection.trustIndicators" :key="indicator.text"
                            class="flex items-center space-x-2">
                            <div class="flex items-center justify-center w-6 h-6 bg-green-100 rounded-full">
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span :class="indicator.color" class="font-medium">{{ indicator.text }}</span>
                        </div>
                    </div>

                    <button @click="showPlanModal = true"
                        class="px-8 py-4 text-lg font-semibold text-white transition-all duration-200 transform bg-blue-600 shadow-lg rounded-xl hover:bg-blue-700 hover:scale-105">
                        {{ salesData.heroSection.ctaButtonText }}
                    </button>
                </div>
            </div>
        </section>

        <!-- Use Cases Section -->
        <section id="use-cases" class="py-12 sm:py-20 bg-gray-50">
            <div class="px-4 mx-auto max-w-7xl sm:px-6">
                <div class="mb-8 text-center sm:mb-16">
                    <h2 class="px-4 mb-3 text-2xl font-bold text-gray-900 sm:text-3xl md:text-4xl sm:mb-4">
                        ¿Cuál es tu Objetivo?
                    </h2>
                    <p class="max-w-2xl px-4 mx-auto text-lg text-gray-600 sm:text-xl">
                        Elige la opción que mejor describe lo que quieres lograr
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 sm:gap-8">
                    <div v-for="useCase in salesData.useCases" :key="useCase.id" :class="useCase.color"
                        class="p-4 transition-all duration-300 transform border border-gray-200 cursor-pointer rounded-2xl sm:p-6 md:p-8 hover:shadow-xl hover:-translate-y-2">

                        <div class="flex items-center mb-4 sm:mb-6">
                            <div :class="useCase.accentColor"
                                class="flex items-center justify-center w-10 h-10 mr-3 bg-white rounded-lg sm:w-12 sm:h-12 sm:mr-4">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <!-- Icon placeholder - you can add specific icons here -->
                                    <path d="M12 2L2 7v10c0 5.55 3.84 9.74 9 11 5.16-1.26 9-5.45 9-11V7l-10-5z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 sm:text-2xl">{{ useCase.title }}</h3>
                            </div>
                        </div>

                        <h4 :class="useCase.accentColor" class="mb-2 text-lg font-semibold sm:text-xl sm:mb-3">
                            {{ useCase.headline }}
                        </h4>

                        <p class="mb-4 text-sm text-gray-600 sm:mb-6 sm:text-base">
                            {{ useCase.description }}
                        </p>

                        <ul class="mb-6 space-y-2">
                            <li v-for="benefit in useCase.benefits" :key="benefit"
                                class="flex items-center text-gray-700">
                                <svg class="w-5 h-5 mr-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                {{ benefit }}
                            </li>
                        </ul>

                        <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-3">
                            <button @click="selectUseCase(useCase.id)" :class="getButtonClass(useCase.accentColor)"
                                class="flex-1 py-2 text-sm font-semibold text-white transition-opacity rounded-lg sm:py-3 hover:opacity-90 sm:text-base">
                                {{ useCase.ctaText }}
                            </button>
                            <Link :href="getUseCasePageUrl(useCase.id)"
                                class="px-3 py-2 text-sm font-medium text-center text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm sm:px-4 sm:py-3 hover:bg-gray-50 sm:text-base">
                            Ver más
                            </Link>
                        </div>

                        <!-- Testimonial -->
                        <div v-if="useCase.testimonial" class="p-4 mt-6 bg-white border-l-4 rounded-lg"
                            :class="useCase.accentColor.replace('text-', 'border-')">
                            <p class="mb-2 italic text-gray-600">"{{ useCase.testimonial.text }}"</p>
                            <p class="text-sm font-medium text-gray-800">- {{ useCase.testimonial.author }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why Choose Us Section -->
        <section class="py-20 bg-white">
            <div class="px-6 mx-auto max-w-7xl">
                <div class="mb-16 text-center">
                    <h2 class="mb-4 text-4xl font-bold text-gray-900">
                        {{ salesData.whyChooseUs.title }}
                    </h2>
                    <p class="max-w-2xl mx-auto text-xl text-gray-600">
                        {{ salesData.whyChooseUs.subtitle }}
                    </p>
                </div>

                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                    <div v-for="feature in salesData.whyChooseUs.features" :key="feature.title" class="text-center">
                        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-full">
                            <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <!-- Icon placeholder -->
                                <path d="M12 2L2 7v10c0 5.55 3.84 9.74 9 11 5.16-1.26 9-5.45 9-11V7l-10-5z" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-xl font-semibold text-gray-900">{{ feature.title }}</h3>
                        <p class="text-gray-600">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="py-20 bg-white">
            <div class="px-6 mx-auto max-w-7xl">
                <div class="mb-16 text-center">
                    <h2 class="mb-4 text-4xl font-bold text-gray-900">
                        {{ salesData.testimonials.title }}
                    </h2>
                    <p class="max-w-2xl mx-auto text-xl text-gray-600">
                        {{ salesData.testimonials.subtitle }}
                    </p>
                </div>

                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="review in salesData.testimonials.reviews" :key="review.name"
                        class="p-6 transition-shadow bg-white border border-gray-200 shadow-lg rounded-xl hover:shadow-xl">

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
                        <p class="mb-4 italic text-gray-700">"{{ review.text }}"</p>

                        <!-- Reviewer Info -->
                        <div class="pt-4 border-t">
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
            <div class="px-6 mx-auto max-w-7xl">
                <div class="mb-16 text-center">
                    <h2 class="mb-4 text-4xl font-bold text-white">
                        {{ salesData.forDesigners.title }}
                    </h2>
                    <p class="max-w-2xl mx-auto text-xl text-purple-100">
                        {{ salesData.forDesigners.subtitle }}
                    </p>
                </div>

                <div class="grid gap-8 mb-12 md:grid-cols-2 lg:grid-cols-4">
                    <div v-for="benefit in salesData.forDesigners.benefits" :key="benefit.title" class="text-center">
                        <div
                            class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-white rounded-full bg-opacity-20">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <!-- Icon placeholder -->
                                <path d="M12 2L2 7v10c0 5.55 3.84 9.74 9 11 5.16-1.26 9-5.45 9-11V7l-10-5z" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-xl font-semibold text-white">{{ benefit.title }}</h3>
                        <p class="text-purple-100">{{ benefit.description }}</p>
                    </div>
                </div>

                <div class="text-center">
                    <button @click="selectUseCase('web-designers')"
                        class="px-8 py-4 text-lg font-semibold text-purple-600 transition-all duration-200 transform bg-white shadow-lg rounded-xl hover:bg-gray-100 hover:scale-105">
                        {{ salesData.forDesigners.cta }}
                    </button>
                </div>
            </div>
        </section>


        <!-- Guarantee Section -->
        <section class="py-16 bg-blue-600">
            <div class="max-w-4xl px-6 mx-auto text-center">
                <div class="flex items-center justify-center mb-6">
                    <div class="flex items-center justify-center w-16 h-16 bg-white rounded-full">
                        <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7v10c0 5.55 3.84 9.74 9 11 5.16-1.26 9-5.45 9-11V7l-10-5z" />
                        </svg>
                    </div>
                </div>
                <h2 class="mb-4 text-3xl font-bold text-white">{{ salesData.guarantee.title }}</h2>
                <p class="text-xl text-blue-100">{{ salesData.guarantee.description }}</p>
            </div>
        </section>

        <!-- Footer -->
        <Footer />

        <!-- WhatsApp Button -->
        <WhatsAppButton />

        <!-- Modal de Selección de Planes -->
        <div v-if="showPlanModal" @click="closeModalOnBackdrop"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
            <div @click.stop class="bg-white rounded-2xl max-w-6xl w-full max-h-[95vh] overflow-y-auto relative">
                <!-- Botón de cerrar más grande y visible -->
                <button @click="showPlanModal = false"
                    class="absolute z-10 p-2 text-gray-600 transition-colors bg-gray-100 rounded-full top-4 right-4 hover:bg-gray-200 hover:text-gray-800">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>

                <div class="p-8">
                    <div class="mb-20 text-center">
                        <h2 class="mb-2 text-3xl font-bold text-gray-900">{{ salesData.pricing.title }}</h2>
                        <p class="text-lg text-gray-600">{{ salesData.pricing.subtitle }}</p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-3">
                        <!-- Planes dinámicos desde JSON -->
                        <div v-for="plan in salesData.pricing.plans" :key="plan.id"
                            :class="plan.popular ? 'ring-2 ring-blue-500 scale-105' : ''"
                            class="relative p-6 bg-white border border-gray-200 rounded-xl">

                            <div v-if="plan.popular"
                                class="absolute px-4 py-2 text-sm font-medium text-white transform -translate-x-1/2 bg-blue-500 rounded-full shadow-lg -top-4 left-1/2">
                                Más Popular
                            </div>

                            <div class="mb-6 text-center">
                                <h3 class="mb-2 text-2xl font-bold text-gray-900">{{ plan.name }}</h3>
                                <p class="mb-4 text-gray-600">{{ plan.description }}</p>
                                <div class="flex items-center justify-center">
                                    <span class="text-4xl font-bold text-gray-900">${{ plan.price }}</span>
                                    <span class="ml-2 text-gray-600">/ {{ plan.period }}</span>
                                </div>
                            </div>

                            <ul class="mb-8 space-y-3">
                                <li v-for="feature in plan.features" :key="feature"
                                    class="flex items-start text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    <span v-html="feature"></span>
                                </li>
                            </ul>

                            <button @click="selectPlanFromModal(plan.id)"
                                :class="plan.popular ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-800 hover:bg-gray-900'"
                                class="w-full py-3 font-semibold text-white transition-colors rounded-lg">
                                {{ plan.ctaText }}
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
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import WhatsAppButton from '@/Components/WhatsAppButton.vue'
import Footer from '@/Components/Footer.vue'

const props = defineProps({
    salesData: Object,
    focusedUseCase: Object,
    canLogin: Boolean,
    canRegister: Boolean,
    auth: Object,
})

const selectedUseCase = ref(null)
const selectedPlan = ref(null)
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

const getButtonClass = (accentColor) => {
    // Map text colors to background colors
    const colorMap = {
        'text-blue-600': 'bg-blue-600',
        'text-green-600': 'bg-green-600',
        'text-purple-600': 'bg-purple-600',
        'text-brown-custom': 'bg-orange-600', // Fallback to orange for brown-custom
        'text-orange-600': 'bg-orange-600'
    }

    return colorMap[accentColor] || 'bg-blue-600' // Default fallback
}

const closeModalOnBackdrop = (event) => {
    // Solo cerrar si el clic fue en el backdrop, no en el contenido del modal
    if (event.target === event.currentTarget) {
        showPlanModal.value = false
    }
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

    if (!selectedUseCase.value) {
        // If no use case selected, default to entrepreneurs (most common)
        selectedUseCase.value = 'entrepreneurs'
        console.log('🔍 No use case selected, defaulting to:', selectedUseCase.value)
    }

    console.log('🔍 About to send request with:', {
        use_case: selectedUseCase.value,
        plan: planId
    })

    showPlanModal.value = false

    // Use Inertia router to avoid CSRF issues
    router.post(route('sales.start-purchase'), {
        use_case: selectedUseCase.value,
        plan: planId
    })
}
</script>
