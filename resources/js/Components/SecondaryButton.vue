<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center rounded-control border border-line bg-white px-5 py-2.5 text-sm font-semibold text-ink shadow-pill transition duration-150 ease-in-out hover:bg-app focus:outline-none focus:ring-2 focus:ring-nav focus:ring-offset-2 disabled:opacity-40"
    >
        <slot />
    </button>
</template>
