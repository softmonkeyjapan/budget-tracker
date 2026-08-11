<script setup>
import { computed } from 'vue';
import { GroupedBar, StackedBar } from '@unovis/ts';
import { VisAxis, VisGroupedBar, VisStackedBar, VisTooltip, VisXYContainer } from '@unovis/vue';
import AuthenticatedLayout from '@/Shared/Layouts/AuthenticatedLayout.vue';
import Amount from '@/Shared/Components/Amount.vue';
import { ChartContainer } from '@/Shared/Components/ui/chart';
import { usePrivacy } from '@/Shared/Composables/usePrivacy';
import { useTheme } from '@/Shared/Composables/useTheme';
import { amountLabel } from '@/Shared/utils/currency';
import { formatMonth } from '@/Shared/utils/month';
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

const { theme } = useTheme();
const { hidden } = usePrivacy();

const incomeColor = computed(() => (theme.value === 'dark' ? '#46D6A4' : '#23C48E'));
const expenseColor = computed(() => (theme.value === 'dark' ? '#FF747A' : '#FF5B62'));
const primaryColor = computed(() => (theme.value === 'dark' ? '#62A4FF' : '#2F80ED'));

const monthTickFormat = (i) => (props.rows[i] ? formatMonth(props.rows[i].month, { month: 'short' }) : '');

const incomeExpenseX = (_d, i) => i;
const incomeExpenseY = [(d) => d.income, (d) => d.expense];
const incomeExpenseColor = (_d, i) => [incomeColor.value, expenseColor.value][i];

const incomeExpenseTriggers = computed(() => ({
    [GroupedBar.selectors.bar]: (d) => {
        const item = d.data?.datum ?? d.datum ?? d.data ?? d;
        const seriesIdx = d.seriesIndex ?? d.stackIndex ?? 0;
        const seriesLabel = seriesIdx === 1 ? 'Dépenses' : 'Revenus';
        const amount = hidden.value ? '•••••' : amountLabel(seriesIdx === 1 ? item.expense : item.income);
        return `<div class="rounded-lg px-2 py-1 text-xs text-white shadow-soft" style="background-color: #172033">${formatMonth(item.month, { month: 'long', year: 'numeric' })} · ${seriesLabel} · ${amount}</div>`;
    },
}));

const balanceX = (_d, i) => i;
const balanceY = (d) => d.balance;
const balanceColor = (d) => (d.balance >= 0 ? primaryColor.value : expenseColor.value);

const balanceTriggers = computed(() => ({
    [StackedBar.selectors.bar]: (d) => {
        const item = d.datum ?? d;
        const amount = hidden.value ? '•••••' : amountLabel(item.balance);
        return `<div class="rounded-lg px-2 py-1 text-xs text-white shadow-soft" style="background-color: #172033">${formatMonth(item.month, { month: 'long', year: 'numeric' })} · ${amount}</div>`;
    },
}));

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
                    <h2 class="text-xl font-heading font-extrabold leading-tight text-foreground">
                        Comparaison mensuelle
                    </h2>
                    <p class="mt-0.5 text-sm capitalize text-muted-foreground">{{ rangeLabel }}</p>
                </div>

                <select
                    class="rounded-lg border border-border bg-card px-3 py-1.5 text-sm font-semibold text-foreground shadow-pill"
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
                <div class="flex items-center gap-4 rounded-xl bg-card p-5 shadow-soft">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-income/10 text-xl text-income"
                    >
                        ↗
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            Revenus cumulés
                        </p>
                        <p class="truncate text-xl font-extrabold text-income">
                            <Amount :value="incomeTotal" />
                        </p>
                        <p class="text-xs text-muted-foreground">Sur {{ months }} mois</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 rounded-xl bg-card p-5 shadow-soft">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-expense/10 text-xl text-expense"
                    >
                        ↓
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            Dépenses cumulées
                        </p>
                        <p class="truncate text-xl font-extrabold text-expense">
                            <Amount :value="expenseTotal" />
                        </p>
                        <p class="text-xs text-muted-foreground">Sur {{ months }} mois</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 rounded-xl bg-card p-5 shadow-soft">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-xl text-primary"
                    >
                        ▰
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            Solde cumulé
                        </p>
                        <p class="truncate text-xl font-extrabold text-primary">
                            <Amount :value="balanceTotal" />
                        </p>
                        <p class="text-xs text-muted-foreground">{{ conservedPercentage }} % conservé</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <div class="rounded-xl bg-card p-5 shadow-soft">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-foreground">Revenus et dépenses par mois</h3>
                            <p class="text-xs text-muted-foreground">
                                <span class="text-income">■ Revenus</span>
                                <span class="ml-3 text-expense">■ Dépenses</span>
                            </p>
                        </div>

                        <ChartContainer :config="{}" class="mt-4 aspect-auto h-48 w-full">
                            <VisXYContainer :data="rows" :x-domain="[-0.5, rows.length - 0.5]">
                                <VisGroupedBar
                                    :x="incomeExpenseX"
                                    :y="incomeExpenseY"
                                    :color="incomeExpenseColor"
                                    :rounded-corners="4"
                                    :group-max-width="96"
                                />
                                <VisAxis type="x" :num-ticks="rows.length" :tick-format="monthTickFormat" />
                                <VisTooltip :triggers="incomeExpenseTriggers" />
                            </VisXYContainer>
                        </ChartContainer>
                    </div>

                    <div class="rounded-xl bg-card p-5 shadow-soft">
                        <h3 class="font-semibold text-foreground">Solde mensuel</h3>

                        <ChartContainer :config="{}" class="mt-4 aspect-auto h-32 w-full">
                            <VisXYContainer :data="rows" :x-domain="[-0.5, rows.length - 0.5]">
                                <VisStackedBar
                                    :x="balanceX"
                                    :y="balanceY"
                                    :color="balanceColor"
                                    :rounded-corners="4"
                                    :bar-max-width="96"
                                />
                                <VisAxis type="x" :num-ticks="rows.length" :tick-format="monthTickFormat" />
                                <VisTooltip :triggers="balanceTriggers" />
                            </VisXYContainer>
                        </ChartContainer>
                    </div>
                </div>

                <div class="h-fit rounded-xl bg-card p-6 shadow-soft">
                    <h3 class="mb-3 text-sm font-semibold text-foreground">Points clés</h3>

                    <div v-if="bestBalanceMonth" class="rounded-lg bg-muted p-4">
                        <p class="text-xs text-muted-foreground">Meilleur solde</p>
                        <strong class="text-sm text-foreground">
                            {{ formatMonth(bestBalanceMonth.month) }} · <Amount :value="bestBalanceMonth.balance" />
                        </strong>
                    </div>

                    <div class="mt-3 rounded-lg bg-muted p-4">
                        <p class="text-xs text-muted-foreground">Dépenses moyennes</p>
                        <strong class="text-sm text-foreground"><Amount :value="averageExpense" /></strong>
                    </div>

                    <div class="mt-3 rounded-lg bg-muted p-4">
                        <p class="text-xs text-muted-foreground">Revenus moyens</p>
                        <strong class="text-sm text-foreground"><Amount :value="averageIncome" /></strong>
                    </div>

                    <h3 class="mb-2 mt-8 text-sm font-semibold text-foreground">Lecture</h3>
                    <p class="text-xs text-muted-foreground">
                        Comparaison fondée uniquement sur les entrées et dépenses réelles de chaque mois.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
