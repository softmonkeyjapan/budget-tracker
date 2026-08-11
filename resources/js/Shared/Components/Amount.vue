<script setup>
import { computed } from 'vue';
import { usePrivacy } from '@/Shared/Composables/usePrivacy';
import { amountLabel } from '@/Shared/utils/currency';

const props = defineProps({
    value: { type: Number, required: true },
    prefix: { type: String, default: '' },
    raw: { type: Boolean, default: false },
});

const { hidden } = usePrivacy();

const label = computed(() => {
    const formatted = props.raw ? new Intl.NumberFormat('fr-FR').format(props.value) : amountLabel(props.value);

    return `${props.prefix}${formatted}`;
});
</script>

<template>
    <span data-amount :class="{ 'blur-sm select-none': hidden }">{{ label }}</span>
</template>
