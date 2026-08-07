<script setup>
import { computed, ref, toRef } from 'vue';
import { Donut } from '@unovis/ts';
import { VisDonut, VisSingleContainer } from '@unovis/vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Amount from '@/Components/Amount.vue';
import CategoryIcon from '@/Components/CategoryIcon.vue';
import { Button } from '@/Components/ui/button';
import { ChartContainer } from '@/Components/ui/chart';
import { useMonthNavigation } from '@/Composables/useMonthNavigation';
import { usePrivacy } from '@/Composables/usePrivacy';
import { useTheme } from '@/Composables/useTheme';
import { amountLabel } from '@/utils/currency';
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
const { theme } = useTheme();

const donutData = computed(() => {
    const segments = props.categories
        .filter((category) => category.amount > 0)
        .map((category) => ({
            label: category.name,
            value: category.amount,
            color: category.color ?? '#676E80',
        }));

    const unspent = Math.max(0, props.incomeTotal - props.expenseTotal);
    if (unspent > 0) {
        segments.push({
            label: 'Non dépensé',
            value: unspent,
            color: theme.value === 'dark' ? '#282F3E' : '#EEF0F5',
        });
    }

    return segments;
});

const donutValue = (d) => d.value;
const donutColor = (d) => d.color;

const { hidden } = usePrivacy();

const hoveredSegment = ref(null);
const tooltipPosition = ref({ x: 0, y: 0 });

// VisTooltip doesn't wire up correctly on VisSingleContainer (Donut never
// receives it), so the hover tooltip is done by hand: D3 binds each pie
// segment's data on the DOM node itself, readable via `__data__`.
function onDonutMouseMove(event) {
    const segmentEl = event.target.closest(`.${Donut.selectors.segment}`);
    if (!segmentEl) {
        hoveredSegment.value = null;
        return;
    }

    hoveredSegment.value = segmentEl.__data__?.data ?? null;
    const rect = event.currentTarget.getBoundingClientRect();
    tooltipPosition.value = { x: event.clientX - rect.left, y: event.clientY - rect.top };
}

function onDonutMouseLeave() {
    hoveredSegment.value = null;
}

const donutTooltipLabel = computed(() => {
    if (!hoveredSegment.value) {
        return '';
    }

    const amount = hidden.value ? '•••••' : amountLabel(hoveredSegment.value.value);
    return `${hoveredSegment.value.label} · ${amount}`;
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-heading font-extrabold leading-tight text-foreground">Dashboard</h2>
                    <p class="mt-0.5 text-sm capitalize text-muted-foreground">Vue mensuelle</p>
                </div>

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
                            Entrées d'argent
                        </p>
                        <p class="truncate text-xl font-extrabold text-income">
                            <Amount :value="incomeTotal" />
                        </p>
                        <p class="text-xs text-muted-foreground">{{ incomeCount }} entrée(s) ce mois</p>
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
                            Dépenses
                        </p>
                        <p class="truncate text-xl font-extrabold text-expense">
                            <Amount :value="expenseTotal" />
                        </p>
                        <p class="text-xs text-muted-foreground">{{ expensePercentage }} % des revenus</p>
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
                            Solde
                        </p>
                        <p class="truncate text-xl font-extrabold text-primary">
                            <Amount :value="balance" />
                        </p>
                        <p class="text-xs text-muted-foreground">{{ unspentPercentage }} % disponible</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="flex flex-col justify-center rounded-xl bg-card p-6 shadow-soft lg:col-span-2">
                    <div class="grid items-center gap-6 xl:grid-cols-[.8fr_1.3fr]">
                        <div>
                            <h3 class="font-semibold text-foreground">Répartition des revenus</h3>
                            <p class="mt-1 text-sm text-muted-foreground">
                                100 % = entrées d'argent du mois
                            </p>

                            <div
                                class="relative mx-auto mt-4 h-40 w-40"
                                @mousemove="onDonutMouseMove"
                                @mouseleave="onDonutMouseLeave"
                            >
                                <ChartContainer :config="{}" class="aspect-square h-40 w-40">
                                    <VisSingleContainer :data="donutData" class="h-40 w-40">
                                        <VisDonut
                                            :value="donutValue"
                                            :color="donutColor"
                                            :corner-radius="2"
                                            :pad-angle="0.012"
                                            :arc-width="28"
                                        />
                                    </VisSingleContainer>
                                </ChartContainer>
                                <div class="pointer-events-none absolute inset-0 grid place-content-center text-center">
                                    <strong class="text-lg text-foreground">
                                        <Amount :value="incomeTotal" raw />
                                    </strong>
                                    <span class="text-xs text-muted-foreground">revenus</span>
                                </div>
                                <div
                                    v-if="hoveredSegment"
                                    class="pointer-events-none absolute z-10 -translate-x-1/2 -translate-y-full whitespace-nowrap rounded-lg px-2 py-1 text-xs text-white shadow-soft"
                                    :style="{
                                        backgroundColor: '#172033',
                                        left: tooltipPosition.x + 'px',
                                        top: tooltipPosition.y - 8 + 'px',
                                    }"
                                >
                                    {{ donutTooltipLabel }}
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="font-semibold text-foreground">Utilisation par catégorie</h3>
                            <div class="mt-3 space-y-3">
                                <div
                                    v-for="category in categories"
                                    :key="category.id"
                                    class="flex items-center gap-3"
                                >
                                    <span
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-base"
                                        :style="{
                                            backgroundColor: (category.color ?? '#676E80') + '22',
                                            color: category.color ?? '#676E80',
                                        }"
                                    >
                                        <CategoryIcon :icon="category.icon" class="h-4 w-4" />
                                    </span>
                                    <p class="min-w-0 flex-1 truncate font-medium text-foreground">
                                        {{ category.name }}
                                    </p>
                                    <strong class="whitespace-nowrap text-primary">
                                        {{ category.percentage }} %
                                    </strong>
                                    <span class="whitespace-nowrap text-sm text-muted-foreground">
                                        <Amount :value="category.amount" />
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="h-fit rounded-xl bg-card p-6 shadow-soft">
                    <h3 class="mb-3 text-sm font-semibold text-foreground">Entrées du mois</h3>

                    <div class="mb-4 space-y-3">
                        <div
                            v-for="income in recentIncomes"
                            :key="income.id"
                            class="rounded-lg bg-muted p-4"
                        >
                            <p class="text-xs text-muted-foreground">
                                {{ new Date(income.date).toLocaleDateString('fr-FR', { day: '2-digit', month: 'long' }) }}
                                <template v-if="income.description"> · {{ income.description }}</template>
                            </p>
                            <strong class="text-sm text-foreground"><Amount :value="income.amount" /></strong>
                        </div>
                    </div>

                    <Link :href="route('incomes.create', { month })">
                        <Button variant="default" type="button" class="w-full justify-center">
                            + Ajouter une entrée
                        </Button>
                    </Link>

                    <h3 class="mb-3 mt-6 text-sm font-semibold text-foreground">Actions rapides</h3>
                    <Link :href="route('expenses.create', { month })">
                        <Button variant="secondary" type="button" class="w-full justify-center">
                            + Nouvelle dépense
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl bg-card shadow-soft">
                <div class="flex items-center justify-between border-b border-border px-5 py-4">
                    <h3 class="font-semibold text-foreground">5 dernières dépenses</h3>
                    <Link
                        :href="route('expenses.index', { month })"
                        class="rounded-lg border border-border bg-card px-4 py-1.5 text-sm font-semibold text-foreground shadow-pill hover:bg-accent"
                    >
                        Tout voir
                    </Link>
                </div>

                <p v-if="lastExpenses.length === 0" class="p-5 text-sm text-muted-foreground">
                    Aucune dépense pour ce mois.
                </p>

                <div
                    v-for="expense in lastExpenses"
                    :key="expense.id"
                    class="grid grid-cols-[100px_1fr_1fr_140px] items-center gap-2 border-b border-border px-5 py-3 last:border-b-0"
                >
                    <span class="text-sm text-muted-foreground">
                        {{ new Date(expense.date).toLocaleDateString('fr-FR') }}
                    </span>
                    <span class="flex min-w-0 items-center gap-2">
                        <span
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-sm"
                            :style="{
                                backgroundColor: (expense.category.resolved_color ?? '#676E80') + '22',
                                color: expense.category.resolved_color ?? '#676E80',
                            }"
                        >
                            <CategoryIcon :icon="expense.category.resolved_icon" class="h-4 w-4" />
                        </span>
                        <span class="truncate text-sm font-medium text-foreground">
                            {{ expense.category.name }}
                        </span>
                    </span>
                    <span class="truncate text-sm text-muted-foreground">
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
