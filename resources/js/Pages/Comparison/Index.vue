<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { amountLabel } from '@/utils/currency';
import { formatMonth } from '@/utils/month';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    months: {
        type: Number,
        required: true,
    },
    rows: {
        type: Array,
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
    balanceTotal: {
        type: Number,
        required: true,
    },
    averageIncome: {
        type: Number,
        required: true,
    },
    averageExpense: {
        type: Number,
        required: true,
    },
    bestBalanceMonth: {
        type: Object,
        default: null,
    },
});

const rangeLabel = computed(() => {
    if (props.rows.length === 0) {
        return '';
    }

    const format = { month: 'short', year: 'numeric' };
    const first = formatMonth(props.rows[0].month, format);
    const last = formatMonth(props.rows[props.rows.length - 1].month, format);

    return `${first} — ${last}`;
});

const conservedPercentage = computed(() => {
    if (props.incomeTotal === 0) {
        return 0;
    }

    return Math.round((props.balanceTotal / props.incomeTotal) * 1000) / 10;
});

const maxAmount = computed(
    () => Math.max(1, ...props.rows.map((row) => Math.max(row.income, row.expense))),
);

const maxBalance = computed(
    () => Math.max(1, ...props.rows.map((row) => Math.abs(row.balance))),
);

function barHeight(amount) {
    return `${Math.round((amount / maxAmount.value) * 100)}%`;
}

function balanceHeight(balance) {
    return `${Math.max(4, Math.round((Math.abs(balance) / maxBalance.value) * 100))}%`;
}

function changeMonths(event) {
    router.get(route('comparison', { months: Number(event.target.value) }));
}
</script>

<template>
    <Head title="Comparaison mensuelle" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-extrabold leading-tight text-ink">
                        Comparaison mensuelle
                    </h2>
                    <p class="mt-0.5 text-sm capitalize text-muted">{{ rangeLabel }}</p>
                </div>

                <select
                    class="rounded-control border border-line bg-white px-3 py-1.5 text-sm font-semibold text-ink shadow-pill"
                    :value="months"
                    @change="changeMonths"
                >
                    <option :value="3">3 derniers mois</option>
                    <option :value="6">6 derniers mois</option>
                    <option :value="12">12 derniers mois</option>
                    <option :value="24">24 derniers mois</option>
                </select>
            </div>
        </template>

        <div class="space-y-6 p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="flex items-center gap-4 rounded-card bg-white p-5 shadow-soft">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-control bg-income/10 text-xl text-income"
                    >
                        ↗
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">
                            Revenus cumulés
                        </p>
                        <p class="truncate text-xl font-extrabold text-income">
                            {{ amountLabel(incomeTotal) }}
                        </p>
                        <p class="text-xs text-muted">Sur {{ months }} mois</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 rounded-card bg-white p-5 shadow-soft">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-control bg-expense/10 text-xl text-expense"
                    >
                        ↓
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">
                            Dépenses cumulées
                        </p>
                        <p class="truncate text-xl font-extrabold text-expense">
                            {{ amountLabel(expenseTotal) }}
                        </p>
                        <p class="text-xs text-muted">Sur {{ months }} mois</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 rounded-card bg-white p-5 shadow-soft">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-control bg-nav/10 text-xl text-nav"
                    >
                        ▰
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">
                            Solde cumulé
                        </p>
                        <p class="truncate text-xl font-extrabold text-nav">
                            {{ amountLabel(balanceTotal) }}
                        </p>
                        <p class="text-xs text-muted">{{ conservedPercentage }} % conservé</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <div class="rounded-card bg-white p-5 shadow-soft">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-ink">Revenus et dépenses par mois</h3>
                            <p class="text-xs text-muted">
                                <span class="text-income">■ Revenus</span>
                                <span class="ml-3 text-expense">■ Dépenses</span>
                            </p>
                        </div>

                        <div class="mt-4 flex h-48 items-end gap-3 overflow-x-auto">
                            <div
                                v-for="row in rows"
                                :key="row.month"
                                class="flex min-w-[2.5rem] flex-1 flex-col items-center gap-1"
                            >
                                <div class="flex h-40 w-full items-end justify-center gap-0.5">
                                    <div
                                        class="w-1/2 rounded-t bg-income"
                                        :style="{ height: barHeight(row.income) }"
                                    ></div>
                                    <div
                                        class="w-1/2 rounded-t bg-expense"
                                        :style="{ height: barHeight(row.expense) }"
                                    ></div>
                                </div>
                                <small class="text-xs capitalize text-muted">
                                    {{ formatMonth(row.month, { month: 'short' }) }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-card bg-white p-5 shadow-soft">
                        <h3 class="font-semibold text-ink">Solde mensuel</h3>

                        <div class="mt-4 flex h-32 items-end gap-3 overflow-x-auto">
                            <div
                                v-for="row in rows"
                                :key="row.month"
                                class="flex min-w-[2.5rem] flex-1 flex-col items-center gap-1"
                            >
                                <div class="flex h-24 w-full items-end justify-center">
                                    <div
                                        class="w-2/3 rounded-t"
                                        :class="row.balance >= 0 ? 'bg-nav' : 'bg-expense'"
                                        :style="{ height: balanceHeight(row.balance) }"
                                    ></div>
                                </div>
                                <small class="text-xs capitalize text-muted">
                                    {{ formatMonth(row.month, { month: 'short' }) }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="h-fit rounded-card bg-white p-6 shadow-soft">
                    <h3 class="mb-3 text-sm font-semibold text-ink">Points clés</h3>

                    <div v-if="bestBalanceMonth" class="rounded-control bg-app p-4">
                        <p class="text-xs text-muted">Meilleur solde</p>
                        <strong class="text-sm text-ink">
                            {{ formatMonth(bestBalanceMonth.month) }} · {{ amountLabel(bestBalanceMonth.balance) }}
                        </strong>
                    </div>

                    <div class="mt-3 rounded-control bg-app p-4">
                        <p class="text-xs text-muted">Dépenses moyennes</p>
                        <strong class="text-sm text-ink">{{ amountLabel(averageExpense) }}</strong>
                    </div>

                    <div class="mt-3 rounded-control bg-app p-4">
                        <p class="text-xs text-muted">Revenus moyens</p>
                        <strong class="text-sm text-ink">{{ amountLabel(averageIncome) }}</strong>
                    </div>

                    <h3 class="mb-2 mt-8 text-sm font-semibold text-ink">Lecture</h3>
                    <p class="text-xs text-muted">
                        Comparaison fondée uniquement sur les entrées et dépenses réelles de chaque mois.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
