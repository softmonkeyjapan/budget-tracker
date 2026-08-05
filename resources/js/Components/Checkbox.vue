<script setup>
import { computed } from 'vue';
import { Check } from '@lucide/vue';
import { CheckboxIndicator, CheckboxRoot } from 'reka-ui';
import { cn } from '@/lib/utils';

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
    <CheckboxRoot
        :model-value="isChecked"
        @update:model-value="onUpdate"
        :class="
            cn(
                'peer size-4 shrink-0 rounded border border-line bg-surface shadow-sm outline-none transition-shadow focus-visible:ring-2 focus-visible:ring-nav disabled:cursor-not-allowed disabled:opacity-50',
                'data-[state=checked]:border-nav data-[state=checked]:bg-nav data-[state=checked]:text-white',
            )
        "
    >
        <CheckboxIndicator class="grid place-content-center text-current">
            <Check class="size-3.5" />
        </CheckboxIndicator>
    </CheckboxRoot>
</template>
