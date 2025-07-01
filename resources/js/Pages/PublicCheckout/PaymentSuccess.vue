<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-purple-50">
        <Head title="¡Cuenta Verificada! - Fj Group CA" />
        
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-4xl mx-auto px-6 py-4">
                <div class="flex items-center">
                    <img src="/images/logo.png" alt="Fj Group CA" class="h-10 w-auto mr-3">
                    <h1 class="text-2xl font-bold text-gray-900">Fj Group CA</h1>
                </div>
            </div>
        </header>

        <main class="max-w-4xl mx-auto px-6 py-12">
            <!-- Success Message -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-4">¡Email Verificado Exitosamente!</h2>
                <p class="text-xl text-gray-600 mb-2">Tu cuenta ha sido creada y tu factura está lista</p>
                <p class="text-gray-500">Factura #{{ invoice.invoice_number }} por ${{ parseFloat(invoice.total_amount).toFixed(2) }}</p>
            </div>

            <!-- Invoice Summary -->
            <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">Resumen de tu Compra</h3>
                
                <!-- Product Details -->
                <div class="space-y-4 mb-6">
                    <div class="flex justify-between items-center py-3 border-b border-gray-100">
                        <div>
                            <h4 class="font-semibold text-gray-900">{{ purchaseContext.product_name || 'Hosting Plan' }}</h4>
                            <p class="text-sm text-gray-600">{{ purchaseContext.plan || 'Professional' }} - {{ getBillingCycleName() }}</p>
                            <p class="text-sm text-gray-500">Dominio: {{ purchaseContext.domain }}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-gray-900">${{ parseFloat(invoice.subtotal).toFixed(2) }}</div>
                        </div>
                    </div>
                    
                    <!-- Domain if applicable -->
                    <div v-if="purchaseContext.domain_price && purchaseContext.domain_price > 0" 
                         class="flex justify-between items-center py-3 border-b border-gray-100">
                        <div>
                            <h4 class="font-semibold text-gray-900">Registro de Dominio</h4>
                            <p class="text-sm text-gray-600">{{ purchaseContext.domain }}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-gray-900">${{ parseFloat(purchaseContext.domain_price).toFixed(2) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Total -->
                <div class="border-t border-gray-200 pt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-xl font-bold text-gray-900">Total a Pagar:</span>
                        <span class="text-2xl font-bold text-blue-600">${{ parseFloat(invoice.total_amount).toFixed(2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment Options -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">Elige tu Método de Pago</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- PayPal Option -->
                    <div class="border-2 border-blue-200 rounded-xl p-6 hover:border-blue-400 transition-colors">
                        <div class="text-center mb-4">
                            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106zm14.146-14.42a3.35 3.35 0 0 0-.607-.541c-.013.076-.026.175-.041.254-.93 4.778-4.005 6.430-7.97 6.430h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106h4.608a.641.641 0 0 0 .633-.74l.033-.207.629-3.99.04-.22a.641.641 0 0 1 .633-.54h.398c3.66 0 6.526-1.486 7.36-5.781.348-1.797.167-3.297-.653-4.471z"/>
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-gray-900 mb-2">PayPal</h4>
                            <p class="text-gray-600 mb-4">Pago seguro y rápido con PayPal</p>
                        </div>
                        
                        <Link :href="route('client.paypal.payment.create', { invoice: invoice.id })"
                              class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors text-center block">
                            Pagar con PayPal
                        </Link>
                    </div>

                    <!-- Other Methods Option -->
                    <div class="border-2 border-gray-200 rounded-xl p-6 hover:border-gray-400 transition-colors">
                        <div class="text-center mb-4">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-gray-900 mb-2">Otros Medios</h4>
                            <p class="text-gray-600 mb-4">Transferencia bancaria, Zelle, y más opciones</p>
                        </div>
                        
                        <Link :href="route('client.invoices.show', invoice.id)"
                              class="w-full bg-gray-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-gray-700 transition-colors text-center block">
                            Ver Otros Métodos
                        </Link>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="mt-8 p-6 bg-gray-50 rounded-lg">
                    <h4 class="font-semibold text-gray-900 mb-3">¿Necesitas ayuda con el pago?</h4>
                    <div class="text-sm text-gray-600 space-y-2">
                        <p>📧 <strong>Email:</strong> soporte@fjgroupca.com</p>
                        <p>📱 <strong>WhatsApp:</strong> +58 412 8172337</p>
                        <p class="text-xs text-gray-500 mt-3">
                            También puedes acceder a tu panel de cliente para ver más opciones de pago y gestionar tu factura.
                        </p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
    invoice: Object,
    purchaseContext: Object,
})

const getBillingCycleName = () => {
    const cycleNames = {
        'monthly': 'Mensual',
        'quarterly': 'Trimestral', 
        'semi_annually': 'Semestral',
        'annually': 'Anual',
        'biennially': 'Bienal',
        'triennially': 'Trienal'
    }
    return cycleNames[props.purchaseContext?.billing_cycle_slug] || 'Plan'
}
</script>
