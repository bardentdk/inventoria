<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    ComputerDesktopIcon,
    TicketIcon,
    UsersIcon,
    ArrowLeftOnRectangleIcon
} from '@heroicons/vue/24/outline';

const page = usePage();
// Utilisation de computed pour éviter les erreurs d'accès si user est null au démarrage
const user = computed(() => page.props.auth.user || { name: 'Invité', email: '' });

// On définit la nav dans une fonction ou computed pour être sûr que route() est disponible
const navigation = computed(() => {
    const isAdmin = user.value.role === 'admin';
    return [
        {
            name: 'Dashboard',
            href: route('dashboard'),
            icon: ComputerDesktopIcon,
            active: route().current('dashboard')
        },
        {
            name: 'Inventaire',
            href: route('assets.index'),
            icon: ComputerDesktopIcon,
            active: route().current('assets.*')
        },
        {
            name: 'Tickets',
            href: route('tickets.index'),
            icon: TicketIcon,
            active: route().current('tickets.*')
        },
        ...(isAdmin ? [{
            name: 'Utilisateurs',
            href: route('users.index'),
            icon: UsersIcon,
            active: route().current('users.*')
        }] : [])
    ];
});
</script>

<template>
    <div class="min-h-screen bg-slate-50 dark:bg-slate-900 flex">

        <aside class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 text-white border-r border-slate-800 shadow-2xl hidden lg:flex flex-col transition-all">
            <div class="h-20 flex items-center px-8 border-b border-slate-800/50">
                <span class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">
                    NEXA
                </span>
            </div>

            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <Link v-for="item in navigation" :key="item.name" :href="item.href"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group"
                    :class="item.active
                        ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20'
                        : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                >
                    <component :is="item.icon" class="w-6 h-6 shrink-0" />
                    <span class="font-medium">{{ item.name }}</span>
                </Link>
            </nav>

            <div class="p-4 border-t border-slate-800/50">
                <div class="flex items-center gap-3 p-3 rounded-lg bg-slate-800/50 border border-slate-700/50">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center font-bold text-white">
                        {{ user.name.charAt(0) }}
                    </div>
                    <div class="flex-1 min-w-0 overflow-hidden">
                        <p class="text-sm font-medium truncate text-white">{{ user.name }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ user.email }}</p>
                    </div>
                    <Link :href="route('logout')" method="post" as="button" class="text-slate-400 hover:text-red-400 transition-colors">
                        <ArrowLeftOnRectangleIcon class="w-5 h-5" />
                    </Link>
                </div>
            </div>
        </aside>

        <main class="flex-1 lg:ml-72 p-8 transition-all w-full">
            <slot />
        </main>
    </div>
</template>