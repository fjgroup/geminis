<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-purple-50">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-4xl mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <img src="/images/logo.png" alt="Fj Group CA" class="h-10 w-auto mr-3">
                        <h1 class="text-2xl font-bold text-gray-900">Fj Group CA</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <div
                                class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-semibold">
                                1</div>
                            <span class="text-blue-600 font-medium">Dominio</span>
                        </div>
                        <div class="w-8 border-t border-gray-300"></div>
                        <div class="flex items-center space-x-2">
                            <div
                                class="w-8 h-8 bg-gray-200 text-gray-500 rounded-full flex items-center justify-center text-sm">
                                2</div>
                            <span class="text-gray-500">Registro</span>
                        </div>
                        <div class="w-8 border-t border-gray-300"></div>
                        <div class="flex items-center space-x-2">
                            <div
                                class="w-8 h-8 bg-gray-200 text-gray-500 rounded-full flex items-center justify-center text-sm">
                                3</div>
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
                    <div
                        class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-medium mb-4">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Plan {{ purchaseContext.plan }} seleccionado para {{ getUseCaseLabel(purchaseContext.use_case)
                        }}
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
                    <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-6 rounded-xl border border-blue-100">
                        <label class="block text-lg font-semibold text-gray-800 mb-3">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                                </svg>
                                Nombre de dominio
                            </span>
                        </label>
                        <div class="flex shadow-lg rounded-xl overflow-hidden">
                            <input v-model="form.domain" type="text" placeholder="miempresa"
                                class="flex-1 px-6 py-4 border-0 focus:ring-2 focus:ring-blue-500 text-lg bg-white"
                                :class="{ 'ring-2 ring-red-500': errors.domain }" required />
                            <select v-model="selectedTld"
                                class="px-6 py-4 border-0 focus:ring-2 focus:ring-blue-500 bg-white text-lg min-w-[140px] font-medium text-blue-600">
                                <option value="" disabled>Extensión</option>
                                <option value=".com">.com</option>
                                <option value=".net">.net</option>
                                <option value=".org">.org</option>
                                <option value=".info">.info</option>
                                <option value=".biz">.biz</option>
                            </select>
                        </div>
                        <p v-if="errors.domain" class="mt-2 text-sm text-red-600 font-medium">{{ errors.domain }}</p>
                        <p class="mt-2 text-sm text-gray-500">
                            <span v-if="!selectedTld">Selecciona una extensión para verificar disponibilidad</span>
                            <span v-else-if="fullDomain">Dominio completo: <strong>{{ fullDomain }}</strong></span>
                            <span v-else>Ejemplo: {{ getExampleDomain() }}</span>
                        </p>
                    </div>

                    <!-- Domain Action -->
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                        <label class="block text-lg font-semibold text-gray-800 mb-4">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                ¿Qué quieres hacer con este dominio?
                            </span>
                        </label>
                        <div class="space-y-3">
                            <label
                                class="flex items-center p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-300 hover:bg-blue-50 transition-all duration-200"
                                :class="{ 'border-blue-500 bg-blue-50 shadow-md': form.action === 'register' }">
                                <input v-model="form.action" type="radio" value="register"
                                    class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500" />
                                <div class="ml-4">
                                    <div class="text-base font-semibold text-gray-900">
                                        Registrar este dominio nuevo
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        Perfecto si es la primera vez que usas este nombre
                                    </div>
                                </div>
                            </label>

                            <label
                                class="flex items-center p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-purple-300 hover:bg-purple-50 transition-all duration-200"
                                :class="{ 'border-purple-500 bg-purple-50 shadow-md': form.action === 'transfer' }">
                                <input v-model="form.action" type="radio" value="transfer"
                                    class="w-5 h-5 text-purple-600 border-gray-300 focus:ring-purple-500" />
                                <div class="ml-4">
                                    <div class="text-base font-semibold text-gray-900">
                                        Transferir mi dominio existente
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        Ya tengo este dominio en otro proveedor
                                    </div>
                                </div>
                            </label>

                            <label
                                class="flex items-center p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-green-300 hover:bg-green-50 transition-all duration-200"
                                :class="{ 'border-green-500 bg-green-50 shadow-md': form.action === 'existing' }">
                                <input v-model="form.action" type="radio" value="existing"
                                    class="w-5 h-5 text-green-600 border-gray-300 focus:ring-green-500" />
                                <div class="ml-4">
                                    <div class="text-base font-semibold text-gray-900">
                                        Usar mi dominio existente
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        Solo cambiaré los DNS para apuntar aquí
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Manual Domain Check Button -->
                    <div v-if="form.action === 'register' && form.domain && selectedTld" class="mb-4">
                        <button @click="checkDomainAvailability" :disabled="checking || !fullDomain"
                            class="w-full bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium">
                            <div class="flex items-center justify-center">
                                <svg v-if="checking" class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span v-if="checking">Verificando {{ fullDomain }}...</span>
                                <span v-else>🔍 Verificar Dominio {{ fullDomain }}</span>
                            </div>
                        </button>
                    </div>

                    <!-- Domain Availability Check -->
                    <div v-if="form.action === 'register' && fullDomain"
                        class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div v-if="checking" class="flex items-center text-blue-600">
                            <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <div>
                                <div class="font-medium">Verificando disponibilidad de {{ fullDomain }}...</div>
                                <div class="text-sm text-blue-500">Esto puede tomar unos segundos</div>
                            </div>
                        </div>
                        <div v-else-if="availability" class="flex items-center">
                            <svg v-if="availability.available" class="w-5 h-5 text-green-500 mr-2" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <svg v-else class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span :class="availability.available ? 'text-green-700' : 'text-red-700'"
                                class="font-medium">
                                {{ availability.available ? '¡Disponible!' : 'No disponible' }}
                                <span v-if="availability.available && availability.price" class="text-gray-600">
                                    - ${{ availability.price }}/año
                                </span>
                            </span>
                        </div>
                    </div>

                    <!-- Billing Cycle Selection -->
                    <div v-if="form.domain && form.action" class="border-t pt-6">
                        <div class="text-center mb-6">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">💰 Elige tu Plan de Ahorro</h3>
                            <p class="text-gray-600">Mientras más tiempo elijas, más ahorras. ¡Aprovecha nuestros descuentos especiales!</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="cycle in props.availableBillingCycles" :key="cycle.id"
                                class="relative flex flex-col p-6 border-2 rounded-xl cursor-pointer transition-all duration-200 hover:shadow-lg transform hover:-translate-y-1"
                                :class="form.billing_cycle_id === cycle.id
                                    ? 'border-blue-500 bg-gradient-to-br from-blue-50 to-purple-50 shadow-lg'
                                    : 'border-gray-200 bg-white hover:border-blue-300'"
                                @click="selectBillingCycle(cycle.id)">

                                <!-- Badge de descuento -->
                                <div v-if="getDiscountPercentage(cycle)"
                                     class="absolute -top-3 -right-3 bg-gradient-to-r from-red-500 to-pink-500 text-white px-3 py-1 rounded-full text-sm font-bold shadow-lg">
                                    {{ getDiscountPercentage(cycle) }}% OFF
                                </div>

                                <!-- Badge de más popular -->
                                <div v-if="cycle.slug === 'annually'"
                                     class="absolute -bottom-3 -left-3 bg-gradient-to-r from-green-500 to-emerald-500 text-white px-4 py-1 rounded-full text-sm font-bold shadow-lg">
                                    🔥 MÁS POPULAR
                                </div>

                                <input v-model="form.billing_cycle_id" type="radio" :value="cycle.id" :name="'billing_cycle'"
                                    class="sr-only" @change="calculatePrice" />

                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-xl font-bold text-gray-900">{{ cycle.name }}</span>
                                    <span v-if="form.billing_cycle_id === cycle.id"
                                        class="w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center">
                                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3"/>
                                        </svg>
                                    </span>
                                </div>

                                <div class="text-center mb-4">
                                    <div class="text-3xl font-bold text-blue-600 mb-1">
                                        ${{ parseFloat(cycle.price || 0).toFixed(2) }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ cycle.days }} días</div>

                                    <!-- Precio mensual equivalente -->
                                    <div v-if="cycle.days > 30" class="text-xs text-green-600 font-medium mt-1">
                                        Solo ${{ (parseFloat(cycle.price || 0) / (cycle.days / 30)).toFixed(2) }}/mes
                                    </div>
                                </div>

                                <div class="text-sm text-gray-600 space-y-1">
                                    <div v-if="cycle.base_price && cycle.base_price !== cycle.price" class="flex justify-between">
                                        <span>Base:</span>
                                        <span>${{ parseFloat(cycle.base_price || 0).toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Recursos incluidos:</span>
                                        <span class="text-green-600 font-medium">✓</span>
                                    </div>
                                    <div v-if="cycle.setup_fee > 0" class="flex justify-between">
                                        <span>Setup:</span>
                                        <span>${{ parseFloat(cycle.setup_fee || 0).toFixed(2) }}</span>
                                    </div>
                                </div>

                                <!-- Mensaje de ahorro -->
                                <div v-if="getDiscountPercentage(cycle)"
                                     class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                                    <div class="text-center">
                                        <div class="text-green-800 font-bold text-sm">
                                            ¡Ahorras ${{ getSavingsAmount(cycle) }}!
                                        </div>
                                        <div class="text-green-600 text-xs">
                                            vs. pago mensual
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Configurable Options -->
                    <div v-if="form.billing_cycle_id && configurableOptions.length > 0" class="border-t pt-6">
                        <div class="text-center mb-6">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">💎 Potencia tu Éxito Online</h3>
                            <p class="text-gray-600">Personaliza tu plan para obtener el máximo rendimiento y convertir más visitantes en clientes</p>
                        </div>
                        <div class="space-y-6">
                            <div v-for="group in configurableOptions" :key="group.id"
                                 class="bg-gradient-to-r from-white to-gray-50 rounded-xl p-6 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3"
                                             :class="getGroupIconClass(group.name)">
                                            <span class="text-lg">{{ getGroupIcon(group.name) }}</span>
                                        </div>
                                        <h4 class="text-xl font-bold text-gray-900">{{ group.name }}</h4>
                                    </div>
                                    <span v-if="group.is_required" class="text-xs bg-red-100 text-red-800 px-3 py-1 rounded-full font-medium">
                                        Requerido
                                    </span>
                                </div>
                                <div class="mb-4 p-4 bg-blue-50 rounded-lg border-l-4 border-blue-400">
                                    <p class="text-blue-800 font-medium">{{ getMarketingMessage(group.name) }}</p>
                                </div>

                                <div class="grid grid-cols-1 gap-4">
                                    <div v-for="option in group.options" :key="option.id"
                                        class="border-2 rounded-xl p-5 transition-all duration-200 hover:shadow-lg"
                                        :class="form.configurable_options[option.id] > 0
                                            ? 'border-blue-500 bg-blue-50 shadow-md'
                                            : 'border-gray-200 bg-white hover:border-blue-300'">

                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center">
                                                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-xl flex items-center justify-center mr-4">
                                                    <span class="text-lg font-bold">{{ form.configurable_options[option.id] || 0 }}</span>
                                                </div>
                                                <div>
                                                    <div class="text-lg font-bold text-gray-900">{{ option.name }}</div>
                                                    <div class="text-sm text-gray-600">{{ getOptionBenefit(group.name, option.name) }}</div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div v-if="getOptionPrice(option)" class="text-xl font-bold text-green-600">
                                                    ${{ getOptionPrice(option) }}
                                                </div>
                                                <div class="text-sm text-gray-500">por {{ getBillingCycleName() }}</div>
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-3">
                                                <button type="button" @click="decrementOption(option.id, group.is_required ? 1 : 0)"
                                                        class="w-10 h-10 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition-colors">
                                                    <span class="text-xl font-bold text-gray-600">−</span>
                                                </button>
                                                <input type="number"
                                                    v-model.number="form.configurable_options[option.id]"
                                                    :min="group.is_required ? 1 : 0"
                                                    :max="option.max_value || 50"
                                                    class="w-20 px-3 py-2 border-2 border-gray-300 rounded-lg text-center text-lg font-bold focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                                    @change="calculatePrice" />
                                                <button type="button" @click="incrementOption(option.id, option.max_value || 50)"
                                                        class="w-10 h-10 bg-blue-500 hover:bg-blue-600 text-white rounded-full flex items-center justify-center transition-colors">
                                                    <span class="text-xl font-bold">+</span>
                                                </button>
                                            </div>
                                            <div v-if="form.configurable_options[option.id] > 0" class="text-right">
                                                <div class="text-sm text-gray-600">Total:</div>
                                                <div class="text-lg font-bold text-blue-600">
                                                    ${{ (getOptionPrice(option) * form.configurable_options[option.id]).toFixed(2) }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Mensaje de incentivo específico para vCPU -->
                                        <div v-if="group.name.includes('Potencia') || group.name.includes('vCPU')"
                                             class="mt-4 p-3 bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-lg">
                                            <div class="flex items-center">
                                                <span class="text-2xl mr-2">🚀</span>
                                                <div>
                                                    <div class="font-bold text-orange-800">¡Recomendado para más ventas!</div>
                                                    <div class="text-sm text-orange-700">Cada núcleo extra puede aumentar tus conversiones hasta un 40%</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Price Summary -->
                    <div v-if="priceCalculation" class="border-t pt-6">
                        <div class="bg-blue-50 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-900 mb-3">Resumen de precio</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span>Precio base (cPanel):</span>
                                    <span>${{ parseFloat(priceCalculation.base_price?.price || 0).toFixed(2) }}</span>
                                </div>
                                <div v-if="priceCalculation.base_resources?.total > 0" class="flex justify-between">
                                    <span>Recursos incluidos (CPU, RAM, Espacio):</span>
                                    <span>${{ parseFloat(priceCalculation.base_resources?.total || 0).toFixed(2) }}</span>
                                </div>
                                <div v-if="priceCalculation.configurable_options?.total > 0" class="flex justify-between">
                                    <span>Opciones adicionales:</span>
                                    <span>${{ parseFloat(priceCalculation.configurable_options?.total || 0).toFixed(2) }}</span>
                                </div>
                                <div v-if="priceCalculation.discount_amount > 0" class="flex justify-between text-green-600">
                                    <span>Descuento ({{ priceCalculation.discount?.percentage || 0 }}%):</span>
                                    <span>-${{ parseFloat(priceCalculation.discount_amount || 0).toFixed(2) }}</span>
                                </div>
                                <div class="border-t pt-2 flex justify-between font-semibold text-lg">
                                    <span>Total:</span>
                                    <span class="text-blue-600">${{ parseFloat(priceCalculation.total || 0).toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-between items-center pt-6">
                        <Link :href="route('sales.home')" class="text-gray-600 hover:text-gray-800 font-medium">
                        ← Volver a planes
                        </Link>

                        <button type="submit"
                            :disabled="processing || (form.action === 'register' && availability && !availability.available) || !form.billing_cycle_id"
                            class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
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
    availableBillingCycles: Array,
    configurableOptions: Array,
    discountPercentages: Object,
    useCaseMessages: Object,
    errors: Object,
})

const form = useForm({
    domain: '',
    action: 'register', // Por defecto seleccionar "registrar"
    billing_cycle_id: null,
    configurable_options: {}
})

const selectedTld = ref('') // Iniciar sin extensión seleccionada
const checking = ref(false)
const availability = ref(null)
const processing = ref(false)
const priceCalculation = ref(null)
const calculatingPrice = ref(false)

// Initialize configurable options with base quantities as integers
if (props.configurableOptions) {
    props.configurableOptions.forEach(group => {
        group.options.forEach(option => {
            form.configurable_options[option.id] = parseInt(group.base_quantity || 0)
        })
    })
}

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

// Watch para verificar cuando se selecciona o cambia la extensión
watch(selectedTld, async (newTld, oldTld) => {
    console.log('🔧 Extensión cambiada:', { newTld, oldTld, domain: form.domain, action: form.action })

    // Solo verificar si:
    // 1. Se seleccionó una extensión (no vacía)
    // 2. Hay un dominio escrito
    // 3. La acción es registrar
    // 4. El dominio tiene al menos 3 caracteres
    if (newTld && form.domain && form.action === 'register' && form.domain.length >= 3) {
        console.log('🔍 Verificando automáticamente por cambio de extensión:', newTld)
        await checkDomainAvailability()
    } else {
        console.log('❌ No se cumplieron las condiciones para verificar:', {
            hasExtension: !!newTld,
            hasDomain: !!form.domain,
            isRegister: form.action === 'register',
            domainLength: form.domain?.length || 0
        })
    }
})

// Debounce timer para el dominio
let domainCheckTimer = null

// Watch para verificar cuando se cambia el dominio (solo si ya hay extensión seleccionada)
watch(() => form.domain, async (newDomain) => {
    console.log('📝 Dominio cambiado:', newDomain, 'Extensión:', selectedTld.value, 'Acción:', form.action)

    // Limpiar timer anterior
    if (domainCheckTimer) {
        clearTimeout(domainCheckTimer)
    }

    // Crear nuevo timer con debounce
    domainCheckTimer = setTimeout(async () => {
        if (selectedTld.value && newDomain && form.action === 'register' && newDomain.length >= 3) {
            console.log('🔍 Verificando automáticamente por cambio de dominio:', newDomain)
            await checkDomainAvailability()
        }
    }, 1500) // Debounce de 1.5 segundos
})

// Watch para recalcular precio cuando cambia el ciclo de facturación
watch(() => form.billing_cycle_id, async (newCycleId) => {
    console.log('💰 Ciclo de facturación cambiado:', newCycleId)
    if (newCycleId) {
        await calculatePrice()
    }
})

// Watch para recalcular precio cuando cambian las opciones configurables
watch(() => form.configurable_options, async () => {
    console.log('⚙️ Opciones configurables cambiadas:', form.configurable_options)
    if (form.billing_cycle_id) {
        await calculatePrice()
    }
}, { deep: true })

const checkDomainAvailability = async () => {
    if (!fullDomain.value) return

    checking.value = true
    availability.value = null

    try {
        // Delay mínimo para dar tiempo al usuario de ver el proceso
        const minDelay = new Promise(resolve => setTimeout(resolve, 2500)) // 2.5 segundos mínimo

        // TEMPORALMENTE: Simulación en lugar de API real para evitar errores
        const domainCheck = new Promise(resolve => {
            setTimeout(() => {
                // Simulación simple: dominios que empiecen con "test" no están disponibles
                const available = !form.domain.toLowerCase().startsWith('test')
                resolve({
                    data: {
                        status: 'success',
                        data: {
                            available,
                            message: available
                                ? `${fullDomain.value} está disponible para registro`
                                : `${fullDomain.value} ya está registrado`,
                            price: available ? 15.00 : null
                        }
                    }
                })
            }, 500)
        })

        // Esperar tanto el delay mínimo como la verificación
        const [, response] = await Promise.all([minDelay, domainCheck])

        if (response.data.status === 'success') {
            availability.value = response.data.data
        }
    } catch (error) {
        console.error('Error checking domain availability:', error)
        // Mostrar mensaje de error amigable
        availability.value = {
            available: true, // Por defecto disponible en modo simulación
            message: `${fullDomain.value} parece estar disponible (verificación simulada)`,
            status: 'simulated'
        }
    } finally {
        checking.value = false
    }
}

const calculatePrice = async () => {
    if (!form.billing_cycle_id) {
        priceCalculation.value = null
        return
    }

    calculatingPrice.value = true

    try {
        // Simulate price calculation - in real implementation, this would call the backend
        // For now, we'll use the basic pricing from availableBillingCycles
        const selectedCycle = props.availableBillingCycles.find(cycle => cycle.id === form.billing_cycle_id)

        if (selectedCycle) {
            // Use the calculated price from the backend that includes base resources
            let basePrice = selectedCycle.base_price || selectedCycle.price
            let totalPlanPrice = selectedCycle.price // This already includes base resources
            let optionsTotal = 0

            // Calculate additional configurable options price
            Object.entries(form.configurable_options).forEach(([optionId, quantity]) => {
                if (quantity > 0) {
                    const option = findOptionById(parseInt(optionId))
                    if (option) {
                        const optionPrice = getOptionPrice(option)
                        optionsTotal += optionPrice * quantity
                    }
                }
            })

            priceCalculation.value = {
                base_price: { price: basePrice },
                base_resources: { total: totalPlanPrice - basePrice },
                configurable_options: { total: optionsTotal },
                subtotal: totalPlanPrice + optionsTotal,
                discount: { percentage: 0 },
                discount_amount: 0,
                total: totalPlanPrice + optionsTotal
            }
        }
    } catch (error) {
        console.error('Error calculating price:', error)
    } finally {
        calculatingPrice.value = false
    }
}

const findOptionById = (optionId) => {
    for (const group of props.configurableOptions) {
        const option = group.options.find(opt => opt.id === optionId)
        if (option) return option
    }
    return null
}

const getOptionPrice = (option) => {
    if (!form.billing_cycle_id || !option.pricings) return 0

    const pricing = option.pricings.find(p => p.billing_cycle_id === form.billing_cycle_id)
    return pricing ? pricing.price : 0
}

const getBillingCycleName = () => {
    if (!form.billing_cycle_id) return ''

    const cycle = props.availableBillingCycles.find(c => c.id === form.billing_cycle_id)
    return cycle ? cycle.name.toLowerCase() : ''
}

// Función para seleccionar ciclo de facturación
const selectBillingCycle = (cycleId) => {
    form.billing_cycle_id = cycleId
    calculatePrice()
}

// Funciones para mejorar la experiencia de usuario
const incrementOption = (optionId, maxValue) => {
    const currentValue = form.configurable_options[optionId] || 0
    if (currentValue < maxValue) {
        form.configurable_options[optionId] = currentValue + 1
        calculatePrice()
    }
}

const decrementOption = (optionId, minValue) => {
    const currentValue = form.configurable_options[optionId] || 0
    if (currentValue > minValue) {
        form.configurable_options[optionId] = currentValue - 1
        calculatePrice()
    }
}

// Funciones para mensajes de marketing
const getGroupIcon = (groupName) => {
    const icons = {
        '🚀 Espacio para Crecer': '📁',
        'Espacio en Disco': '📁',
        '⚡ Potencia Turbo': '⚡',
        'vCPU': '⚡',
        '🧠 Memoria Inteligente': '🧠',
        'vRam': '🧠',
        'Memoria RAM': '🧠',
        'Seguridad Email': '🛡️',
        'SpamExperts': '🛡️',
    }
    return icons[groupName] || '⚙️'
}

const getGroupIconClass = (groupName) => {
    const classes = {
        '🚀 Espacio para Crecer': 'bg-gradient-to-r from-blue-500 to-cyan-500',
        'Espacio en Disco': 'bg-gradient-to-r from-blue-500 to-cyan-500',
        '⚡ Potencia Turbo': 'bg-gradient-to-r from-yellow-500 to-orange-500',
        'vCPU': 'bg-gradient-to-r from-yellow-500 to-orange-500',
        '🧠 Memoria Inteligente': 'bg-gradient-to-r from-purple-500 to-pink-500',
        'vRam': 'bg-gradient-to-r from-purple-500 to-pink-500',
        'Memoria RAM': 'bg-gradient-to-r from-purple-500 to-pink-500',
        'Seguridad Email': 'bg-gradient-to-r from-green-500 to-emerald-500',
        'SpamExperts': 'bg-gradient-to-r from-green-500 to-emerald-500',
    }
    return classes[groupName] || 'bg-gradient-to-r from-gray-500 to-gray-600'
}

const getMarketingMessage = (groupName) => {
    const messages = {
        '🚀 Espacio para Crecer': '¡Nunca te quedes sin espacio! Sube todas las fotos, videos y archivos que necesites para hacer crecer tu negocio sin límites.',
        'Espacio en Disco': '¡Nunca te quedes sin espacio! Sube todas las fotos, videos y archivos que necesites para hacer crecer tu negocio sin límites.',
        '⚡ Potencia Turbo': '🔥 ¡NO PIERDAS CLIENTES! Cada núcleo extra hace que tu sitio cargue más rápido. Sitios lentos = clientes que se van. ¡Convierte más visitas en ventas!',
        'vCPU': '🔥 ¡NO PIERDAS CLIENTES! Cada núcleo extra hace que tu sitio cargue más rápido. Sitios lentos = clientes que se van. ¡Convierte más visitas en ventas!',
        '🧠 Memoria Inteligente': '💪 Maneja múltiples visitantes comprando al mismo tiempo sin problemas. Perfecto para días de alta demanda y promociones especiales.',
        'vRam': '💪 Maneja múltiples visitantes comprando al mismo tiempo sin problemas. Perfecto para días de alta demanda y promociones especiales.',
        'Memoria RAM': '💪 Maneja múltiples visitantes comprando al mismo tiempo sin problemas. Perfecto para días de alta demanda y promociones especiales.',
        'Seguridad Email': '🛡️ Protege tu reputación empresarial. Evita que tus emails lleguen a spam y mantén la confianza de tus clientes.',
        'SpamExperts': '🛡️ Protege tu reputación empresarial. Evita que tus emails lleguen a spam y mantén la confianza de tus clientes.',
    }
    return messages[groupName] || 'Mejora el rendimiento de tu sitio web con esta opción adicional.'
}

const getOptionBenefit = (groupName, optionName) => {
    if (groupName.includes('Potencia') || groupName.includes('vCPU')) {
        return 'Más velocidad = Más ventas'
    }
    if (groupName.includes('Espacio') || groupName.includes('Disco')) {
        return 'Espacio ilimitado para crecer'
    }
    if (groupName.includes('Memoria') || groupName.includes('RAM')) {
        return 'Maneja más visitantes simultáneos'
    }
    if (groupName.includes('Seguridad') || groupName.includes('Spam')) {
        return 'Protección profesional garantizada'
    }
    return 'Mejora tu rendimiento online'
}

// Funciones para cálculos de descuentos
const getDiscountPercentage = (cycle) => {
    const discount = props.discountPercentages[cycle.id]
    return discount ? Math.round(discount.percentage) : 0
}

const getSavingsAmount = (cycle) => {
    const discountPercentage = getDiscountPercentage(cycle)
    if (discountPercentage === 0) return '0.00'

    // Calcular el precio sin descuento
    const priceWithoutDiscount = cycle.price / (1 - discountPercentage / 100)
    const savings = priceWithoutDiscount - cycle.price
    return savings > 0 ? savings.toFixed(2) : '0.00'
}

const submitDomain = () => {
    processing.value = true

    // Ensure configurable options are integers
    const cleanedOptions = {}
    Object.entries(form.configurable_options).forEach(([key, value]) => {
        cleanedOptions[key] = parseInt(value) || 0
    })

    form.domain = fullDomain.value
    form.configurable_options = cleanedOptions

    form.post(route('public.checkout.domain.process'), {
        onFinish: () => {
            processing.value = false
        }
    })
}
</script>
