<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'block rounded-control bg-nav/10 px-3 py-2 text-sm font-semibold text-nav transition duration-150 ease-in-out'
        : 'block rounded-control px-3 py-2 text-sm font-medium text-muted transition duration-150 ease-in-out hover:bg-app hover:text-ink',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
