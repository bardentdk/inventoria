<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'user', // Défaut
});

const submit = () => {
    form.post(route('users.store'));
};
</script>

<template>
    <Head title="Créer un utilisateur" />

    <AppLayout>
        <div class="max-w-2xl mx-auto">
            <div class="md:flex md:items-center md:justify-between mb-8">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Nouvel Utilisateur</h2>
            </div>

            <div class="bg-white dark:bg-slate-800 shadow-xl rounded-2xl border border-slate-200 dark:border-slate-700/50 p-8">
                <form @submit.prevent="submit" class="space-y-6">

                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Nom complet</label>
                        <input type="text" v-model="form.name" class="px-5 mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5 shadow-sm focus:ring-blue-600" required />
                        <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Adresse Email</label>
                        <input type="email" v-model="form.email" class="px-5 mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5 shadow-sm focus:ring-blue-600" required />
                        <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Rôle d'accès</label>
                        <select v-model="form.role" class="px-5 mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5 shadow-sm focus:ring-blue-600">
                            <option value="user">Utilisateur (Standard)</option>
                            <option value="admin">Administrateur (Accès total)</option>
                        </select>
                        <p class="mt-1 text-xs text-slate-500">Les administrateurs peuvent gérer les utilisateurs et voir tous les tickets.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Mot de passe temporaire</label>
                        <input type="password" v-model="form.password" class="px-5 mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5 shadow-sm focus:ring-blue-600" required placeholder="Minimum 8 caractères" />
                        <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</div>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-100 dark:border-slate-700">
                        <Link :href="route('users.index')" class="text-sm font-medium text-slate-500 hover:text-slate-800 dark:text-slate-400">Annuler</Link>
                        <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2.5 rounded-xl font-medium shadow-lg shadow-blue-500/30 transition-all disabled:opacity-50">
                            Créer le compte
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>