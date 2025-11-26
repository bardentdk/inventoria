<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { Head, useForm } from '@inertiajs/vue3';
    import { ref, watch } from 'vue';
    import {
        Combobox, ComboboxInput, ComboboxButton, ComboboxOptions, ComboboxOption, TransitionRoot
    } from '@headlessui/vue';
    import { CheckIcon, ChevronUpDownIcon, ComputerDesktopIcon } from '@heroicons/vue/20/solid';
    import debounce from 'lodash/debounce';

    const form = useForm({
        title: '',
        type: 'it_issue', // Valeur par défaut
        priority: 'medium',
        description: '',
        asset_id: null,
    });

    // --- LOGIQUE DE RECHERCHE MATÉRIEL (AUTOCOMPLETE) ---
    const selectedAsset = ref(null);
    const query = ref('');
    const filteredAssets = ref([]);
    const isSearching = ref(false);

    // Fonction qui interroge le serveur quand on tape
    const searchAssets = debounce(async (searchQuery) => {
        if (searchQuery === '') {
            filteredAssets.value = [];
            return;
        }
        isSearching.value = true;
        try {
            // On appelle notre route API définie plus tôt
            const response = await fetch(route('assets.search', { search: searchQuery }));
            filteredAssets.value = await response.json();
        } catch (error) {
            console.error('Erreur recherche:', error);
        } finally {
            isSearching.value = false;
        }
    }, 300); // 300ms de pause avant d'envoyer la requête

    // Met à jour l'ID dans le formulaire quand on sélectionne un asset
    watch(selectedAsset, (value) => {
        form.asset_id = value ? value.id : null;
    });

    const submit = () => {
        form.post(route('tickets.store'));
    };
</script>

<template>
    <Head title="Nouveau Ticket" />

    <AppLayout>
        <div class="max-w-3xl mx-auto">
            <div class="md:flex md:items-center md:justify-between mb-8">
                <div class="min-w-0 flex-1">
                    <h2 class="text-2xl font-bold leading-7 text-slate-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
                        Ouvrir un nouveau ticket
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Un problème technique ? Une demande logistique ? Nous sommes là.
                    </p>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none rounded-2xl border border-slate-200 dark:border-slate-700/50 overflow-hidden">
                <form @submit.prevent="submit" class="p-8 space-y-8">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-slate-200">Type de demande</label>
                            <select v-model="form.type" class="mt-2 block w-full rounded-xl border-0 py-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 dark:bg-slate-900 dark:ring-slate-700 dark:text-white">
                                <option value="it_issue">Panne Informatique</option>
                                <option value="logistics">Logistique / Fournitures</option>
                                <option value="supply_request">Demande d'achat</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-slate-200">Priorité</label>
                            <select v-model="form.priority" class="mt-2 block w-full rounded-xl border-0 py-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 dark:bg-slate-900 dark:ring-slate-700 dark:text-white">
                                <option value="low">Basse (Pas urgent)</option>
                                <option value="medium">Moyenne (Standard)</option>
                                <option value="high">Haute (Bloquant)</option>
                                <option value="critical">Critique (Arrêt de service)</option>
                            </select>
                        </div>
                    </div>

                    <div v-if="form.type === 'it_issue'" class="relative">
                        <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-slate-200 mb-2">
                            Matériel concerné <span class="text-slate-500 font-normal">(Recherche par nom, série ou code)</span>
                        </label>

                        <Combobox v-model="selectedAsset" nullable>
                            <div class="relative mt-1">
                                <div class="relative w-full cursor-default overflow-hidden rounded-xl bg-white dark:bg-slate-900 text-left shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/75 focus-visible:ring-offset-2 focus-visible:ring-offset-blue-300 sm:text-sm">
                                    <ComboboxInput
                                        class="w-full border-none py-3 pl-3 pr-10 text-sm leading-5 text-slate-900 dark:text-white bg-transparent focus:ring-0"
                                        :displayValue="(asset) => asset ? `${asset.name} (S/N: ${asset.serial_number})` : ''"
                                        @change="query = $event.target.value; searchAssets($event.target.value)"
                                        placeholder="Tapez pour rechercher un équipement..."
                                    />
                                    <ComboboxButton class="absolute inset-y-0 right-0 flex items-center pr-2">
                                        <ChevronUpDownIcon class="h-5 w-5 text-slate-400" aria-hidden="true" />
                                    </ComboboxButton>
                                </div>
                                <TransitionRoot
                                    leave="transition ease-in duration-100"
                                    leaveFrom="opacity-100"
                                    leaveTo="opacity-0"
                                    @after-leave="query = ''"
                                >
                                    <ComboboxOptions class="absolute mt-1 max-h-60 w-full overflow-auto rounded-md bg-white dark:bg-slate-800 py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm z-50">
                                        <div v-if="filteredAssets.length === 0 && query !== '' && !isSearching" class="relative cursor-default select-none px-4 py-2 text-slate-700 dark:text-slate-300">
                                            Aucun matériel trouvé.
                                        </div>
                                        <div v-if="isSearching" class="relative cursor-default select-none px-4 py-2 text-slate-500">
                                            Recherche en cours...
                                        </div>

                                        <ComboboxOption
                                            v-for="asset in filteredAssets"
                                            as="template"
                                            :key="asset.id"
                                            :value="asset"
                                            v-slot="{ selected, active }"
                                        >
                                            <li class="relative cursor-default select-none py-2 pl-10 pr-4"
                                                :class="{
                                                    'bg-blue-600 text-white': active,
                                                    'text-slate-900 dark:text-slate-100': !active,
                                                }">
                                                <div class="flex flex-col">
                                                    <span class="block truncate" :class="{ 'font-medium': selected, 'font-normal': !selected }">
                                                        {{ asset.name }}
                                                    </span>
                                                    <span class="text-xs opacity-70">
                                                        Ref: {{ asset.inventory_code }} • S/N: {{ asset.serial_number }}
                                                    </span>
                                                </div>
                                                <span v-if="selected" class="absolute inset-y-0 left-0 flex items-center pl-3"
                                                    :class="{ 'text-white': active, 'text-blue-600': !active }">
                                                    <CheckIcon class="h-5 w-5" aria-hidden="true" />
                                                </span>
                                            </li>
                                        </ComboboxOption>
                                    </ComboboxOptions>
                                </TransitionRoot>
                            </div>
                        </Combobox>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-slate-200">Titre du ticket</label>
                            <input type="text" v-model="form.title" class="mt-2 block w-full rounded-xl border-0 py-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 dark:bg-slate-900 dark:ring-slate-700 dark:text-white" placeholder="Ex: Mon écran ne s'allume plus" />
                            <div v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-slate-200">Description détaillée</label>
                            <textarea v-model="form.description" rows="5" class="mt-2 block w-full rounded-xl border-0 py-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 dark:bg-slate-900 dark:ring-slate-700 dark:text-white" placeholder="Décrivez le problème avec le plus de détails possible..."></textarea>
                            <div v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-x-6">
                        <button type="button" class="text-sm font-semibold leading-6 text-slate-900 dark:text-white hover:underline" @click="$inertia.visit(route('dashboard'))">Annuler</button>
                        <button type="submit" :disabled="form.processing"
                            class="rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-all disabled:opacity-50">
                            {{ form.processing ? 'Envoi en cours...' : 'Créer le ticket' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>