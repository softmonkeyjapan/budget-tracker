<script setup>
import { Link } from '@inertiajs/vue3';
import { DropdownMenuItem } from 'reka-ui';

defineProps({
    href: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <DropdownMenuItem as-child>
        <Link
            :href="href"
            class="block w-full rounded-sm px-4 py-2 text-start text-sm leading-5 text-ink outline-none transition duration-150 ease-in-out hover:bg-app focus:bg-app"
        >
            <slot />
        </Link>
    </DropdownMenuItem>
</template>
