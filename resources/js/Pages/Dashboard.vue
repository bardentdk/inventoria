<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { Bars3Icon, XMarkIcon, ComputerDesktopIcon, TicketIcon } from '@heroicons/vue/24/outline';

const navigation = [
  { name: 'Dashboard', href: '/dashboard', icon: ComputerDesktopIcon, current: true },
  { name: 'Tickets', href: '/tickets', icon: TicketIcon, current: false },
];
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white font-sans">

    <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
      <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-slate-900/95 backdrop-blur-xl border-r border-white/10 px-6 pb-4">
        <div class="flex h-16 shrink-0 items-center text-2xl font-bold tracking-tighter text-blue-500">
          NEXA<span class="text-white">.io</span>
        </div>
        <nav class="flex flex-1 flex-col">
          <ul role="list" class="flex flex-1 flex-col gap-y-7">
            <li>
              <ul role="list" class="-mx-2 space-y-1">
                <li v-for="item in navigation" :key="item.name">
                  <Link :href="item.href"
                        :class="[item.current ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30' : 'text-slate-400 hover:text-white hover:bg-white/5', 'group flex gap-x-3 rounded-xl p-3 text-sm font-semibold leading-6 transition-all duration-200']">
                    <component :is="item.icon" class="h-6 w-6 shrink-0" aria-hidden="true" />
                    {{ item.name }}
                  </Link>
                </li>
              </ul>
            </li>
          </ul>
        </nav>
      </div>
    </div>

    <main class="lg:pl-72 py-10 px-4 mx-2 sm:mx-10 sm:px-6 lg:px-8">
      <div class="mb-8 flex justify-between items-center">
        <h1 class="text-3xl font-bold tracking-tight">Vue d'ensemble</h1>
        <button class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg font-medium transition shadow-lg shadow-blue-500/20">
          + Nouveau Ticket
        </button>
      </div>

      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50">
          <p class="text-sm font-medium text-slate-500">Tickets Ouverts</p>
          <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">12</p>
          <div class="mt-2 text-xs text-red-400">2 critiques</div>
        </div>

        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50">
          <p class="text-sm font-medium text-slate-500">Matériel en Stock</p>
          <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">143</p>
          <div class="mt-2 text-xs text-green-400">+5 cette semaine</div>
        </div>
      </div>

      <div class="mt-8 bg-white dark:bg-slate-800 rounded-2xl shadow-sm overflow-hidden border border-slate-200 dark:border-slate-700/50">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
          <h3 class="font-semibold">Dernières demandes</h3>
        </div>
        </div>
    </main>
  </div>
</template>