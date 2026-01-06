<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { MagnifyingGlassIcon, PlusIcon, PencilSquareIcon, EyeIcon, ArrowDownTrayIcon, ArrowUpTrayIcon, TrashIcon, ExclamationTriangleIcon, GiftIcon } from '@heroicons/vue/24/outline';
import debounce from 'lodash/debounce';

const props = defineProps({
    assets: Object,
    filters: Object,
    structures: Array,
});

// --- 1. GESTION DES FILTRES ---
const search = ref(props.filters.search || '');
const selectedStructure = ref(props.filters.structure || '');
const showDonations = ref(props.filters.show_donations === 'true');

// Fonction centrale pour recharger la page en gardant TOUS les filtres actifs
const updateParams = () => {
    router.get(route('assets.index'), { 
        search: search.value,
        structure: selectedStructure.value,
        show_donations: showDonations.value ? 'true' : null,
    }, { preserveState: true, replace: true });
};

// Déclencheurs
const filter = () => {
    updateParams(); // Appelé par le Select Structure et Entrée sur Recherche
};

const toggleDonationView = () => {
    showDonations.value = !showDonations.value;
    updateParams();
};

// Recherche automatique (Debounce)
watch(search, debounce(() => {
    updateParams();
}, 300));


// --- 2. IMPORT FICHIER ---
const importForm = useForm({
    file: null,
});
const fileInput = ref(null);

const triggerImport = () => {
    fileInput.value.click();
};

const handleImport = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    importForm.file = file;
    importForm.post(route('assets.import'), {
        forceFormData: true,
        onSuccess: () => {
            fileInput.value.value = '';
            importForm.reset();
        },
        onFinish: () => importForm.reset(),
    });
};

// --- 3. SUPPRESSION TOTALE ---
const confirmDestroyAllOpen = ref(false);
const destroyAllForm = useForm({ password: '' });

const destroyAllAssets = () => {
    destroyAllForm.post(route('assets.destroy-all'), {
        preserveScroll: true,
        onSuccess: () => {
            confirmDestroyAllOpen.value = false;
            destroyAllForm.reset();
        },
        onFinish: () => destroyAllForm.reset(),
    });
};

// --- 4. UTILITAIRES ---
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
                <a :href="route('assets.export', { search: search, structure: selectedStructure, show_donations: showDonations ? 'true' : null })"
                    class="flex items-center gap-2 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 px-4 py-2 rounded-lg font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm">
                    <ArrowDownTrayIcon class="w-5 h-5" />
                    Exporter CSV
                </a>

                <input type="file" ref="fileInput" class="hidden" accept=".csv, .xlsx" @change="handleImport" />
                <button @click="triggerImport" :disabled="importForm.processing"
                    class="flex items-center gap-2 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 px-4 py-2 rounded-lg font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm disabled:opacity-50">
                    <ArrowUpTrayIcon v-if="!importForm.processing" class="w-5 h-5" />
                    <span v-else class="w-5 h-5 border-2 border-slate-500 border-t-transparent rounded-full animate-spin"></span>
                    Importer
                </button>

                <Link :href="route('assets.create')"
                    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg font-medium shadow-lg shadow-blue-500/30 transition-all">
                    <PlusIcon class="w-5 h-5" />
                    Ajouter
                </Link>

                <template v-if="props.assets">
                    <button v-if="$page.props.auth.user.role === 'admin'" @click="confirmDestroyAllOpen = true"
                        class="flex items-center gap-2 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800 px-4 py-2 rounded-lg font-medium hover:bg-red-100 dark:hover:bg-red-900/40 transition shadow-sm ml-2">
                        <TrashIcon class="w-5 h-5" />
                        <span class="hidden sm:inline">Tout Vider</span>
                    </button>
                </template>
            </div>
        </div>

        <div class="mb-6">
            <div class="flex flex-col sm:flex-row gap-4 mb-6">
                <div class="relative flex-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <MagnifyingGlassIcon class="h-5 w-5 text-slate-400" />
                    </div>
                    <input 
                        v-model="search" 
                        @keyup.enter="filter"
                        type="text" 
                        placeholder="Rechercher (Nom, Série, Code)..."
                        class="block w-full rounded-xl border-0 py-2.5 pl-10 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 dark:bg-slate-800 dark:ring-slate-700 dark:text-white"
                    /> 
                </div>

                <select 
                    v-model="selectedStructure" 
                    @change="filter"
                    class="rounded-lg border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white focus:ring-blue-500"
                >
                    <option value="">Toutes les structures</option>
                    <option v-for="struct in structures" :key="struct.id" :value="struct.id">
                        {{ struct.name }}
                    </option>
                </select>

                <button 
                    @click="toggleDonationView"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all shadow-sm border ml-2 whitespace-nowrap"
                    :class="showDonations 
                        ? 'bg-purple-600 text-white border-purple-600 hover:bg-purple-500' 
                        : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700'"
                >
                    <GiftIcon class="w-5 h-5" />
                    {{ showDonations ? 'Vue Donations' : 'Vue Inventaire' }}
                </button>
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
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Structure</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Statut</th>
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        <tr v-for="asset in assets.data" :key="asset.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <Link :href="route('assets.show', asset.id)" class="text-sm font-medium text-slate-900 dark:text-white hover:text-blue-600 transition-colors block">
                                    {{ asset.name }}
                                    <span v-if="asset.is_donation" class="ml-2 inline-flex items-center rounded-md bg-purple-50 px-2 py-1 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-700/10">
                                        Don
                                    </span>
                                </Link>
                                <div v-if="asset.user" class="text-xs text-blue-500 mt-0.5 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    Assigné à {{ asset.user.name }}
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <Link :href="route('assets.show', asset.id)" class="group block cursor-pointer">
                                    <div class="text-sm text-slate-500 group-hover:text-blue-600 transition-colors">S/N: {{ asset.serial_number }}</div>
                                    <div class="text-xs text-slate-400">Ref: {{ asset.inventory_code }}</div>
                                </Link>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600 dark:bg-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                    {{ asset.category.name }}
                                </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <span v-if="asset.structure" class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-900/30 dark:text-blue-300 dark:ring-blue-500/30">
                                    {{ asset.structure.name }}
                                </span>
                                <span v-else class="text-slate-400 italic text-xs">--</span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize', getStatusColor(asset.status)]">
                                    {{ asset.status }}
                                </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-3">
                                    <Link :href="route('assets.show', asset.id)" class="text-slate-400 hover:text-blue-600 transition-colors" title="Voir la fiche">
                                        <EyeIcon class="w-5 h-5" />
                                    </Link>
                                    <Link :href="route('assets.edit', asset.id)" class="text-slate-400 hover:text-orange-600 transition-colors" title="Modifier">
                                        <PencilSquareIcon class="w-5 h-5" />
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="assets.data.length === 0">
                            <td colspan="6" class="px-6 py-10 text-center text-slate-500">
                                Aucun matériel trouvé dans cette vue.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="bg-slate-50 dark:bg-slate-900/50 px-6 py-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-slate-700 dark:text-slate-400">
                            Affichage de <span class="font-medium">{{ assets.from }}</span> à <span class="font-medium">{{ assets.to }}</span> sur <span class="font-medium">{{ assets.total }}</span> résultats
                        </p>
                    </div>
                    <div>
                        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                            <Link v-for="link in assets.links" :key="link.label" :href="link.url ?? '#'" v-html="link.label" 
                                :class="['relative inline-flex items-center px-4 py-2 text-sm font-semibold focus:z-20', link.active ? 'z-10 bg-blue-600 text-white' : 'text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 dark:text-slate-200 dark:ring-slate-700', !link.url ? 'opacity-50 cursor-not-allowed' : '', link.label.includes('Previous') ? 'rounded-l-md' : '', link.label.includes('Next') ? 'rounded-r-md' : '']" />
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <TransitionRoot as="template" :show="confirmDestroyAllOpen">
            <Dialog as="div" class="relative z-50" @close="confirmDestroyAllOpen = false">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-slate-900/75 transition-opacity" />
                </TransitionChild>
                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                            <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white dark:bg-slate-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                        <ExclamationTriangleIcon class="h-6 w-6 text-red-600 dark:text-red-400" aria-hidden="true" />
                                    </div>
                                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                        <DialogTitle as="h3" class="text-base font-semibold leading-6 text-slate-900 dark:text-white">Vider tout l'inventaire ?</DialogTitle>
                                        <div class="mt-2">
                                            <p class="text-sm text-slate-500 dark:text-slate-400">Action irréversible.</p>
                                            <div class="mt-4">
                                                <input type="password" v-model="destroyAllForm.password" class="w-full rounded-md border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white shadow-sm focus:border-red-500 focus:ring-red-500" placeholder="Mot de passe admin" @keyup.enter="destroyAllAssets">
                                                <p v-if="destroyAllForm.errors.password" class="mt-1 text-sm text-red-600">{{ destroyAllForm.errors.password }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                    <button type="button" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto disabled:opacity-50" :disabled="destroyAllForm.processing" @click="destroyAllAssets">
                                        <span v-if="destroyAllForm.processing">Suppression...</span>
                                        <span v-else>Tout Supprimer</span>
                                    </button>
                                    <button type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-slate-700 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-white shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 hover:bg-slate-50 dark:hover:bg-slate-600 sm:mt-0 sm:w-auto" @click="confirmDestroyAllOpen = false">Annuler</button>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </AppLayout>
</template>