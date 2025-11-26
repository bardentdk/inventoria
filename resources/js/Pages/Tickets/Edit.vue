<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    ticket: Object,
});

const form = useForm({
    title: props.ticket.title,
    type: props.ticket.type,
    priority: props.ticket.priority,
    description: props.ticket.description,
});

const submit = () => {
    form.put(route('tickets.update', props.ticket.id));
};

const deleteTicket = () => {
    if(confirm('Supprimer ce ticket ? Tous les messages associés seront perdus.')) {
        form.delete(route('tickets.destroy', props.ticket.id));
    }
};
</script>

<template>
    <Head :title="`Modifier Ticket #${ticket.id}`" />

    <AppLayout>
        <div class="max-w-3xl mx-auto">
            <div class="mb-8 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Modifier le ticket</h2>
                <button v-if="$page.props.auth.user.role === 'admin'" @click="deleteTicket" type="button" class="text-red-600 hover:text-red-800 text-sm font-medium">
                    Supprimer le ticket
                </button>
            </div>

            <div class="bg-white dark:bg-slate-800 shadow rounded-2xl border border-slate-200 dark:border-slate-700/50 p-8">
                <form @submit.prevent="submit" class="space-y-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Type</label>
                            <select v-model="form.type" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5">
                                <option value="it_issue">Panne Informatique</option>
                                <option value="logistics">Logistique</option>
                                <option value="supply_request">Demande d'achat</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Priorité</label>
                            <select v-model="form.priority" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5">
                                <option value="low">Basse</option>
                                <option value="medium">Moyenne</option>
                                <option value="high">Haute</option>
                                <option value="critical">Critique</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Titre</label>
                        <input type="text" v-model="form.title" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5" />
                        <div v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Description</label>
                        <textarea v-model="form.description" rows="5" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5"></textarea>
                        <div v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</div>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                        <Link :href="route('tickets.show', ticket.id)" class="text-slate-500 hover:text-slate-700 dark:text-slate-400">Annuler</Link>
                        <button type="submit" :disabled="form.processing" class="bg-blue-600 text-white px-6 py-2 rounded-xl hover:bg-blue-500 transition">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>