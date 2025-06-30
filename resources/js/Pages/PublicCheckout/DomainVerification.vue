<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-purple-50">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-4xl mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">Fj Group CA</h1>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-semibold">1</div>
                            <span class="text-blue-600 font-medium">Dominio</span>
                        </div>
                        <div class="w-8 border-t border-gray-300"></div>
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-gray-200 text-gray-500 rounded-full flex items-center justify-center text-sm">2</div>
                            <span class="text-gray-500">Registro</span>
                        </div>
                        <div class="w-8 border-t border-gray-300"></div>
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-gray-200 text-gray-500 rounded-full flex items-center justify-center text-sm">3</div>
                            <span class="text-gray-500">Pago</span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-4xl mx-auto px-6 py-12">
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <!-- Progress Context -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-medium mb-4">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Plan {{ purchaseContext.plan }} seleccionado para {{ getUseCaseLabel(purchaseContext.use_case) }}
                    </div>
                    
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">
                        {{ getCurrentMessage('domain_title') }}
                    </h2>
                    <p class="text-lg text-gray-600">
                        {{ getCurrentMessage('domain_subtitle') }}
                    </p>
                </div>

                <!-- Domain Form -->
                <form @submit.prevent="submitDomain" class="space-y-6">
                    <!-- Domain Input -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nombre de dominio
                        </label>
                        <div class="flex">
                            <input
                                v-model="form.domain"
                                type="text"
                                placeholder="miempresa"
                                class="flex-1 px-4 py-3 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                                :class="{ 'border-red-500': errors.domain }"
                                required
                            />
                            <select
                                v-model="selectedTld"
                                class="px-4 py-3 border-l-0 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-lg"
                            >
                                <option value=".com">.com</option>
                                <option value=".net">.net</option>
                                <option value=".org">.org</option>
                                <option value=".info">.info</option>
                            </select>
                        </div>
                        <p v-if="errors.domain" class="mt-1 text-sm text-red-600">{{ errors.domain }}</p>
                        <p class="mt-2 text-sm text-gray-500">
                            Ejemplo: {{ getExampleDomain() }}
                        </p>
                    </div>

                    <!-- Domain Action -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            ¿Qué quieres hacer con este dominio?
                        </label>
                        <div class="space-y-3">
                            <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                                <input
                                    v-model="form.action"
                                    type="radio"
                                    value="register"
                                    class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                />
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        Registrar este dominio nuevo
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Perfecto si es la primera vez que usas este nombre
                                    </div>
                                </div>
                            </label>

                            <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                                <input
                                    v-model="form.action"
                                    type="radio"
                                    value="transfer"
                                    class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                />
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        Transferir mi dominio existente
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Ya tengo este dominio en otro proveedor
                                    </div>
                                </div>
                            </label>

                            <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                                <input
                                    v-model="form.action"
                                    type="radio"
                                    value="existing"
                                    class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                />
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        Usar mi dominio existente
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Solo cambiaré los DNS para apuntar aquí
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Domain Availability Check -->
                    <div v-if="form.action === 'register' && fullDomain" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div v-if="checking" class="flex items-center text-blue-600">
                            <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Verificando disponibilidad...
                        </div>
                        <div v-else-if="availability" class="flex items-center">
                            <svg v-if="availability.available" class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <svg v-else class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <span :class="availability.available ? 'text-green-700' : 'text-red-700'" class="font-medium">
                                {{ availability.available ? '¡Disponible!' : 'No disponible' }}
                                <span v-if="availability.available && availability.price" class="text-gray-600">
                                    - ${{ availability.price }}/año
                                </span>
                            </span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-between items-center pt-6">
                        <Link :href="route('sales.home')" 
                              class="text-gray-600 hover:text-gray-800 font-medium">
                            ← Volver a planes
                        </Link>
                        
                        <button
                            type="submit"
                            :disabled="processing || (form.action === 'register' && availability && !availability.available)"
                            class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <span v-if="processing">Procesando...</span>
                            <span v-else>Continuar →</span>
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
    purchaseContext: Object,
    product: Object,
    useCaseMessages: Object,
    errors: Object,
})

const form = useForm({
    domain: '',
    action: 'register'
})

const selectedTld = ref('.com')
const checking = ref(false)
const availability = ref(null)
const processing = ref(false)

const fullDomain = computed(() => {
    return form.domain ? form.domain + selectedTld.value : ''
})

const getCurrentMessage = (key) => {
    const useCase = props.purchaseContext.use_case
    return props.useCaseMessages[useCase]?.[key] || ''
}

const getUseCaseLabel = (useCase) => {
    const labels = {
        'educators': 'Educadores',
        'small-business': 'Pequeños Negocios',
        'entrepreneurs': 'Emprendedores',
        'professionals': 'Profesionales'
    }
    return labels[useCase] || useCase
}

const getExampleDomain = () => {
    const examples = {
        'educators': 'academiaonline.com',
        'small-business': 'mipyme.com',
        'entrepreneurs': 'mitienda.com',
        'professionals': 'miportafolio.com'
    }
    return examples[props.purchaseContext.use_case] || 'midominio.com'
}

// Watch for domain changes to check availability
watch([() => form.domain, selectedTld], async () => {
    if (form.action === 'register' && form.domain && form.domain.length > 2) {
        await checkDomainAvailability()
    }
}, { debounce: 500 })

const checkDomainAvailability = async () => {
    if (!fullDomain.value) return
    
    checking.value = true
    availability.value = null
    
    try {
        const response = await axios.get(route('api.domain.checkAvailability'), {
            params: { domain: fullDomain.value }
        })
        
        if (response.data.status === 'success') {
            availability.value = response.data.data
        }
    } catch (error) {
        console.error('Error checking domain availability:', error)
    } finally {
        checking.value = false
    }
}

const submitDomain = () => {
    processing.value = true
    
    form.domain = fullDomain.value
    form.post(route('public.checkout.domain.process'), {
        onFinish: () => {
            processing.value = false
        }
    })
}
</script>
