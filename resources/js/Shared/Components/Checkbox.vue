<script setup>
import { computed } from 'vue';
import { Checkbox as CheckboxPrimitive } from '@/Shared/Components/ui/checkbox';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        required: true,
    },
    value: {
        default: null,
    },
});

const isChecked = computed(() => {
    return Array.isArray(props.checked) ? props.checked.includes(props.value) : props.checked;
});

const onUpdate = (value) => {
    if (Array.isArray(props.checked)) {
        const next = props.checked.filter((item) => item !== props.value);

        if (value) {
            next.push(props.value);
        }

        emit('update:checked', next);
    } else {
        emit('update:checked', value);
    }
};
</script>

<template>
    <CheckboxPrimitive
        :model-value="isChecked"
        @update:model-value="onUpdate"
        class="rounded border-border bg-card shadow-sm focus-visible:ring-ring data-[state=checked]:border-primary data-[state=checked]:bg-primary"
    />
</template>
