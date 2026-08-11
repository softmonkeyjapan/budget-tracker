<script setup>
import { computed, ref } from 'vue';
import { StackedBar } from '@unovis/ts';
import { VisAxis, VisStackedBar, VisTooltip, VisXYContainer } from '@unovis/vue';
import { ChartContainer } from '@/Shared/Components/ui/chart';
import { usePrivacy } from '@/Shared/Composables/usePrivacy';
import { amountLabel } from '@/Shared/utils/currency';

const props = defineProps({
    general: {
        type: Array,
        required: true,
    },
    detail: {
        type: Array,
        required: true,
    },
});

const tabs = [
    { key: 'general', label: 'Générale' },
    { key: 'detail', label: 'Détail' },
];

const activeTab = ref('general');

const data = computed(() => (activeTab.value === 'general' ? props.general : props.detail));

const barX = (_d, i) => i;
const barY = (d) => d.amount;
const barColor = (d) => d.color ?? '#676E80';

function truncateLabel(name) {
    return name.length > 8 ? `${name.slice(0, 7)}…` : name;
}

const xTickFormat = (i) => (data.value[i] ? truncateLabel(data.value[i].name) : '');

const { hidden } = usePrivacy();

const barTriggers = {
    [StackedBar.selectors.bar]: (d) => {
        const item = d.datum ?? d;
        const amount = hidden.value ? '•••••' : amountLabel(item.amount);
        return `<div class="rounded-lg px-2 py-1 text-xs text-white shadow-soft" style="background-color: #172033">${item.name} · ${amount} · ${item.percentage} %</div>`;
    },
};

const legend = computed(() => {
    if (activeTab.value !== 'detail') {
        return [];
    }

    const seen = new Map();

    for (const item of props.detail) {
        const name = item.root_name ?? item.name;

        if (!seen.has(name)) {
            seen.set(name, item.color ?? '#676E80');
        }
    }

    return Array.from(seen, ([name, color]) => ({ name, color }));
});
</script>

<template>
    <div class="rounded-xl bg-card p-6 shadow-soft">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="font-semibold text-foreground">Dépenses par catégorie</h3>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ activeTab === 'general' ? 'Regroupées par catégorie principale' : 'Regroupées par sous-catégorie' }}
                </p>
            </div>

            <div class="flex shrink-0 gap-1 rounded-lg bg-muted p-1">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    type="button"
                    class="rounded-lg px-3 py-1 text-xs font-semibold transition-colors"
                    :class="activeTab === tab.key ? 'bg-card text-foreground shadow-soft' : 'text-muted-foreground hover:text-foreground'"
                    @click="activeTab = tab.key"
                >
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <p v-if="data.length === 0" class="mt-4 p-5 text-center text-sm text-muted-foreground">
            Aucune dépense pour ce mois.
        </p>

        <template v-else>
            <ChartContainer :config="{}" class="mt-6 aspect-auto h-56 w-full">
                <VisXYContainer :data="data" :x-domain="[-0.5, data.length - 0.5]">
                    <VisStackedBar :x="barX" :y="barY" :color="barColor" :rounded-corners="6" :bar-max-width="140" />
                    <VisAxis type="x" :num-ticks="data.length" :tick-format="xTickFormat" />
                    <VisTooltip :triggers="barTriggers" />
                </VisXYContainer>
            </ChartContainer>

            <div v-if="legend.length > 0" class="mt-4 flex flex-wrap gap-x-4 gap-y-2 border-t border-border pt-3">
                <span v-for="entry in legend" :key="entry.name" class="flex items-center gap-1.5 text-xs text-muted-foreground">
                    <span class="h-2.5 w-2.5 shrink-0 rounded-full" :style="{ backgroundColor: entry.color }"></span>
                    {{ entry.name }}
                </span>
            </div>
        </template>
    </div>
</template>
