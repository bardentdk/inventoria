<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { PlusIcon, XMarkIcon, UserIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

const props = defineProps({
    categories: Array,
    users: Array, // <--- Nouvelle prop reçue du contrôleur
});

const categoriesList = ref(props.categories);

const form = useForm({
    name: '',
    serial_number: '',
    inventory_code: '',
    category_id: '',
    status: 'available',
    user_id: '', // <--- Nouveau champ pour l'utilisateur
    specs: '',
});

// Reset user_id si on change le statut vers autre chose que 'assigned'
watch(() => form.status, (newStatus) => {
    if (newStatus !== 'assigned') {
        form.user_id = '';
    }
});

// --- GESTION AJOUT RAPIDE CATÉGORIE ---
const showCatModal = ref(false);
const newCatName = ref('');
const isCreatingCat = ref(false);

const createCategory = async () => {
    if (!newCatName.value) return;
    isCreatingCat.value = true;
    try {
        const response = await axios.post(route('categories.store.api'), { name: newCatName.value });
        categoriesList.value.push(response.data);
        form.category_id = response.data.id;
        newCatName.value = '';
        showCatModal.value = false;
    } catch (error) {
        alert("Erreur lors de la création.");
    } finally {
        isCreatingCat.value = false;
    }
};

const submit = () => {
    form.post(route('assets.store'));
};
</script>

<template>
    <Head title="Ajouter un équipement" />

    <AppLayout>
        <div v-if="showCatModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-sm w-full p-6 border border-slate-200 dark:border-slate-700 relative">
                <button @click="showCatModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
                    <XMarkIcon class="w-5 h-5" />
                </button>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Nouvelle Catégorie</h3>
                <form @submit.prevent="createCategory">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nom</label>
                        <input type="text" v-model="newCatName" class="py-3 px-2 bg-slate-50 focus:bg-white focus:shadow-lg block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white focus:ring-blue-600" autoFocus />
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showCatModal = false" class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700">Annuler</button>
                        <button type="submit" :disabled="!newCatName || isCreatingCat" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-500 disabled:opacity-50">
                            {{ isCreatingCat ? 'Création...' : 'Créer & Sélectionner' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="max-w-3xl mx-auto">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">Ajouter un nouveau matériel</h2>

            <div class="bg-white dark:bg-slate-800 shadow rounded-2xl border border-slate-200 dark:border-slate-700/50 p-8">
                <form @submit.prevent="submit" class="space-y-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Nom du matériel</label>
                            <input type="text" v-model="form.name" class="px-2 mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5 shadow-sm focus:ring-blue-600" placeholder="Ex: MacBook Pro M3" required />
                            <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Catégorie</label>
                            <div class="flex gap-2 mt-2">
                                <select v-model="form.category_id" class="px-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5 shadow-sm focus:ring-blue-600" required>
                                    <option value="" disabled>Choisir...</option>
                                    <option v-for="cat in categoriesList" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                                <button type="button" @click="showCatModal = true" class="flex-shrink-0 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 p-2.5 rounded-xl border border-slate-300 dark:border-slate-600 transition-colors">
                                    <PlusIcon class="w-6 h-6" />
                                </button>
                            </div>
                            <div v-if="form.errors.category_id" class="text-red-500 text-xs mt-1">{{ form.errors.category_id }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Numéro de Série (S/N)</label>
                            <input type="text" v-model="form.serial_number" class="px-2 mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5 shadow-sm focus:ring-blue-600" placeholder="Unique" required />
                            <div v-if="form.errors.serial_number" class="text-red-500 text-xs mt-1">{{ form.errors.serial_number }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Code Inventaire (Ref Interne)</label>
                            <input type="text" v-model="form.inventory_code" class="px-2 mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5 shadow-sm focus:ring-blue-600" placeholder="Ex: NEXA-001" required />
                            <div v-if="form.errors.inventory_code" class="text-red-500 text-xs mt-1">{{ form.errors.inventory_code }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 transition-all" :class="form.status === 'assigned' ? 'md:grid-cols-2' : ''">

                        <div>
                            <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">État initial</label>
                            <select v-model="form.status" class="px-2 mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5 shadow-sm focus:ring-blue-600">
                                <option value="available">Disponible (En stock)</option>
                                <option value="assigned">Assigné à un utilisateur</option>
                                <option value="repair">En réparation</option>
                                <option value="broken">Hors Service (HS)</option>
                            </select>
                            <div v-if="form.errors.status" class="text-red-500 text-xs mt-1">{{ form.errors.status }}</div>
                        </div>

                        <div v-if="form.status === 'assigned'" class="animate-in fade-in slide-in-from-left-4 duration-300">
                            <label class="block text-sm font-medium text-blue-600 dark:text-blue-400 flex items-center gap-2">
                                <UserIcon class="w-4 h-4" /> Bénéficiaire
                            </label>
                            <select v-model="form.user_id" class="px-2 mt-2 block w-full rounded-xl border-blue-300 ring-1 ring-blue-100 dark:bg-slate-900 dark:border-blue-700 dark:ring-blue-900/20 dark:text-white py-2.5 shadow-sm focus:ring-blue-600" required>
                                <option value="" disabled>Sélectionner la personne...</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                            </select>
                            <div v-if="form.errors.user_id" class="text-red-500 text-xs mt-1">{{ form.errors.user_id }}</div>
                        </div>

                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Spécifications techniques / Notes</label>
                        <textarea v-model="form.specs" rows="3" class="px-2 mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5 shadow-sm focus:ring-blue-600" placeholder="Ram, Stockage, État physique..."></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-100 dark:border-slate-700">
                        <Link :href="route('assets.index')" class="text-sm font-medium text-slate-500 hover:text-slate-800 dark:text-slate-400">Annuler</Link>
                        <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2.5 rounded-xl font-medium shadow-lg shadow-blue-500/30 transition-all disabled:opacity-50">
                            Enregistrer le matériel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>