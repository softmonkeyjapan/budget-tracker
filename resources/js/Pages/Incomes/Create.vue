<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Amount from '@/Components/Amount.vue';
import { Button } from '@/Components/ui/button';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    month: {
        type: String,
        required: true,
    },
    recentIncomes: {
        type: Array,
        required: true,
    },
});

function defaultDate() {
    const today = new Date().toISOString().slice(0, 10);

    return today.slice(0, 7) === props.month ? today : `${props.month}-01`;
}

const form = useForm({
    amount: '',
    date: defaultDate(),
    description: '',
});

const projectedTotal = computed(
    () => props.recentIncomes.reduce((sum, income) => sum + income.amount, 0) + (Number(form.amount) || 0),
);

function submit() {
    form.transform((data) => ({
        ...data,
        amount: Math.round(Number(data.amount)),
    })).post(route('incomes.store'));
}
</script>

<template>
    <Head title="Nouvelle entrée d'argent" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-extrabold leading-tight text-ink">
                Nouvelle entrée d'argent
            </h2>
            <p class="mt-0.5 text-sm text-muted">Aucune catégorie nécessaire</p>
        </template>

        <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
            <div class="rounded-card bg-surface p-6 shadow-soft">
                <div class="flex items-center gap-4 rounded-control bg-income/10 p-4">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-control bg-income/20 text-2xl text-income"
                    >
                        ↗
                    </span>
                    <div>
                        <h3 class="font-semibold text-ink">Entrée réelle</h3>
                        <p class="text-sm text-muted">
                            Salaire, freelance, remboursement ou autre revenu
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="mt-6 space-y-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="amount" value="Montant (FCFP)" />
                            <TextInput
                                id="amount"
                                type="number"
                                min="1"
                                class="mt-1 block w-full"
                                v-model="form.amount"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.amount" />
                        </div>

                        <div>
                            <InputLabel for="date" value="Date" />
                            <TextInput
                                id="date"
                                type="date"
                                class="mt-1 block w-full"
                                v-model="form.date"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.date" />
                        </div>

                        <div class="sm:col-span-2">
                            <InputLabel for="description" value="Description (optionnelle)" />
                            <TextInput
                                id="description"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.description"
                            />
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>
                    </div>

                    <div class="flex justify-end gap-2">
                        <Link
                            :href="route('incomes.index', { month })"
                            class="rounded-control border border-line bg-surface px-5 py-2.5 text-sm font-semibold text-ink shadow-pill hover:bg-app"
                        >
                            Annuler
                        </Link>
                        <Button variant="primary" :disabled="form.processing">
                            Enregistrer l'entrée
                        </Button>
                    </div>
                </form>
            </div>

            <div class="rounded-card bg-surface p-6 shadow-soft">
                <h3 class="mb-3 text-sm font-semibold text-ink">Entrées récentes</h3>
                <p v-if="recentIncomes.length === 0" class="text-sm text-muted">
                    Aucune entrée ce mois-ci pour l'instant.
                </p>
                <div
                    v-for="income in recentIncomes"
                    :key="income.id"
                    class="flex items-center gap-3 border-b border-line py-3 last:border-b-0"
                >
                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-control bg-income/20 text-lg text-income"
                    >
                        ↗
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-ink">
                            {{ income.description ?? 'Entrée sans description' }}
                        </p>
                        <p class="text-xs text-muted">
                            {{ new Date(income.date).toLocaleDateString('fr-FR') }}
                        </p>
                    </div>
                    <strong class="whitespace-nowrap text-sm text-income">
                        <Amount :value="income.amount" prefix="+" />
                    </strong>
                </div>
            </div>
            </div>

            <div class="h-fit rounded-card bg-surface p-6 shadow-soft">
                <h3 class="mb-3 text-sm font-semibold text-ink">Règle métier</h3>
                <p class="rounded-control bg-peach/20 p-3 text-xs text-ink">
                    <strong>Pas de catégorie.</strong> Une entrée d'argent contient uniquement
                    un montant, une date et une description facultative.
                </p>

                <h3 class="mb-3 mt-6 text-sm font-semibold text-ink">Impact mensuel</h3>
                <div class="rounded-control bg-surface-secondary p-3">
                    <p class="text-xs text-muted">Revenus après saisie</p>
                    <p class="text-lg font-bold text-income">
                        <Amount :value="projectedTotal" />
                    </p>
                </div>
                <p class="mt-3 text-xs text-muted">
                    Les pourcentages d'utilisation seront recalculés sur ce nouveau total.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
