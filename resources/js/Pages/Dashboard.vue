<script setup>
import { computed, toRef } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Amount from '@/Components/Amount.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useMonthNavigation } from '@/Composables/useMonthNavigation';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    month: {
        type: String,
        required: true,
    },
    incomeTotal: {
        type: Number,
        required: true,
    },
    expenseTotal: {
        type: Number,
        required: true,
    },
    balance: {
        type: Number,
        required: true,
    },
    incomeCount: {
        type: Number,
        required: true,
    },
    expensePercentage: {
        type: Number,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    unspentPercentage: {
        type: Number,
        required: true,
    },
    lastExpenses: {
        type: Array,
        required: true,
    },
    recentIncomes: {
        type: Array,
        required: true,
    },
});

const { monthLabel, shiftMonth } = useMonthNavigation(toRef(props, 'month'), 'dashboard');

const pieGradient = computed(() => {
    const usedCap = Math.max(0, Math.min(100, 100 - props.unspentPercentage));
    let cumulative = 0;
    const stops = [];

    for (const category of props.categories) {
        if (category.percentage <= 0 || cumulative >= usedCap) {
            continue;
        }

        const start = cumulative;
        cumulative = Math.min(usedCap, cumulative + category.percentage);
        stops.push(`${category.color ?? '#8A90A2'} ${start}% ${cumulative}%`);
    }

    stops.push(`#EEF0F5 ${cumulative}% 100%`);

    return `conic-gradient(${stops.join(', ')})`;
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-extrabold leading-tight text-ink">Dashboard</h2>
                    <p class="mt-0.5 text-sm capitalize text-muted">Vue mensuelle</p>
                </div>

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
            </div>
        </template>

        <div class="space-y-6 p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="flex items-center gap-4 rounded-card bg-surface p-5 shadow-soft">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-control bg-income/10 text-xl text-income"
                    >
                        ↗
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">
                            Entrées d'argent
                        </p>
                        <p class="truncate text-xl font-extrabold text-income">
                            <Amount :value="incomeTotal" />
                        </p>
                        <p class="text-xs text-muted">{{ incomeCount }} entrée(s) ce mois</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 rounded-card bg-surface p-5 shadow-soft">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-control bg-expense/10 text-xl text-expense"
                    >
                        ↓
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">
                            Dépenses
                        </p>
                        <p class="truncate text-xl font-extrabold text-expense">
                            <Amount :value="expenseTotal" />
                        </p>
                        <p class="text-xs text-muted">{{ expensePercentage }} % des revenus</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 rounded-card bg-surface p-5 shadow-soft">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-control bg-nav/10 text-xl text-nav"
                    >
                        ▰
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">
                            Solde
                        </p>
                        <p class="truncate text-xl font-extrabold text-nav">
                            <Amount :value="balance" />
                        </p>
                        <p class="text-xs text-muted">{{ unspentPercentage }} % disponible</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="flex flex-col justify-center rounded-card bg-surface p-6 shadow-soft lg:col-span-2">
                    <div class="grid items-center gap-6 xl:grid-cols-[.8fr_1.3fr]">
                        <div>
                            <h3 class="font-semibold text-ink">Répartition des revenus</h3>
                            <p class="mt-1 text-sm text-muted">
                                100 % = entrées d'argent du mois
                            </p>

                            <div
                                class="relative mx-auto mt-4 h-40 w-40 rounded-full"
                                :style="{ backgroundImage: pieGradient }"
                            >
                                <div
                                    class="absolute inset-8 grid place-content-center rounded-full bg-surface text-center"
                                >
                                    <strong class="text-lg text-ink">
                                        <Amount :value="incomeTotal" raw />
                                    </strong>
                                    <span class="text-xs text-muted">revenus</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="font-semibold text-ink">Utilisation par catégorie</h3>
                            <div class="mt-3 space-y-3">
                                <div
                                    v-for="category in categories"
                                    :key="category.id"
                                    class="flex items-center gap-3"
                                >
                                    <span
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-control text-base"
                                        :style="{
                                            backgroundColor: (category.color ?? '#8A90A2') + '22',
                                            color: category.color ?? '#8A90A2',
                                        }"
                                    >
                                        {{ category.icon ?? '📁' }}
                                    </span>
                                    <p class="min-w-0 flex-1 truncate font-medium text-ink">
                                        {{ category.name }}
                                    </p>
                                    <strong class="whitespace-nowrap text-nav">
                                        {{ category.percentage }} %
                                    </strong>
                                    <span class="whitespace-nowrap text-sm text-muted">
                                        <Amount :value="category.amount" />
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="h-fit rounded-card bg-surface p-6 shadow-soft">
                    <h3 class="mb-3 text-sm font-semibold text-ink">Entrées du mois</h3>

                    <div class="mb-4 space-y-3">
                        <div
                            v-for="income in recentIncomes"
                            :key="income.id"
                            class="rounded-control bg-surface-secondary p-4"
                        >
                            <p class="text-xs text-muted">
                                {{ new Date(income.date).toLocaleDateString('fr-FR', { day: '2-digit', month: 'long' }) }}
                                <template v-if="income.description"> · {{ income.description }}</template>
                            </p>
                            <strong class="text-sm text-ink"><Amount :value="income.amount" /></strong>
                        </div>
                    </div>

                    <Link :href="route('incomes.create', { month })">
                        <PrimaryButton type="button" class="w-full justify-center">
                            + Ajouter une entrée
                        </PrimaryButton>
                    </Link>

                    <h3 class="mb-3 mt-6 text-sm font-semibold text-ink">Actions rapides</h3>
                    <Link :href="route('expenses.create', { month })">
                        <SecondaryButton type="button" class="w-full justify-center">
                            + Nouvelle dépense
                        </SecondaryButton>
                    </Link>
                </div>
            </div>

            <div class="overflow-hidden rounded-card bg-surface shadow-soft">
                <div class="flex items-center justify-between border-b border-line px-5 py-4">
                    <h3 class="font-semibold text-ink">5 dernières dépenses</h3>
                    <Link
                        :href="route('expenses.index', { month })"
                        class="rounded-control border border-line bg-surface px-4 py-1.5 text-sm font-semibold text-ink shadow-pill hover:bg-app"
                    >
                        Tout voir
                    </Link>
                </div>

                <p v-if="lastExpenses.length === 0" class="p-5 text-sm text-muted">
                    Aucune dépense pour ce mois.
                </p>

                <div
                    v-for="expense in lastExpenses"
                    :key="expense.id"
                    class="grid grid-cols-[100px_1fr_1fr_140px] items-center gap-2 border-b border-line px-5 py-3 last:border-b-0"
                >
                    <span class="text-sm text-muted">
                        {{ new Date(expense.date).toLocaleDateString('fr-FR') }}
                    </span>
                    <span class="flex min-w-0 items-center gap-2">
                        <span
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-control text-sm"
                            :style="{
                                backgroundColor: (expense.category.resolved_color ?? '#8A90A2') + '22',
                                color: expense.category.resolved_color ?? '#8A90A2',
                            }"
                        >
                            {{ expense.category.resolved_icon ?? '📁' }}
                        </span>
                        <span class="truncate text-sm font-medium text-ink">
                            {{ expense.category.name }}
                        </span>
                    </span>
                    <span class="truncate text-sm text-muted">
                        {{ expense.description ?? '—' }}
                    </span>
                    <strong class="whitespace-nowrap text-right text-sm text-expense">
                        <Amount :value="expense.amount" prefix="−" />
                    </strong>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
