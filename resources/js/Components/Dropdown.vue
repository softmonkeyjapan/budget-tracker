<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

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

const closeOnEscape = (e) => {
    if (open.value && e.key === 'Escape') {
        open.value = false;
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

const widthClass = computed(() => {
    return {
        48: 'w-48',
    }[props.width.toString()];
});

const positionClasses = computed(() =>
    props.direction === 'up' ? 'bottom-full mb-2' : 'top-full mt-2',
);

const alignmentClasses = computed(() => {
    if (props.align === 'left') {
        return props.direction === 'up'
            ? 'ltr:origin-bottom-left rtl:origin-bottom-right start-0'
            : 'ltr:origin-top-left rtl:origin-top-right start-0';
    } else if (props.align === 'right') {
        return props.direction === 'up'
            ? 'ltr:origin-bottom-right rtl:origin-bottom-left end-0'
            : 'ltr:origin-top-right rtl:origin-top-left end-0';
    }

    return props.direction === 'up' ? 'origin-bottom' : 'origin-top';
});

const open = ref(false);
</script>

<template>
    <div class="relative">
        <div @click="open = !open">
            <slot name="trigger" />
        </div>

        <!-- Full Screen Dropdown Overlay -->
        <div
            v-show="open"
            class="fixed inset-0 z-40"
            @click="open = false"
        ></div>

        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-show="open"
                class="absolute z-50 rounded-card shadow-soft"
                :class="[widthClass, positionClasses, alignmentClasses]"
                style="display: none"
                @click="open = false"
            >
                <div
                    class="rounded-card ring-1 ring-line"
                    :class="contentClasses"
                >
                    <slot name="content" />
                </div>
            </div>
        </Transition>
    </div>
</template>
