<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ComputerDesktopIcon,
    QrCodeIcon,
    UserCircleIcon,
    PencilSquareIcon,
    WrenchScrewdriverIcon,
    CheckBadgeIcon,
    ClockIcon
} from '@heroicons/vue/24/outline';

defineProps({
    asset: Object,
});

const getStatusColor = (status) => {
    const colors = {
        available: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        assigned: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        repair: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
        broken: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
};
</script>

<template>
    <Head :title="asset.name" />

    <AppLayout>
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
                    <ComputerDesktopIcon class="w-8 h-8 text-blue-600" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-3">
                        {{ asset.name }}
                        <span :class="['text-xs font-bold px-2 py-1 rounded-full uppercase tracking-wide', getStatusColor(asset.status)]">
                            {{ asset.status }}
                        </span>
                    </h1>
                    <p class="text-slate-500 text-sm mt-1 flex items-center gap-4">
                        <span class="flex items-center gap-1"><QrCodeIcon class="w-4 h-4" /> {{ asset.inventory_code }}</span>
                        <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                        <span>S/N: {{ asset.serial_number }}</span>
                    </p>
                </div>
            </div>
            <div class="mt-4 md:mt-0">
                <Link :href="route('assets.edit', asset.id)" class="flex items-center gap-2 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 px-4 py-2 rounded-lg font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                    <PencilSquareIcon class="w-5 h-5" />
                    Modifier
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="space-y-8">

                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 p-6">
                    <h3 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <UserCircleIcon class="w-5 h-5 text-blue-500" />
                        Utilisateur Actuel
                    </h3>

                    <div v-if="asset.user" class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-100 dark:border-slate-700">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
                            {{ asset.user.name.charAt(0) }}
                        </div>
                        <div>
                            <p class="font-bold text-slate-900 dark:text-white">{{ asset.user.name }}</p>
                            <p class="text-xs text-slate-500">{{ asset.user.email }}</p>
                            <p class="text-xs text-blue-500 mt-1">{{ asset.user.department || 'Aucun service' }}</p>
                        </div>
                    </div>

                    <div v-else class="text-center py-6 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-dashed border-slate-300 dark:border-slate-700">
                        <p class="text-slate-500 text-sm">Ce matériel est actuellement en stock.</p>
                        <p class="text-xs text-green-600 font-medium mt-1">Disponible pour assignation</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 p-6">
                    <h3 class="font-bold text-slate-900 dark:text-white mb-4">Fiche Technique</h3>
                    <div class="space-y-4 text-sm">
                        <div class="flex justify-between border-b border-slate-100 dark:border-slate-700 pb-2">
                            <span class="text-slate-500">Catégorie</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ asset.category.name }}</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 dark:border-slate-700 pb-2">
                            <span class="text-slate-500">Ajouté le</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ new Date(asset.created_at).toLocaleDateString() }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 mb-2">Configuration / Notes</span>
                            <p class="text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-900/50 p-3 rounded-lg border border-slate-100 dark:border-slate-700">
                                {{ asset.specs || 'Aucune spécification renseignée.' }}
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                        <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <WrenchScrewdriverIcon class="w-5 h-5 text-orange-500" />
                            Historique des incidents
                        </h3>
                        <span class="text-xs font-medium bg-slate-100 dark:bg-slate-700 px-2 py-1 rounded-full text-slate-600 dark:text-slate-300">
                            {{ asset.tickets.length }} ticket(s)
                        </span>
                    </div>

                    <div v-if="asset.tickets.length > 0" class="p-6">
                        <div class="relative border-l-2 border-slate-200 dark:border-slate-700 ml-3 space-y-8">

                            <div v-for="ticket in asset.tickets" :key="ticket.id" class="relative pl-8">
                                <span class="absolute -left-[9px] top-1 h-4 w-4 rounded-full border-2 border-white dark:border-slate-800"
                                    :class="ticket.status === 'resolved' ? 'bg-green-500' : 'bg-blue-500'">
                                </span>

                                <div class="bg-slate-50 dark:bg-slate-900/30 p-4 rounded-xl border border-slate-100 dark:border-slate-700/50 hover:border-blue-200 transition-colors">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-bold text-slate-900 dark:text-white text-sm">
                                            <Link :href="route('tickets.show', ticket.id)" class="hover:underline hover:text-blue-600">
                                                {{ ticket.title }}
                                            </Link>
                                        </h4>
                                        <span class="text-xs text-slate-400 whitespace-nowrap">
                                            {{ new Date(ticket.created_at).toLocaleDateString() }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2 mb-3">
                                        {{ ticket.description }}
                                    </p>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-bold">
                                                {{ ticket.user.name.charAt(0) }}
                                            </div>
                                            <span class="text-xs text-slate-500">Signalé par {{ ticket.user.name }}</span>
                                        </div>
                                        <span :class="[
                                            'text-[10px] font-bold uppercase px-2 py-0.5 rounded',
                                            ticket.status === 'resolved' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'
                                        ]">
                                            {{ ticket.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div v-else class="p-12 text-center">
                        <CheckBadgeIcon class="w-16 h-16 text-green-100 mx-auto mb-4" />
                        <h4 class="text-slate-900 dark:text-white font-medium">Aucun incident</h4>
                        <p class="text-slate-500 text-sm mt-1">Ce matériel fonctionne parfaitement depuis sa mise en service.</p>
                    </div>

                </div>
            </div>
        </div>

    </AppLayout>
</template>