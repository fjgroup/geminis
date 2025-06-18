<script setup>
import { ref, onMounted, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue'; // O el layout que prefieras
import { Head } from '@inertiajs/vue3';
// Si projectPhases.js está en resources/js/projectPhases.js
// y este archivo está en resources/js/Pages/Admin/ProjectProgress.vue
// la importación podría ser:
import { projectPhases } from '../../projectPhases'; // Ajusta según tu estructura final
// O si lo pones en '@/Data/projectPhases.js' (configurando alias en vite.config.js si es necesario)
// import { projectPhases } from '@/Data/projectPhases';

// Estado para las fases expandidas (guardamos los IDs de las fases)
const expandedPhases = ref(new Set());
// Estado para las tareas completadas (guardamos los IDs de las tareas, ej: "6.1", "7.2")
const completedTasks = ref(new Set());

const LOCAL_STORAGE_EXPANDED_KEY = 'projectTrackerExpandedPhases';
const LOCAL_STORAGE_COMPLETED_KEY = 'projectTrackerCompletedTasks';

// Cargar estado desde localStorage al montar el componente
onMounted(() => {
    const storedExpanded = localStorage.getItem(LOCAL_STORAGE_EXPANDED_KEY);
    if (storedExpanded) {
        expandedPhases.value = new Set(JSON.parse(storedExpanded));
    }

    const storedCompleted = localStorage.getItem(LOCAL_STORAGE_COMPLETED_KEY);
    if (storedCompleted) {
        completedTasks.value = new Set(JSON.parse(storedCompleted));
    }
});

// Guardar estado de fases expandidas en localStorage cuando cambie
watch(expandedPhases, (newValue) => {
    localStorage.setItem(LOCAL_STORAGE_EXPANDED_KEY, JSON.stringify(Array.from(newValue)));
}, { deep: true });

// Guardar estado de tareas completadas en localStorage cuando cambie
watch(completedTasks, (newValue) => {
    localStorage.setItem(LOCAL_STORAGE_COMPLETED_KEY, JSON.stringify(Array.from(newValue)));
}, { deep: true });

const togglePhase = (phaseId) => {
    const newSet = new Set(expandedPhases.value);
    if (newSet.has(phaseId)) {
        newSet.delete(phaseId);
    } else {
        newSet.add(phaseId);
    }
    expandedPhases.value = newSet;
};

const toggleTask = (taskId) => {
    const newSet = new Set(completedTasks.value);
    if (newSet.has(taskId)) {
        newSet.delete(taskId);
    } else {
        newSet.add(taskId);
    }
    completedTasks.value = newSet;
};

const isTaskCompleted = (taskId) => {
    return completedTasks.value.has(taskId);
};

const isPhaseExpanded = (phaseId) => {
    return expandedPhases.value.has(phaseId);
};

const getPhaseProgress = (phase) => {
    if (!phase.tasks || phase.tasks.length === 0) {
        return { completed: 0, total: 0, percentage: 0 };
    }
    const completedCount = phase.tasks.filter(task => isTaskCompleted(task.id)).length;
    const totalTasks = phase.tasks.length;
    const percentage = totalTasks > 0 ? Math.round((completedCount / totalTasks) * 100) : 0;
    return { completed: completedCount, total: totalTasks, percentage };
};

</script>

<template>
    <AdminLayout title="Progreso del Proyecto">

        <Head title="Progreso del Proyecto Geminis" />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Progreso del Proyecto Geminis
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6 md:p-8">
                        <div v-for="phase in projectPhases" :key="phase.id"
                            class="mb-4 border rounded-md dark:border-gray-700">
                            <div class="flex items-center justify-between p-4 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                                @click="togglePhase(phase.id)">
                                <div class="flex-grow">
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ phase.id }}:
                                        {{
                                        phase.title }}</h3>
                                    <div v-if="phase.tasks && phase.tasks.length > 0"
                                        class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        Progreso: {{ getPhaseProgress(phase).completed }} / {{
                                        getPhaseProgress(phase).total }}
                                        tareas ({{ getPhaseProgress(phase).percentage }}%)
                                        <div class="w-full mt-1 bg-gray-200 rounded-full h-1.5 dark:bg-gray-600">
                                            <div class="bg-blue-600 h-1.5 rounded-full"
                                                :style="{ width: getPhaseProgress(phase).percentage + '%' }"></div>
                                        </div>
                                    </div>
                                </div>
                                <span class="ml-4 text-gray-500 dark:text-gray-400">
                                    {{ isPhaseExpanded(phase.id) ? '▼' : '▶' }}
                                </span>
                            </div>

                            <div v-if="isPhaseExpanded(phase.id) && phase.tasks && phase.tasks.length > 0"
                                class="p-4 border-t dark:border-gray-700">
                                <ul class="space-y-2">
                                    <li v-for="task in phase.tasks" :key="task.id"
                                        class="flex items-center p-2 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                        :class="{ 'bg-green-50 dark:bg-green-700/20 line-through text-gray-500 dark:text-gray-400': isTaskCompleted(task.id) }">
                                        <input type="checkbox" :id="`task-${task.id}`"
                                            :checked="isTaskCompleted(task.id)" @change="toggleTask(task.id)"
                                            class="w-4 h-4 mr-3 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600" />
                                        <label :for="`task-${task.id}`" class="flex-grow cursor-pointer">
                                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ task.id
                                                }}</span> -
                                            <span class="text-gray-600 dark:text-gray-300">{{ task.description }}</span>
                                        </label>
                                    </li>
                                </ul>
                            </div>
                            <div v-else-if="isPhaseExpanded(phase.id) && (!phase.tasks || phase.tasks.length === 0)"
                                class="p-4 text-sm text-gray-500 border-t dark:border-gray-700 dark:text-gray-400">
                                No hay tareas definidas para esta fase.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Puedes añadir estilos específicos aquí si es necesario */
.line-through {
    text-decoration: line-through;
}
</style>
