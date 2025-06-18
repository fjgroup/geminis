<script setup lang="ts">
import { computed } from 'vue';
import Icon from "../UI/Icon.vue";
import Tooltip from "../UI/Tooltip.vue";

// Definiciones de tipos traducidas de las interfaces de React
interface PlanFeatureObject {
  text: string;
  explanation?: string;
}
type PlanFeature = string | PlanFeatureObject;

interface PlanData {
  id: string;
  planIcon?: string; // Puede ser opcional según el uso en la plantilla React (plan.planIcon || categoryIcon)
  name: string;
  description: string;
  accounts_limit?: string | number;
  accounts_limit_text?: string;
  storage_gb?: number;
  storage_type?: string;
  storage_gb_info?: string; // Para VPS
  cpu_cores?: number;
  cpu_cores_info?: string; // Para VPS
  ram_gb?: number;
  ram_gb_info?: string; // Para VPS
  domains_limit?: number;
  domains_limit_text?: string;
  features: PlanFeature[];
  price_introductory: number;
  price_renewal: number;
  billing_cycle: string;
  notes?: string;
}

interface PlanCardProps {
  plan: PlanData;
  categoryIcon: string;
  currencySymbol: string;
  markupPercentage: number;
  buttonText: string;
}

// Definición de Props
const props = defineProps<PlanCardProps>();

// Definición de Emits
const emit = defineEmits<{
  (e: 'contact', planName: string): void;
}>();

// Lógica traducida
const baseRenewalPrice = computed(() => props.plan.price_renewal);
const markup = computed(() => props.markupPercentage);

const displayedMonthlyRenewalPrice = computed(() => baseRenewalPrice.value * (1 + markup.value));
// El precio introductorio es un 8% más que el precio base de renovación, y este es el precio final mostrado.
const displayedIntroPrice = computed(() => baseRenewalPrice.value * 1.08);

const introDiscountPercent = computed(() => {
  if (displayedMonthlyRenewalPrice.value > 0 && displayedIntroPrice.value < displayedMonthlyRenewalPrice.value) {
    return ((displayedMonthlyRenewalPrice.value - displayedIntroPrice.value) / displayedMonthlyRenewalPrice.value) * 100;
  }
  return 0;
});

// Precios por pago adelantado (mensual equivalente, mostrando el precio base con descuento)
const prepay12mMonthlyPrice = computed(() => baseRenewalPrice.value); // Ahorra la comisión
const prepay24mMonthlyPrice = computed(() => baseRenewalPrice.value * 0.90); // 10% dto sobre base
const prepay36mMonthlyPrice = computed(() => baseRenewalPrice.value * 0.80); // 20% dto sobre base

const calculatePrepayDiscountPercent = (prepayPrice: number) => {
  if (displayedMonthlyRenewalPrice.value > 0 && prepayPrice < displayedMonthlyRenewalPrice.value) {
    return ((displayedMonthlyRenewalPrice.value - prepayPrice) / displayedMonthlyRenewalPrice.value) * 100;
  }
  return 0;
};

const discount12mPercent = computed(() => calculatePrepayDiscountPercent(prepay12mMonthlyPrice.value));
const discount24mPercent = computed(() => calculatePrepayDiscountPercent(prepay24mMonthlyPrice.value));
const discount36mPercent = computed(() => calculatePrepayDiscountPercent(prepay36mMonthlyPrice.value));

const handleContact = () => {
  emit('contact', props.plan.name);
};

</script>

<template>
    <div
        class="plan-card bg-slate-50 text-slate-900 rounded-xl shadow-2xl p-6 flex flex-col transition-all duration-300 hover:shadow-brand-blue/30 hover:scale-[1.02] animate-zoom-in h-full">
        <div class="flex items-center mb-5">
            <Icon :name="categoryIcon" class="w-10 h-10 mr-3 text-brand-blue" />
            <h3 class="text-2xl font-semibold text-slate-800">{{ plan.name }}</h3>
            <Icon v-if="plan.planIcon" :name="plan.planIcon" class="w-10 h-10 ml-3 text-brand-blue" />
        </div>
        <p class="text-slate-600 mb-2 text-sm min-h-[40px]">{{ plan.description }}</p>

        <div class="my-5">
            <div class="flex items-baseline">
                <p class="text-4xl font-bold text-slate-800">
                    {{ currencySymbol }}{{ displayedIntroPrice.toFixed(2) }}
                    <span class="text-lg font-normal text-slate-500">/{{ plan.billing_cycle }}</span>
                </p>
                <span v-if="introDiscountPercent > 0" class="ml-2 text-sm font-semibold text-green-600">
                    (Ahorra ~{{ introDiscountPercent.toFixed(0) }}%)
                </span>
            </div>
            <div class="text-sm text-slate-500">
                <span>Renovación: {{ currencySymbol }}{{ displayedMonthlyRenewalPrice.toFixed(2) }}/{{
                    plan.billing_cycle }}</span>
            </div>
            <p v-if="plan.notes" class="mt-1 text-xs text-slate-500">{{ plan.notes }}</p>
        </div>

        <!-- Nueva sección para destacar ahorros por prepago -->
        <div
            class="my-4 p-3.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center text-sm shadow-sm">
            <Icon name="tag" class="w-7 h-7 mr-2.5 text-emerald-600 flex-shrink-0" />
            <div class="flex-grow">
                <span class="font-semibold">¡Oferta Especial!</span>
                <span class="block sm:inline sm:ml-1">Ahorra más pagando por adelantado.</span>
            </div>
            <Tooltip :text="`Pagando por adelantado (precio mensual equivalente):
- 12 meses: ${currencySymbol}${prepay12mMonthlyPrice.toFixed(2)}/mes (Ahorra ${discount12mPercent.toFixed(0)}%)
- 24 meses: ${currencySymbol}${prepay24mMonthlyPrice.toFixed(2)}/mes (Ahorra ${discount24mPercent.toFixed(0)}%)
- 36 meses: ${currencySymbol}${prepay36mMonthlyPrice.toFixed(2)}/mes (Ahorra ${discount36mPercent.toFixed(0)}%)`">
                <Icon name="information-circle"
                    class="flex-shrink-0 w-5 h-5 ml-2 cursor-help text-emerald-500 hover:text-emerald-700" />
            </Tooltip>
        </div>

        <ul class="flex-grow mb-6 space-y-2 text-sm text-slate-700">
            <li v-if="plan.storage_gb"><strong>Almacenamiento:</strong> {{ plan.storage_gb }} GB {{ plan.storage_type }}
            </li>
            <li v-if="plan.storage_gb_info"><strong>Almacenamiento:</strong> {{ plan.storage_gb_info }}</li>
            <li v-if="plan.accounts_limit"><strong>Cuentas:</strong> {{ plan.accounts_limit }}</li>
            <li v-if="plan.accounts_limit_text"><strong>Cuentas:</strong> {{ plan.accounts_limit_text }}</li>
            <li v-if="plan.cpu_cores"><strong>CPU Cores:</strong> {{ plan.cpu_cores }}</li>
            <li v-if="plan.cpu_cores_info"><strong>CPU Cores:</strong> {{ plan.cpu_cores_info }}</li>
            <li v-if="plan.ram_gb"><strong>RAM:</strong> {{ plan.ram_gb }} GB</li>
            <li v-if="plan.ram_gb_info"><strong>RAM:</strong> {{ plan.ram_gb_info }}</li>
            <li v-if="plan.domains_limit"><strong>Dominios:</strong> {{ plan.domains_limit }}</li>
            <li v-if="plan.domains_limit_text"><strong>Dominios:</strong> {{ plan.domains_limit_text }}</li>
            <li v-for="(feature, index) in plan.features" :key="index" class="flex items-center">
                <Icon name="check-circle" class="flex-shrink-0 w-5 h-5 mr-2 text-green-500" />
                {{ typeof feature === 'string' ? feature : feature.text }}
                <Tooltip v-if="typeof feature === 'object' && feature.explanation" :text="feature.explanation">
                    <Icon name="information-circle" class="w-4 h-4 text-slate-500 ml-1.5 cursor-help" />
                </Tooltip>
            </li>
        </ul>
        <button @click="handleContact"
            class="w-full mt-auto plan-card-button border-2 border-blue-800 text-blue-800 font-semibold py-2.5 px-4 rounded-lg hover:bg-blue-800 hover:text-white transition-colors duration-300 flex items-center justify-center group">
            {{ buttonText }}
            <Icon name="arrow-right" class="w-5 h-5 ml-2 transition-transform transform group-hover:translate-x-1" />
        </button>
    </div>
</template>

<style scoped>
/* Los estilos de Tailwind CSS se aplican directamente en la plantilla. */
/* Añadir estilos específicos aquí si fuera necesario */
</style>
