<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { 
    Dialog, 
    DialogPanel, 
    TransitionChild, 
    TransitionRoot 
} from '@headlessui/vue';
import { 
    ComputerDesktopIcon, 
    TicketIcon, 
    UsersIcon, 
    ArrowLeftOnRectangleIcon,
    CubeIcon,
    Bars3Icon,
    XMarkIcon,
    CodeBracketIcon,
    SunIcon,  // Nouveau
    MoonIcon  // Nouveau
} from '@heroicons/vue/24/outline';

const page = usePage();
const user = computed(() => page.props.auth.user);

// État pour ouvrir/fermer le menu mobile
const sidebarOpen = ref(false);

// --- GESTION DARK MODE ---
const isDark = ref(false);

const toggleTheme = () => {
    isDark.value = !isDark.value;
    if (isDark.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

// Au chargement, on vérifie la préférence enregistrée
onMounted(() => {
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        isDark.value = true;
        document.documentElement.classList.add('dark');
    } else {
        isDark.value = false;
        document.documentElement.classList.remove('dark');
    }
});

// Définition du menu
const navigation = computed(() => {
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
            active: route().current('assets.*') 
        },
        { 
            name: 'Tickets', 
            href: route('tickets.index'), 
            icon: TicketIcon, 
            active: route().current('tickets.*') 
        },
    ];

    if (user.value && user.value.role === 'admin') {
        items.push({ 
            name: 'Utilisateurs', 
            href: route('users.index'), 
            icon: UsersIcon, 
            active: route().current('users.*') 
        });
    }
    if (user.value && user.value.role === 'admin') {
        items.push({ 
            name: 'Logs d\'activités', 
            href: route('logs.index'), 
            icon: CodeBracketIcon, 
            active: route().current('logs.*') 
        });
    }

    return items;
});

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="h-full bg-slate-50 dark:bg-slate-900 transition-colors duration-300">
        
        <TransitionRoot as="template" :show="sidebarOpen">
            <Dialog as="div" class="relative z-50 lg:hidden" @close="sidebarOpen = false">
                <TransitionChild as="template" enter="transition-opacity ease-linear duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="transition-opacity ease-linear duration-300" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-slate-900/80" />
                </TransitionChild>

                <div class="fixed inset-0 flex">
                    <TransitionChild as="template" enter="transition ease-in-out duration-300 transform" enter-from="-translate-x-full" enter-to="translate-x-0" leave="transition ease-in-out duration-300 transform" leave-from="translate-x-0" leave-to="-translate-x-full">
                        <DialogPanel class="relative mr-16 flex w-full max-w-xs flex-1 bg-white dark:bg-slate-900 ring-1 ring-slate-900/10 dark:ring-white/10">
                            
                            <TransitionChild as="template" enter="ease-in-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in-out duration-300" leave-from="opacity-100" leave-to="opacity-0">
                                <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                                    <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                                        <span class="sr-only">Fermer le menu</span>
                                        <XMarkIcon class="h-6 w-6 text-white" aria-hidden="true" />
                                    </button>
                                </div>
                            </TransitionChild>

                            <div class="flex grow flex-col gap-y-5 overflow-y-auto px-6 pb-4">
                                <div class="flex h-16 shrink-0 items-center">
                                    <div class="flex items-center gap-2 mt-10 pb-5 sm:mt-20">
                                        <img src="img/logo_australe_white.png" alt="" class="w-[70px] md:w-[100px] brightness-0 dark:brightness-100 transition-all duration-300" />
                                    </div>
                                </div>
                                <nav class="flex flex-1 flex-col">
                                    <ul role="list" class="flex flex-1 flex-col gap-y-7">
                                        <li>
                                            <ul role="list" class="-mx-2 space-y-1">
                                                <li v-for="item in navigation" :key="item.name">
                                                    <Link :href="item.href" 
                                                        :class="[
                                                            item.active 
                                                                ? 'bg-blue-50 text-blue-600 dark:bg-blue-600 dark:text-white' 
                                                                : 'text-slate-700 hover:text-blue-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800', 
                                                            'group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold transition-colors'
                                                        ]"
                                                        @click="sidebarOpen = false"
                                                    >
                                                        <component :is="item.icon" class="h-6 w-6 shrink-0" aria-hidden="true" />
                                                        {{ item.name }}
                                                    </Link>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="mt-auto">
                                            <button @click="toggleTheme" class="flex items-center gap-x-4 py-2 text-sm font-medium leading-6 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white -mx-2 px-2 mb-2">
                                                <SunIcon v-if="isDark" class="h-5 w-5" />
                                                <MoonIcon v-else class="h-5 w-5" />
                                                {{ isDark ? 'Mode clair' : 'Mode sombre' }}
                                            </button>

                                            <Link :href="route('profile.edit')" class="flex items-center gap-x-4 py-3 text-sm font-semibold leading-6 text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-800 rounded-md -mx-2 px-2 transition-colors">
                                                <div class="h-8 w-8 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center border border-slate-300 dark:border-slate-700">
                                                    {{ user.name.charAt(0) }}
                                                </div>
                                                <span class="sr-only">Mon profil</span>
                                                <span aria-hidden="true">{{ user.name }}</span>
                                            </Link>
                                            <button @click="logout" class="w-full text-left flex items-center gap-x-4 py-2 text-sm font-medium leading-6 text-red-600 dark:text-red-400 hover:text-red-500 -mx-2 px-2 mt-2">
                                                <ArrowLeftOnRectangleIcon class="h-5 w-5" />
                                                Déconnexion
                                            </button>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </Dialog>
        </TransitionRoot>

        <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 transition-colors duration-300">
            <div class="flex grow flex-col gap-y-5 overflow-y-auto px-6 pb-4">
                <div class="flex h-16 shrink-0 items-center mt-5 sm:mt-10 mb-5">
                    <Link :href="route('dashboard')" class="flex items-center gap-2">
                        <img src="img/logo_australe_white.png" alt="" class="w-[70px] md:w-[100px] brightness-0 dark:brightness-100 transition-all duration-300" />
                    </Link>
                </div>
                <nav class="flex flex-1 flex-col">
                    <ul role="list" class="flex flex-1 flex-col gap-y-7">
                        <li>
                            <ul role="list" class="-mx-2 space-y-1">
                                <li v-for="item in navigation" :key="item.name">
                                    <Link :href="item.href" :class="[
                                        item.active 
                                            ? 'bg-blue-50 text-blue-600 dark:bg-blue-600 dark:text-white shadow-sm' 
                                            : 'text-slate-700 hover:bg-slate-50 hover:text-blue-600 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white', 
                                        'group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold transition-all duration-200'
                                    ]">
                                        <component :is="item.icon" class="h-6 w-6 shrink-0" aria-hidden="true" />
                                        {{ item.name }}
                                    </Link>
                                </li>
                            </ul>
                        </li>
                        <li class="mt-auto">
                            <div class="flex flex-col gap-2 bg-slate-50 dark:bg-slate-800/50 rounded-xl p-3 border border-slate-200 dark:border-slate-700/50 transition-colors">
                                
                                <div class="flex items-center gap-x-4">
                                    <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-inner text-white font-bold">
                                        {{ user.name.charAt(0) }}
                                    </div>
                                    <div class="flex flex-col truncate">
                                        <span class="truncate text-slate-900 dark:text-white font-semibold">{{ user.name }}</span>
                                        <Link :href="route('profile.edit')" class="text-xs text-slate-500 dark:text-slate-400 font-normal hover:text-blue-600 dark:hover:text-blue-400">Voir mon profil</Link>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-200 dark:border-slate-700">
                                    <button @click="toggleTheme" class="p-1.5 text-slate-500 hover:text-orange-500 dark:text-slate-400 dark:hover:text-yellow-400 transition rounded-lg hover:bg-white dark:hover:bg-slate-700" title="Changer le thème">
                                        <SunIcon v-if="isDark" class="h-5 w-5" />
                                        <MoonIcon v-else class="h-5 w-5" />
                                    </button>

                                    <button @click="logout" class="p-1.5 text-slate-500 hover:text-red-500 dark:text-slate-400 dark:hover:text-red-400 transition rounded-lg hover:bg-white dark:hover:bg-slate-700" title="Déconnexion">
                                        <ArrowLeftOnRectangleIcon class="h-5 w-5" />
                                    </button>
                                </div>
                            </div>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="sticky top-0 z-40 flex items-center gap-x-6 bg-white dark:bg-slate-900 px-4 py-4 shadow-sm border-b border-slate-200 dark:border-slate-800 sm:px-6 lg:hidden transition-colors duration-300">
            <button type="button" class="-m-2.5 p-2.5 text-slate-700 dark:text-slate-400 lg:hidden" @click="sidebarOpen = true">
                <span class="sr-only">Ouvrir le menu</span>
                <Bars3Icon class="h-6 w-6" aria-hidden="true" />
            </button>
            <div class="flex-1 text-sm font-semibold leading-6 text-slate-900 dark:text-white">
                <img src="img/logo_australe_white.png" alt="" class="w-[70px] sm:w-[100px] brightness-0 dark:brightness-100 transition-all duration-300">
            </div>
            <Link :href="route('profile.edit')">
                <span class="sr-only">Profil</span>
                <div class="h-8 w-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-700 dark:text-white border border-slate-300 dark:border-slate-600">
                    {{ user.name.charAt(0) }}
                </div>
            </Link>
        </div>

        <main class="py-10 lg:pl-72">
            <div class="px-4 sm:px-6 lg:px-8 w-full max-w-7xl mx-auto overflow-x-hidden">
                <slot />
            </div>
        </main>
    </div>
</template>