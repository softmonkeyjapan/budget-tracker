<script setup>
import { useTheme } from '@/Composables/useTheme';

const { theme, toggle } = useTheme();
</script>

<template>
    <button
        type="button"
        @click="toggle"
        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-control border border-line bg-surface text-ink transition duration-150 ease-in-out hover:bg-app"
        :aria-label="theme === 'dark' ? 'Activer le mode clair' : 'Activer le mode sombre'"
    >
        {{ theme === 'dark' ? '☀' : '☾' }}
    </button>
</template>
