<script setup>
import { ref, toRef } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Amount from '@/Components/Amount.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { useMonthNavigation } from '@/Composables/useMonthNavigation';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    incomes: {
        type: Array,
        required: true,
    },
    month: {
        type: String,
        required: true,
    },
});

const { monthLabel, shiftMonth } = useMonthNavigation(toRef(props, 'month'), 'incomes.index');

const editingIncome = ref(null);

const editForm = useForm({
    amount: '',
    date: '',
    description: '',
});

function startEdit(income) {
    editingIncome.value = income;
    editForm.clearErrors();
    editForm.amount = income.amount;
    editForm.date = income.date;
    editForm.description = income.description ?? '';
}

function cancelEdit() {
    editingIncome.value = null;
}

function submitEdit() {
    editForm.transform((data) => ({
        ...data,
        amount: Math.round(Number(data.amount)),
    })).put(route('incomes.update', editingIncome.value.id), {
        onSuccess: () => cancelEdit(),
    });
}

const incomePendingDeletion = ref(null);

function confirmDestroy(income) {
    incomePendingDeletion.value = income;
}

function cancelDestroy() {
    incomePendingDeletion.value = null;
}

function destroy() {
    router.delete(route('incomes.destroy', incomePendingDeletion.value.id), {
        onFinish: () => {
            incomePendingDeletion.value = null;
        },
    });
}
</script>

<template>
    <Head title="Entrées d'argent" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-extrabold leading-tight text-ink">
                        Entrées d'argent
                    </h2>
                    <p class="mt-0.5 text-sm capitalize text-muted">{{ monthLabel }}</p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1 rounded-control border border-line px-2 py-1">
                        <button
                            type="button"
                            class="rounded-control px-2 py-1 text-muted hover:bg-app hover:text-ink"
                            @click="shiftMonth(-1)"
                        >
                            ‹
                        </button>
                        <span class="px-2 text-sm font-semibold capitalize text-ink">
                            {{ monthLabel }}
                        </span>
                        <button
                            type="button"
                            class="rounded-control px-2 py-1 text-muted hover:bg-app hover:text-ink"
                            @click="shiftMonth(1)"
                        >
                            ›
                        </button>
                    </div>

                    <Link :href="route('incomes.create', { month })">
                        <PrimaryButton type="button">+ Ajouter une entrée</PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="p-6">
            <div class="overflow-hidden rounded-card bg-surface shadow-soft">
                <div
                    class="grid grid-cols-[100px_1fr_140px_170px] gap-2 border-b border-line px-5 py-3 text-xs font-semibold uppercase tracking-wide text-muted"
                >
                    <span>Date</span>
                    <span>Description</span>
                    <span class="text-right">Montant</span>
                    <span></span>
                </div>

                <p v-if="incomes.length === 0" class="p-5 text-sm text-muted">
                    Aucune entrée pour ce mois.
                </p>

                <div
                    v-for="income in incomes"
                    :key="income.id"
                    class="grid grid-cols-[100px_1fr_140px_170px] items-center gap-2 border-b border-line px-5 py-3 last:border-b-0"
                >
                    <span class="text-sm text-muted">
                        {{ new Date(income.date).toLocaleDateString('fr-FR') }}
                    </span>
                    <span class="truncate text-sm font-medium text-ink">
                        {{ income.description ?? '—' }}
                    </span>
                    <strong class="whitespace-nowrap text-right text-sm text-income">
                        <Amount :value="income.amount" prefix="+" />
                    </strong>
                    <span class="flex justify-end gap-3 whitespace-nowrap text-sm">
                        <button
                            type="button"
                            class="text-muted hover:text-ink"
                            @click="startEdit(income)"
                        >
                            Modifier
                        </button>
                        <button
                            type="button"
                            class="text-expense hover:text-expense/80"
                            @click="confirmDestroy(income)"
                        >
                            Supprimer
                        </button>
                    </span>
                </div>
            </div>
        </div>

        <Modal :show="editingIncome !== null" @close="cancelEdit">
            <div class="p-6" v-if="editingIncome">
                <h2 class="text-lg font-semibold text-ink">Modifier l'entrée</h2>

                <form @submit.prevent="submitEdit" class="mt-4 space-y-4">
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
                        <InputLabel for="edit-description" value="Description (optionnelle)" />
                        <TextInput
                            id="edit-description"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="editForm.description"
                        />
                        <InputError class="mt-2" :message="editForm.errors.description" />
                    </div>

                    <div class="flex justify-end gap-2">
                        <SecondaryButton type="button" @click="cancelEdit">Annuler</SecondaryButton>
                        <PrimaryButton :disabled="editForm.processing">Enregistrer</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="incomePendingDeletion !== null" @close="cancelDestroy">
            <div class="p-6" v-if="incomePendingDeletion">
                <h2 class="text-lg font-semibold text-ink">Supprimer cette entrée ?</h2>
                <p class="mt-1 text-sm text-muted">Cette action est irréversible.</p>

                <div class="mt-6 flex justify-end gap-2">
                    <SecondaryButton @click="cancelDestroy">Annuler</SecondaryButton>
                    <DangerButton @click="destroy">Supprimer</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
