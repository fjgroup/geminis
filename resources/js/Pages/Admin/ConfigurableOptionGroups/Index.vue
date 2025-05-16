<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'; // Ajusta tu layout
import { Head, Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref, computed } from 'vue'; // Importar computed
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import Alert from '@/Components/Alert.vue'; // Importar el componente Alert
import { usePage } from '@inertiajs/vue3'; // Importar usePage
import { PlusIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline';


const props = defineProps({
    groups: Object, // Objeto de paginación de Inertia
});


const page = usePage(); // Obtener el objeto page
const showConfirmDeleteModal = ref(false);
const groupToDelete = ref(null);

// Para manejar los mensajes flash de forma segura
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);


const confirmDeleteGroup = (group) => {
    groupToDelete.value = group;
    showConfirmDeleteModal.value = true;
};

const deleteGroup = () => {
    if (groupToDelete.value) {
        router.delete(route('admin.configurable-option-groups.destroy', groupToDelete.value.id), {
            onSuccess: () => {
                showConfirmDeleteModal.value = false;
                groupToDelete.value = null;
                // Aquí podrías añadir una notificación toast si la tienes configurada
            },
            // onError: () => { // Manejar errores si es necesario }
        });
    }
};

const closeModal = () => {
    showConfirmDeleteModal.value = false;
    groupToDelete.value = null;
};

</script>

<template>
    <AdminLayout title="Grupos de Opciones Configurables">

        <Head title="Gestionar Grupos de Opciones Configurables" />
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Grupos de Opciones Configurables
                </h2>
                <Link :href="route('admin.configurable-option-groups.create')">
                <PrimaryButton class="flex items-center">
                    <PlusIcon class="w-5 h-5 mr-2" />
                    Crear Nuevo Grupo
                </PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div
                        class="p-6 text-gray-900 bg-white border-b border-gray-200 dark:bg-gray-800 dark:text-gray-100 md:p-8">
                        <Alert :message="flashSuccess" type="success" class="mb-4" />
                        <Alert :message="flashError" type="error" class="mb-4" />

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Nombre</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Producto Asociado</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Orden</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="groups.data.length === 0">
                                        <td colspan="4"
                                            class="px-6 py-4 text-sm text-center text-gray-500 whitespace-nowrap">
                                            No hay grupos de opciones configurables.
                                        </td>
                                    </tr>
                                    <tr v-for="group in groups.data" :key="group.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ group.name }}</div>
                                            <div class="text-sm text-gray-500">{{ group.description }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{
                                            group.product_name }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{
                                            group.display_order }}
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                            <Link :href="route('admin.configurable-option-groups.edit', group.id)"
                                                class="text-indigo-600 hover:text-indigo-900">
                                            <SecondaryButton class="flex items-center">
                                                <PencilSquareIcon class="w-4 h-4 mr-1" />
                                                Editar
                                            </SecondaryButton>
                                            </Link>
                                            <DangerButton @click="confirmDeleteGroup(group)" class="ml-2">
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
                        <Pagination class="mt-6" :links="groups.links" />
                    </div>
                </div>
            </div>
        </div>

        <ConfirmationModal :show="showConfirmDeleteModal" @close="closeModal">
            <template #title>
                Eliminar Grupo de Opciones
            </template>
            <template #content>
                ¿Estás seguro de que quieres eliminar el grupo "{{ groupToDelete?.name }}"? Esta acción no se puede
                deshacer.
            </template>
            <template #footer>
                <SecondaryButton @click="closeModal">Cancelar</SecondaryButton>
                <DangerButton @click="deleteGroup" class="ml-2" :class="{ 'opacity-25': router.processing }"
                    :disabled="router.processing">
                    Eliminar
                </DangerButton>
            </template>
        </ConfirmationModal>

    </AdminLayout>
</template>

<style scoped>
/* Add any specific styles here */
</style>
