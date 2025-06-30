<script setup>
import { ref, computed, onMounted, watchEffect, watch } from 'vue'; // Importar watchEffect
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CartSummary from '@/Components/Client/CartSummary.vue';
import axios from 'axios';

const props = defineProps({
    initialCart: Object,
    mainServiceProducts: Array,
    sslProducts: Array,
    licenseProducts: Array,
    discountPercentages: Object,      // Descuentos por producto y ciclo
    configurableOptionPrices: Object, // Precios de opciones configurables
});

const formPrimaryService = useForm({
    product_id: null,
    pricing_id: null,
    configurable_options: {},
    calculated_price: null,
    billing_cycle_id: null
});
const formAdditionalService = useForm({ product_id: null, pricing_id: null });

const currentCart = ref(null); // Se inicializará con watchEffect
const currentSelectedMainProduct = ref(null);
const selectedConfigurableOptions = ref({});

// Estado para precios dinámicos por producto y ciclo
const dynamicPricesCache = ref({});

// Estado para controlar la expansión de las opciones configurables
const expandedConfigSections = ref({});

// Computed para asegurar que selectedConfigurableOptions esté siempre inicializado
const safeSelectedOptions = computed(() => {
    const options = selectedConfigurableOptions.value;

    // Asegurar que cada producto tenga su objeto inicializado
    props.mainServiceProducts.forEach(product => {
        if (!options[product.id]) {
            options[product.id] = {};
        }

        // Inicializar opciones para cada grupo
        if (product.configurable_option_groups) {
            product.configurable_option_groups.forEach(group => {
                // Para radio buttons (grupos)
                if (!options[product.id][group.id]) {
                    options[product.id][group.id] = null;
                }

                // Para checkboxes (opciones individuales)
                if (group.options) {
                    group.options.forEach(option => {
                        if (options[product.id][option.id] === undefined) {
                            options[product.id][option.id] = false;
                        }
                    });
                }
            });
        }
    });

    return options;
});
const activeDomainName = ref(''); // Se actualizará con watchEffect

// Computed para el nombre de la cuenta activa (evita problemas de sintaxis)
const displayAccountName = computed(() => {
    return activeDomainName.value || 'Cuenta Activa';
});

// Reaccionar a los cambios en la prop initialCart
watchEffect(() => {
    console.log('SelectServicesPage: watchEffect - initialCart prop cambió o se inicializó.');
    // Crear una copia profunda para evitar modificar la prop directamente si fuera necesario,
    // aunque para visualización y re-renderizado, usarla directamente o una copia superficial es común.
    // Para este caso, si currentCart solo se usa para leer y pasar a otros componentes, props.initialCart es suficiente.
    // Si se necesita modificar localmente (ej. antes de una actualización del backend), una copia profunda es más segura.
    currentCart.value = JSON.parse(JSON.stringify(props.initialCart));

    if (currentCart.value?.accounts && currentCart.value.active_account_id) {
        const activeAccount = currentCart.value.accounts.find(acc => acc.account_id === currentCart.value.active_account_id);
        if (activeAccount && activeAccount.domain_info) {
            activeDomainName.value = activeAccount.domain_info.domain_name;
            console.log('SelectServicesPage: watchEffect - activeDomainName actualizado:', activeDomainName.value);
        } else {
            activeDomainName.value = 'Cuenta Activa (sin dominio)'; // Mensaje más claro
            console.log('SelectServicesPage: watchEffect - Cuenta activa sin nombre de dominio.');
        }

        // Inicializar opciones configurables desde el carrito existente
        if (activeAccount && activeAccount.primary_service) {
            const productId = activeAccount.primary_service.product_id;

            // Asegurar que el producto tenga su objeto inicializado
            if (!selectedConfigurableOptions.value[productId]) {
                selectedConfigurableOptions.value[productId] = {};
            }

            // Si hay opciones configurables guardadas, reconstruir la estructura del frontend
            if (activeAccount.primary_service.configurable_options) {
                console.log('🔄 Reconstruyendo opciones configurables desde carrito:', {
                    productId,
                    saved_options: activeAccount.primary_service.configurable_options
                });

                // Reconstruir opciones desde la estructura guardada
                Object.entries(activeAccount.primary_service.configurable_options).forEach(([optionId, optionData]) => {
                    if (typeof optionData === 'object' && optionData !== null) {
                        // Nueva estructura: {option_id, group_id, value, quantity}
                        selectedConfigurableOptions.value[productId][optionId] = optionData.value === true || optionData.value > 0;
                        if (optionData.quantity && optionData.quantity > 1) {
                            selectedConfigurableOptions.value[productId][`${optionId}_quantity`] = optionData.quantity;
                        }
                    }
                });
            }
        }
    } else {
        activeDomainName.value = 'N/A (Carrito no disponible o sin cuenta activa)';
        console.log('SelectServicesPage: watchEffect - Carrito no disponible o sin cuenta activa.');
    }
});


const formatCurrency = (value, currencyCode = 'USD') => {
    if (typeof value !== 'number' || isNaN(value)) return '';
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: currencyCode }).format(value);
};

// Obtener cantidad base de un grupo para un producto
const getBaseQuantity = (product, groupId) => {
    if (!product.configurable_option_groups) return 0;

    const group = product.configurable_option_groups.find(g => g.id === groupId);
    return group?.pivot?.base_quantity || 0;
};

// Obtener unidad de un grupo
const getGroupUnit = (group) => {
    const name = group.name.toLowerCase();
    if (name.includes('espacio') || name.includes('disco')) return ' GB';
    if (name.includes('vcpu') || name.includes('cpu')) return ' cores';
    if (name.includes('ram') || name.includes('memoria')) return ' GB';
    if (name.includes('backup')) return ' backups';
    if (name.includes('email')) return ' emails';
    if (name.includes('dominio')) return ' dominios';
    return '';
};

// Función para obtener el label del tipo de opción
const getOptionTypeLabel = (optionType) => {
    const types = {
        'dropdown': 'Lista desplegable',
        'radio': 'Selección única',
        'checkbox': 'Activar/Desactivar',
        'quantity': 'Cantidad',
        'text': 'Texto libre'
    };
    return types[optionType] || optionType;
};

// Función para obtener el precio de una opción (usando precios mensuales)
const getOptionPricing = (option, product) => {
    // Buscar en los precios configurables que vienen del backend
    const optionPrices = props.configurableOptionPrices?.[option.id];

    if (optionPrices && optionPrices[1]) {
        // Retornar el precio mensual (ciclo 1)
        return optionPrices[1];
    }

    // Fallback: buscar en option.pricings si existe
    if (option.pricings && option.pricings.length > 0) {
        return option.pricings.find(pricing => pricing.billing_cycle_id === 1) || option.pricings[0];
    }

    return null;
};

// Función para formatear nombres de recursos
const formatResourceName = (key) => {
    const resourceNames = {
        'disk_space': 'Espacio en Disco',
        'vcpu_cores': 'vCPU',
        'ram_memory': 'Memoria RAM',
        'bandwidth': 'Transferencia',
        'email_accounts': 'Cuentas Email',
        'databases': 'Bases de Datos',
        'domains': 'Dominios',
        'subdomains': 'Subdominios',
        'ssl_certificates': 'Certificados SSL',
        'backups': 'Copias de Seguridad'
    };
    return resourceNames[key] || key.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
};

// Función para formatear valores de recursos
const formatResourceValue = (key, value) => {
    if (key.includes('disk_space') || key.includes('ram') || key.includes('bandwidth')) {
        return `${value} GB`;
    }
    if (key.includes('vcpu')) {
        return `${value} cores`;
    }
    return value;
};

// ===== CALCULADORA DINÁMICA DE PRECIOS =====

// Función para calcular precio dinámico completo
const calculateDynamicPrice = (productId, billingCycleId, configurableOptions = {}) => {
    // 1. Obtener precio base del producto
    const product = props.mainServiceProducts?.find(p => p.id === productId);
    if (!product) return { total: 0, breakdown: {} };

    const pricing = product.pricings?.find(p => p.billing_cycle.id === billingCycleId);
    if (!pricing) return { total: 0, breakdown: {} };

    const basePrice = parseFloat(pricing.price) || 0;

    // 2. Calcular precio de recursos base incluidos
    let baseResourcesTotal = 0;
    const baseResourcesDetails = [];

    if (product.configurable_option_groups) {
        for (const group of product.configurable_option_groups) {
            const baseQuantity = parseFloat(group.base_quantity) || 0;

            if (baseQuantity > 0 && group.options && group.options.length > 0) {
                const option = group.options[0]; // Primera opción del grupo
                const optionPrices = props.configurableOptionPrices?.[option.id];

                if (optionPrices) {
                    // Usar precio mensual (ciclo 1) y multiplicar por duración del ciclo
                    const monthlyPricing = optionPrices[1]; // Siempre usar precio mensual
                    if (monthlyPricing) {
                        const monthlyUnitPrice = parseFloat(monthlyPricing.price) || 0;

                        // Obtener duración del ciclo en meses
                        const product = props.mainServiceProducts?.find(p => p.id === productId);
                        const pricing = product?.pricings?.find(p => p.billing_cycle.id === billingCycleId);
                        const cycleDurationMonths = pricing ? Math.round(pricing.billing_cycle.days / 30) : 1;

                        // Calcular precio total para el ciclo
                        const unitPriceForCycle = monthlyUnitPrice * cycleDurationMonths;
                        const lineTotal = unitPriceForCycle * baseQuantity;
                        baseResourcesTotal += lineTotal;

                        baseResourcesDetails.push({
                            group_name: group.name,
                            option_name: option.name,
                            base_quantity: baseQuantity,
                            unit_price: unitPriceForCycle,
                            line_total: lineTotal,
                            monthly_price: monthlyUnitPrice,
                            cycle_months: cycleDurationMonths
                        });
                    } else {
                        console.warn(`❌ No hay precio mensual para opción ${option.id}`);
                    }
                } else {
                    console.warn(`❌ No hay precios para opción ${option.id}`);
                }
            }
        }
    }

    // 3. Calcular precio de opciones configurables adicionales
    let configurableOptionsTotal = 0;
    const configurableOptionsDetails = [];

    for (const [optionId, quantity] of Object.entries(configurableOptions)) {
        const optionPrices = props.configurableOptionPrices?.[optionId];
        if (optionPrices && quantity > 0) {
            // Usar precio mensual (ciclo 1) y multiplicar por duración del ciclo
            const monthlyPricing = optionPrices[1]; // Siempre usar precio mensual
            if (monthlyPricing) {
                const monthlyUnitPrice = parseFloat(monthlyPricing.price) || 0;

                // Obtener duración del ciclo en meses
                const product = props.mainServiceProducts?.find(p => p.id === productId);
                const pricing = product?.pricings?.find(p => p.billing_cycle.id === billingCycleId);
                const cycleDurationMonths = pricing ? Math.round(pricing.billing_cycle.days / 30) : 1;

                // Calcular precio total para el ciclo
                const unitPriceForCycle = monthlyUnitPrice * cycleDurationMonths;
                const lineTotal = unitPriceForCycle * parseFloat(quantity);
                configurableOptionsTotal += lineTotal;

                configurableOptionsDetails.push({
                    option_id: optionId,
                    quantity: parseFloat(quantity),
                    unit_price: unitPriceForCycle,
                    line_total: lineTotal,
                    monthly_price: monthlyUnitPrice,
                    cycle_months: cycleDurationMonths
                });
            }
        }
    }

    // 4. Calcular subtotal
    const subtotal = basePrice + baseResourcesTotal + configurableOptionsTotal;

    // 5. Aplicar descuento
    const discountKey = `${productId}-${billingCycleId}`;
    const discount = props.discountPercentages?.[discountKey];
    const discountPercentage = discount ? parseFloat(discount.percentage) : 0;
    const discountAmount = subtotal * (discountPercentage / 100);

    // 6. Calcular total final
    const total = subtotal - discountAmount;

    return {
        total: Math.round(total * 100) / 100,
        breakdown: {
            base_price: basePrice,
            base_resources: {
                total: baseResourcesTotal,
                details: baseResourcesDetails
            },
            configurable_options: {
                total: configurableOptionsTotal,
                details: configurableOptionsDetails
            },
            subtotal: subtotal,
            discount: {
                percentage: discountPercentage,
                amount: discountAmount,
                name: discount?.name || 'Sin descuento'
            }
        }
    };
};

// Función para obtener precio dinámico con opciones personalizadas
const getDynamicPriceWithOptions = (productId, billingCycleId) => {
    // Obtener opciones configurables del producto
    const configurableOptions = {};

    // Buscar opciones del producto específico
    const productOptions = selectedConfigurableOptions.value[productId];
    if (productOptions) {
        // Procesar opciones del producto
        for (const [key, value] of Object.entries(productOptions)) {
            if (key.endsWith('_quantity') && value > 0) {
                // Es una cantidad: extraer el option_id
                const optionId = key.replace('_quantity', '');
                configurableOptions[optionId] = parseFloat(value);
            } else if (value === true) {
                // Es un checkbox activado: cantidad = 1
                configurableOptions[key] = 1;
            }
        }
    }

    // console.log(`🧮 Calculando precio para producto ${productId}, ciclo ${billingCycleId}:`, configurableOptions);

    // Calcular precio con las opciones configurables
    const result = calculateDynamicPrice(productId, billingCycleId, configurableOptions);

    // Guardar en cache para evitar recálculos innecesarios
    const cacheKey = `${productId}-${billingCycleId}-${JSON.stringify(configurableOptions)}`;
    dynamicPricesCache.value[cacheKey] = result;

    return result.total;
};

// Función para calcular precio con descuento (LEGACY - mantenida para compatibilidad)
const calculatePriceWithDiscount = (basePrice, productId, billingCycleId) => {
    const discount = getDiscountPercentage(productId, billingCycleId);
    return basePrice * (1 - discount / 100);
};

// Función para obtener el descuento para un producto y ciclo específico
const getDiscountPercentage = (productId, billingCycleId) => {
    // Buscar descuento en los datos del producto
    const product = props.mainServiceProducts?.find(p => p.id === productId);
    if (!product) return 0;

    const pricing = product.pricings?.find(p => p.billing_cycle.id === billingCycleId);
    if (!pricing || !pricing.discount_percentage) return 0;

    return pricing.discount_percentage.percentage || 0;
};

// Debug: Mostrar datos recibidos
console.log('=== DATOS PARA CALCULADORA ===');
console.log('Productos:', props.mainServiceProducts);
console.log('Descuentos:', props.discountPercentages);
console.log('Precios opciones:', props.configurableOptionPrices);

// Debug específico: Verificar precios por ciclo
if (props.configurableOptionPrices) {
    console.log('=== VERIFICACIÓN PRECIOS POR CICLO ===');
    Object.keys(props.configurableOptionPrices).forEach(optionId => {
        const optionPrices = props.configurableOptionPrices[optionId];
        console.log(`Opción ${optionId}:`, optionPrices);

        // Verificar que tenga precios para todos los ciclos (1-6)
        for (let cycleId = 1; cycleId <= 6; cycleId++) {
            if (optionPrices[cycleId]) {
                console.log(`  Ciclo ${cycleId}: $${optionPrices[cycleId].price}`);
            } else {
                console.warn(`  ❌ Falta precio para ciclo ${cycleId}`);
            }
        }
    });
}

// Test de la calculadora
onMounted(() => {
    if (props.mainServiceProducts && props.mainServiceProducts.length > 0) {
        const testProduct = props.mainServiceProducts[0];
        if (testProduct.pricings && testProduct.pricings.length > 0) {
            // Test ciclo mensual
            const testPricing = testProduct.pricings[0];
            const result = calculateDynamicPrice(testProduct.id, testPricing.billing_cycle.id);
            console.log('=== TEST CALCULADORA ===');
            console.log('Producto:', testProduct.name);
            console.log('Ciclo:', testPricing.billing_cycle.name);
            console.log('Resultado:', result);

            // Test todos los ciclos
            console.log('=== TEST TODOS LOS CICLOS (USANDO PRECIOS MENSUALES) ===');
            testProduct.pricings.forEach(pricing => {
                const result = calculateDynamicPrice(testProduct.id, pricing.billing_cycle.id);
                const months = Math.round(pricing.billing_cycle.days / 30);

                console.log(`${pricing.billing_cycle.name} (${months} meses):`);
                console.log(`  💰 Subtotal: $${result.breakdown.subtotal}`);

                if (result.breakdown.discount.percentage > 0) {
                    console.log(`  🎯 Descuento: ${result.breakdown.discount.percentage}% (-$${result.breakdown.discount.amount})`);
                    console.log(`  🏆 TOTAL CON DESCUENTO: $${result.total}`);
                } else {
                    console.log(`  🏆 TOTAL (sin descuento): $${result.total}`);
                }

                if (result.breakdown.base_resources.details.length > 0) {
                    console.log(`  📦 Recursos base:`, result.breakdown.base_resources.details.map(r =>
                        `${r.group_name}: $${r.monthly_price} x ${r.cycle_months} meses = $${r.unit_price}`
                    ));
                }
            });
        }
    }
});

// Watcher para actualizar precios cuando cambien las opciones configurables

watch(selectedConfigurableOptions, (newOptions) => {
    console.log('🔄 Opciones configurables cambiaron:', newOptions);
    console.log('🔍 currentSelectedMainProduct:', currentSelectedMainProduct.value);

    // Limpiar cache de precios para forzar recálculo
    dynamicPricesCache.value = {};

    // Buscar qué producto tiene opciones modificadas
    for (const [productId, productOptions] of Object.entries(newOptions)) {
        const product = props.mainServiceProducts?.find(p => p.id == productId);
        if (product && productOptions && Object.keys(productOptions).length > 0) {
            console.log('💰 Recalculando precios para producto ID:', productId, 'Nombre:', product.name);
            console.log('📝 Opciones del producto:', productOptions);

            // Mostrar precios actualizados para todos los ciclos
            if (product.pricings && product.pricings.length > 0) {
                console.log('💲 PRECIOS ACTUALIZADOS CON PERSONALIZACIONES:');
                product.pricings.forEach(pricing => {
                    const newPrice = getDynamicPriceWithOptions(product.id, pricing.billing_cycle.id);
                    const months = Math.round(pricing.billing_cycle.days / 30);
                    console.log(`  ${pricing.billing_cycle.name} (${months} meses): $${newPrice}`);
                });
            }

            // Solo procesar el primer producto con cambios
            break;
        }
    }
}, { deep: true });

// Función para toggle de expansión de opciones configurables
const toggleConfigSection = (productId) => {
    expandedConfigSections.value[productId] = !expandedConfigSections.value[productId];
    console.log(`🔄 Toggle sección configuración producto ${productId}:`, expandedConfigSections.value[productId]);
};

// Función para verificar si una sección está expandida
const isConfigSectionExpanded = (productId) => {
    return expandedConfigSections.value[productId] || false;
};

const selectMainProductForConfiguration = (product) => {
    console.log('--- selectMainProductForConfiguration ---');
    console.log('Producto seleccionado para configurar:', product.id, product.name);
    currentSelectedMainProduct.value = product;
    if (!selectedConfigurableOptions.value[product.id]) {
        selectedConfigurableOptions.value[product.id] = {};
    }
    // console.log('Grupos de opciones configurables para el producto:', product.configurable_option_groups);
};

const areAllConfigurableOptionsSelected = (product, selections) => {
    if (!product || !product.configurable_option_groups || product.configurable_option_groups.length === 0) {
        return true;
    }
    for (const group of product.configurable_option_groups) {
        if (group.options && group.options.length > 0) {
            // Asumir que todas las opciones son requeridas si el grupo existe y tiene opciones.
            // Esta es una simplificación.
            if (!selections || !selections[group.id]) {
                console.warn(`Validación de opciones: Opción faltante para el grupo ${group.name} (ID: ${group.id})`);
                return false;
            }
        }
    }
    return true;
};

const handleSelectPrimaryService = (productId, pricingId) => {
    console.log('🚀 Seleccionando servicio - Producto:', productId, 'Pricing:', pricingId);

    if (!pricingId) {
        console.error('ERROR CRÍTICO: pricingId es nulo/indefinido.');
        alert('Por favor, selecciona un ciclo de facturación válido.');
        return;
    }

    formPrimaryService.product_id = productId;
    formPrimaryService.pricing_id = pricingId;

    const product = props.mainServiceProducts.find(p => p.id === productId);

    // Obtener opciones configurables seleccionadas
    const productOptionsSelections = selectedConfigurableOptions.value[productId] || {};

    console.log('🔍 Opciones para producto', productId, ':', productOptionsSelections);

    // SIEMPRE asignar las opciones configurables, incluso si no hay producto seleccionado actualmente
    formPrimaryService.configurable_options = productOptionsSelections;

    // Validar opciones requeridas solo si hay opciones configurables
    if (product && product.configurable_option_groups && product.configurable_option_groups.length > 0) {
        if (!areAllConfigurableOptionsSelected(product, productOptionsSelections)) {
            alert('Por favor, completa todas las opciones configurables requeridas para este plan.');
            return;
        }
    }

    // Calcular precio dinámico final y enviarlo
    const pricing = product?.pricings?.find(p => p.id === pricingId);
    const billingCycleId = pricing?.billing_cycle?.id;
    const finalPrice = getDynamicPriceWithOptions(productId, billingCycleId);

    // Agregar información de precio calculado
    formPrimaryService.calculated_price = finalPrice;
    formPrimaryService.billing_cycle_id = billingCycleId;

    console.log('💰 Precio final:', finalPrice, '| Ciclo:', billingCycleId);

    console.log('🔍 OPCIONES CONFIGURABLES:');
    console.log('- Seleccionadas:', selectedConfigurableOptions.value[productId]);
    console.log('- En formulario:', formPrimaryService.configurable_options);
    console.log('- Total opciones:', Object.keys(formPrimaryService.configurable_options || {}).length);
    formPrimaryService.post(route('client.cart.account.setPrimaryService'), {
        preserveScroll: true, // Inertia intentará mantener el scroll
        preserveState: false, // Permitir que las props se recarguen y actualicen la página. True puede prevenirlo.
        // Con redirect back(), preserveState: false o no ponerlo es usualmente lo que se quiere
        // para que las props (como initialCart y mensajes flash) se actualicen.
        onSuccess: (page) => {
            console.log('POST a setPrimaryService ÉXITO.');
            // Ya no es necesario actualizar currentCart.value desde page.props aquí,
            // watchEffect se encargará cuando props.initialCart cambie.
            window.dispatchEvent(new CustomEvent('cart-updated')); // Mantener por si CartSummary no usa props
            if (page.props.flash && page.props.flash.success) {
                // alert(page.props.flash.success); // O usar un sistema de notificaciones más elegante
            }
        },
        onError: (errors) => {
            console.error('POST a setPrimaryService FALLÓ:', errors);
            let errorMessages = 'Ocurrió un error.';
            if (errors && typeof errors === 'object') {
                errorMessages = Object.values(errors).join(' ');
            } else if (typeof errors === 'string') {
                errorMessages = errors;
            }
            alert(`Error al añadir servicio principal: ${errorMessages}`);
        },
        onFinish: () => {
            console.log('POST a setPrimaryService FINALIZADO.');
        }
    });
};

const handleAddAdditionalService = (productId, pricingId) => {
    console.log('--- handleAddAdditionalService INVOCADO ---');
    console.log('Producto Adicional ID:', productId, 'Pricing ID:', pricingId);
    formAdditionalService.product_id = productId;
    formAdditionalService.pricing_id = pricingId;

    console.log('Datos formAdditionalService ANTES POST:', JSON.parse(JSON.stringify(formAdditionalService.data())));
    formAdditionalService.post(route('client.cart.add'), {
        preserveScroll: true,
        preserveState: false, // Similar a arriba
        onSuccess: (page) => {
            console.log('POST a addItem (adicional) ÉXITO.');
            window.dispatchEvent(new CustomEvent('cart-updated'));
            if (page.props.flash && page.props.flash.success) {
                // alert(page.props.flash.success);
            }
        },
        onError: (errors) => {
            console.error('POST a addItem (adicional) FALLÓ:', errors);
            let errorMessages = 'Ocurrió un error.';
            if (errors && typeof errors === 'object') {
                errorMessages = Object.values(errors).join(' ');
            } else if (typeof errors === 'string') {
                errorMessages = errors;
            }
            alert(`Error al añadir servicio adicional: ${errorMessages}`);
        },
        onFinish: () => {
            console.log('POST a addItem (adicional) FINALIZADO.');
        }
    });
};

const goToFinalCheckout = () => {
    console.log('--- goToFinalCheckout INVOCADO ---');
    router.visit(route('client.checkout.confirm'));
};

onMounted(() => {
    console.log('--- onMounted ---');
    console.log('Props:', props);
    console.log('safeSelectedOptions:', safeSelectedOptions.value);
});

</script>

<template>

    <Head title="Seleccionar Servicios" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Paso 2: Selecciona tus
                Servicios</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-10">
                    <div class="p-6 space-y-8 bg-white shadow-sm md:col-span-6 dark:bg-gray-800 sm:rounded-lg">

                        <div>
                            <p class="mb-4 text-lg text-gray-700 dark:text-gray-300">
                                Añadiendo servicios para: <strong class="text-indigo-600">{{ displayAccountName
                                }}</strong>
                            </p>

                            <section>
                                <h3 class="mb-3 text-xl font-medium text-gray-900 dark:text-gray-100">Servicios
                                    Principales
                                    (Elige uno)</h3>
                                <div class="space-y-4">
                                    <div v-for="product in props.mainServiceProducts" :key="product.id"
                                        class="p-4 border rounded-lg dark:border-gray-700"
                                        :class="{ 'ring-2 ring-indigo-500': currentSelectedMainProduct && currentSelectedMainProduct.id === product.id }">

                                        <div>
                                            <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{
                                                product.name
                                            }}</h4>
                                            <p class="mb-3 text-sm text-gray-600 dark:text-gray-400">{{
                                                product.description }}
                                            </p>

                                            <!-- Características base del producto -->
                                            <div v-if="product.base_resources && Object.keys(product.base_resources).length > 0"
                                                class="p-3 mb-4 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                                                <h5 class="mb-2 text-sm font-medium text-blue-800 dark:text-blue-200">
                                                    Características incluidas:</h5>
                                                <div
                                                    class="grid grid-cols-2 gap-2 text-xs text-blue-700 md:grid-cols-3 dark:text-blue-300">
                                                    <div v-for="(value, key) in product.base_resources" :key="key"
                                                        v-if="value">
                                                        <span class="font-medium">{{ formatResourceName(key) }}:</span>
                                                        {{
                                                            formatResourceValue(key, value) }}
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Ciclos de facturación (ahora arriba) -->
                                            <div class="mb-4">
                                                <p class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Elige tu
                                                    ciclo de facturación:</p>
                                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                                    <button v-for="pricing in product.pricings" :key="pricing.id"
                                                        @click="handleSelectPrimaryService(product.id, pricing.id)"
                                                        :disabled="formPrimaryService.processing"
                                                        class="flex flex-col items-center justify-center p-4 min-h-[120px] text-center border rounded-lg hover:bg-indigo-50 dark:hover:bg-gray-700 dark:border-gray-600 transition-colors duration-200 shadow-sm hover:shadow-md">
                                                        <span
                                                            class="mb-2 text-sm font-semibold text-gray-900 dark:text-gray-100">{{
                                                                pricing.billing_cycle.name }}</span>
                                                        <div class="flex flex-col items-center space-y-1">
                                                            <div class="text-center">
                                                                <!-- Precio calculado dinámicamente -->
                                                                <span
                                                                    class="text-xl font-bold text-indigo-600 dark:text-indigo-400">
                                                                    {{
                                                                        formatCurrency(getDynamicPriceWithOptions(product.id,
                                                                            pricing.billing_cycle.id),
                                                                            pricing.currency_code) }}
                                                                </span>
                                                                <!-- Precio original tachado si hay descuento -->
                                                                <div v-if="getDiscountPercentage(product.id, pricing.billing_cycle.id) > 0"
                                                                    class="text-sm text-gray-500 line-through">
                                                                    {{ formatCurrency(pricing.price,
                                                                        pricing.currency_code) }}
                                                                </div>
                                                            </div>
                                                            <!-- Badge de descuento -->
                                                            <span
                                                                v-if="getDiscountPercentage(product.id, pricing.billing_cycle.id) > 0"
                                                                class="px-2 py-1 text-xs font-medium text-green-600 bg-green-100 rounded-full dark:text-green-400 dark:bg-green-900/30">
                                                                -{{ getDiscountPercentage(product.id,
                                                                    pricing.billing_cycle.id)
                                                                }}% descuento
                                                            </span>
                                                            <span v-if="pricing.setup_fee && pricing.setup_fee > 0"
                                                                class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                                Setup: {{ formatCurrency(pricing.setup_fee,
                                                                    pricing.currency_code) }}
                                                            </span>
                                                        </div>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Sección de configuración (ahora siempre visible si hay opciones) -->
                                        <div v-if="product.configurable_option_groups && product.configurable_option_groups.length > 0"
                                            class="p-4 mt-6 space-y-4 border border-purple-200 rounded-lg bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 dark:border-purple-700">
                                            <!-- Encabezado clickeable para expandir/contraer -->
                                            <div @click="toggleConfigSection(product.id)"
                                                class="flex items-center justify-between p-4 mb-6 transition-all duration-200 rounded-lg cursor-pointer hover:bg-purple-100/50 dark:hover:bg-purple-900/40">
                                                <div class="flex items-center space-x-3">
                                                    <svg class="text-purple-600 w-7 h-7 dark:text-purple-400"
                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                    </svg>
                                                    <h5 class="text-2xl font-bold text-purple-800 dark:text-purple-200">
                                                        Configura y Potencia tu servicio</h5>

                                                    <!-- Icono de expansión -->
                                                    <svg class="w-6 h-6 text-purple-600 transition-transform duration-200 dark:text-purple-400"
                                                        :class="{ 'rotate-180': isConfigSectionExpanded(product.id) }"
                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                </div>

                                                <!-- Indicador de precio actualizado -->
                                                <div
                                                    class="px-4 py-3 text-right bg-purple-100 rounded-lg dark:bg-purple-900/30">
                                                    <div
                                                        class="mb-1 text-sm font-medium text-purple-600 dark:text-purple-400">
                                                        Precio personalizado:
                                                    </div>
                                                    <div
                                                        class="text-2xl font-bold text-purple-800 dark:text-purple-200">
                                                        {{ formatCurrency(getDynamicPriceWithOptions(product.id, 1),
                                                            'USD') }}<span class="text-lg">/mes</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Texto cuando está contraído -->
                                            <div v-show="!isConfigSectionExpanded(product.id)" class="py-4 text-center">
                                                <p class="text-lg font-medium text-purple-600 dark:text-purple-400">
                                                    👆 Haz clic arriba para personalizar tu servicio
                                                </p>
                                            </div>

                                            <!-- Contenido expandible -->
                                            <div v-show="isConfigSectionExpanded(product.id)"
                                                class="transition-all duration-300">
                                                <p
                                                    class="mb-6 text-lg leading-relaxed text-purple-700 dark:text-purple-300">
                                                    Personaliza tu plan agregando recursos adicionales según tus
                                                    necesidades. Los precios se actualizan automáticamente.
                                                </p>

                                                <div v-for="group in product.configurable_option_groups" :key="group.id"
                                                    class="p-6 bg-white border border-purple-200 shadow-sm rounded-xl dark:border-purple-600 dark:bg-gray-800">
                                                    <div class="flex items-center justify-between mb-4">
                                                        <div>
                                                            <label
                                                                class="text-xl font-bold text-gray-800 dark:text-gray-200">
                                                                {{ group.name }}
                                                                <span v-if="group.is_required"
                                                                    class="text-red-500">*</span>
                                                            </label>
                                                            <!-- Mostrar cantidad base si existe -->
                                                            <div v-if="group.base_quantity"
                                                                class="mt-2 text-sm font-medium text-blue-600 dark:text-blue-400">
                                                                ✅ Incluido: {{ group.base_quantity }} {{
                                                                    group.name.toLowerCase() }}
                                                            </div>
                                                        </div>
                                                        <span v-if="group.is_required"
                                                            class="px-3 py-1 text-sm font-medium text-yellow-700 bg-yellow-100 rounded-full">
                                                            Obligatorio
                                                        </span>
                                                    </div>

                                                    <p v-if="group.description"
                                                        class="mb-4 text-base leading-relaxed text-gray-600 dark:text-gray-400">
                                                        {{ group.description }}
                                                    </p>

                                                    <div v-if="group.options && group.options.length > 0"
                                                        class="space-y-4">
                                                        <div v-for="option in group.options" :key="option.id"
                                                            class="grid items-center grid-cols-3 gap-6 p-4 transition-all border border-gray-200 rounded-xl bg-gray-50 dark:bg-gray-700 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 hover:shadow-md">
                                                            <!-- Columna 1: Información de la opción -->
                                                            <div class="flex-1">
                                                                <div class="flex items-center space-x-3">
                                                                    <!-- Checkbox para opciones no obligatorias -->
                                                                    <input
                                                                        v-if="option.option_type === 'checkbox' || !group.is_required"
                                                                        :id="`option_${option.id}`" type="checkbox"
                                                                        v-model="safeSelectedOptions[product.id][option.id]"
                                                                        class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">

                                                                    <!-- Radio button para opciones obligatorias -->
                                                                    <input v-else-if="group.is_required"
                                                                        :id="`option_${option.id}`" type="radio"
                                                                        :name="`group_${group.id}`" :value="option.id"
                                                                        v-model="safeSelectedOptions[product.id][group.id]"
                                                                        class="w-4 h-4 text-purple-600 border-gray-300 focus:ring-purple-500">

                                                                    <label :for="`option_${option.id}`"
                                                                        class="flex-1 cursor-pointer">
                                                                        <div
                                                                            class="mb-2 text-lg font-bold text-gray-900 dark:text-gray-100">
                                                                            {{ option.name }}
                                                                        </div>
                                                                        <div v-if="option.description"
                                                                            class="mb-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                                                                            {{ option.description }}
                                                                        </div>
                                                                        <div
                                                                            class="text-sm text-gray-500 dark:text-gray-500">
                                                                            📋 {{ getOptionTypeLabel(option.option_type)
                                                                            }}
                                                                            <span
                                                                                v-if="option.min_value || option.max_value"
                                                                                class="px-2 py-1 ml-2 text-xs bg-gray-100 rounded dark:bg-gray-600">
                                                                                {{ option.min_value || 0 }} - {{
                                                                                    option.max_value || '∞' }}
                                                                            </span>
                                                                        </div>
                                                                    </label>
                                                                </div>

                                                                <!-- Input de cantidad movido a la columna 3 -->
                                                            </div>

                                                            <!-- Columna 2: Precio destacado más a la derecha -->
                                                            <div class="flex justify-end">
                                                                <div
                                                                    class="px-6 py-4 text-right rounded-lg bg-green-50 dark:bg-green-900/20">
                                                                    <div v-if="getOptionPricing(option, product)">
                                                                        <div
                                                                            class="text-3xl font-bold text-green-700 dark:text-green-400">
                                                                            {{ formatCurrency(getOptionPricing(option,
                                                                                product).price) }}
                                                                        </div>
                                                                        <div
                                                                            class="text-base font-medium text-green-600 dark:text-green-500">
                                                                            <span
                                                                                v-if="option.option_type === 'quantity'">por
                                                                                unidad / mes</span>
                                                                            <span v-else>por servicio / mes</span>
                                                                        </div>
                                                                        <div v-if="getOptionPricing(option, product).setup_fee > 0"
                                                                            class="mt-2 text-sm text-gray-500">
                                                                            Setup: {{
                                                                                formatCurrency(getOptionPricing(option,
                                                                                    product).setup_fee) }}
                                                                        </div>
                                                                    </div>
                                                                    <div v-else class="text-base text-gray-400">
                                                                        Sin precio
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Columna 3: Input de cantidad centrado cuando está activo -->
                                                            <div class="flex items-center justify-center">
                                                                <div v-if="option.option_type === 'quantity' && (selectedConfigurableOptions[product.id][option.id] || group.is_required)"
                                                                    class="text-center">
                                                                    <label
                                                                        class="block mb-2 text-sm font-medium text-purple-700 dark:text-purple-300">
                                                                        📊 Cantidad adicional:
                                                                    </label>
                                                                    <input type="number" :min="option.min_value || 1"
                                                                        :max="option.max_value || 999"
                                                                        v-model="selectedConfigurableOptions[product.id][`${option.id}_quantity`]"
                                                                        class="w-32 px-3 py-2 text-lg font-medium text-center bg-white border-2 border-purple-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:border-purple-600">
                                                                </div>
                                                                <div v-else-if="option.option_type === 'quantity'"
                                                                    class="text-sm text-center text-gray-400">
                                                                    <span>Selecciona para configurar</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div v-else class="text-xs italic text-gray-400">
                                                        No hay opciones disponibles para este grupo.
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Fin del contenido expandible -->
                                        </div>

                                    </div>
                                    <p v-if="formPrimaryService.errors.product_id" class="text-sm text-red-500">
                                        {{ formPrimaryService.errors.product_id }}</p>
                                    <p v-if="formPrimaryService.errors.pricing_id" class="text-sm text-red-500">
                                        {{ formPrimaryService.errors.pricing_id }}</p>
                                    <p v-if="formPrimaryService.errors.configurable_options"
                                        class="text-sm text-red-500">
                                        {{ formPrimaryService.errors.configurable_options }}</p>
                                    <p v-if="formPrimaryService.errors.general_error" class="text-sm text-red-500">
                                        {{ formPrimaryService.errors.general_error }}</p>
                                </div>
                            </section>

                            <!-- Sección SSL eliminada para simplificar la interfaz -->

                            <!-- Sección Licencias eliminada para simplificar la interfaz -->
                        </div>

                        <div class="flex justify-between mt-8">
                            <button @click="router.visit(route('client.checkout.selectDomain'))"
                                :disabled="formPrimaryService.processing || formAdditionalService.processing"
                                class="px-6 py-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                                ← Regresar a Dominios
                            </button>
                            <button @click="goToFinalCheckout"
                                :disabled="formPrimaryService.processing || formAdditionalService.processing"
                                class="px-6 py-3 text-base font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Continuar al Pago
                            </button>
                        </div>
                    </div>

                    <div class="md:col-span-4">
                        <div class="sticky top-6">
                            <CartSummary ref="cartSummaryComp" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Estilos específicos si son necesarios */
</style>
