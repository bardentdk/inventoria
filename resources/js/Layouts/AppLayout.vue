<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    ComputerDesktopIcon,
    TicketIcon,
    UsersIcon,
    ArrowLeftOnRectangleIcon,
    CubeIcon, 
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';

const page = usePage();

// On récupère l'utilisateur de manière sécurisée
const user = computed(() => page.props.auth.user);

// Définition du menu
const navigation = computed(() => {
    // Liste de base accessible à TOUS
    const items = [
        {
            name: 'Dashboard',
            href: route('dashboard'),
            icon: ComputerDesktopIcon,
            active: route().current('dashboard')
        },
        {
            name: 'Inventaire',
            href: route('assets.index'),
            icon: CubeIcon,
            active: route().current('assets.*') // Reste actif sur index, create, edit...
        },
        {
            name: 'Tickets',
            href: route('tickets.index'),
            icon: TicketIcon,
            active: route().current('tickets.*')
        },
    ];

    // Ajout conditionnel pour l'ADMIN uniquement
    if (user.value && user.value.role === 'admin') {
        items.push({
            name: 'Utilisateurs',
            href: route('users.index'),
            icon: UsersIcon,
            active: route().current('users.*')
        });
    }
    // Ajout conditionnel pour l'ADMIN uniquement
    if (user.value && user.value.role === 'admin') {
        items.push({
            name: 'Logs d\'activités',
            href: route('logs.index'),
            icon: ExclamationTriangleIcon,
            active: route().current('logs.*')
        });
    }

    return items;
});
</script>

<template>
    <div class="min-h-screen bg-slate-50 dark:bg-slate-900 flex text-sm font-medium">

        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-white border-r border-slate-800 shadow-2xl hidden lg:flex flex-col transition-all">

            <div class="h-20 flex items-center px-6 border-b border-slate-800/50">
                <Link :href="route('dashboard')" class="flex items-center gap-2">
                    <div class="bg-blue-600 w-8 h-8 rounded-lg flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-blue-500/30">
                        N
                    </div>
                    <span class="text-xl font-bold tracking-tight text-white">
                        NEXA<span class="text-blue-500">.io</span>
                    </span>
                </Link>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                <Link v-for="item in navigation" :key="item.name" :href="item.href"
                    class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all duration-200 group relative"
                    :class="item.active
                        ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/25'
                        : 'text-slate-400 hover:bg-white/5 hover:text-white'"
                >
                    <component :is="item.icon" class="w-6 h-6 shrink-0" />
                    <span>{{ item.name }}</span>

                    <span v-if="item.active" class="absolute right-3 w-1.5 h-1.5 rounded-full bg-white/50"></span>
                </Link>
            </nav>

            <div class="p-4 border-t border-slate-800/50">
                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/50 border border-slate-700/50">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center font-bold text-white shadow-inner">
                        {{ user.name.charAt(0) }}
                    </div>
                    <div class="flex-1 min-w-0 overflow-hidden">
                        <p class="text-sm font-semibold truncate text-white">{{ user.name }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ user.role === 'admin' ? 'Administrateur' : 'Membre' }}</p>
                    </div>
                    <Link :href="route('logout')" method="post" as="button" class="text-slate-500 hover:text-red-400 transition-colors p-1">
                        <ArrowLeftOnRectangleIcon class="w-5 h-5" />
                    </Link>
                </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col lg:pl-64 min-h-screen transition-all">
            <main class="flex-1 p-6 lg:p-10 w-full max-w-7xl mx-auto">
                <slot />
            </main>
        </div>

    </div>
</template>