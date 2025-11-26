<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    categories: Array, // Reçu depuis le Controller
});

const form = useForm({
    name: '',
    serial_number: '',
    inventory_code: '',
    category_id: '',
    status: 'available', // Valeur par défaut
    specs: '',
});

const submit = () => {
    form.post(route('assets.store'));
};
</script>

<template>
    <Head title="Ajouter un équipement" />

    <AppLayout>
        <div class="max-w-3xl mx-auto">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">Ajouter un nouveau matériel</h2>

            <div class="bg-white dark:bg-slate-800 shadow rounded-2xl border border-slate-200 dark:border-slate-700/50 p-8">
                <form @submit.prevent="submit" class="space-y-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Nom du matériel</label>
                            <input type="text" v-model="form.name" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5 shadow-sm focus:ring-blue-600" placeholder="Ex: MacBook Pro M3" required />
                            <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Catégorie</label>
                            <select v-model="form.category_id" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5 shadow-sm focus:ring-blue-600" required>
                                <option value="" disabled>Choisir...</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <div v-if="form.errors.category_id" class="text-red-500 text-xs mt-1">{{ form.errors.category_id }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Numéro de Série (S/N)</label>
                            <input type="text" v-model="form.serial_number" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5 shadow-sm focus:ring-blue-600" placeholder="Unique" required />
                            <div v-if="form.errors.serial_number" class="text-red-500 text-xs mt-1">{{ form.errors.serial_number }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Code Inventaire (Ref Interne)</label>
                            <input type="text" v-model="form.inventory_code" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5 shadow-sm focus:ring-blue-600" placeholder="Ex: NEXA-001" required />
                            <div v-if="form.errors.inventory_code" class="text-red-500 text-xs mt-1">{{ form.errors.inventory_code }}</div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">État initial</label>
                        <select v-model="form.status" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5 shadow-sm focus:ring-blue-600">
                            <option value="available">Disponible (En stock)</option>
                            <option value="assigned">Assigné (Déjà utilisé)</option>
                            <option value="repair">En réparation</option>
                            <option value="broken">Hors Service (HS)</option>
                        </select>
                        <div v-if="form.errors.status" class="text-red-500 text-xs mt-1">{{ form.errors.status }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Spécifications techniques / Notes</label>
                        <textarea v-model="form.specs" rows="3" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5 shadow-sm focus:ring-blue-600" placeholder="Ram, Stockage, État physique..."></textarea>
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