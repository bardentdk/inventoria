<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { ArrowDownTrayIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import debounce from 'lodash/debounce';

const props = defineProps({
    activities: Object,
    users: Array,
    filters: Object
});

const selectedUser = ref(props.filters.user_id || '');
const search = ref(props.filters.search || '');

const updateParams = () => {
    router.get(route('activity.index'), { 
        user_id: selectedUser.value,
        search: search.value
    }, { preserveState: true, replace: true });
};

watch(selectedUser, () => updateParams());
watch(search, debounce(() => updateParams(), 300));

// Helper pour afficher proprement le JSON des changements
const formatProperties = (properties) => {
    if (!properties || (!properties.attributes && !properties.old)) return null;
    return properties;
};
</script>

<template>
    <Head title="Journal d'activité" />

    <AppLayout>
        <div class="sm:flex sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Journal d'activité</h1>
                <p class="mt-2 text-sm text-slate-500">Traçabilité complète des actions sur la plateforme.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a :href="route('activity.export', { user_id: selectedUser })" 
                   class="flex items-center gap-2 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 px-4 py-2 rounded-lg font-medium hover:bg-slate-50 transition shadow-sm">
                    <ArrowDownTrayIcon class="w-5 h-5" />
                    Exporter les logs
                </a>
            </div>
        </div>

        <div class="mb-6 flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <select v-model="selectedUser" class="py-3 px-2 w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white shadow-sm focus:ring-blue-500">
                    <option value="">Tous les utilisateurs</option>
                    <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                </select>
            </div>
            <div class="flex-1 relative">
                <MagnifyingGlassIcon class="w-5 h-5 absolute left-3 top-3 text-slate-400" />
                <input v-model="search" type="text" placeholder="Rechercher une action..." class="py-3 px-2 pl-10 w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white shadow-sm focus:ring-blue-500">
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 shadow rounded-2xl border border-slate-200 dark:border-slate-700/50 overflow-hidden">
            <ul role="list" class="divide-y divide-slate-200 dark:divide-slate-700">
                <li v-for="log in activities.data" :key="log.id" class="p-4 hover:bg-slate-50 dark:hover:bg-slate-900/50 transition">
                    <div class="flex space-x-3">
                        <div v-if="log.causer" class="flex-shrink-0">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-blue-500">
                                <span class="text-xs font-medium leading-none text-white">{{ log.causer.name.charAt(0) }}</span>
                            </span>
                        </div>
                        <div v-else class="flex-shrink-0">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-500">
                                <span class="text-xs font-medium leading-none text-white">S</span>
                            </span>
                        </div>

                        <div class="flex-1 space-y-1">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-medium text-slate-900 dark:text-white">
                                    <span v-if="log.causer">{{ log.causer.name }}</span>
                                    <span v-else>Système</span>
                                    <span class="text-slate-500 font-normal"> a effectué : </span>
                                    <span class="font-bold text-blue-600 dark:text-blue-400 uppercase text-xs border border-blue-200 px-1 rounded">{{ log.description }}</span>
                                </h3>
                                <p class="text-xs text-slate-500">{{ new Date(log.created_at).toLocaleString() }}</p>
                            </div>
                            
                            <p class="text-sm text-slate-600 dark:text-slate-300">
                                Sur : <span class="font-medium">{{ log.subject_type?.split('\\').pop() }} #{{ log.subject_id }}</span>
                            </p>

                            <div v-if="formatProperties(log.properties)" class="mt-2 bg-slate-50 dark:bg-slate-900 rounded p-2 text-xs font-mono text-slate-600 dark:text-slate-400 overflow-x-auto">
                                <div v-if="log.properties.attributes" class="grid grid-cols-1 gap-1">
                                    <div v-for="(value, key) in log.properties.attributes" :key="key">
                                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ key }}:</span>
                                        <span v-if="log.properties.old && log.properties.old[key]" class="text-red-500 line-through mx-1">{{ log.properties.old[key] }}</span>
                                        <span class="text-green-600">-> {{ value }}</span>
                                    </div>
                                </div>
                                <div v-else>
                                    {{ log.properties }}
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <li v-if="activities.data.length === 0" class="p-8 text-center text-slate-500">
                    Aucune activité enregistrée pour ces critères.
                </li>
            </ul>
            
            <div class="bg-slate-50 dark:bg-slate-900/50 px-6 py-3 border-t border-slate-200 dark:border-slate-700">
                </div>
        </div>
    </AppLayout>
</template>