<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ComputerDesktopIcon,
    TicketIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    PlusIcon
} from '@heroicons/vue/24/outline';

// On reçoit les vraies données depuis le DashboardController
const props = defineProps({
    stats: Object,
    recent_tickets: Array,
});
</script>

<template>
    <Head title="Tableau de bord" />

    <AppLayout>

        <div class="sm:flex sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Vue d'ensemble</h1>
                <p class="mt-2 text-sm text-slate-500">Bienvenue sur votre espace de gestion NEXA.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <Link :href="route('tickets.create')" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg font-medium shadow-lg shadow-blue-500/20 transition-all">
                    <PlusIcon class="w-5 h-5" />
                    Nouveau Ticket
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">

            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 flex flex-col justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Tickets Ouverts</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ stats.open_tickets }}</p>
                </div>
                <div class="mt-4 flex items-center text-xs text-orange-500 bg-orange-50 dark:bg-orange-900/20 w-fit px-2 py-1 rounded">
                    <ExclamationTriangleIcon class="w-4 h-4 mr-1" /> En cours de traitement
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 flex flex-col justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Parc Informatique</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ stats.total_assets }}</p>
                </div>
                <div class="mt-4 flex items-center text-xs text-blue-500 bg-blue-50 dark:bg-blue-900/20 w-fit px-2 py-1 rounded">
                    <ComputerDesktopIcon class="w-4 h-4 mr-1" /> Équipements enregistrés
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 flex flex-col justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">En Stock (Dispo)</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ stats.available_assets }}</p>
                </div>
                <div class="mt-4 flex items-center text-xs text-green-500 bg-green-50 dark:bg-green-900/20 w-fit px-2 py-1 rounded">
                    <CheckCircleIcon class="w-4 h-4 mr-1" /> Prêts à l'emploi
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 flex flex-col justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Mes Demandes</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ stats.my_tickets }}</p>
                </div>
                <div class="mt-4 flex items-center text-xs text-purple-500 bg-purple-50 dark:bg-purple-900/20 w-fit px-2 py-1 rounded">
                    <TicketIcon class="w-4 h-4 mr-1" /> Tickets crées par moi
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm overflow-hidden border border-slate-200 dark:border-slate-700/50">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <h3 class="font-semibold text-slate-900 dark:text-white">Activité Récente</h3>
                <Link :href="route('tickets.index')" class="text-sm text-blue-600 hover:text-blue-500">Tout voir &rarr;</Link>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Sujet</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Demandeur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        <tr v-for="ticket in recent_tickets" :key="ticket.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <Link :href="route('tickets.show', ticket.id)" class="text-sm font-medium text-slate-900 dark:text-white hover:text-blue-600">
                                    {{ ticket.title }}
                                </Link>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                {{ ticket.user }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="{
                                    'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300': ticket.status === 'open',
                                    'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300': ticket.status === 'in_progress',
                                    'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300': ticket.status === 'resolved',
                                    'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300': ticket.status === 'closed',
                                }" class="px-2 py-1 rounded-full text-xs font-bold capitalize">
                                    {{ ticket.status.replace('_', ' ') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                {{ ticket.date }}
                            </td>
                        </tr>
                        <tr v-if="recent_tickets.length === 0">
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500 italic">
                                Aucune activité récente à afficher.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </AppLayout>
</template>