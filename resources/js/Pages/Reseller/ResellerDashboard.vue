<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // Assuming a shared layout
import { Head, Link } from '@inertiajs/vue3';
import { defineProps, computed } from 'vue'; // Import computed if needed for derived values

const props = defineProps({
    clients: Array, // Array of client objects { id, name, email, created_at }
    clientCount: Number,
    activeServicesCount: Number,
    // auth: Object, // Authenticated user (reseller) is available via $page.props.auth.user
});

// Helper to format dates (optional, or use a global utility)
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
};
</script>

<template>
    <Head title="Reseller Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Reseller Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Welcome Message -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Welcome, {{ \$page.props.auth.user.name }}!
                    </h3>
                </div>

                <!-- Summary Statistics Cards -->
                <div class="grid grid-cols-1 gap-6 mb-6 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Total Clients Card -->
                    <div class="p-6 overflow-hidden bg-white rounded-lg shadow-sm dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">
                            Total Clients
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                            {{ clientCount }}
                        </dd>
                    </div>

                    <!-- Active Services Card -->
                    <div class="p-6 overflow-hidden bg-white rounded-lg shadow-sm dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">
                            Active Services (Your Clients)
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                            {{ activeServicesCount }}
                        </dd>
                    </div>
                    
                    <!-- Placeholder for other stats -->
                    <!--
                    <div class="p-6 overflow-hidden bg-white rounded-lg shadow-sm dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">
                            Upcoming Renewals
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                            0 
                        </dd>
                    </div>
                    -->
                </div>

                <!-- Quick Navigation / Actions for Reseller -->
                <div class="mb-6">
                    <h3 class="mb-2 text-lg font-medium text-gray-900 dark:text-gray-100">Quick Actions</h3>
                    <div class="flex space-x-4">
                        <Link :href="route('reseller.clients.index')" 
                              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                            Manage My Clients
                        </Link>
                        <!-- Add other relevant links as features are built -->
                        <!-- e.g., View Client Orders, Reseller Settings -->
                    </div>
                </div>

                <!-- List of Reseller's Clients -->
                <div class="p-6 bg-white border-b border-gray-200 shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <h3 class="mb-4 text-lg font-semibold leading-6 text-gray-900 dark:text-gray-100">
                        My Clients (Recent)
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Name</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Email</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Registration Date</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                <tr v-if="clients.length === 0">
                                    <td colspan="4" class="px-6 py-4 text-sm text-center text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        You have no clients yet.
                                    </td>
                                </tr>
                                <tr v-for="client in clients" :key="client.id">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap dark:text-gray-100">{{ client.name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-300">{{ client.email }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-300">{{ formatDate(client.created_at) }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                        <Link :href="route('admin.users.show', client.id)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            View Details
                                        </Link>
                                        <!-- Note: The route 'admin.users.show' might need to be adjusted if resellers have a different view for user details,
                                             or if the admin view is appropriate but needs to be scoped by policy for resellers.
                                             For now, assuming it links to a generic user detail view. -->
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Add pagination if clients are paginated by the controller -->
                    <!-- <Pagination :links="clients.links" class="mt-6" v-if="clients.links" /> -->
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
