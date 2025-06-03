<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue'; // Asumiendo nueva ruta
import SecondaryButton from '@/Components/SecondaryButton.vue'; // Asumiendo nueva ruta
import DangerButton from '@/Components/DangerButton.vue'; // Asumiendo nueva ruta
import Pagination from '@/Components/UI/Pagination.vue';
import { ref, computed } from 'vue';
import ConfirmationModal from '@/Components/UI/ConfirmationModal.vue';
import Alert from '@/Components/UI/Alert.vue';
import { usePage } from '@inertiajs/vue3';
import { PlusIcon, PencilSquareIcon, TrashIcon, EyeIcon } from '@heroicons/vue/24/outline';


const props = defineProps({
    users: Object, // El objeto paginado de Laravel
    // filters: Object, // Si pasas filtros desde el controlador
    // can: Object, // Si pasas permisos
});

const editUser = (userId) => {
    router.get(route('admin.users.edit', userId));
};

const deleteUser = (userId) => {
    if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
        router.delete(route('admin.users.destroy', userId), {
            preserveScroll: true,
            onSuccess: () => {
                // Opcional: mostrar notificación de éxito
            },
            onError: () => {
                // Opcional: mostrar notificación de error
            }
        });
    }
};
</script>

<template>
    <AdminLayout title="Gestionar Usuarios"> <!-- Asegúrate que el título de la prop es 'title' en AdminLayout -->

        <Head title="Gestionar Usuarios" />
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Lista de Usuarios
                </h2>
                <Link :href="route('admin.users.create')">
                    <PrimaryButton class="flex items-center">
                        <PlusIcon class="w-5 h-5 mr-2" />
                        Crear Usuario
                    </PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- El botón de crear usuario se movió al header -->

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Nombre</th>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Email</th>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Rol</th>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Estado</th> <!-- Corregido: Estado -->
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Creado el</th>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="user in users.data" :key="user.id">
                                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">{{ user.name }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ user.email }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                            <span class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full"
                                                :class="{
                                                    'bg-blue-100 text-blue-800': user.role === 'admin',
                                                    'bg-green-100 text-green-800': user.role === 'client',
                                                    'bg-yellow-100 text-yellow-800': user.role === 'reseller',
                                                }">
                                                {{ user.role }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                            <span class="inline-block w-3 h-3 rounded-full"
                                                :class="{
                                                    'bg-green-500': user.status === 'active',
                                                    'bg-red-500': user.status === 'inactive',
                                                    'bg-yellow-500': user.status === 'suspended',
                                                }"
                                                :title="user.status">
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                            {{ user.created_at_formatted }}
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                            <Link :href="route('admin.users.edit', user.id)"
                                                class="text-indigo-600 hover:text-indigo-900">
                                            <SecondaryButton class="flex items-center">
                                                <PencilSquareIcon class="w-4 h-4 mr-1" />
                                                Editar
                                            </SecondaryButton>
                                            </Link>
                                            <DangerButton @click="deleteUser(user.id)" class="ml-2">

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
                        <Pagination class="mt-6" :links="users.links" v-if="users.data.length > 0" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
