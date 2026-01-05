<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    logs: Object,
});

const formatEvent = (event) => {
    const map = {
        created: { text: 'Création', class: 'bg-green-100 text-green-800' },
        updated: { text: 'Modification', class: 'bg-blue-100 text-blue-800' },
        deleted: { text: 'Suppression', class: 'bg-red-100 text-red-800' },
    };
    return map[event] || { text: event, class: 'bg-gray-100 text-gray-800' };
};

const formatSubject = (type) => {
    if (!type) return 'Système';
    if (type.includes('Ticket')) return 'Ticket';
    if (type.includes('Asset')) return 'Matériel';
    if (type.includes('User')) return 'Utilisateur';
    return type;
};

// --- LA FONCTION MAGIQUE ---
const getName = (log) => {
    // 1. Si l'objet existe encore (pas supprimé)
    if (log.subject) {
        return log.subject.name || log.subject.title || ('#' + log.subject_id);
    }

    // 2. Si l'objet est supprimé, on fouille dans les archives (properties)
    if (log.properties) {
        // Parfois stocké dans 'attributes'
        if (log.properties.attributes) {
            return log.properties.attributes.name || log.properties.attributes.title || null;
        }
        // Parfois stocké dans 'old' (spécifique aux suppressions)
        if (log.properties.old) {
            return log.properties.old.name || log.properties.old.title || null;
        }
    }

    // 3. Si on ne trouve rien, on affiche l'ID et on loggue l'erreur pour debug
    console.log('Données manquantes pour le log ID:', log.id, log);
    return 'Élément #' + log.subject_id;
};
</script>

<template>
    <Head title="Journal d'audit" />

    <AppLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Journal d'activité</h1>
            <p class="mt-2 text-sm text-slate-500">Historique des actions sensibles sur la plateforme.</p>
        </div>

        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-2xl border border-slate-200 dark:border-slate-700/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Utilisateur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Cible</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Détail (Nom/Titre)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        <tr v-for="log in logs.data" :key="log.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-white font-medium">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-600">
                                        {{ log.causer ? log.causer.name.charAt(0) : 'S' }}
                                    </div>
                                    {{ log.causer ? log.causer.name : 'Système' }}
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="['px-2.5 py-0.5 text-xs font-bold rounded-full', formatEvent(log.event).class]">
                                    {{ formatEvent(log.event).text }}
                                </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                {{ formatSubject(log.subject_type) }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 dark:text-slate-300 font-medium">
                                {{ getName(log) }}
                                <span class="text-xs text-slate-400 font-normal ml-1">(#{{ log.subject_id }})</span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                {{ new Date(log.created_at).toLocaleString('fr-FR') }}
                            </td>
                        </tr>
                        
                        <tr v-if="logs.data.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500 italic">
                                Le journal est vide pour le moment.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div v-if="logs.links && logs.links.length > 3" class="px-6 py-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                <Link v-if="logs.prev_page_url" :href="logs.prev_page_url" class="text-sm font-medium text-blue-600 hover:underline">Précédent</Link>
                <span v-else class="text-sm text-slate-400 cursor-not-allowed">Précédent</span>

                <Link v-if="logs.next_page_url" :href="logs.next_page_url" class="text-sm font-medium text-blue-600 hover:underline">Suivant</Link>
                <span v-else class="text-sm text-slate-400 cursor-not-allowed">Suivant</span>
            </div>
        </div>
    </AppLayout>
</template>