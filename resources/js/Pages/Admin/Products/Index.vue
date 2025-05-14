<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue'; //
import PrimaryButton from '@/Components/PrimaryButton.vue'; // Ruta actualizada para consistencia
import { PlusIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline'; // Añadido para el icono


const props = defineProps({
    products: Object, // El objeto paginado de Laravel con los productos
});


const deleteProduct = (productId, productName) => {
    if (confirm(`¿Estás seguro de que deseas eliminar el producto "${productName}"?`)) {
        router.delete(route('admin.products.destroy', productId), {
            preserveScroll: true,
            onSuccess: () => {
                // Opcional: mostrar notificación de éxito
                // Por ejemplo, si usas un sistema de notificaciones global:
                // router.page.props.flash.success = 'Producto eliminado exitosamente.';
            },
            onError: (errors) => {
                // Opcional: mostrar notificación de error
                // alert('Error al eliminar el producto.');
                console.error('Error deleting product:', errors);
            }
        });
    }
};
</script>

<template>
    <AdminLayout title="Manage Products">

        <Head title="Manage Products" />

        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Gestionar Productos
                </h2>

                <Link :href="route('admin.products.create')">
                <PrimaryButton class="flex items-center">
                    <PlusIcon class="w-5 h-5 mr-2" />
                    Crear Producto
                </PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                            ID</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                            Nombre</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                            Slug</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                            Tipo</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                            Propietario</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                            Estado</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                            Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                    <tr v-if="products.data.length === 0">
                                        <td colspan="7"
                                            class="px-6 py-4 text-sm text-center text-gray-500 whitespace-nowrap dark:text-gray-400">
                                            No se encontraron productos.
                                        </td>
                                    </tr>
                                    <tr v-for="product in products.data" :key="product.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td
                                            class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">
                                            {{
                                            product.id }}</td>
                                        <td
                                            class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">
                                            {{
                                            product.name }}</td>
                                        <td
                                            class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                                            {{
                                            product.slug }}</td>
                                        <td
                                            class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                                            {{
                                            product.type }}</td>
                                        <td
                                            class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                                            {{
                                            product.owner_name || 'N/A' }}</td>
                                        <td
                                            class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                                            <span class="inline-block w-3 h-3 rounded-full"
                                                :class="{
                                                    'bg-green-500': product.status === 'active',
                                                    'bg-red-500': product.status === 'inactive',
                                                    'bg-blue-500': product.status === 'hidden',
                                                }" :title="product.status">
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                            <!-- <Link :href="route('admin.products.show', product.id)" class="mr-2 text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">Ver</Link> -->
                                            <Link :href="route('admin.products.edit', product.id)" class="inline-flex items-center px-2 py-1 text-sm font-medium text-indigo-600 rounded-md hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                <PencilSquareIcon class="w-4 h-4 mr-1" />
                                                Editar
                                            </Link>
                                            <button @click="deleteProduct(product.id, product.name)" class="inline-flex items-center px-2 py-1 ml-2 text-sm font-medium text-red-600 rounded-md hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                <TrashIcon class="w-4 h-4 mr-1" />
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <Pagination class="mt-6" :links="products.links" v-if="products.data.length > 0" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
