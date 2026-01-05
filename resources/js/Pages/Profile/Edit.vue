<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { UserCircleIcon, KeyIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    user: Object,
});

// Formulaire Info
const formInfo = useForm({
    name: props.user.name,
    email: props.user.email,
});

// Formulaire Password
const formPassword = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updateInfo = () => {
    formInfo.patch(route('profile.update'));
};

const updatePassword = () => {
    formPassword.put(route('profile.password'), {
        preserveScroll: true,
        onSuccess: () => formPassword.reset(),
    });
};
</script>

<template>
    <Head title="Mon Profil" />

    <AppLayout>
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-8">Mon Profil</h1>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <div class="bg-white dark:bg-slate-800 shadow rounded-2xl border border-slate-200 dark:border-slate-700/50 p-6">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <UserCircleIcon class="w-6 h-6 text-blue-500" />
                        Informations Personnelles
                    </h2>
                    
                    <form @submit.prevent="updateInfo" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Nom complet</label>
                            <input type="text" v-model="formInfo.name" class="py-3 px-3 mt-1 block w-full rounded-xl border-slate-300 bg-slate-50 focus:bg-white focus:shadow-lg dark:bg-slate-900 dark:border-slate-700 dark:text-white" /> 
                            <div v-if="formInfo.errors.name" class="text-red-500 text-xs mt-1">{{ formInfo.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Adresse Email</label>
                            <input type="email" v-model="formInfo.email" class="py-3 px-3 mt-1 block w-full rounded-xl border-slate-300 bg-slate-50 focus:bg-white focus:shadow-lg dark:bg-slate-900 dark:border-slate-700 dark:text-white" />
                            <div v-if="formInfo.errors.email" class="text-red-500 text-xs mt-1">{{ formInfo.errors.email }}</div>
                        </div>

                        <div class="pt-4 flex justify-end">
                            <button type="submit" :disabled="formInfo.processing" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-500 transition disabled:opacity-50">
                                Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-white dark:bg-slate-800 shadow rounded-2xl border border-slate-200 dark:border-slate-700/50 p-6">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <KeyIcon class="w-6 h-6 text-orange-500" />
                        Changer le mot de passe
                    </h2>

                    <form @submit.prevent="updatePassword" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Mot de passe actuel</label>
                            <input type="password" v-model="formPassword.current_password" class="py-3 px-3 mt-1 block w-full rounded-xl border-slate-300 bg-slate-50 focus:bg-white focus:shadow-lg dark:bg-slate-900 dark:border-slate-700 dark:text-white" />
                            <div v-if="formPassword.errors.current_password" class="text-red-500 text-xs mt-1">{{ formPassword.errors.current_password }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Nouveau mot de passe</label>
                            <input type="password" v-model="formPassword.password" class="py-3 px-3 mt-1 block w-full rounded-xl border-slate-300 bg-slate-50 focus:bg-white focus:shadow-lg dark:bg-slate-900 dark:border-slate-700 dark:text-white" />
                            <div v-if="formPassword.errors.password" class="text-red-500 text-xs mt-1">{{ formPassword.errors.password }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Confirmer le nouveau mot de passe</label>
                            <input type="password" v-model="formPassword.password_confirmation" class="mt-1 py-3 px-3 block w-full rounded-xl border-slate-300 bg-slate-50 focus:bg-white focus:shadow-lg dark:bg-slate-900 dark:border-slate-700 dark:text-white" />
                        </div>

                        <div class="pt-4 flex justify-end">
                            <button type="submit" :disabled="formPassword.processing" class="bg-slate-900 dark:bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 transition disabled:opacity-50">
                                Changer le mot de passe
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </AppLayout>
</template>