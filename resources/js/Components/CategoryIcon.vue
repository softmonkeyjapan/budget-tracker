<script setup>
defineProps({
    icon: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <svg
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
    >
        <template v-if="icon === 'home'">
            <path d="M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1h-5v-7h-6v7H4a1 1 0 0 1-1-1z" />
        </template>
        <template v-else-if="icon === 'cart'">
            <circle cx="9" cy="21" r="1" />
            <circle cx="19" cy="21" r="1" />
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
        </template>
        <template v-else-if="icon === 'truck'">
            <rect x="1" y="4" width="14" height="12" rx="1" />
            <path d="M15 8h4l3 4v4h-7z" />
            <circle cx="6" cy="18.5" r="2" />
            <circle cx="17.5" cy="18.5" r="2" />
        </template>
        <template v-else-if="icon === 'heart'">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78z" />
        </template>
        <template v-else-if="icon === 'star'">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
        </template>
        <template v-else>
            <path d="M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2.59 12.58a2 2 0 0 1 0-2.83l7.17-7.17A2 2 0 0 1 11 2h7a2 2 0 0 1 2 2v7a2 2 0 0 1-.41 1.41z" />
            <line x1="7" y1="7" x2="7.01" y2="7" />
        </template>
    </svg>
</template>
