<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { MagnifyingGlassIcon, PlusIcon, PencilSquareIcon } from '@heroicons/vue/24/outline';
import debounce from 'lodash/debounce';

const props = defineProps({
    assets: Object,
    filters: Object,
});

// Recherche réactive
const search = ref(props.filters.search);

watch(search, debounce((value) => {
    router.get(route('assets.index'), { search: value }, { preserveState: true, replace: true });
}, 300));

// Helper pour les couleurs de badges
const getStatusColor = (status) => {
    const colors = {
        available: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        assigned: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        repair: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
        broken: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
};
</script>

<template>
    <Head title="Inventaire" />

    <AppLayout>
        <div class="sm:flex sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Inventaire</h1>
                <p class="mt-2 text-sm text-slate-500">Gérez l'ensemble du parc informatique.</p>
            </div>
            <div class="mt-4 sm:mt-0 flex gap-4">
                <Link :href="route('assets.create')" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg font-medium shadow-lg shadow-blue-500/30 transition-all">
                    <PlusIcon class="w-5 h-5" />
                    Ajouter
                </Link>
            </div>
        </div>

        <div class="mb-6">
            <div class="relative max-w-sm">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <MagnifyingGlassIcon class="h-5 w-5 text-slate-400" />
                </div>
                <input v-model="search" type="text" placeholder="Rechercher (Nom, Série, Code)..."
                    class="block w-full rounded-xl border-0 py-2.5 pl-10 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 dark:bg-slate-800 dark:ring-slate-700 dark:text-white" />
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-900/50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Matériel</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Référence</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Catégorie</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Statut</th>
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        <tr v-for="asset in assets.data" :key="asset.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-900 dark:text-white">{{ asset.name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-500">S/N: {{ asset.serial_number }}</div>
                                <div class="text-xs text-slate-400">Ref: {{ asset.inventory_code }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                    <div class="text-sm font-medium text-slate-900 dark:text-white">{{ asset.name }}</div>

                                    <div class="text-sm font-medium text-slate-900 dark:text-white hover:text-blue-600 transition-colors">
                                        <Link :href="route('assets.show', asset.id)">
                                            {{ asset.name }}
                                        </Link>
                                    </div>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize', getStatusColor(asset.status)]">
                                    {{ asset.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <Link :href="route('assets.edit', asset.id)" class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
                                    <PencilSquareIcon class="w-5 h-5" />
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="assets.data.length === 0">
                            <td colspan="5" class="px-6 py-10 text-center text-slate-500">
                                Aucun matériel trouvé.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="bg-slate-50 dark:bg-slate-900/50 px-6 py-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-slate-700 dark:text-slate-400">
                            Affichage de <span class="font-medium">{{ assets.from }}</span> à <span class="font-medium">{{ assets.to }}</span> sur <span class="font-medium">{{ assets.total }}</span> résultats
                        </p>
                    </div>
                    <div>
                        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                            <Link v-for="link in assets.links" :key="link.label" :href="link.url ?? '#'"
                                v-html="link.label"
                                :class="[
                                    'relative inline-flex items-center px-4 py-2 text-sm font-semibold focus:z-20',
                                    link.active
                                        ? 'z-10 bg-blue-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600'
                                        : 'text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 focus:outline-offset-0 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-800',
                                    !link.url ? 'opacity-50 cursor-not-allowed' : '',
                                    // Arrondir les coins du premier et dernier élément
                                    link.label.includes('Previous') ? 'rounded-l-md' : '',
                                    link.label.includes('Next') ? 'rounded-r-md' : ''
                                ]"
                            />
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>