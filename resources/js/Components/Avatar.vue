<script setup>
import { computed } from 'vue';
import { colorForName, initials } from '@/utils/avatar';
import { Avatar, AvatarFallback } from '@/Components/ui/avatar';

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
    <Avatar :class="size">
        <AvatarFallback class="font-extrabold text-white" :style="{ backgroundColor: background }">
            {{ label }}
        </AvatarFallback>
    </Avatar>
</template>
