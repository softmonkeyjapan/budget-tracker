<script setup>
import { computed } from 'vue';
import Amount from '@/Components/Amount.vue';

const props = defineProps({
    data: {
        type: Array,
        required: true,
    },
});

const maxAmount = computed(() => props.data.reduce((max, item) => Math.max(max, item.amount), 0));

function barHeight(amount) {
    return maxAmount.value === 0 ? 0 : Math.max(4, Math.round((amount / maxAmount.value) * 100));
}

const legend = computed(() => {
    const seen = new Map();

    for (const item of props.data) {
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
        <h3 class="font-semibold text-ink">Dépenses par sous-catégorie</h3>
        <p class="mt-1 text-sm text-muted">Regroupées par catégorie parente</p>

        <p v-if="data.length === 0" class="mt-4 p-5 text-center text-sm text-muted">
            Aucune dépense pour ce mois.
        </p>

        <template v-else>
            <div class="mt-6 flex h-52 items-end gap-3 overflow-x-auto pb-1">
                <div
                    v-for="item in data"
                    :key="item.id"
                    class="group relative flex h-full min-w-[52px] flex-1 flex-col items-center justify-end"
                >
                    <div
                        class="pointer-events-none absolute bottom-full mb-2 whitespace-nowrap rounded-control bg-ink px-2 py-1 text-xs text-white opacity-0 shadow-soft transition-opacity group-hover:opacity-100"
                    >
                        {{ item.name }} · <Amount :value="item.amount" raw /> FCFP
                    </div>
                    <div
                        class="w-full rounded-t-control transition-[height]"
                        :style="{
                            height: barHeight(item.amount) + '%',
                            backgroundColor: item.color ?? '#8A90A2',
                        }"
                    ></div>
                    <span class="mt-2 w-full truncate text-center text-xs text-muted" :title="item.name">
                        {{ item.name }}
                    </span>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-x-4 gap-y-2 border-t border-line pt-3">
                <span v-for="entry in legend" :key="entry.name" class="flex items-center gap-1.5 text-xs text-muted">
                    <span class="h-2.5 w-2.5 shrink-0 rounded-full" :style="{ backgroundColor: entry.color }"></span>
                    {{ entry.name }}
                </span>
            </div>
        </template>
    </div>
</template>
