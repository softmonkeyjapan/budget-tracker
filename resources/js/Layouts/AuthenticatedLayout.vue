<script setup>
import AppSidebar from '@/Components/AppSidebar.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import FeedbackBubble from '@/Components/FeedbackBubble.vue';
import { SidebarInset, SidebarProvider, SidebarTrigger } from '@/Components/ui/sidebar';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <SidebarProvider class="bg-app">
        <AppSidebar />

        <SidebarInset>
            <!-- Mobile Top Bar -->
            <nav class="flex h-16 shrink-0 items-center gap-3 border-b border-line bg-surface px-4 md:hidden">
                <SidebarTrigger />
                <Link :href="route('dashboard')" class="flex items-center gap-2">
                    <ApplicationLogo class="h-8 w-8 shrink-0" />
                    <span class="text-base font-extrabold text-ink">Budget Tracker</span>
                </Link>
            </nav>

            <!-- Page Heading -->
            <header class="bg-surface" v-if="$slots.header">
                <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1">
                <div class="mx-auto max-w-6xl">
                    <slot />
                </div>
            </main>
        </SidebarInset>

        <FeedbackBubble v-if="$page.props.auth.user.is_admin" />
    </SidebarProvider>
</template>
