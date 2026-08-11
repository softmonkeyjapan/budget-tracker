<script setup>
import { computed } from 'vue';
import { Dialog, DialogContent } from '@/Shared/Components/ui/dialog';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close']);

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const preventCloseUnlessCloseable = (event) => {
    if (!props.closeable) {
        event.preventDefault();
    }
};

const maxWidthClass = computed(() => {
    return {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[props.maxWidth];
});
</script>

<template>
    <Dialog :open="show" @update:open="(value) => !value && close()">
        <DialogContent
            :show-close-button="closeable"
            :class="['max-h-[calc(100vh-3rem)] overflow-y-auto overflow-x-hidden bg-card shadow-soft', maxWidthClass]"
            @escape-key-down="preventCloseUnlessCloseable"
            @pointer-down-outside="preventCloseUnlessCloseable"
        >
            <slot />
        </DialogContent>
    </Dialog>
</template>
