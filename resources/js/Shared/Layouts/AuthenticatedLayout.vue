<script setup>
import AppSidebar from '@/Shared/Components/AppSidebar.vue';
import ApplicationLogo from '@/Shared/Components/ApplicationLogo.vue';
import FeedbackBubble from '@/Domains/Feedback/Components/FeedbackBubble.vue';
import { SidebarInset, SidebarProvider, SidebarTrigger } from '@/Shared/Components/ui/sidebar';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <SidebarProvider class="bg-background">
        <AppSidebar />

        <SidebarInset>
            <!-- Mobile Top Bar -->
            <nav class="flex h-16 shrink-0 items-center gap-3 border-b border-border bg-card px-4 md:hidden">
                <SidebarTrigger />
                <Link :href="route('dashboard')" class="flex items-center gap-2">
                    <ApplicationLogo class="h-8 w-8 shrink-0" />
                    <span class="text-base font-heading font-extrabold text-foreground">Budget Tracker</span>
                </Link>
            </nav>

            <!-- Page Heading -->
            <header class="bg-card" v-if="$slots.header">
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
