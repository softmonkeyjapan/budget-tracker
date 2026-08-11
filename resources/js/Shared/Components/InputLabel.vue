<script setup>
import { Label } from '@/Shared/Components/ui/label';

defineProps({
    value: {
        type: String,
    },
});
</script>

<template>
    <Label class="block text-sm font-medium text-foreground">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </Label>
</template>
