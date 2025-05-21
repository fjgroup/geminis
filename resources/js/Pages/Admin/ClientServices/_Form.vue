<script setup>
import { ref, watch, computed } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextareaInput from '@/Components/TextareaInput.vue'; // Asumiendo que tienes este componente o usa <textarea>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import { Link } from '@inertiajs/vue3';
import { ArrowDownTrayIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    form: Object, // El objeto useForm de Inertia
    products: Array, // [{ id: id, name: name, pricings: [{ id: id, billing_cycle_id: id, price: price, billing_cycle: { id: id, name: name } }, ...] }, ...]
    statusOptions: Array, // [{ value: 'status_val', label: 'Status Label' }, ...]
    // clients: Array, // Para selectores con búsqueda asíncrona, no se pasarían todos
    // resellers: Array, // Para selectores con búsqueda asíncrona, no se pasarían todos
    isEdit: {
        type: Boolean,
        default: false,
    }
});

// 1. Log de props y products después de la definición de props

const emit = defineEmits(['submit']);

// Computed para obtener el producto seleccionado
const selectedProduct = computed(() => {
    return props.products?.find(product => product.value === parseInt(props.form.product_id));
});

// Computed para formatear los ciclos de facturación disponibles basados en el producto seleccionado
const billingCycleOptions = computed(() => {
    // Obtener los pricings del producto seleccionado
    const productPricings = selectedProduct.value?.pricings || [];

    // Mapear los pricings a opciones de ciclo de facturación, evitando duplicados por ciclo
    const cyclesMap = new Map();
    productPricings.forEach(pricing => {
        if (pricing.billing_cycle) {
            cyclesMap.set(pricing.billing_cycle.id, {
                value: pricing.billing_cycle.id,
                label: pricing.billing_cycle.name,
                pricing_id: pricing.id, // Guardar el ID del pricing también
                price: pricing.price,   // Guardar el precio también
            });
        }
    });

    return Array.from(cyclesMap.values());
});

// Función para calcular la próxima fecha de vencimiento
function calculateNextDueDate(registrationDate, numberOfDays) {
    if (!registrationDate || typeof numberOfDays !== 'number' || numberOfDays < 0) {
        return '';
    }

    const date = new Date(registrationDate);
    // Clonar la fecha para no modificar la original
    const nextDate = new Date(date);

    // Sumar los días al timestamp
    nextDate.setDate(nextDate.getDate() + numberOfDays);

    // Formatear la fecha en YYYY-MM-DD
    const year = nextDate.getFullYear();
    const month = String(nextDate.getMonth() + 1).padStart(2, '0');
    const day = String(nextDate.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}


// Observar cambios en form.product_id para resetear ciclo y pricing
watch(() => props.form.product_id, (newProductId) => {
    // Resetear billing_cycle_id y product_pricing_id cuando cambie el producto
    props.form.billing_cycle_id = null;
    props.form.product_pricing_id = null;
    props.form.billing_amount = 0.00; // Resetear también el monto de facturación
});

// Observar cambios en form.billing_cycle_id para actualizar la próxima fecha de vencimiento, product_pricing_id y billing_amount
watch(() => props.form.billing_cycle_id, (newBillingCycleId) => {
    if (newBillingCycleId) {
        // Encontrar el pricing correspondiente al producto seleccionado y el ciclo de facturación
        const selectedPricing = selectedProduct.value?.pricings?.find(pricing =>
            pricing.billing_cycle_id === parseInt(newBillingCycleId)
        );

        if (selectedPricing) {
            // Usar los días del ciclo de facturación relacionado para calcular la próxima fecha de vencimiento
            props.form.next_due_date = calculateNextDueDate(props.form.registration_date, selectedPricing.billing_cycle?.days);
            // Actualizar product_pricing_id y billing_amount basándose en el pricing encontrado
            props.form.product_pricing_id = selectedPricing.id;
            props.form.billing_amount = parseFloat(selectedPricing.price).toFixed(2);
        } else {
            // Si no se encuentra el pricing (puede pasar si se carga un formulario de edición con valores inválidos)
            props.form.next_due_date = '';
            props.form.product_pricing_id = null;
            props.form.billing_amount = 0.00;
        }
    } else {
        // Si se borra la selección del ciclo, resetear la fecha, pricing_id y monto
        props.form.next_due_date = '';
        props.form.product_pricing_id = null;
        props.form.billing_amount = 0.00;
    }
}, { immediate: true }); // Ejecutar inmediatamente si billing_cycle_id ya tiene valor (en edición)


const submitForm = () => {
    emit('submit');
};

// TODO: Implementar componentes de búsqueda asíncrona para Cliente y Revendedor
// Por ahora, se usarán TextInput para sus IDs.

</script>

<template>
    <form @submit.prevent="submitForm">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <InputLabel for="client_id" value="ID del Cliente" required />
                <TextInput id="client_id" type="number" class="block w-full mt-1" v-model="form.client_id" />
                <InputError class="mt-2" :message="form.errors.client_id" />
                <!-- <AsyncSelect v-model="form.client_id" searchUrl="admin.search.clients" placeholder="Buscar cliente..." /> -->
            </div>

            <div>
                <InputLabel for="product_id" value="Producto" required />
                <SelectInput id="product_id" class="block w-full mt-1" v-model="form.product_id" :options="products"
                    placeholder="Seleccione un producto" />
                <InputError class="mt-2" :message="form.errors.product_id" />
            </div>

            <div>
                <InputLabel for="billing_cycle_id" value="Ciclo de Facturación" required />
                <SelectInput id="billing_cycle_id" class="block w-full mt-1" v-model="form.billing_cycle_id"
                    :options="billingCycleOptions"
                    placeholder="Seleccione un ciclo" />
                <InputError class="mt-2" :message="form.errors.billing_cycle_id" />
            </div>

            <div>
                <InputLabel for="billing_amount" value="Monto de Facturación" required />
                <TextInput id="billing_amount" type="number" step="0.01" class="block w-full mt-1" v-model="form.billing_amount" />
                <InputError class="mt-2" :message="form.errors.billing_amount" />
            </div>

            <div>
                <InputLabel for="registration_date" value="Fecha de Registro" required />
                <TextInput id="registration_date" type="date" class="block w-full mt-1" v-model="form.registration_date" />
                <InputError class="mt-2" :message="form.errors.registration_date" />
            </div>

            <div>
                <InputLabel for="next_due_date" value="Próxima Fecha de Vencimiento" required />
                <TextInput id="next_due_date" type="date" class="block w-full mt-1" v-model="form.next_due_date" />
                <InputError class="mt-2" :message="form.errors.next_due_date" />
            </div>

            <div>
                <InputLabel for="status" value="Estado" required />
                <SelectInput id="status" class="block w-full mt-1" v-model="form.status" :options="statusOptions" />
                <InputError class="mt-2" :message="form.errors.status" />
            </div>

            <div>
                <InputLabel for="domain_name" value="Nombre de Dominio (Opcional)" />
                <TextInput id="domain_name" type="text" class="block w-full mt-1" v-model="form.domain_name" />
                <InputError class="mt-2" :message="form.errors.domain_name" />
            </div>

            <div>
                <InputLabel for="username" value="Nombre de Usuario (Servicio, Opcional)" />
                <TextInput id="username" type="text" class="block w-full mt-1" v-model="form.username" autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.username" />
            </div>

            <div>
                <InputLabel for="password_encrypted" value="Contraseña (Servicio, Opcional)" />
                <TextInput id="password_encrypted" type="password" class="block w-full mt-1" v-model="form.password_encrypted" autocomplete="new-password" />
                <InputError class="mt-2" :message="form.errors.password_encrypted" />
            </div>

            <div>
                <InputLabel for="reseller_id" value="ID del Revendedor (Opcional)" />
                <TextInput id="reseller_id" type="number" class="block w-full mt-1" v-model="form.reseller_id" />
                <InputError class="mt-2" :message="form.errors.reseller_id" />
                <!-- <AsyncSelect v-model="form.reseller_id" searchUrl="admin.search.resellers" placeholder="Buscar revendedor..." /> -->
            </div>

            <div>
                <InputLabel for="server_id" value="ID del Servidor (Opcional)" />
                <TextInput id="server_id" type="number" class="block w-full mt-1" v-model="form.server_id" />
                <InputError class="mt-2" :message="form.errors.server_id" />
                <!-- TODO: Cuando exista el modelo Server, cambiar a AsyncSelect o SelectInput -->
            </div>
        </div>

        <div class="mt-6">
            <InputLabel for="notes" value="Notas (Opcional)" />
            <TextareaInput id="notes" class="block w-full mt-1" v-model="form.notes" :rows="4" />
            <InputError class="mt-2" :message="form.errors.notes" />
        </div>

        <div class="flex items-center justify-end pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
            <Link :href="route('admin.client-services.index')"
                class="flex items-center px-4 py-2 mr-4 text-sm text-gray-600 border border-gray-300 rounded-md dark:text-gray-400 dark:border-gray-500 hover:text-gray-900 hover:bg-gray-50 dark:hover:bg-gray-700">
            <XMarkIcon class="w-5 h-5 mr-1" />
            Cancelar
            </Link>
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                class="flex items-center">
                <ArrowDownTrayIcon class="w-5 h-5 mr-2" />
                {{ isEdit ? 'Actualizar Servicio' : 'Crear Servicio' }}
            </PrimaryButton>
        </div>
    </form>
</template>
