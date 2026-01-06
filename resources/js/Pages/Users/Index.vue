<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';
import { MagnifyingGlassIcon, UserPlusIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    users: Object,
    filters: Object,
});

const search = ref(props.filters.search);

watch(search, debounce((value) => {
    router.get(route('users.index'), { search: value }, { preserveState: true, replace: true });
}, 300));

const deleteUser = (user) => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer ${user.name} ? Cette action est irréversible.`)) {
        router.delete(route('users.destroy', user.id));
    }
};
</script>

<template>
    <Head title="Gestion des Utilisateurs" />

    <AppLayout>
        <div class="sm:flex sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Utilisateurs</h1>
                <p class="mt-2 text-sm text-slate-500">Gérez les accès formateurs, staff et admin.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <Link :href="route('users.create')" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg font-medium shadow-lg shadow-blue-500/30 transition-all">
                    <UserPlusIcon class="w-5 h-5" />
                    Créer un compte
                </Link>
            </div>
        </div>

        <div class="mb-6 max-w-sm relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <MagnifyingGlassIcon class="h-5 w-5 text-slate-400" />
            </div>
            <input v-model="search" type="text" placeholder="Rechercher un utilisateur..."
                class="block w-full rounded-xl border-0 py-2.5 pl-10 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 dark:bg-slate-800 dark:ring-slate-700 dark:text-white" />
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 overflow-hidden">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-900/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Utilisateur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Rôle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Structures</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date création</th>
                        <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <tr v-for="user in users.data" :key="user.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0 rounded-full bg-slate-200 dark:bg-slate-600 flex items-center justify-center font-bold text-slate-500 dark:text-slate-300">
                                    {{ user.name.charAt(0) }}
                                </div>
                                <div class="ml-4">
                                    <div class="font-medium text-slate-900 dark:text-white">{{ user.name }}</div>
                                    <div class="text-sm text-slate-500">{{ user.email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="[
                                'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset',
                                user.role === 'admin'
                                    ? 'bg-purple-50 text-purple-700 ring-purple-600/20 dark:bg-purple-900/20 dark:text-purple-400'
                                    : 'bg-slate-50 text-slate-700 ring-slate-600/20 dark:bg-slate-900/20 dark:text-slate-400'
                            ]">
                                {{ user.role === 'admin' ? 'Administrateur' : 'Utilisateur' }}
                            </span>
                        </td>
                        
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                <span v-for="struct in user.structures" :key="struct.id" 
                                    class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-900/30 dark:text-blue-300 dark:ring-blue-500/30">
                                    {{ struct.name }}
                                </span>
                                <span v-if="!user.structures || user.structures.length === 0" class="text-slate-400 text-xs italic">
                                    --
                                </span>
                            </div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                            {{ new Date(user.created_at).toLocaleDateString() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex justify-end gap-3">
                            <Link :href="route('users.edit', user.id)" class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
                                <PencilSquareIcon class="w-5 h-5" />
                            </Link>
                            <button @click="deleteUser(user)" class="text-red-600 hover:text-red-900 dark:hover:text-red-400">
                                <TrashIcon class="w-5 h-5" />
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>