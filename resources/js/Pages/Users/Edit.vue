<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
});

// On pré-remplit le formulaire avec les données existantes
const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    password: '', // Vide par défaut
});

const submit = () => {
    form.put(route('users.update', props.user.id));
};
</script>

<template>
    <Head :title="`Modifier ${user.name}`" />

    <AppLayout>
        <div class="max-w-2xl mx-auto">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">Modifier l'utilisateur</h2>

            <div class="bg-white dark:bg-slate-800 shadow rounded-2xl border border-slate-200 dark:border-slate-700/50 p-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Nom complet</label>
                        <input type="text" v-model="form.name" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5" required />
                        <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Email</label>
                        <input type="email" v-model="form.email" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5" required />
                        <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Rôle</label>
                        <select v-model="form.role" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5">
                            <option value="user">Utilisateur</option>
                            <option value="admin">Administrateur</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Nouveau mot de passe</label>
                        <input type="password" v-model="form.password" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5" placeholder="Laisser vide pour ne pas changer" />
                        <p class="text-xs text-slate-500 mt-1">Ne remplissez ce champ que si vous souhaitez changer le mot de passe.</p>
                        <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</div>
                    </div>

                    <div class="flex items-center justify-end gap-4">
                        <Link :href="route('users.index')" class="text-slate-500 hover:text-slate-700 dark:text-slate-400">Annuler</Link>
                        <button type="submit" :disabled="form.processing" class="bg-blue-600 text-white px-6 py-2 rounded-xl hover:bg-blue-500 transition">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>