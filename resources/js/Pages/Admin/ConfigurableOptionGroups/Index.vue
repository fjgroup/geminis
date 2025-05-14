<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'; // Ajusta tu layout
import { Head, Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue'; // Asumiendo que tienes este componente
import DangerButton from '@/Components/DangerButton.vue';   // Asumiendo que tienes este componente
import Pagination from '@/Components/Pagination.vue'; // Asumiendo que tienes este componente
import { ref } from 'vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue'; // Asumiendo que tienes este componente

const props = defineProps({
    groups: Object, // Objeto de paginación de Inertia
});

const showConfirmDeleteModal = ref(false);
const groupToDelete = ref(null);

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
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Grupos de Opciones Configurables
                </h2>
                <Link :href="route('admin.configurable-option-groups.create')">
                <PrimaryButton>Crear Nuevo Grupo</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200 md:p-8">
                        <div v-if="$page.props.flash.success"
                            class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                            {{ $page.props.flash.success }}
                        </div>

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
                                            <SecondaryButton>Editar</SecondaryButton>
                                            </Link>
                                            <DangerButton @click="confirmDeleteGroup(group)" class="ml-2">
                                                Eliminar
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
