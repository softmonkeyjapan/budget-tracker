<script setup>
import { Button } from '@/Components/ui/button';

defineProps({
    meta: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['update:page', 'update:perPage']);

const perPageOptions = [20, 50, 100];
</script>

<template>
    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-border px-5 py-3 text-sm text-muted-foreground">
        <div class="flex items-center gap-2">
            <label for="per-page" class="whitespace-nowrap">Par page</label>
            <select
                id="per-page"
                :value="meta.per_page"
                class="rounded-lg border-border bg-card text-sm text-foreground shadow-sm focus:border-ring focus:ring-ring"
                @change="emit('update:perPage', Number($event.target.value))"
            >
                <option v-for="option in perPageOptions" :key="option" :value="option">{{ option }}</option>
            </select>
            <span class="whitespace-nowrap">{{ meta.total }} au total</span>
        </div>

        <div class="flex items-center gap-3">
            <Button
                variant="outline"
                size="sm"
                type="button"
                :disabled="meta.current_page <= 1"
                @click="emit('update:page', meta.current_page - 1)"
            >
                Précédent
            </Button>
            <span class="whitespace-nowrap">Page {{ meta.current_page }} / {{ meta.last_page }}</span>
            <Button
                variant="outline"
                size="sm"
                type="button"
                :disabled="meta.current_page >= meta.last_page"
                @click="emit('update:page', meta.current_page + 1)"
            >
                Suivant
            </Button>
        </div>
    </div>
</template>
