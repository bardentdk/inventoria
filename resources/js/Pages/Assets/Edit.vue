<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { ExclamationTriangleIcon, TrashIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    asset: Object,
    categories: Array,
    structures: Array,
});

const form = useForm({
    name: props.asset.name,
    serial_number: props.asset.serial_number,
    inventory_code: props.asset.inventory_code,
    category_id: props.asset.category_id,
    status: props.asset.status,
    specs: props.asset.specs,
    // CORRECTION ICI : On utilise structure_id (singulier) et non un tableau
    structure_id: props.asset.structure_id, 
});

const submit = () => {
    form.put(route('assets.update', props.asset.id));
};

// --- LOGIQUE SUPPRESSION SÉCURISÉE ---
const showDeleteModal = ref(false);
const deleteConfirmationText = ref('');
const isDeleting = ref(false);

const confirmDelete = () => {
    if (deleteConfirmationText.value.toLowerCase() === 'supprimer') {
        isDeleting.value = true;
        router.delete(route('assets.destroy', props.asset.id));
    }
};
</script>

<template>
    <Head :title="`Modifier ${asset.name}`" />

    <AppLayout>
        <TransitionRoot as="template" :show="showDeleteModal">
            <Dialog as="div" class="relative z-50" @close="showDeleteModal = false">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" />
                </TransitionChild>

                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                            <DialogPanel class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6 border border-slate-200 dark:border-slate-700">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                        <ExclamationTriangleIcon class="h-6 w-6 text-red-600 dark:text-red-400" aria-hidden="true" />
                                    </div>
                                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                        <DialogTitle as="h3" class="text-base font-semibold leading-6 text-slate-900 dark:text-white">
                                            Supprimer ce matériel ?
                                        </DialogTitle>
                                        <div class="mt-2">
                                            <p class="text-sm text-slate-500">
                                                Attention : Cette action supprimera définitivement l'équipement <strong>{{ asset.name }}</strong> ainsi que tout son historique d'attribution.
                                            </p>
                                            <div class="mt-4">
                                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                                    Pour confirmer, tapez <span class="font-bold select-all">supprimer</span> :
                                                </label>
                                                <input 
                                                    type="text" 
                                                    v-model="deleteConfirmationText"
                                                    class="block w-full rounded-lg border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white focus:ring-red-500 focus:border-red-500 sm:text-sm p-2.5"
                                                    placeholder="supprimer"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                    <button 
                                        type="button" 
                                        class="inline-flex w-full justify-center rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto disabled:opacity-50 transition-all"
                                        :disabled="deleteConfirmationText.toLowerCase() !== 'supprimer' || isDeleting"
                                        @click="confirmDelete"
                                    >
                                        {{ isDeleting ? 'Suppression...' : 'Confirmer la suppression' }}
                                    </button>
                                    <button type="button" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white dark:bg-slate-700 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-slate-200 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 hover:bg-slate-50 dark:hover:bg-slate-600 sm:mt-0 sm:w-auto" @click="showDeleteModal = false">
                                        Annuler
                                    </button>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <div class="max-w-3xl mx-auto">
            <div class="mb-8 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Modifier un équipement</h2>
                
                <button v-if="$page.props.auth.user.role === 'admin'" @click="showDeleteModal = true" type="button" class="flex items-center gap-2 text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <TrashIcon class="w-5 h-5" />
                    Supprimer
                </button>
            </div>

            <div class="bg-white dark:bg-slate-800 shadow rounded-2xl border border-slate-200 dark:border-slate-700/50 p-8">
                <form @submit.prevent="submit" class="space-y-6">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Nom du matériel</label>
                            <input type="text" v-model="form.name" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5" />
                            <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Catégorie</label>
                            <select v-model="form.category_id" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5">
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Numéro de Série (S/N)</label>
                            <input type="text" v-model="form.serial_number" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Code Inventaire (Interne)</label>
                            <input type="text" v-model="form.inventory_code" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">État actuel</label>
                        <select v-model="form.status" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5">
                            <option value="available">Disponible (Stock)</option>
                            <option value="assigned">Assigné (En cours d'utilisation)</option>
                            <option value="repair">En réparation</option>
                            <option value="broken">Hors Service (HS)</option>
                        </select>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
                            Structure de rattachement
                        </label>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            
                            <div class="relative flex items-start">
                                <div class="flex h-6 items-center">
                                    <input 
                                        type="radio" 
                                        id="struct-none"
                                        :value="null" 
                                        v-model="form.structure_id"
                                        class="h-5 w-5 border-slate-300 text-blue-600 focus:ring-blue-600 dark:bg-slate-900 dark:border-slate-600"
                                    >
                                </div>
                                <div class="ml-3 text-sm leading-6 w-full">
                                    <label 
                                        for="struct-none" 
                                        class="font-medium text-slate-900 dark:text-white block p-3 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 transition-all duration-200 select-none shadow-sm"
                                        :class="{ 'border-slate-500 bg-slate-50 dark:bg-slate-800 ring-1 ring-slate-500': form.structure_id === null }"
                                    >
                                        Aucune (Non rattaché)
                                    </label>
                                </div>
                            </div>

                            <div v-for="struct in structures" :key="struct.id" class="relative flex items-start">
                                <div class="flex h-6 items-center">
                                    <input 
                                        type="radio" 
                                        :id="'struct-' + struct.id"
                                        :value="struct.id" 
                                        v-model="form.structure_id"
                                        class="h-5 w-5 border-slate-300 text-blue-600 focus:ring-blue-600 dark:bg-slate-900 dark:border-slate-600"
                                    >
                                </div>
                                
                                <div class="ml-3 text-sm leading-6 w-full">
                                    <label 
                                        :for="'struct-' + struct.id" 
                                        class="font-medium text-slate-900 dark:text-white block p-3 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-blue-400 dark:hover:border-blue-500 transition-all duration-200 select-none shadow-sm"
                                        :class="{ 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 ring-1 ring-blue-500': form.structure_id === struct.id }"
                                    >
                                        {{ struct.name }}
                                    </label>
                                </div>
                            </div>

                        </div>
                        
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            Sélectionnez la structure propriétaire de ce matériel.
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 dark:text-slate-200">Spécifications techniques</label>
                        <textarea v-model="form.specs" rows="3" class="mt-2 block w-full rounded-xl border-slate-300 dark:bg-slate-900 dark:border-slate-700 dark:text-white py-2.5"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                        <Link :href="route('assets.index')" class="text-slate-500 hover:text-slate-700 dark:text-slate-400">Annuler</Link>
                        <button type="submit" :disabled="form.processing" class="bg-blue-600 text-white px-6 py-2 rounded-xl hover:bg-blue-500 transition">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>