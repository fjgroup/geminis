<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/Forms/Buttons/PrimaryButton.vue';
import SecondaryButton from '@/Components/Forms/Buttons/SecondaryButton.vue';
import DangerButton from '@/Components/Forms/Buttons/DangerButton.vue';
import ConfirmationModal from '@/Components/UI/ConfirmationModal.vue';
import Alert from '@/Components/UI/Alert.vue';
import { ref, computed } from 'vue';
import { PlusIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    paymentMethods: Array,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);


const showConfirmDeleteModal = ref(false);
const methodToDelete = ref(null);

const confirmDeleteMethod = (method) => {
    methodToDelete.value = method;
    showConfirmDeleteModal.value = true;
};

const closeModal = () => {
    showConfirmDeleteModal.value = false;
    methodToDelete.value = null;
};

const deleteMethod = () => {
    if (methodToDelete.value) {
        router.delete(route('admin.payment-methods.destroy', { payment_method: methodToDelete.value.id }), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            // onError: () => { /* Handle error if needed */ },
        });
    }
};
</script>

<template>
    <AdminLayout title="Métodos de Pago">
        <Head title="Gestionar Métodos de Pago" />

        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Métodos de Pago
                </h2>
                <Link :href="route('admin.payment-methods.create')">
                    <PrimaryButton class="flex items-center">
                        <PlusIcon class="w-5 h-5 mr-2" />
                        Crear Método de Pago
                    </PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <Alert :message="flashSuccess" type="success" v-if="flashSuccess" class="mb-4" />
                <Alert :message="flashError" type="danger" v-if="flashError" class="mb-4" />

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Nombre</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Banco</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Nº Cuenta</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase">Activo</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="paymentMethods.length === 0">
                                        <td colspan="5" class="px-6 py-4 text-sm text-center text-gray-500 whitespace-nowrap">
                                            No hay métodos de pago registrados.
                                        </td>
                                    </tr>
                                    <tr v-for="method in paymentMethods" :key="method.id">
                                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">{{ method.name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ method.bank_name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ method.account_number }}</td>
                                        <td class="px-6 py-4 text-sm text-center text-gray-500 whitespace-nowrap">
                                            <span class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full"
                                                  :class="method.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                                {{ method.is_active ? 'Sí' : 'No' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                            <Link :href="route('admin.payment-methods.edit', { payment_method: method.id })" class="text-indigo-600 hover:text-indigo-900">
                                                <SecondaryButton class="flex items-center">
                                                    <PencilSquareIcon class="w-4 h-4 mr-1" />
                                                    Editar
                                                </SecondaryButton>
                                            </Link>
                                            <DangerButton @click="confirmDeleteMethod(method)" class="ml-2">
                                                <span class="flex items-center">
                                                    <TrashIcon class="w-4 h-4 mr-1" />
                                                    Eliminar
                                                </span>
                                            </DangerButton>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmationModal :show="showConfirmDeleteModal" @close="closeModal">
            <template #title>
                Eliminar Método de Pago
            </template>
            <template #content>
                ¿Estás seguro de que deseas eliminar el método de pago "{{ methodToDelete?.name }}"? Esta acción no se puede deshacer.
            </template>
            <template #footer>
                <SecondaryButton @click="closeModal">
                    Cancelar
                </SecondaryButton>
                <DangerButton class="ml-3" @click="deleteMethod" :class="{ 'opacity-25': router.processing }" :disabled="router.processing">
                    Eliminar
                </DangerButton>
            </template>
        </ConfirmationModal>

    </AdminLayout>
</template>
