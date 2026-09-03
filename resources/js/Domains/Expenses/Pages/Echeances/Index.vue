<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Shared/Layouts/AuthenticatedLayout.vue';
import Amount from '@/Shared/Components/Amount.vue';
import CategoryIcon from '@/Shared/Components/CategoryIcon.vue';
import Modal from '@/Shared/Components/Modal.vue';
import { Button } from '@/Shared/Components/ui/button';
import TextInput from '@/Shared/Components/TextInput.vue';
import InputLabel from '@/Shared/Components/InputLabel.vue';
import InputError from '@/Shared/Components/InputError.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

defineProps({
    echeances: {
        type: Array,
        required: true,
    },
});

const frequencyLabels = {
    mensuelle: 'Mensuelle',
    trimestrielle: 'Trimestrielle',
    annuelle: 'Annuelle',
};

const statusLabels = {
    en_attente: 'À venir',
    generee: 'Générée',
    annulee: 'Annulée',
};

const editingOccurrence = ref(null);

const editForm = useForm({
    date: '',
    amount: '',
});

function startEdit(occurrence) {
    editingOccurrence.value = occurrence;
    editForm.clearErrors();
    editForm.date = occurrence.date;
    editForm.amount = occurrence.amount;
}

function cancelEdit() {
    editingOccurrence.value = null;
}

function submitEdit() {
    editForm.transform((data) => ({
        ...data,
        amount: Math.round(Number(data.amount)),
    })).patch(route('echeance-occurrences.update', editingOccurrence.value.id), {
        onSuccess: () => cancelEdit(),
    });
}

const echeancePendingCancellation = ref(null);

function confirmCancel(echeance) {
    echeancePendingCancellation.value = echeance;
}

function cancelCancellation() {
    echeancePendingCancellation.value = null;
}

function cancelEcheance() {
    router.patch(route('echeances.cancel', echeancePendingCancellation.value.id), {}, {
        onFinish: () => {
            echeancePendingCancellation.value = null;
        },
    });
}
</script>

<template>
    <Head title="Échéances" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-heading font-extrabold leading-tight text-foreground">Échéances</h2>
                    <p class="mt-0.5 text-sm text-muted-foreground">
                        Dépenses programmées à l'avance, générées automatiquement à leur date.
                    </p>
                </div>
                <Link :href="route('echeances.create')">
                    <Button variant="default">Nouvel échéancier</Button>
                </Link>
            </div>
        </template>

        <div class="space-y-4 p-6">
            <div
                v-for="echeance in echeances"
                :key="echeance.id"
                class="overflow-hidden rounded-xl bg-card shadow-soft"
            >
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-border px-5 py-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <span
                            v-if="echeance.category"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-base"
                            :style="{
                                backgroundColor: (echeance.category.resolved_color ?? '#676E80') + '22',
                                color: echeance.category.resolved_color ?? '#676E80',
                            }"
                        >
                            <CategoryIcon :icon="echeance.category.resolved_icon" class="h-4 w-4" />
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-foreground">{{ echeance.description }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ echeance.category?.name }} · {{ frequencyLabels[echeance.frequency] }} ·
                                {{ echeance.occurrences_total
                                    ? `${echeance.occurrences_generated}/${echeance.occurrences_total}`
                                    : `${echeance.occurrences_generated} générée(s), illimité` }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <span
                            class="shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold"
                            :class="echeance.status === 'annulee'
                                ? 'bg-muted text-muted-foreground'
                                : 'bg-emerald-500/10 text-emerald-600'"
                        >
                            {{ echeance.status === 'annulee' ? 'Annulé' : 'Actif' }}
                        </span>
                        <button
                            v-if="echeance.status === 'active'"
                            type="button"
                            class="text-sm text-expense hover:text-expense/80"
                            @click="confirmCancel(echeance)"
                        >
                            Annuler l'échéancier
                        </button>
                    </div>
                </div>

                <div
                    v-for="occurrence in echeance.occurrences"
                    :key="occurrence.id"
                    class="flex flex-wrap items-center justify-between gap-3 border-b border-border px-5 py-3 last:border-b-0"
                >
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-muted-foreground">
                            {{ new Date(occurrence.date).toLocaleDateString('fr-FR') }}
                        </span>
                        <span
                            class="shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold"
                            :class="{
                                'bg-amber-500/10 text-amber-600': occurrence.status === 'en_attente',
                                'bg-emerald-500/10 text-emerald-600': occurrence.status === 'generee',
                                'bg-muted text-muted-foreground': occurrence.status === 'annulee',
                            }"
                        >
                            {{ statusLabels[occurrence.status] }}
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        <strong class="whitespace-nowrap text-sm text-expense">
                            <Amount :value="occurrence.amount" prefix="−" />
                        </strong>
                        <button
                            v-if="occurrence.status === 'en_attente'"
                            type="button"
                            class="text-sm text-primary hover:text-primary/80"
                            @click="startEdit(occurrence)"
                        >
                            Modifier
                        </button>
                    </div>
                </div>

                <p v-if="echeance.occurrences.length === 0" class="p-5 text-sm text-muted-foreground">
                    Aucune occurrence.
                </p>
            </div>

            <p v-if="echeances.length === 0" class="rounded-xl bg-card p-6 text-sm text-muted-foreground shadow-soft">
                Aucun échéancier pour le moment.
            </p>
        </div>

        <Modal :show="editingOccurrence !== null" @close="cancelEdit">
            <div class="p-6" v-if="editingOccurrence">
                <h2 class="text-lg font-semibold text-foreground">Modifier cette échéance</h2>

                <form @submit.prevent="submitEdit" class="mt-4 space-y-4">
                    <div>
                        <InputLabel for="edit-date" value="Date" />
                        <TextInput
                            id="edit-date"
                            type="date"
                            class="mt-1 block w-full"
                            v-model="editForm.date"
                            required
                        />
                        <InputError class="mt-2" :message="editForm.errors.date" />
                    </div>

                    <div>
                        <InputLabel for="edit-amount" value="Montant (FCFP)" />
                        <TextInput
                            id="edit-amount"
                            type="number"
                            min="1"
                            class="mt-1 block w-full"
                            v-model="editForm.amount"
                            required
                        />
                        <InputError class="mt-2" :message="editForm.errors.amount" />
                    </div>

                    <div class="flex justify-end gap-2">
                        <Button variant="secondary" type="button" @click="cancelEdit">Annuler</Button>
                        <Button variant="default" :disabled="editForm.processing">Enregistrer</Button>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="echeancePendingCancellation !== null" @close="cancelCancellation">
            <div class="p-6" v-if="echeancePendingCancellation">
                <h2 class="text-lg font-semibold text-foreground">Annuler cet échéancier ?</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Les occurrences déjà générées restent inchangées. Les occurrences à venir seront annulées.
                </p>

                <div class="mt-6 flex justify-end gap-2">
                    <Button variant="secondary" @click="cancelCancellation">Retour</Button>
                    <Button variant="destructive" @click="cancelEcheance">Annuler l'échéancier</Button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
