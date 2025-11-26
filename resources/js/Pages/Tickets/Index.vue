<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';
import {
    PlusIcon,
    MagnifyingGlassIcon,
    FunnelIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    tickets: Object,
    filters: Object,
});

// Gestion des filtres
const search = ref(props.filters.search || '');
const status = ref(props.filters.status || 'all');

// Trigger de recherche
const updateSearch = debounce((val) => {
    router.get(route('tickets.index'), { search: val, status: status.value }, { preserveState: true, replace: true });
}, 300);

const updateStatus = () => {
    router.get(route('tickets.index'), { search: search.value, status: status.value }, { preserveState: true, replace: true });
};

watch(search, updateSearch);

// Helpers visuels
const priorities = {
    low: { label: 'Basse', class: 'text-slate-500 bg-slate-100 dark:bg-slate-800' },
    medium: { label: 'Moyenne', class: 'text-blue-600 bg-blue-50 dark:bg-blue-900/20' },
    high: { label: 'Haute', class: 'text-orange-600 bg-orange-50 dark:bg-orange-900/20' },
    critical: { label: 'Critique', class: 'text-red-600 bg-red-50 dark:bg-red-900/20' },
};

const statuses = {
    open: { label: 'Ouvert', dot: 'bg-blue-500' },
    in_progress: { label: 'En cours', dot: 'bg-orange-500' },
    resolved: { label: 'Résolu', dot: 'bg-green-500' },
    closed: { label: 'Fermé', dot: 'bg-slate-500' },
};
</script>

<template>
    <Head title="Tickets de support" />

    <AppLayout>
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-bold leading-7 text-slate-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
                    Tickets & Support
                </h2>
                <p class="mt-1 text-sm text-slate-500">Suivez l'état de vos demandes et incidents.</p>
            </div>
            <div class="mt-4 flex md:ml-4 md:mt-0">
                <Link :href="route('tickets.create')" class="ml-3 inline-flex items-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-all">
                    <PlusIcon class="-ml-0.5 mr-1.5 h-5 w-5" aria-hidden="true" />
                    Nouveau Ticket
                </Link>
            </div>
        </div>

        <div class="mb-6 flex flex-col sm:flex-row gap-4">
            <div class="relative flex-1 max-w-sm">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <MagnifyingGlassIcon class="h-5 w-5 text-slate-400" />
                </div>
                <input v-model="search" type="text" placeholder="Rechercher (Titre, ID)..."
                    class="block w-full rounded-xl border-0 py-2.5 pl-10 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 dark:bg-slate-800 dark:ring-slate-700 dark:text-white" />
            </div>

            <div class="flex items-center gap-2">
                <FunnelIcon class="h-5 w-5 text-slate-400 hidden sm:block" />
                <select v-model="status" @change="updateStatus" class="block w-full rounded-xl border-0 py-2.5 pl-3 pr-10 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-blue-600 sm:text-sm sm:leading-6 dark:bg-slate-800 dark:ring-slate-700 dark:text-white">
                    <option value="all">Tous les statuts</option>
                    <option value="open">Ouverts</option>
                    <option value="in_progress">En cours</option>
                    <option value="resolved">Résolus</option>
                    <option value="closed">Fermés</option>
                </select>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700/50 rounded-2xl overflow-hidden">
            <ul role="list" class="divide-y divide-slate-100 dark:divide-slate-700">
                <li v-for="ticket in tickets.data" :key="ticket.id" class="relative flex justify-between gap-x-6 px-6 py-5 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors cursor-pointer">
                    <div class="flex min-w-0 gap-x-4">
                        <div class="min-w-0 flex-auto">
                            <p class="text-sm font-semibold leading-6 text-slate-900 dark:text-white">
                                <Link :href="route('tickets.show', ticket.id)">
                                    <span class="absolute inset-x-0 -top-px bottom-0" />
                                    {{ ticket.title }}
                                </Link>
                            </p>
                            <p class="mt-1 flex text-xs leading-5 text-slate-500">
                                <span class="truncate">#{{ ticket.id }} • Ouvert par {{ ticket.user.name }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-x-4">
                        <div :class="['rounded-md py-1 px-2 text-xs font-medium ring-1 ring-inset', priorities[ticket.priority].class, 'ring-black/10 dark:ring-white/10']">
                            {{ priorities[ticket.priority].label }}
                        </div>

                        <div class="hidden sm:flex sm:flex-col sm:items-end">
                            <div class="flex items-center gap-x-1.5">
                                <div :class="['flex-none rounded-full p-1', statuses[ticket.status].dot]">
                                    <div class="h-1.5 w-1.5 rounded-full bg-white opacity-50" />
                                </div>
                                <p class="text-xs leading-5 text-slate-500 capitalize">{{ statuses[ticket.status].label }}</p>
                            </div>
                            <p class="text-xs leading-5 text-slate-400">
                                {{ new Date(ticket.created_at).toLocaleDateString() }}
                            </p>
                        </div>
                        <svg class="h-5 w-5 flex-none text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </li>

                <li v-if="tickets.data.length === 0" class="px-6 py-10 text-center">
                    <p class="text-slate-500">Aucun ticket trouvé.</p>
                </li>
            </ul>

            <div v-if="tickets.next_page_url || tickets.prev_page_url" class="flex items-center justify-between border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 px-4 py-3 sm:px-6">
                <div class="flex flex-1 justify-between sm:justify-end gap-2">
                    <Link :href="tickets.prev_page_url || '#'" :disabled="!tickets.prev_page_url" :class="{'opacity-50 cursor-not-allowed': !tickets.prev_page_url}" class="relative inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 focus-visible:outline-offset-0 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-700">Précédent</Link>
                    <Link :href="tickets.next_page_url || '#'" :disabled="!tickets.next_page_url" :class="{'opacity-50 cursor-not-allowed': !tickets.next_page_url}" class="relative inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 focus-visible:outline-offset-0 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-700">Suivant</Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>