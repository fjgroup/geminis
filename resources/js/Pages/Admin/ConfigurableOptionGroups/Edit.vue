<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'; // Ajusta tu layout
import { Head, useForm, router, Link, usePage } from '@inertiajs/vue3'; //
import ConfigurableOptionGroupForm from './_Form.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputLabel from '@/Components/Forms/InputLabel.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import InputError from '@/Components/Forms/InputError.vue';
import ConfirmationModal from '@/Components/UI/ConfirmationModal.vue';
import Alert from '@/Components/UI/Alert.vue';
import { ref, computed } from 'vue';
import { PlusCircleIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline'; // Asegúrate de tener estos iconos instalados

const props = defineProps({
    group: Object,
    products: Array,
    errors: Object, // Para errores de validación de las opciones
});

const page = usePage(); // Obtener el objeto page
// Para manejar los mensajes flash de forma segura
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);


const form = useForm({
    _method: 'PUT', // This is correct
    name: props.group.name,
    description: props.group.description || '',
    product_id: props.group.product_id || null,
    display_order: props.group.display_order || 0
});

const submit = () => {
    form.put(route('admin.configurable-option-groups.update', props.group.id)); // Change from form.post to form.put
};

// --- Lógica para Configurable Options ---
const showAddOptionModal = ref(false);
const showEditOptionModal = ref(false);
const showConfirmDeleteOptionModal = ref(false);

const optionToDelete = ref(null);
const optionToEdit = ref(null);

const newOptionForm = useForm({
    name: '',
    value: '',
    display_order: 0,
});

const editOptionForm = useForm({
    id: null,
    name: '',
    value: '',
    display_order: 0,
});

const openAddOptionModal = () => {
    newOptionForm.reset();
    showAddOptionModal.value = true;
};

const addOption = () => {
    newOptionForm.post(route('admin.configurable-option-groups.options.store', props.group.id), {
        preserveScroll: true,
        onSuccess: () => {
            showAddOptionModal.value = false;
            newOptionForm.reset();
        },
        // onError: () => { // Los errores se manejarán a través de props.errors }
    });
};

const confirmDeleteOption = (option) => {
    optionToDelete.value = option;
    showConfirmDeleteOptionModal.value = true;
};

const deleteOption = () => {
    if (optionToDelete.value) {
        router.delete(route('admin.options.destroy', optionToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                showConfirmDeleteOptionModal.value = false;
                optionToDelete.value = null;
            }
        });
    }
};

const closeModal = () => { // Añadir esta función
    showConfirmDeleteOptionModal.value = false;
    optionToDelete.value = null;
};

const openEditOptionModal = (option) => {
    optionToEdit.value = option;
    editOptionForm.id = option.id;
    editOptionForm.name = option.name;
    editOptionForm.value = option.value || '';
    editOptionForm.display_order = option.display_order || 0;
    showEditOptionModal.value = true;
};

const updateOption = () => {
    editOptionForm.put(route('admin.options.update', editOptionForm.id), {
        preserveScroll: true,
        onSuccess: () => {
            showEditOptionModal.value = false;
            optionToEdit.value = null;
            editOptionForm.reset();
        }
    });
};

const groupOptions = computed(() => props.group.options || []);

</script>

<template>
    <AdminLayout :title="'Editar Grupo - ' + group.name">

        <Head :title="'Editar Grupo de Opciones - ' + group.name" />
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Editar Grupo de Opciones: {{ group.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="p-6 overflow-hidden bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg">
                    <Alert :message="flashSuccess" type="success" class="mb-4" />
                    <Alert :message="flashError" type="error" class="mb-4" />


                    <ConfigurableOptionGroupForm :form="form" :products="products" :isEdit="true" @submit="submit" />

                    <!-- Sección para Configurable Options -->
                    <div class="pt-6 mt-8 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Opciones Configurables</h3>
                            <PrimaryButton @click="openAddOptionModal" class="flex items-center">
                                <PlusCircleIcon class="w-5 h-5 mr-2" />
                                Añadir Opción
                            </PrimaryButton>
                        </div>

                        <div v-if="groupOptions.length === 0" class="py-4 text-center text-gray-500 dark:text-gray-400">
                            No hay opciones configurables para este grupo.
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                            Nombre</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                            Versión/Año (opcional)</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                            Orden</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                            Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                    <tr v-for="option in groupOptions" :key="option.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td
                                            class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{ option.name }}</td>
                                        <td
                                            class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                                            {{ option.value }}</td>
                                        <td
                                            class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                                            {{ option.display_order }}</td>
                                        <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                            <SecondaryButton @click="openEditOptionModal(option)"
                                                class="flex items-center mr-2">
                                                <PencilSquareIcon class="w-4 h-4 mr-1" />
                                                Editar
                                            </SecondaryButton>
                                            <DangerButton @click="confirmDeleteOption(option)"
                                                class="flex items-center">
                                                <TrashIcon class="w-4 h-4 mr-1" />
                                                Eliminar
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

        <!-- Modal para Añadir Opción -->
        <ConfirmationModal :show="showAddOptionModal" @close="showAddOptionModal = false">
            <template #title>Añadir Nueva Opción Configurable</template>
            <template #content>
                <form @submit.prevent="addOption" id="addOptionForm">
                    <div class="mb-4">
                        <InputLabel for="new_option_name" value="Nombre de la Opción" />
                        <TextInput id="new_option_name" type="text" class="block w-full mt-1"
                            v-model="newOptionForm.name" required />
                        <InputError class="mt-2" :message="newOptionForm.errors.name || errors?.name" />
                    </div>
                    <div class="mb-4">
                        <InputLabel for="new_option_value" value="Versión/Año (opcional)" />
                        <TextInput id="new_option_value" type="text" class="block w-full mt-1"
                            v-model="newOptionForm.value" />
                        <InputError class="mt-2" :message="newOptionForm.errors.value || errors?.value" />
                    </div>
                    <div>
                        <InputLabel for="new_option_display_order" value="Prioridad" />
                        <TextInput id="new_option_display_order" type="number" class="block w-full mt-1"
                            v-model="newOptionForm.display_order" />
                        <InputError class="mt-2"
                            :message="newOptionForm.errors.display_order || errors?.display_order" />
                    </div>
                </form>
            </template>
            <template #footer>
                <SecondaryButton @click="showAddOptionModal = false">Cancelar</SecondaryButton>
                <PrimaryButton @click="addOption" class="ml-3" :class="{ 'opacity-25': newOptionForm.processing }"
                    :disabled="newOptionForm.processing">
                    Guardar Opción
                </PrimaryButton>
            </template>
        </ConfirmationModal>

        <!-- Modal para Editar Opción -->
        <ConfirmationModal :show="showEditOptionModal" @close="showEditOptionModal = false">
            <template #title>Editar Opción Configurable</template>
            <template #content>
                <form @submit.prevent="updateOption" id="editOptionForm">
                    <div class="mb-4">
                        <InputLabel for="edit_option_name" value="Nombre de la Opción" />
                        <TextInput id="edit_option_name" type="text" class="block w-full mt-1"
                            v-model="editOptionForm.name" required />
                        <InputError class="mt-2" :message="editOptionForm.errors.name || errors?.name" />
                    </div>
                    <div class="mb-4">
                        <InputLabel for="edit_option_value" value="Versión/Año (opcional)" />
                        <TextInput id="edit_option_value" type="text" class="block w-full mt-1"
                            v-model="editOptionForm.value" />
                        <InputError class="mt-2" :message="editOptionForm.errors.value || errors?.value" />
                    </div>
                    <div>
                        <InputLabel for="edit_option_display_order" value="Prioridad" />
                        <TextInput id="edit_option_display_order" type="number" class="block w-full mt-1"
                            v-model="editOptionForm.display_order" />
                        <InputError class="mt-2"
                            :message="editOptionForm.errors.display_order || errors?.display_order" />
                    </div>
                </form>
            </template>
            <template #footer>
                <SecondaryButton @click="showEditOptionModal = false">Cancelar</SecondaryButton>
                <PrimaryButton @click="updateOption" class="ml-3" :class="{ 'opacity-25': editOptionForm.processing }"
                    :disabled="editOptionForm.processing">
                    Actualizar Opción
                </PrimaryButton>
            </template>
        </ConfirmationModal>

        <!-- Modal para Confirmar Eliminación de Opción -->
        <ConfirmationModal :show="showConfirmDeleteOptionModal" @close="closeModal">
            <template #title>Eliminar Opción Configurable</template>
            <template #content>
                ¿Estás seguro de que quieres eliminar la opción "{{ optionToDelete?.name }}"?
            </template>
            <template #footer>
                <SecondaryButton @click="closeModal">Cancelar</SecondaryButton>
                <DangerButton @click="deleteOption" class="ml-3" :class="{ 'opacity-25': router.processing }"
                    :disabled="router.processing">
                    Eliminar
                </DangerButton>
            </template>
        </ConfirmationModal>

    </AdminLayout>
</template>

<style scoped>
/* Puedes añadir estilos específicos aquí si es necesario */
</style>
