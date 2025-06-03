<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
// import { Link, router, usePage } from '@inertiajs/vue3';
// import PrimaryButton from '@/Components/PrimaryButton.vue';
// import SecondaryButton from '@/Components/SecondaryButton.vue';
// import DangerButton from '@/Components/DangerButton.vue';
// import ConfirmationModal from '@/Components/UI/ConfirmationModal.vue';
// import Alert from '@/Components/UI/Alert.vue';
// import { ref, computed } from 'vue';
// import { PlusIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline';

// const props = defineProps({
//     paymentMethods: Array,
// });

// const page = usePage();
// const flashSuccess = computed(() => page.props.flash.success);
// const flashError = computed(() => page.props.flash.error);


// const showConfirmDeleteModal = ref(false);
// const methodToDelete = ref(null);

// const confirmDeleteMethod = (method) => {
//     methodToDelete.value = method;
//     showConfirmDeleteModal.value = true;
// };

// const closeModal = () => {
//     showConfirmDeleteModal.value = false;
//     methodToDelete.value = null;
// };

// const deleteMethod = () => {
//     if (methodToDelete.value) {
//         router.delete(route('admin.payment-methods.destroy', { payment_method: methodToDelete.value.id }), {
//             preserveScroll: true,
//             onSuccess: () => closeModal(),
//             // onError: () => { /* Handle error if needed */ },
//         });
//     }
// };

</script>

<template>
    <AdminLayout title="Métodos de Pago Estático">
        <Head title="Gestionar Métodos de Pago - Prueba Estática" />
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Métodos de Pago (Prueba Estática)
                </h2>
                <!-- Puedes comentar el botón de crear si causa problemas -->
                <!--
                <Link :href="route('admin.payment-methods.create')">
                    <PrimaryButton class="flex items-center">
                        <PlusIcon class="w-5 h-5 mr-2" />
                        Crear Método de Pago
                    </PrimaryButton>
                </Link>
                -->
            </div>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <p>Si ves este mensaje, el componente Vue base y el AdminLayout están funcionando. El problema podría estar en la lógica del componente original o en los datos que recibe.</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Comentar el ConfirmationModal si existe -->
        <!--
        <ConfirmationModal :show="showConfirmDeleteModal" @close="closeModal">
            <template #title>
                Eliminar Método de Pago
            </template>
            <template #content>
                Contenido estático de prueba.
            </template>
            <template #footer>
                Botones estáticos de prueba.
            </template>
        </ConfirmationModal>
        -->
    </AdminLayout>
</template>
