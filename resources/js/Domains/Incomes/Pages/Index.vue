<script setup>
import { ref, toRef } from 'vue';
import AuthenticatedLayout from '@/Shared/Layouts/AuthenticatedLayout.vue';
import Amount from '@/Shared/Components/Amount.vue';
import Modal from '@/Shared/Components/Modal.vue';
import Pagination from '@/Shared/Components/Pagination.vue';
import { Button } from '@/Shared/Components/ui/button';
import TextInput from '@/Shared/Components/TextInput.vue';
import InputLabel from '@/Shared/Components/InputLabel.vue';
import InputError from '@/Shared/Components/InputError.vue';
import { useMonthNavigation } from '@/Shared/Composables/useMonthNavigation';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    incomes: {
        type: Object,
        required: true,
    },
    month: {
        type: String,
        required: true,
    },
});

function currentQuery(overrides = {}) {
    return {
        month: props.month,
        per_page: props.incomes.meta.per_page !== 20 ? props.incomes.meta.per_page : undefined,
        ...overrides,
    };
}

function navigate(overrides = {}) {
    router.get(route('incomes.index', currentQuery(overrides)), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

const { monthLabel, shiftMonth } = useMonthNavigation(toRef(props, 'month'), 'incomes.index', currentQuery);

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
                    <h2 class="text-xl font-heading font-extrabold leading-tight text-foreground">
                        Entrées d'argent
                    </h2>
                    <p class="mt-0.5 text-sm capitalize text-muted-foreground">{{ monthLabel }}</p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1 rounded-lg border border-border px-2 py-1">
                        <button
                            type="button"
                            class="rounded-lg px-2 py-1 text-muted-foreground hover:bg-accent hover:text-foreground"
                            @click="shiftMonth(-1)"
                        >
                            ‹
                        </button>
                        <span class="px-2 text-sm font-semibold capitalize text-foreground">
                            {{ monthLabel }}
                        </span>
                        <button
                            type="button"
                            class="rounded-lg px-2 py-1 text-muted-foreground hover:bg-accent hover:text-foreground"
                            @click="shiftMonth(1)"
                        >
                            ›
                        </button>
                    </div>

                    <Link :href="route('incomes.create', { month })">
                        <Button variant="default" type="button">+ Ajouter une entrée</Button>
                    </Link>
                </div>
            </div>
        </template>

        <div class="p-6">
            <div class="overflow-hidden rounded-xl bg-card shadow-soft">
                <div
                    class="grid grid-cols-[100px_1fr_140px_170px] gap-2 border-b border-border px-5 py-3 text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                >
                    <span>Date</span>
                    <span>Description</span>
                    <span class="text-right">Montant</span>
                    <span></span>
                </div>

                <p v-if="incomes.data.length === 0" class="p-5 text-sm text-muted-foreground">
                    Aucune entrée pour ce mois.
                </p>

                <div
                    v-for="income in incomes.data"
                    :key="income.id"
                    class="grid grid-cols-[100px_1fr_140px_170px] items-center gap-2 border-b border-border px-5 py-3 last:border-b-0"
                >
                    <span class="text-sm text-muted-foreground">
                        {{ new Date(income.date).toLocaleDateString('fr-FR') }}
                    </span>
                    <span class="truncate text-sm font-medium text-foreground">
                        {{ income.description ?? '—' }}
                    </span>
                    <strong class="whitespace-nowrap text-right text-sm text-income">
                        <Amount :value="income.amount" prefix="+" />
                    </strong>
                    <span class="flex justify-end gap-3 whitespace-nowrap text-sm">
                        <button
                            type="button"
                            class="text-muted-foreground hover:text-foreground"
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

                <Pagination
                    :meta="incomes.meta"
                    @update:page="(page) => navigate({ page })"
                    @update:per-page="(perPage) => navigate({ per_page: perPage })"
                />
            </div>
        </div>

        <Modal :show="editingIncome !== null" @close="cancelEdit">
            <div class="p-6" v-if="editingIncome">
                <h2 class="text-lg font-semibold text-foreground">Modifier l'entrée</h2>

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
                        <Button variant="secondary" type="button" @click="cancelEdit">Annuler</Button>
                        <Button variant="default" :disabled="editForm.processing">Enregistrer</Button>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="incomePendingDeletion !== null" @close="cancelDestroy">
            <div class="p-6" v-if="incomePendingDeletion">
                <h2 class="text-lg font-semibold text-foreground">Supprimer cette entrée ?</h2>
                <p class="mt-1 text-sm text-muted-foreground">Cette action est irréversible.</p>

                <div class="mt-6 flex justify-end gap-2">
                    <Button variant="secondary" @click="cancelDestroy">Annuler</Button>
                    <Button variant="destructive" @click="destroy">Supprimer</Button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
