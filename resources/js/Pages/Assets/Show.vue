<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    ComputerDesktopIcon, QrCodeIcon, UserCircleIcon, PencilSquareIcon,
    WrenchScrewdriverIcon, CheckBadgeIcon, ArrowRightStartOnRectangleIcon,
    ArrowLeftEndOnRectangleIcon, ClockIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    asset: Object,
    users: Array, // Liste des utilisateurs pour l'attribution
});

// --- GESTION MODALE ATTRIBUTION ---
const showAssignModal = ref(false);
const assignForm = useForm({
    user_id: '',
    assigned_at: new Date().toISOString().substr(0, 10), // Date du jour par défaut
    comments: ''
});

const submitAssign = () => {
    assignForm.post(route('assets.assign', props.asset.id), {
        onSuccess: () => showAssignModal.value = false
    });
};

// --- GESTION RETOUR ---
const returnForm = useForm({});
const submitReturn = () => {
    if(confirm("Confirmer le retour de ce matériel en stock ?")) {
        returnForm.post(route('assets.return', props.asset.id));
    }
};

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
        <div v-if="showAssignModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-700">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Attribuer le matériel</h3>

                <form @submit.prevent="submitAssign" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Bénéficiaire</label>
                        <select v-model="assignForm.user_id" class="mt-1 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white" required>
                            <option value="" disabled>Choisir une personne...</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Date de début</label>
                        <input type="date" v-model="assignForm.assigned_at" class="mt-1 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white" required />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Commentaire (Optionnel)</label>
                        <textarea v-model="assignForm.comments" rows="2" class="mt-1 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white" placeholder="État neuf, chargeur inclus..."></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showAssignModal = false" class="text-slate-500 hover:text-slate-700">Annuler</button>
                        <button type="submit" :disabled="assignForm.processing" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-500">Valider</button>
                    </div>
                </form>
            </div>
        </div>

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
                        <span>S/N: {{ asset.serial_number }}</span>
                    </p>
                </div>
            </div>

            <div class="mt-4 md:mt-0 flex gap-3">
                <button v-if="$page.props.auth.user.role === 'admin' && asset.status === 'available'"
                    @click="showAssignModal = true"
                    class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-500 shadow-lg shadow-blue-500/30 transition">
                    <ArrowRightStartOnRectangleIcon class="w-5 h-5" />
                    Attribuer
                </button>

                <button v-if="$page.props.auth.user.role === 'admin' && asset.status === 'assigned'"
                    @click="submitReturn"
                    class="flex items-center gap-2 bg-slate-800 text-white px-4 py-2 rounded-lg font-medium hover:bg-slate-700 border border-slate-700 transition">
                    <ArrowLeftEndOnRectangleIcon class="w-5 h-5" />
                    Marquer comme rendu
                </button>

                <Link :href="route('assets.edit', asset.id)" class="flex items-center gap-2 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 px-4 py-2 rounded-lg font-medium hover:bg-slate-50 transition">
                    <PencilSquareIcon class="w-5 h-5" />
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="space-y-8">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 p-6">
                    <h3 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <UserCircleIcon class="w-5 h-5 text-blue-500" />
                        Détenteur actuel
                    </h3>

                    <div v-if="asset.user" class="flex items-center gap-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800">
                        <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-lg">
                            {{ asset.user.name.charAt(0) }}
                        </div>
                        <div>
                            <p class="font-bold text-slate-900 dark:text-white">{{ asset.user.name }}</p>
                            <p class="text-xs text-slate-500">{{ asset.user.email }}</p>
                            <p class="text-xs text-blue-500 mt-1">Depuis le {{ asset.updated_at ? new Date(asset.updated_at).toLocaleDateString() : '-' }}</p>
                        </div>
                    </div>

                    <div v-else class="text-center py-6 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-dashed border-slate-300 dark:border-slate-700">
                        <p class="text-slate-500 text-sm">En stock (Non attribué)</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 p-6">
                    <h3 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <ClockIcon class="w-5 h-5 text-purple-500" />
                        Historique d'attribution
                    </h3>

                    <div class="space-y-4">
                        <div v-for="assign in asset.assignments" :key="assign.id" class="relative pl-6 border-l border-slate-200 dark:border-slate-700 pb-4 last:pb-0">
                            <span class="absolute -left-[5px] top-1 w-2.5 h-2.5 rounded-full"
                                :class="assign.returned_at ? 'bg-slate-300 dark:bg-slate-600' : 'bg-green-500 animate-pulse'"></span>

                            <p class="text-sm font-medium text-slate-900 dark:text-white">{{ assign.user.name }}</p>
                            <p class="text-xs text-slate-500">
                                Du {{ new Date(assign.assigned_at).toLocaleDateString() }}
                                <span v-if="assign.returned_at">au {{ new Date(assign.returned_at).toLocaleDateString() }}</span>
                                <span v-else class="text-green-600 font-bold"> (En cours)</span>
                            </p>
                            <p v-if="assign.comments" class="text-xs text-slate-400 italic mt-1">"{{ assign.comments }}"</p>
                        </div>
                        <p v-if="asset.assignments.length === 0" class="text-sm text-slate-500 italic">Aucun historique.</p>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-8">
                 <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 p-6">
                    <h3 class="font-bold text-slate-900 dark:text-white mb-2">Configuration</h3>
                    <p class="text-slate-600 dark:text-slate-400 text-sm">{{ asset.specs || 'N/A' }}</p>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-700">
                        <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <WrenchScrewdriverIcon class="w-5 h-5 text-orange-500" />
                            Incidents & Pannes
                        </h3>
                    </div>
                    <div v-if="asset.tickets.length === 0" class="p-6 text-center text-slate-500 text-sm">Aucun incident signalé.</div>
                     <div v-else class="p-6 space-y-4">
                        <div v-for="ticket in asset.tickets" :key="ticket.id" class="p-3 bg-slate-50 dark:bg-slate-900/30 rounded-lg">
                            <Link :href="route('tickets.show', ticket.id)" class="font-medium text-blue-600 hover:underline">{{ ticket.title }}</Link>
                            <span class="text-xs text-slate-500 block">{{ new Date(ticket.created_at).toLocaleDateString() }} - {{ ticket.status }}</span>
                        </div>
                     </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>