<script setup>
import { ref, computed } from 'vue';
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
    Bars3Icon, // Icône Menu Hamburger
    XMarkIcon,  // Icône Fermer
    CodeBracketIcon
} from '@heroicons/vue/24/outline';

const page = usePage();
const user = computed(() => page.props.auth.user);

// État pour ouvrir/fermer le menu mobile
const sidebarOpen = ref(false);

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
    <div class="h-full bg-slate-50 dark:bg-slate-900">
        
        <TransitionRoot as="template" :show="sidebarOpen">
            <Dialog as="div" class="relative z-50 lg:hidden" @close="sidebarOpen = false">
                <TransitionChild as="template" enter="transition-opacity ease-linear duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="transition-opacity ease-linear duration-300" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-slate-900/80" />
                </TransitionChild>

                <div class="fixed inset-0 flex">
                    <TransitionChild as="template" enter="transition ease-in-out duration-300 transform" enter-from="-translate-x-full" enter-to="translate-x-0" leave="transition ease-in-out duration-300 transform" leave-from="translate-x-0" leave-to="-translate-x-full">
                        <DialogPanel class="relative mr-16 flex w-full max-w-xs flex-1">
                            
                            <TransitionChild as="template" enter="ease-in-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in-out duration-300" leave-from="opacity-100" leave-to="opacity-0">
                                <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                                    <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                                        <span class="sr-only">Fermer le menu</span>
                                        <XMarkIcon class="h-6 w-6 text-white" aria-hidden="true" />
                                    </button>
                                </div>
                            </TransitionChild>

                            <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-slate-900 px-6 pb-4 ring-1 ring-white/10">
                                <div class="flex h-16 shrink-0 items-center">
                                    <div class="flex items-center gap-2 mt-10 pb-5 sm:mt-20">
                                        <!-- <div class="bg-blue-600 w-8 h-8 rounded-lg flex items-center justify-center text-white font-bold text-lg">N</div>
                                        <span class="text-xl font-bold tracking-tight text-white">NEXA<span class="text-blue-500">.io</span></span> -->
                                        <img src="img/logo_australe_white.png" alt="" class="w-[70px] md:w-[100px]"  />
                                    </div>
                                </div>
                                <nav class="flex flex-1 flex-col">
                                    <ul role="list" class="flex flex-1 flex-col gap-y-7">
                                        <li>
                                            <ul role="list" class="-mx-2 space-y-1">
                                                <li v-for="item in navigation" :key="item.name">
                                                    <Link :href="item.href" 
                                                        :class="[item.active ? 'bg-blue-600 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800', 'group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold']"
                                                        @click="sidebarOpen = false"
                                                    >
                                                        <component :is="item.icon" class="h-6 w-6 shrink-0" aria-hidden="true" />
                                                        {{ item.name }}
                                                    </Link>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="mt-auto">
                                            <Link :href="route('profile.edit')" class="flex items-center gap-x-4 py-3 text-sm font-semibold leading-6 text-white hover:bg-slate-800 rounded-md -mx-2 px-2">
                                                <div class="h-8 w-8 rounded-full bg-slate-800 flex items-center justify-center border border-slate-700">
                                                    {{ user.name.charAt(0) }}
                                                </div>
                                                <span class="sr-only">Mon profil</span>
                                                <span aria-hidden="true">{{ user.name }}</span>
                                            </Link>
                                            <button @click="logout" class="w-full text-left flex items-center gap-x-4 py-2 text-sm font-medium leading-6 text-red-400 hover:text-red-300 -mx-2 px-2 mt-2">
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

        <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
            <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-slate-900 px-6 pb-4">
                <div class="flex h-16 shrink-0 items-center mt-5 sm:mt-10 mb-5">
                    <Link :href="route('dashboard')" class="flex items-center gap-2">
                        <img src="img/logo_australe_white.png" alt="" class="w-[70px] md:w-[100px]" />
                        <!-- <div class="bg-blue-600 w-8 h-8 rounded-lg flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-blue-500/30">N</div>
                        <span class="text-xl font-bold tracking-tight text-white">NEXA<span class="text-blue-500">.io</span></span> -->
                    </Link>
                </div>
                <nav class="flex flex-1 flex-col">
                    <ul role="list" class="flex flex-1 flex-col gap-y-7">
                        <li>
                            <ul role="list" class="-mx-2 space-y-1">
                                <li v-for="item in navigation" :key="item.name">
                                    <Link :href="item.href" :class="[item.active ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/25' : 'text-slate-400 hover:text-white hover:bg-slate-800', 'group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold transition-all duration-200']">
                                        <component :is="item.icon" class="h-6 w-6 shrink-0" aria-hidden="true" />
                                        {{ item.name }}
                                    </Link>
                                </li>
                            </ul>
                        </li>
                        <li class="mt-auto">
                            <div class="flex items-center gap-x-4 py-3 text-sm font-semibold leading-6 text-white bg-slate-800/50 rounded-xl p-3 border border-slate-700/50">
                                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-inner">
                                    {{ user.name.charAt(0) }}
                                </div>
                                <div class="flex flex-col truncate">
                                    <span class="truncate">{{ user.name }}</span>
                                    <Link :href="route('profile.edit')" class="text-xs text-slate-400 font-normal hover:text-blue-400">Voir mon profil</Link>
                                </div>
                                <button @click="logout" class="ml-auto text-slate-400 hover:text-red-400 transition">
                                    <ArrowLeftOnRectangleIcon class="h-5 w-5" />
                                </button>
                            </div>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="sticky top-0 z-40 flex items-center gap-x-6 bg-slate-900 px-4 py-4 shadow-sm sm:px-6 lg:hidden">
            <button type="button" class="-m-2.5 p-2.5 text-slate-400 lg:hidden" @click="sidebarOpen = true">
                <span class="sr-only">Ouvrir le menu</span>
                <Bars3Icon class="h-6 w-6" aria-hidden="true" />
            </button>
            <div class="flex-1 text-sm font-semibold leading-6 text-white">
                <!-- NEXA.io --> <img src="img/logo_australe_white.png" alt="" class="w-[70px] sm:w-[100px]">
            </div>
            <Link :href="route('profile.edit')">
                <span class="sr-only">Profil</span>
                <div class="h-8 w-8 rounded-full bg-slate-700 flex items-center justify-center text-white border border-slate-600">
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