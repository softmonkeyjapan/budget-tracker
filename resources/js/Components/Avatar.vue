<script setup>
import { computed } from 'vue';
import { colorForName, initials } from '@/utils/avatar';

const props = defineProps({
    name: {
        type: String,
        required: true,
    },
    size: {
        type: String,
        default: 'h-10 w-10 text-sm',
    },
});

const background = computed(() => colorForName(props.name));
const label = computed(() => initials(props.name));
</script>

<template>
    <span
        :class="size"
        class="flex shrink-0 items-center justify-center rounded-control font-extrabold text-white"
        :style="{ backgroundColor: background }"
    >
        {{ label }}
    </span>
</template>
