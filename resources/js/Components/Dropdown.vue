<script setup>
import { computed } from 'vue';
import { DropdownMenuContent, DropdownMenuPortal, DropdownMenuRoot, DropdownMenuTrigger } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps({
    align: {
        type: String,
        default: 'right',
    },
    direction: {
        type: String,
        default: 'down',
    },
    width: {
        type: String,
        default: '48',
    },
    contentClasses: {
        type: String,
        default: 'py-1 bg-surface',
    },
});

const widthClass = computed(() => {
    return {
        48: 'w-48',
    }[props.width.toString()];
});

const contentSide = computed(() => (props.direction === 'up' ? 'top' : 'bottom'));

const contentAlign = computed(() => {
    if (props.align === 'left') {
        return 'start';
    } else if (props.align === 'right') {
        return 'end';
    }

    return 'center';
});
</script>

<template>
    <div class="relative">
        <DropdownMenuRoot>
            <DropdownMenuTrigger as-child>
                <slot name="trigger" />
            </DropdownMenuTrigger>

            <DropdownMenuPortal>
                <DropdownMenuContent
                    :side="contentSide"
                    :align="contentAlign"
                    :side-offset="8"
                    :class="
                        cn(
                            'z-50 rounded-card shadow-soft ring-1 ring-line',
                            widthClass,
                            contentClasses,
                        )
                    "
                >
                    <slot name="content" />
                </DropdownMenuContent>
            </DropdownMenuPortal>
        </DropdownMenuRoot>
    </div>
</template>
