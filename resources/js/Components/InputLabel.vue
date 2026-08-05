<script setup>
import { Label } from '@/Components/ui/label';

defineProps({
    value: {
        type: String,
    },
});
</script>

<template>
    <Label class="block text-sm font-medium text-ink">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </Label>
</template>
