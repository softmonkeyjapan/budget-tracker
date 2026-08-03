<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-canvas px-4 py-10"
    >
        <Link href="/" class="mb-6 flex items-center gap-2">
            <ApplicationLogo class="h-10 w-10" />
            <span class="text-lg font-extrabold text-ink">Budget Tracker</span>
        </Link>

        <div class="w-full overflow-hidden rounded-shell bg-white p-8 shadow-shell sm:max-w-md">
            <slot />
        </div>
    </div>
</template>
