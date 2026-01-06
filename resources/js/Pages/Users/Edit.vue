<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    structures:Array,
});

// On pré-remplit le formulaire avec les données existantes
const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    password: '', // Vide par défaut
    structures: props.user.structures ? props.user.structures.map(s => s.id) : [],
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
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
                            Structures de rattachement
                        </label>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            
                            <div v-for="struct in structures" :key="struct.id" class="relative flex items-start">
                                <div class="flex h-6 items-center">
                                    <input 
                                        type="checkbox" 
                                        :id="'struct-' + struct.id"
                                        :value="struct.id" 
                                        v-model="form.structures"
                                        class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-600 dark:bg-slate-900 dark:border-slate-600 transition duration-150 ease-in-out"
                                    >
                                </div>
                                
                                <div class="ml-3 text-sm leading-6 w-full">
                                    <label 
                                        :for="'struct-' + struct.id" 
                                        class="font-medium text-slate-900 dark:text-white block p-3 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-blue-400 dark:hover:border-blue-500 transition-all duration-200 select-none shadow-sm peer-checked:border-blue-600 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20"
                                        :class="{ 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 ring-1 ring-blue-500': form.structures.includes(struct.id) }"
                                    >
                                        {{ struct.name }}
                                    </label>
                                </div>
                            </div>

                        </div>
                        
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            Cochez toutes les structures où cet utilisateur intervient.
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Nom complet</label>
                        <input type="text" v-model="form.name" class="px-5 mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5" required />
                        <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Email</label>
                        <input type="email" v-model="form.email" class="px-5 mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5" required />
                        <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Rôle</label>
                        <select v-model="form.role" class="px-5 mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5">
                            <option value="user">Utilisateur</option>
                            <option value="admin">Administrateur</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Nouveau mot de passe</label>
                        <input type="password" v-model="form.password" class="px-5 mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5" placeholder="Laisser vide pour ne pas changer" />
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