<script setup>
import { useForm, Head } from '@inertiajs/vue3';

// Le formulaire Inertia gère les erreurs, le loading, etc.
const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login.store'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Connexion" />

    <div class="min-h-screen grid lg:grid-cols-2">

        <div class="hidden lg:flex flex-col justify-center items-center bg-slate-900 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full opacity-30">
                <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-blue-600 rounded-full mix-blend-multiply filter blur-3xl animate-blob"></div>
                <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-purple-600 rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-2000"></div>
            </div>

            <div class="relative z-10 text-center px-10">
                <h1 class="text-6xl font-bold text-white mb-6 tracking-tighter">NEXA</h1>
                <p class="text-xl text-blue-200 font-light max-w-md mx-auto leading-relaxed">
                    La gestion d'inventaire nouvelle génération pour votre centre de formation.
                </p>
            </div>
        </div>

        <div class="flex flex-col justify-center items-center bg-slate-50 dark:bg-slate-950 p-8 sm:p-12">
            <div class="w-full max-w-md space-y-8">
                <div>
                    <h2 class="mt-6 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                        Connexion
                    </h2>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                        Accédez à votre espace sécurisé
                    </p>
                </div>

                <form class="mt-8 space-y-6" @submit.prevent="submit">
                    <div class="space-y-4 rounded-md shadow-sm">
                        <div>
                            <label for="email" class="block text-sm font-medium leading-6 text-slate-900 dark:text-slate-200">Email</label>
                            <div class="mt-2">
                                <input id="email" v-model="form.email" type="email" autocomplete="email" required
                                    class="block w-full rounded-lg border-0 px-5 py-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 dark:bg-slate-900 dark:ring-slate-700 dark:text-white" />
                                <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                            </div>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium leading-6 text-slate-900 dark:text-slate-200">Mot de passe</label>
                            <div class="mt-2">
                                <input id="password" v-model="form.password" type="password" autocomplete="current-password" required
                                    class="block w-full rounded-lg border-0 px-5 py-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 dark:bg-slate-900 dark:ring-slate-700 dark:text-white" />
                                <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember-me" v-model="form.remember" type="checkbox"
                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-600 dark:border-slate-700 dark:bg-slate-900" />
                            <label for="remember-me" class="ml-2 block text-sm text-slate-900 dark:text-slate-300">Se souvenir de moi</label>
                        </div>
                    </div>

                    <div>
                        <button type="submit" :disabled="form.processing"
                            class="flex w-full justify-center rounded-lg bg-blue-600 px-3 py-3 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-all duration-200 disabled:opacity-70 disabled:cursor-not-allowed">
                            <span v-if="form.processing">Connexion...</span>
                            <span v-else>Se connecter</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>