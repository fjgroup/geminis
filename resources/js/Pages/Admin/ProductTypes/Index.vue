<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3'; // Added router, usePage
import PrimaryButton from '@/Components/Forms/Buttons/PrimaryButton.vue';
import SecondaryButton from '@/Components/Forms/Buttons/SecondaryButton.vue';
import DangerButton from '@/Components/Forms/Buttons/DangerButton.vue';
import ConfirmationModal from '@/Components/UI/ConfirmationModal.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import Alert from '@/Components/UI/Alert.vue';
import { ref, computed } from 'vue';
import { PlusIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    productTypes: Object, // Paginated object from controller
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const showConfirmDeleteModal = ref(false);
const productTypeToDelete = ref(null);

const confirmDeleteProductType = (productType) => {
    productTypeToDelete.value = productType;
    showConfirmDeleteModal.value = true;
};

const closeModal = () => {
    showConfirmDeleteModal.value = false;
    productTypeToDelete.value = null;
};

const deleteProductType = () => {
    if (productTypeToDelete.value) {
        router.delete(route('admin.product-types.destroy', productTypeToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: () => {
                // Error already handled by flash message from controller if deletion fails due to associations
                closeModal();
            }
        });
    }
};
</script>

<template>

    <Head title="Tipos de Producto" />

    <AdminLayout title="Tipos de Producto">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Gestión de Tipos de Producto
                </h2>
                <Link :href="route('admin.product-types.create')"
                    class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                <PlusIcon class="w-4 h-4 mr-2" />
                CREAR TIPO
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <Alert :message="flashSuccess" type="success" v-if="flashSuccess" class="mb-4" />
                <Alert :message="flashError" type="danger" v-if="flashError" class="mb-4" />

                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Nombre</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Slug</th>
                                        <th
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Requiere Dominio</th>
                                        <th
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Crea Instancia</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-if="!productTypes.data.length">
                                        <td colspan="5"
                                            class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                            No hay tipos de producto definidos.
                                        </td>
                                    </tr>
                                    <tr v-for="ptype in productTypes.data" :key="ptype.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{
                                                ptype.name }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{
                                                ptype.description?.substring(0, 50) + (ptype.description?.length > 50 ?
                                                    '...' :
                                                    '') }}</div>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{
                                                ptype.slug }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                :class="ptype.requires_domain ? 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100'">
                                                {{ ptype.requires_domain ? 'Sí' : 'No' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                :class="ptype.creates_service_instance ? 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100'">
                                                {{ ptype.creates_service_instance ? 'Sí' : 'No' }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <Link :href="route('admin.product-types.edit', ptype.id)">
                                            <SecondaryButton class="flex items-center">
                                                <PencilSquareIcon class="w-4 h-4 mr-1" /> Editar
                                            </SecondaryButton>
                                            </Link>
                                            <DangerButton @click="confirmDeleteProductType(ptype)"
                                                class="flex items-center">
                                                <TrashIcon class="w-4 h-4 mr-1" /> Eliminar
                                            </DangerButton>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <Pagination class="mt-6" :links="productTypes.links" v-if="productTypes.data.length > 0" />
                    </div>
                </div>
            </div>
        </div>

        <ConfirmationModal :show="showConfirmDeleteModal" @close="closeModal">
            <template #title>
                Eliminar Tipo de Producto
            </template>
            <template #content>
                ¿Estás seguro de que deseas eliminar el tipo de producto "{{ productTypeToDelete?.name }}"? Esta acción
                no se puede deshacer.
                Si este tipo de producto está asociado a productos existentes, no se podrá eliminar.
            </template>
            <template #footer>
                <SecondaryButton @click="closeModal">Cancelar</SecondaryButton>
                <DangerButton @click="deleteProductType" class="ml-3" :class="{ 'opacity-25': router.processing }"
                    :disabled="router.processing">
                    Eliminar
                </DangerButton>
            </template>
        </ConfirmationModal>

    </AdminLayout>
</template>
