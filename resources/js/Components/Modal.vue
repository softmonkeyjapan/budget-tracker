<script setup>
import { computed } from 'vue';
import { DialogContent, DialogOverlay, DialogPortal, DialogRoot } from 'reka-ui';

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
    <DialogRoot :open="show" @update:open="(value) => !value && close()">
        <DialogPortal>
            <DialogOverlay
                class="fixed inset-0 z-50 bg-gray-500 opacity-75 data-[state=closed]:animate-out data-[state=open]:animate-in data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0"
            />

            <DialogContent
                :class="[
                    'fixed left-1/2 top-1/2 z-50 max-h-[calc(100vh-3rem)] w-[calc(100%-2rem)] -translate-x-1/2 -translate-y-1/2 transform overflow-y-auto overflow-x-hidden rounded-card bg-surface shadow-soft transition-all',
                    'data-[state=closed]:animate-out data-[state=open]:animate-in data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95',
                    maxWidthClass,
                ]"
                @escape-key-down="preventCloseUnlessCloseable"
                @pointer-down-outside="preventCloseUnlessCloseable"
            >
                <slot />
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
