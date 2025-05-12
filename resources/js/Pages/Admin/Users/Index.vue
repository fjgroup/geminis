<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue'; // Asegúrate que la ruta a tu layout es correcta
import Pagination from '@/Components/Shared/Pagination.vue'; // Un componente de paginación reutilizable

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
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Lista de Usuarios
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-center justify-end mb-4">
                            <!-- <h1 class="text-2xl font-semibold">Usuarios</h1> -->
                            <Link :href="route('admin.users.create')" class="px-4 py-2 font-bold text-white bg-blue-500 rounded hover:bg-blue-700">
                                Crear Usuario
                            </Link>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Nombre</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Email</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Rol</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Estado</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Creado el</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="user in users.data" :key="user.id">
                                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">{{ user.name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ user.email }}</td>
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
                                            <span class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full"
                                                  :class="{
                                                      'bg-green-100 text-green-800': user.status === 'active',
                                                      'bg-yellow-100 text-yellow-800': user.status === 'inactive',
                                                      'bg-red-100 text-red-800': user.status === 'suspended',
                                                  }">
                                                {{ user.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                            {{ user.created_at_formatted }}
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                            <Link :href="route('admin.users.edit', user.id)" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
                                            <button @click="deleteUser(user.id)" class="ml-2 text-red-600 hover:text-red-900">Eliminar</button>
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
