<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-purple-50">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-4xl mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">Fj Group CA</h1>
                    <div class="text-sm text-gray-600">
                        Paso 1 de 3: Crear cuenta
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-2xl mx-auto px-6 py-12">
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <!-- Welcome Message -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-medium mb-4">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Plan {{ salesContext.plan }} para {{ getUseCaseLabel() }}
                    </div>
                    
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">
                        {{ salesContext.messages.welcome_title }}
                    </h2>
                    <p class="text-lg text-gray-600">
                        {{ salesContext.messages.welcome_subtitle }}
                    </p>
                </div>

                <!-- Registration Form -->
                <form @submit.prevent="submitRegistration" class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre completo *
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                                :class="{ 'border-red-500': errors.name }"
                                placeholder="Tu nombre completo"
                            />
                            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Correo electrónico *
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                                :class="{ 'border-red-500': errors.email }"
                                placeholder="tu@email.com"
                            />
                            <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Contraseña *
                            </label>
                            <input
                                v-model="form.password"
                                type="password"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                                :class="{ 'border-red-500': errors.password }"
                                placeholder="Mínimo 8 caracteres"
                            />
                            <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
                        </div>

                        <!-- Password Confirmation -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Confirmar contraseña *
                            </label>
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                                placeholder="Repite tu contraseña"
                            />
                        </div>

                        <!-- Company Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ getCompanyLabel() }}
                            </label>
                            <input
                                v-model="form.company_name"
                                type="text"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                                :placeholder="getCompanyPlaceholder()"
                            />
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Teléfono
                            </label>
                            <input
                                v-model="form.phone"
                                type="tel"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                                placeholder="+58 412 1234567"
                            />
                        </div>
                    </div>

                    <!-- Country -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            País *
                        </label>
                        <select
                            v-model="form.country"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                            :class="{ 'border-red-500': errors.country }"
                        >
                            <option value="">Selecciona tu país</option>
                            <option value="VE">Venezuela</option>
                            <option value="CO">Colombia</option>
                            <option value="PE">Perú</option>
                            <option value="EC">Ecuador</option>
                            <option value="PA">Panamá</option>
                            <option value="MX">México</option>
                            <option value="AR">Argentina</option>
                            <option value="CL">Chile</option>
                            <option value="US">Estados Unidos</option>
                            <option value="ES">España</option>
                        </select>
                        <p v-if="errors.country" class="mt-1 text-sm text-red-600">{{ errors.country }}</p>
                    </div>

                    <!-- Terms and Privacy -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="flex items-start">
                            <input
                                v-model="acceptTerms"
                                type="checkbox"
                                required
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-1"
                            />
                            <span class="ml-3 text-sm text-gray-700">
                                Acepto los 
                                <a href="#" class="text-blue-600 hover:text-blue-800 underline">términos y condiciones</a>
                                y la 
                                <a href="#" class="text-blue-600 hover:text-blue-800 underline">política de privacidad</a>
                            </span>
                        </label>
                    </div>

                    <!-- Next Steps Preview -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <h4 class="font-semibold text-blue-900 mb-3 text-lg">
                            🎉 {{ getNextStepsTitle() }}
                        </h4>
                        <div class="space-y-2 text-blue-800">
                            <div class="flex items-center">
                                <span class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-semibold mr-3">1</span>
                                <span>Crear tu cuenta (estás aquí)</span>
                            </div>
                            <div class="flex items-center">
                                <span class="w-6 h-6 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-semibold mr-3">2</span>
                                <span>{{ salesContext.messages.domain_help }}</span>
                            </div>
                            <div class="flex items-center">
                                <span class="w-6 h-6 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-semibold mr-3">3</span>
                                <span>{{ salesContext.messages.next_steps }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-between items-center pt-6">
                        <Link :href="route('sales.home')" 
                              class="text-gray-600 hover:text-gray-800 font-medium">
                            ← Cambiar plan
                        </Link>
                        
                        <button
                            type="submit"
                            :disabled="processing || !acceptTerms"
                            class="bg-blue-600 text-white px-8 py-4 rounded-lg font-semibold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-lg"
                        >
                            <span v-if="processing">Creando cuenta...</span>
                            <span v-else>Crear mi cuenta y continuar →</span>
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
    salesContext: Object,
    errors: Object,
})

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    company_name: '',
    phone: '',
    country: 'VE'
})

const acceptTerms = ref(false)
const processing = ref(false)

const getUseCaseLabel = () => {
    const labels = {
        'educators': 'Educadores',
        'small-business': 'Pequeños Negocios',
        'entrepreneurs': 'Emprendedores',
        'professionals': 'Profesionales'
    }
    return labels[props.salesContext.use_case] || props.salesContext.use_case
}

const getCompanyLabel = () => {
    const labels = {
        'educators': 'Academia/Institución',
        'small-business': 'Empresa/Negocio',
        'entrepreneurs': 'Empresa/Marca',
        'professionals': 'Empresa (opcional)'
    }
    return labels[props.salesContext.use_case] || 'Empresa/Organización'
}

const getCompanyPlaceholder = () => {
    const placeholders = {
        'educators': 'Academia de Inglés',
        'small-business': 'Mi Empresa',
        'entrepreneurs': 'Mi Tienda',
        'professionals': 'Freelancer'
    }
    return placeholders[props.salesContext.use_case] || 'Nombre de tu empresa'
}

const getNextStepsTitle = () => {
    const titles = {
        'educators': '¡Tu academia estará lista en 3 pasos!',
        'small-business': '¡Tu sitio web estará listo en 3 pasos!',
        'entrepreneurs': '¡Tu tienda online estará lista en 3 pasos!',
        'professionals': '¡Tu portafolio estará listo en 3 pasos!'
    }
    return titles[props.salesContext.use_case] || '¡Tu sitio estará listo en 3 pasos!'
}

const submitRegistration = () => {
    processing.value = true
    
    form.post(route('public.register.with-context.process'), {
        onFinish: () => {
            processing.value = false
        }
    })
}
</script>
