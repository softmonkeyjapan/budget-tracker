<script setup>
import { computed } from 'vue';
import { Check, ChevronDown, Minus } from '@lucide/vue';
import { Button } from '@/Shared/Components/ui/button';
import { Checkbox } from '@/Shared/Components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Shared/Components/ui/dropdown-menu';

const props = defineProps({
    categories: {
        type: Array,
        required: true,
    },
    modelValue: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['update:modelValue']);

// Root categories without children can never have expenses attached, so they
// would be a dead end in this filter.
const roots = computed(() => props.categories.filter((root) => (root.children?.length ?? 0) > 0));

const selectedSet = computed(() => new Set(props.modelValue));

function rootState(root) {
    const childIds = root.children.map((child) => child.id);
    const checkedCount = childIds.filter((id) => selectedSet.value.has(id)).length;

    if (checkedCount === 0) {
        return false;
    }

    return checkedCount === childIds.length ? true : 'indeterminate';
}

function onRootToggle(root, checked) {
    const childIds = root.children.map((child) => child.id);

    const next = checked
        ? [...new Set([...props.modelValue, ...childIds])]
        : props.modelValue.filter((id) => !childIds.includes(id));

    emit('update:modelValue', next);
}

function onChildToggle(child, checked) {
    const next = checked
        ? [...props.modelValue, child.id]
        : props.modelValue.filter((id) => id !== child.id);

    emit('update:modelValue', next);
}

const selectionLabels = computed(() => {
    const labels = [];

    for (const root of roots.value) {
        if (rootState(root) === true) {
            labels.push(root.name);
            continue;
        }

        for (const child of root.children) {
            if (selectedSet.value.has(child.id)) {
                labels.push(child.name);
            }
        }
    }

    return labels;
});

const triggerLabel = computed(() => {
    if (selectionLabels.value.length === 0) {
        return 'Toutes les catégories';
    }

    if (selectionLabels.value.length === 1) {
        return selectionLabels.value[0];
    }

    return `${selectionLabels.value[0]} +${selectionLabels.value.length - 1}`;
});
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="outline" type="button" class="min-w-[220px] justify-between font-normal">
                <span class="truncate">{{ triggerLabel }}</span>
                <ChevronDown class="h-4 w-4 shrink-0 opacity-60" />
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent class="w-64" align="start">
            <template v-for="(root, index) in roots" :key="root.id">
                <DropdownMenuSeparator v-if="index > 0" />

                <label class="flex cursor-pointer items-center gap-2 rounded-sm px-2 py-1.5 text-sm font-semibold hover:bg-accent">
                    <Checkbox :model-value="rootState(root)" @update:model-value="(checked) => onRootToggle(root, checked)">
                        <template #default="{ state }">
                            <Minus v-if="state === 'indeterminate'" class="size-3.5" />
                            <Check v-else class="size-3.5" />
                        </template>
                    </Checkbox>
                    {{ root.name }}
                </label>

                <label
                    v-for="child in root.children"
                    :key="child.id"
                    class="flex cursor-pointer items-center gap-2 rounded-sm py-1.5 pl-8 pr-2 text-sm hover:bg-accent"
                >
                    <Checkbox :model-value="selectedSet.has(child.id)" @update:model-value="(checked) => onChildToggle(child, checked)" />
                    {{ child.name }}
                </label>
            </template>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
