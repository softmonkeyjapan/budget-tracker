<script setup>
import { computed, ref } from 'vue';
import Amount from '@/Components/Amount.vue';

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

const maxAmount = computed(() => data.value.reduce((max, item) => Math.max(max, item.amount), 0));

function barHeight(amount) {
    return maxAmount.value === 0 ? 0 : Math.max(4, Math.round((amount / maxAmount.value) * 100));
}

const legend = computed(() => {
    if (activeTab.value !== 'detail') {
        return [];
    }

    const seen = new Map();

    for (const item of props.detail) {
        const name = item.root_name ?? item.name;

        if (!seen.has(name)) {
            seen.set(name, item.color ?? '#8A90A2');
        }
    }

    return Array.from(seen, ([name, color]) => ({ name, color }));
});
</script>

<template>
    <div class="rounded-card bg-surface p-6 shadow-soft">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="font-semibold text-ink">Dépenses par catégorie</h3>
                <p class="mt-1 text-sm text-muted">
                    {{ activeTab === 'general' ? 'Regroupées par catégorie principale' : 'Regroupées par sous-catégorie' }}
                </p>
            </div>

            <div class="flex shrink-0 gap-1 rounded-control bg-page p-1">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    type="button"
                    class="rounded-control px-3 py-1 text-xs font-semibold transition-colors"
                    :class="activeTab === tab.key ? 'bg-surface text-ink shadow-soft' : 'text-muted hover:text-ink'"
                    @click="activeTab = tab.key"
                >
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <p v-if="data.length === 0" class="mt-4 p-5 text-center text-sm text-muted">
            Aucune dépense pour ce mois.
        </p>

        <template v-else>
            <div class="mt-6 flex h-56 items-end gap-3 overflow-x-auto pb-1">
                <div
                    v-for="item in data"
                    :key="item.id"
                    class="group relative flex h-full min-w-[56px] flex-1 flex-col items-center"
                >
                    <div
                        class="pointer-events-none absolute bottom-full mb-2 whitespace-nowrap rounded-control bg-ink px-2 py-1 text-xs text-white opacity-0 shadow-soft transition-opacity group-hover:opacity-100"
                    >
                        {{ item.name }} · <Amount :value="item.amount" raw /> FCFP
                    </div>
                    <div class="mt-1 flex h-40 w-full items-end">
                        <div
                            class="relative w-full rounded-t-control transition-[height]"
                            :style="{
                                height: barHeight(item.amount) + '%',
                                backgroundColor: item.color ?? '#8A90A2',
                            }"
                        >
                            <span
                                class="absolute inset-x-0 top-1/2 -translate-y-1/2 text-center text-xs font-semibold text-white drop-shadow"
                            >
                                {{ item.percentage }} %
                            </span>
                        </div>
                    </div>
                    <span class="mt-2 w-full truncate text-center text-xs text-muted" :title="item.name">
                        {{ item.name }}
                    </span>
                </div>
            </div>

            <div v-if="legend.length > 0" class="mt-4 flex flex-wrap gap-x-4 gap-y-2 border-t border-line pt-3">
                <span v-for="entry in legend" :key="entry.name" class="flex items-center gap-1.5 text-xs text-muted">
                    <span class="h-2.5 w-2.5 shrink-0 rounded-full" :style="{ backgroundColor: entry.color }"></span>
                    {{ entry.name }}
                </span>
            </div>
        </template>
    </div>
</template>
