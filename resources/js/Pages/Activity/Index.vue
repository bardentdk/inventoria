<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { 
    ArrowDownTrayIcon, 
    MagnifyingGlassIcon, 
    ClockIcon, 
    ComputerDesktopIcon, 
    GlobeAmericasIcon,
    IdentificationIcon
} from '@heroicons/vue/24/outline';
import debounce from 'lodash/debounce';

const props = defineProps({
    activities: Object,
    users: Array,
    filters: Object
});

// --- FILTRES ---
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

// --- FORMATAGE VISUEL ---

// Détermine la couleur du badge selon l'action
const getEventColor = (description, event) => {
    const desc = description.toLowerCase();
    if (desc.includes('connexion') || event === 'login') return 'bg-purple-100 text-purple-700 border-purple-200';
    if (desc === 'created') return 'bg-green-100 text-green-700 border-green-200';
    if (desc === 'updated') return 'bg-blue-100 text-blue-700 border-blue-200';
    if (desc === 'deleted') return 'bg-red-100 text-red-700 border-red-200';
    return 'bg-slate-100 text-slate-700 border-slate-200';
};

// Traduction des termes techniques
const translateEvent = (description) => {
    const map = {
        'created': 'Création',
        'updated': 'Modification',
        'deleted': 'Suppression',
        'Connexion à la plateforme': 'Connexion'
    };
    return map[description] || description;
};

// Récupère le nom du modèle concerné (ex: "Asset" -> "Matériel")
const getSubjectName = (type) => {
    if (!type) return 'Système';
    if (type.includes('Asset')) return 'Matériel';
    if (type.includes('User')) return 'Utilisateur';
    if (type.includes('Structure')) return 'Structure';
    return type.split('\\').pop();
};
</script>

<template>
    <Head title="Journal d'activité" />

    <AppLayout>
        <div class="sm:flex sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Journal d'activité</h1>
                <p class="mt-2 text-sm text-slate-500">
                    Traçabilité complète des actions : Connexions, Modifications, Créations.
                </p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a :href="route('activity.export', { user_id: selectedUser })" 
                   class="flex items-center gap-2 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 px-4 py-2 rounded-lg font-medium hover:bg-slate-50 transition shadow-sm">
                    <ArrowDownTrayIcon class="w-5 h-5" />
                    Exporter les logs (CSV)
                </a>
            </div>
        </div>

        <div class="mb-6 flex flex-col sm:flex-row gap-4 bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50">
            <div class="flex-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-1 block">Filtrer par personne</label>
                <select v-model="selectedUser" class="w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white shadow-sm focus:ring-blue-500">
                    <option value="">Tous les utilisateurs</option>
                    <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                </select>
            </div>
            <div class="flex-1 relative">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-1 block">Recherche textuelle</label>
                <div class="relative">
                    <MagnifyingGlassIcon class="w-5 h-5 absolute left-3 top-3 text-slate-400" />
                    <input v-model="search" type="text" placeholder="Rechercher une action, un IP..." class="pl-10 w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white shadow-sm focus:ring-blue-500">
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div v-for="log in activities.data" :key="log.id" class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700/50 overflow-hidden transition hover:shadow-md">
                
                <div class="p-4 flex items-start gap-4">
                    <div class="flex-shrink-0 mt-1">
                        <div v-if="log.causer" class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold shadow-sm">
                            {{ log.causer.name.charAt(0) }}
                        </div>
                        <div v-else class="h-10 w-10 rounded-full bg-slate-500 flex items-center justify-center text-white font-bold shadow-sm">
                            S
                        </div>
                    </div>

                    <div class="flex-1">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <p class="text-sm font-bold text-slate-900 dark:text-white">
                                    <span v-if="log.causer">{{ log.causer.name }}</span>
                                    <span v-else>Système</span>
                                    
                                    <span class="font-normal text-slate-500 mx-1">a effectué :</span>
                                    
                                    <span :class="['text-xs font-bold px-2 py-0.5 rounded border uppercase tracking-wider', getEventColor(log.description, log.event)]">
                                        {{ translateEvent(log.description) }}
                                    </span>
                                </p>
                                <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-2">
                                    <ClockIcon class="w-3.5 h-3.5" />
                                    {{ new Date(log.created_at).toLocaleString() }}
                                    
                                    <span v-if="log.subject_id" class="flex items-center gap-1 ml-2 text-slate-400">
                                        <IdentificationIcon class="w-3.5 h-3.5" />
                                        {{ getSubjectName(log.subject_type) }} #{{ log.subject_id }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 bg-slate-50 dark:bg-slate-900/50 rounded-lg p-3 text-sm border border-slate-100 dark:border-slate-700">
                            
                            <div v-if="log.description === 'Connexion à la plateforme'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                                    <GlobeAmericasIcon class="w-5 h-5 text-blue-500" />
                                    <span class="font-semibold">IP :</span> 
                                    {{ log.properties?.ip || 'Inconnue' }}
                                </div>
                                <div class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                                    <ComputerDesktopIcon class="w-5 h-5 text-purple-500" />
                                    <span class="font-semibold">Navigateur :</span> 
                                    <span class="truncate max-w-xs" :title="log.properties?.agent">
                                        {{ log.properties?.agent || 'Inconnu' }}
                                    </span>
                                </div>
                            </div>

                            <div v-else-if="log.description === 'updated' && log.properties?.attributes" class="space-y-2">
                                <p class="text-xs font-bold text-slate-500 uppercase">Changements détectés :</p>
                                <div v-for="(newVal, key) in log.properties.attributes" :key="key" class="flex items-center gap-2 text-xs sm:text-sm">
                                    <span v-if="key !== 'updated_at'" class="font-mono font-bold text-slate-700 dark:text-slate-300 min-w-[120px]">
                                        {{ key }} :
                                    </span>
                                    
                                    <template v-if="key !== 'updated_at'">
                                        <span class="text-red-500 line-through bg-red-50 px-1 rounded decoration-red-500/50">
                                            {{ log.properties.old?.[key] ?? 'Vide' }}
                                        </span>
                                        
                                        <span class="text-slate-400">➜</span>
                                        
                                        <span class="text-green-600 font-medium bg-green-50 px-1 rounded">
                                            {{ newVal ?? 'Vide' }}
                                        </span>
                                    </template>
                                </div>
                            </div>

                            <div v-else-if="log.description === 'created' && log.properties?.attributes" class="space-y-1">
                                <p class="text-xs font-bold text-slate-500 uppercase">Données initiales :</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1">
                                    <div v-for="(val, key) in log.properties.attributes" :key="key" class="text-xs flex gap-2">
                                        <span class="font-bold text-slate-600 dark:text-slate-400">{{ key }}:</span>
                                        <span class="text-slate-800 dark:text-slate-200 truncate">{{ val }}</span>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="text-slate-500 italic text-xs">
                                Aucune donnée technique supplémentaire.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="activities.data.length === 0" class="text-center py-12 bg-white dark:bg-slate-800 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700">
                <p class="text-slate-500">Aucune activité trouvée pour ces critères.</p>
            </div>

            <div class="flex justify-between items-center mt-6">
                <p class="text-sm text-slate-500">
                    Page {{ activities.current_page }} sur {{ activities.last_page }}
                </p>
                <div class="flex gap-2">
                    <Link v-if="activities.prev_page_url" :href="activities.prev_page_url" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm hover:bg-slate-50">Précédent</Link>
                    <Link v-if="activities.next_page_url" :href="activities.next_page_url" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm hover:bg-slate-50">Suivant</Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>