<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    asset: Object,
    categories: Array,
});

const form = useForm({
    name: props.asset.name,
    serial_number: props.asset.serial_number,
    inventory_code: props.asset.inventory_code,
    category_id: props.asset.category_id,
    status: props.asset.status,
    specs: props.asset.specs,
});

const submit = () => {
    form.put(route('assets.update', props.asset.id));
};

const deleteAsset = () => {
    if(confirm('Êtes-vous sûr de vouloir supprimer définitivement cet équipement ?')) {
        form.delete(route('assets.destroy', props.asset.id));
    }
};
</script>

<template>
    <Head :title="`Modifier ${asset.name}`" />

    <AppLayout>
        <div class="max-w-3xl mx-auto">
            <div class="mb-8 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Modifier un équipement</h2>
                <button @click="deleteAsset" type="button" class="text-red-600 hover:text-red-800 text-sm font-medium">
                    Supprimer cet équipement
                </button>
            </div>

            <div class="bg-white dark:bg-slate-800 shadow rounded-2xl border border-slate-200 dark:border-slate-700/50 p-8">
                <form @submit.prevent="submit" class="space-y-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Nom du matériel</label>
                            <input type="text" v-model="form.name" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5" />
                            <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Catégorie</label>
                            <select v-model="form.category_id" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5">
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Numéro de Série (S/N)</label>
                            <input type="text" v-model="form.serial_number" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Code Inventaire (Interne)</label>
                            <input type="text" v-model="form.inventory_code" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">État actuel</label>
                        <select v-model="form.status" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5">
                            <option value="available">Disponible (Stock)</option>
                            <option value="assigned">Assigné (En cours d'utilisation)</option>
                            <option value="repair">En réparation</option>
                            <option value="broken">Hors Service (HS)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Spécifications techniques</label>
                        <textarea v-model="form.specs" rows="3" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                        <Link :href="route('assets.index')" class="text-slate-500 hover:text-slate-700 dark:text-slate-400">Annuler</Link>
                        <button type="submit" :disabled="form.processing" class="bg-blue-600 text-white px-6 py-2 rounded-xl hover:bg-blue-500 transition">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>