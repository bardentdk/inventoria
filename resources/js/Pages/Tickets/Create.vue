<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';
import { ComputerDesktopIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    my_assets: Array, // <--- Reçu du contrôleur
});

const form = useForm({
    title: '',
    type: 'it_issue',
    priority: 'medium',
    description: '',
    asset_id: null,
    asset_search: '',
});

// --- LOGIQUE DE RECHERCHE SIMPLE (Datalist) ---
const foundAssets = ref([]);
const isSearching = ref(false);

const searchAssets = debounce(async (query) => {
    if (!query || query.length < 2) return;

    isSearching.value = true;
    try {
        const response = await fetch(route('assets.search', { search: query }));
        foundAssets.value = await response.json();
    } catch (error) {
        console.error(error);
    } finally {
        isSearching.value = false;
    }
}, 300);

const onAssetSelect = (event) => {
    const selected = foundAssets.value.find(a =>
        `${a.name} (${a.serial_number})` === event.target.value
    );

    if (selected) {
        selectAsset(selected);
    }
};

// Fonction pour sélectionner un asset (depuis la recherche ou les boutons rapides)
const selectAsset = (asset) => {
    form.asset_id = asset.id;
    // On formatte l'affichage dans l'input
    form.asset_search = `${asset.name} (${asset.serial_number})`;
};

// Fonction pour désélectionner
const clearAsset = () => {
    form.asset_id = null;
    form.asset_search = '';
};

watch(() => form.asset_search, (val) => {
    // Si l'utilisateur efface le champ, on reset l'ID
    if (!val) form.asset_id = null;
    searchAssets(val);
});

const submit = () => {
    form.post(route('tickets.store'));
};
</script>

<template>
    <Head title="Nouveau Ticket" />

    <AppLayout>
        <div class="max-w-3xl mx-auto">
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Ouvrir un ticket</h2>
                <p class="text-slate-500 text-sm">Décrivez votre problème, nous intervenons rapidement.</p>
            </div>

            <div class="bg-white dark:bg-slate-800 shadow-xl rounded-2xl border border-slate-200 dark:border-slate-700/50 p-6 md:p-8">
                <form @submit.prevent="submit" class="space-y-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Type de demande</label>
                            <select v-model="form.type" class="px-5 mt-2 w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5 shadow-sm focus:ring-blue-600">
                                <option value="it_issue">Panne Informatique</option>
                                <option value="logistics">Logistique / Fournitures</option>
                                <option value="supply_request">Demande d'achat</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Urgence</label>
                            <select v-model="form.priority" class="px-5  mt-2 w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5 shadow-sm focus:ring-blue-600">
                                <option value="low">Basse</option>
                                <option value="medium">Moyenne</option>
                                <option value="high">Haute</option>
                                <option value="critical">Critique</option>
                            </select>
                        </div>
                    </div>

                    <div v-if="form.type === 'it_issue'">
                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-200 mb-2">
                            Matériel concerné
                            <span v-if="isSearching" class="text-blue-500 text-xs ml-2 animate-pulse">Recherche...</span>
                        </label>

                        <div v-if="my_assets.length > 0 && !form.asset_id" class="mb-4">
                            <p class="text-xs text-slate-500 mb-2">Sélection rapide de votre matériel :</p>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="asset in my_assets"
                                    :key="asset.id"
                                    type="button"
                                    @click="selectAsset(asset)"
                                    class="flex items-center gap-2 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-sm transition-colors dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800 dark:hover:bg-blue-900/50"
                                >
                                    <ComputerDesktopIcon class="w-4 h-4" />
                                    {{ asset.name }}
                                </button>
                            </div>
                        </div>

                        <div class="relative">
                            <input
                                type="text"
                                list="assets-list"
                                v-model="form.asset_search"
                                @input="onAssetSelect"
                                class="w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5 pl-10 pr-10 shadow-sm focus:ring-blue-600 placeholder:text-slate-400"
                                placeholder="Ou recherchez un autre équipement..."
                                autocomplete="off"
                            />
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>

                            <button v-if="form.asset_id" @click="clearAsset" type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-red-500">
                                <XMarkIcon class="h-5 w-5" />
                            </button>
                        </div>

                        <datalist id="assets-list">
                            <option v-for="asset in foundAssets" :key="asset.id" :value="`${asset.name} (${asset.serial_number})`">
                                Ref: {{ asset.inventory_code }}
                            </option>
                        </datalist>

                        <div v-if="form.asset_id" class="mt-2 flex items-center gap-2 text-sm text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 p-2 rounded-lg border border-green-100 dark:border-green-900/50">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Matériel sélectionné avec succès.
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Sujet</label>
                        <input type="text" v-model="form.title" class="px-5 mt-2 w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5 shadow-sm focus:ring-blue-600" placeholder="Ex: Impossible de me connecter au Wifi" />
                        <div v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Description</label>
                        <textarea v-model="form.description" rows="4" class="px-5  mt-2 w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5 shadow-sm focus:ring-blue-600" placeholder="Plus vous donnez de détails, plus vite nous pourrons vous aider..."></textarea>
                        <div v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</div>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                        <button type="button" @click="$inertia.visit(route('dashboard'))" class="text-sm font-medium text-slate-500 hover:text-slate-800 dark:text-slate-400 transition">Annuler</button>
                        <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2.5 rounded-xl font-medium shadow-lg shadow-blue-500/30 transition-all disabled:opacity-50">
                            Créer le ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>