<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ComputerDesktopIcon, TicketIcon, ExclamationTriangleIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    stats: Object,
    recent_tickets: Array,
});
</script>

<template>
    <Head title="Tableau de bord" />

    <AppLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Tableau de bord</h1>
            <p class="text-slate-500 mt-2">Bienvenue sur NEXA, voici ce qui se passe aujourd'hui.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Tickets Ouverts</p>
                        <h3 class="text-3xl font-bold mt-2 text-slate-900 dark:text-white">{{ stats.open_tickets }}</h3>
                    </div>
                    <div class="p-3 bg-red-100 text-red-600 rounded-lg dark:bg-red-900/30 dark:text-red-400">
                        <ExclamationTriangleIcon class="w-6 h-6" />
                    </div>
                </div>
                <div class="mt-4 text-xs text-slate-400">Nécessite une attention</div>
            </div>

            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Matériel Total</p>
                        <h3 class="text-3xl font-bold mt-2 text-slate-900 dark:text-white">{{ stats.total_assets }}</h3>
                    </div>
                    <div class="p-3 bg-blue-100 text-blue-600 rounded-lg dark:bg-blue-900/30 dark:text-blue-400">
                        <ComputerDesktopIcon class="w-6 h-6" />
                    </div>
                </div>
                <div class="mt-4 text-xs text-green-500 font-medium">Inventaire à jour</div>
            </div>

            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Matériel Dispo</p>
                        <h3 class="text-3xl font-bold mt-2 text-slate-900 dark:text-white">{{ stats.available_assets }}</h3>
                    </div>
                    <div class="p-3 bg-green-100 text-green-600 rounded-lg dark:bg-green-900/30 dark:text-green-400">
                        <CheckCircleIcon class="w-6 h-6" />
                    </div>
                </div>
                 <div class="mt-4 text-xs text-slate-400">Prêt à être assigné</div>
            </div>

            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Mes Demandes</p>
                        <h3 class="text-3xl font-bold mt-2 text-slate-900 dark:text-white">{{ stats.my_tickets }}</h3>
                    </div>
                    <div class="p-3 bg-purple-100 text-purple-600 rounded-lg dark:bg-purple-900/30 dark:text-purple-400">
                        <TicketIcon class="w-6 h-6" />
                    </div>
                </div>
                 <div class="mt-4 text-xs text-slate-400">Historique personnel</div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                <h3 class="font-semibold text-slate-900 dark:text-white">Tickets Récents</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-400">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs uppercase font-medium">
                        <tr>
                            <th class="px-6 py-3">Titre</th>
                            <th class="px-6 py-3">Demandeur</th>
                            <th class="px-6 py-3">Priorité</th>
                            <th class="px-6 py-3">Statut</th>
                            <th class="px-6 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        <tr v-for="ticket in recent_tickets" :key="ticket.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">{{ ticket.title }}</td>
                            <td class="px-6 py-4">{{ ticket.user }}</td>
                            <td class="px-6 py-4">
                                <span :class="{
                                    'text-red-500 bg-red-100 dark:bg-red-900/30 px-2 py-1 rounded text-xs': ticket.priority === 'critical',
                                    'text-orange-500 bg-orange-100 dark:bg-orange-900/30 px-2 py-1 rounded text-xs': ticket.priority === 'high',
                                    'text-blue-500 bg-blue-100 dark:bg-blue-900/30 px-2 py-1 rounded text-xs': ticket.priority === 'medium',
                                    'text-slate-500 bg-slate-100 dark:bg-slate-900/30 px-2 py-1 rounded text-xs': ticket.priority === 'low',
                                }">
                                    {{ ticket.priority }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="capitalize">{{ ticket.status }}</span>
                            </td>
                            <td class="px-6 py-4">{{ ticket.date }}</td>
                        </tr>
                        <tr v-if="recent_tickets.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">Aucun ticket récent.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>