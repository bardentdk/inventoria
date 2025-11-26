<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, onUpdated, nextTick } from 'vue';
import {
    PaperAirplaneIcon,
    CheckCircleIcon,
    ClockIcon,
    NoSymbolIcon,
    ComputerDesktopIcon
} from '@heroicons/vue/24/solid';

const props = defineProps({
    ticket: Object,
    auth_id: Number,
});

// Formulaire pour nouveau message
const form = useForm({
    message: '',
});

// Scroll automatique vers le bas du chat
const messagesContainer = ref(null);
const scrollToBottom = () => {
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
};

const sendMessage = () => {
    form.post(route('tickets.messages.store', props.ticket.id), {
        onSuccess: () => {
            form.reset();
            nextTick(scrollToBottom);
        },
    });
};

const updateStatus = (newStatus) => {
    if(confirm('Voulez-vous changer le statut ce ticket ?')) {
        router.patch(route('tickets.status', props.ticket.id), { status: newStatus });
    }
};

// Couleurs des statuts
const statusColors = {
    open: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
    in_progress: 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
    resolved: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
    closed: 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
};

const statusLabels = {
    open: 'Ouvert', in_progress: 'En cours', resolved: 'Résolu', closed: 'Fermé'
};
</script>

<template>
    <Head :title="`Ticket #${ticket.id}`" />

    <AppLayout>
        <div class="max-w-7xl mx-auto h-[calc(100vh-8rem)] flex flex-col lg:flex-row gap-6">

            <div class="flex-1 flex flex-col bg-white dark:bg-slate-800 rounded-2xl shadow-xl shadow-slate-200/50 dark:shadow-none border border-slate-200 dark:border-slate-700/50 overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800">
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ ticket.title }}</h1>
                        <p class="text-xs text-slate-500">Ticket #{{ ticket.id }} • Créé {{ ticket.created_at }}</p>
                    </div>
                    <span :class="['px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider', statusColors[ticket.status]]">
                        {{ statusLabels[ticket.status] }}
                    </span>
                </div>

                <div ref="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-6 bg-slate-50 dark:bg-slate-900/50">
                    <div class="flex justify-start">
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-4 rounded-2xl rounded-tl-none max-w-[85%] shadow-sm">
                            <p class="text-xs font-bold text-slate-500 mb-1">{{ ticket.user.name }} (Auteur)</p>
                            <p class="text-slate-800 dark:text-slate-200 whitespace-pre-wrap">{{ ticket.description }}</p>
                        </div>
                    </div>

                    <div v-for="msg in ticket.messages" :key="msg.id"
                        class="flex"
                        :class="msg.user_id === auth_id ? 'justify-end' : 'justify-start'">

                        <div :class="[
                            'p-4 rounded-2xl max-w-[85%] shadow-sm text-sm',
                            msg.user_id === auth_id
                                ? 'bg-blue-600 text-white rounded-tr-none'
                                : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-tl-none'
                        ]">
                            <p v-if="msg.user_id !== auth_id" class="text-xs font-bold text-slate-500 mb-1">{{ msg.user.name }}</p>
                            <p class="whitespace-pre-wrap">{{ msg.message }}</p>
                            <p :class="['text-[10px] mt-2 text-right', msg.user_id === auth_id ? 'text-blue-200' : 'text-slate-400']">
                                {{ new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-white dark:bg-slate-800 border-t border-slate-100 dark:border-slate-700">
                    <form @submit.prevent="sendMessage" class="relative">
                        <textarea
                            v-model="form.message"
                            rows="3"
                            class="block w-full rounded-xl border-0 py-3 pl-4 pr-12 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 dark:bg-slate-900 dark:ring-slate-700 dark:text-white resize-none"
                            placeholder="Écrivez votre réponse ici..."
                            required
                            @keydown.enter.exact.prevent="sendMessage"
                        ></textarea>
                        <button
                            type="submit"
                            :disabled="form.processing || !form.message"
                            class="absolute bottom-3 right-3 p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-500 transition-colors disabled:opacity-50"
                        >
                            <PaperAirplaneIcon class="w-5 h-5" />
                        </button>
                    </form>
                </div>
            </div>

            <div class="w-full lg:w-80 space-y-6">

                <div v-if="$page.props.auth.user.role === 'admin'" class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50">
                    <h3 class="font-bold text-slate-900 dark:text-white mb-4 text-sm uppercase tracking-wider">Gestion</h3>
                    <div class="space-y-2">
                        <button @click="updateStatus('resolved')" v-if="ticket.status !== 'resolved'" class="w-full flex items-center gap-2 px-3 py-2 text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 rounded-lg transition dark:bg-green-900/20 dark:text-green-400">
                            <CheckCircleIcon class="w-5 h-5" /> Marquer comme Résolu
                        </button>
                        <button @click="updateStatus('in_progress')" v-if="ticket.status === 'open'" class="w-full flex items-center gap-2 px-3 py-2 text-sm font-medium text-orange-700 bg-orange-50 hover:bg-orange-100 rounded-lg transition dark:bg-orange-900/20 dark:text-orange-400">
                            <ClockIcon class="w-5 h-5" /> Prendre en charge
                        </button>
                        <button @click="updateStatus('closed')" v-if="ticket.status !== 'closed'" class="w-full flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-700 bg-slate-50 hover:bg-slate-100 rounded-lg transition dark:bg-slate-700/50 dark:text-slate-300">
                            <NoSymbolIcon class="w-5 h-5" /> Clôturer
                        </button>
                    </div>
                </div>

                <div v-if="ticket.asset" class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50">
                    <h3 class="font-bold text-slate-900 dark:text-white mb-4 text-sm uppercase tracking-wider flex items-center gap-2">
                        <ComputerDesktopIcon class="w-4 h-4" /> Matériel lié
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div class="p-3 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-100 dark:border-slate-700">
                            <p class="font-bold text-slate-900 dark:text-white">{{ ticket.asset.name }}</p>
                            <p class="text-xs text-slate-500 mt-1">{{ ticket.asset.category.name }}</p>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-700">
                            <span class="text-slate-500">S/N</span>
                            <span class="font-mono text-slate-700 dark:text-slate-300">{{ ticket.asset.serial_number }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-700">
                            <span class="text-slate-500">Ref</span>
                            <span class="font-mono text-slate-700 dark:text-slate-300">{{ ticket.asset.inventory_code }}</span>
                        </div>
                        <div class="pt-2">
                            <span class="block text-xs text-slate-400 mb-1">Spécifications</span>
                            <p class="text-slate-600 dark:text-slate-400 text-xs italic">{{ ticket.asset.specs || 'Aucune info technique' }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50">
                    <h3 class="font-bold text-slate-900 dark:text-white mb-4 text-sm uppercase tracking-wider">Demandeur</h3>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center font-bold text-slate-600">
                            {{ ticket.user.name.charAt(0) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ ticket.user.name }}</p>
                            <p class="text-xs text-slate-500">{{ ticket.user.email }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>