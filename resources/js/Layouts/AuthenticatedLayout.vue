<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Avatar from '@/Components/Avatar.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import FeedbackBubble from '@/Components/FeedbackBubble.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import SidebarLink from '@/Components/SidebarLink.vue';
import PrivacyToggle from '@/Components/PrivacyToggle.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="flex min-h-screen bg-app">
        <!-- Desktop Sidebar -->
        <aside
            class="hidden w-64 shrink-0 flex-col justify-between border-r border-line bg-surface sm:sticky sm:top-0 sm:flex sm:h-screen"
        >
            <div>
                <div class="flex items-center gap-3 px-6 py-6">
                    <Link :href="route('dashboard')" class="flex items-center gap-3">
                        <ApplicationLogo class="h-9 w-9 shrink-0" />
                        <span class="text-lg font-extrabold leading-tight text-ink">
                            Budget Tracker
                        </span>
                    </Link>
                </div>

                <nav class="space-y-1 px-4">
                    <SidebarLink
                        :href="route('dashboard')"
                        :active="route().current('dashboard')"
                    >
                        Dashboard
                    </SidebarLink>
                    <SidebarLink
                        :href="route('categories.index')"
                        :active="route().current('categories.*')"
                    >
                        Catégories
                    </SidebarLink>
                    <SidebarLink
                        :href="route('expenses.index')"
                        :active="route().current('expenses.*')"
                    >
                        Dépenses
                    </SidebarLink>
                    <SidebarLink
                        :href="route('incomes.index')"
                        :active="route().current('incomes.*')"
                    >
                        Entrées
                    </SidebarLink>
                    <SidebarLink
                        :href="route('comparison')"
                        :active="route().current('comparison')"
                    >
                        Comparaison
                    </SidebarLink>
                </nav>
            </div>

            <div class="flex items-center gap-2 border-t border-line p-4">
                <Dropdown align="left" direction="up" width="48" class="min-w-0 flex-1">
                    <template #trigger>
                        <button
                            type="button"
                            class="flex w-full items-center gap-3 rounded-control p-2 text-start transition duration-150 ease-in-out hover:bg-app"
                        >
                            <Avatar :name="$page.props.auth.user.name" />
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-ink">
                                    {{ $page.props.auth.user.name }}
                                </span>
                                <span class="block truncate text-xs text-muted">
                                    {{ $page.props.auth.user.email }}
                                </span>
                            </span>
                        </button>
                    </template>

                    <template #content>
                        <DropdownLink :href="route('profile.edit')">
                            Profile
                        </DropdownLink>
                        <DropdownLink
                            :href="route('logout')"
                            method="post"
                            as="button"
                        >
                            Log Out
                        </DropdownLink>
                    </template>
                </Dropdown>

                <PrivacyToggle />
                <ThemeToggle />
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <!-- Mobile Top Bar -->
            <nav class="bg-surface sm:hidden">
                <div class="flex h-16 items-center justify-between px-4">
                    <Link :href="route('dashboard')" class="flex items-center gap-2">
                        <ApplicationLogo class="h-8 w-8 shrink-0" />
                        <span class="text-base font-extrabold text-ink">Budget Tracker</span>
                    </Link>

                    <button
                        @click="
                            showingNavigationDropdown =
                                !showingNavigationDropdown
                        "
                        class="inline-flex items-center justify-center rounded-control p-2 text-muted transition duration-150 ease-in-out hover:bg-app hover:text-ink focus:bg-app focus:text-ink focus:outline-none"
                    >
                        <svg
                            class="h-6 w-6"
                            stroke="currentColor"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <path
                                :class="{
                                    hidden: showingNavigationDropdown,
                                    'inline-flex':
                                        !showingNavigationDropdown,
                                }"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                            <path
                                :class="{
                                    hidden: !showingNavigationDropdown,
                                    'inline-flex':
                                        showingNavigationDropdown,
                                }"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                >
                    <div class="space-y-1 border-t border-line px-2 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('categories.index')"
                            :active="route().current('categories.*')"
                        >
                            Catégories
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('expenses.index')"
                            :active="route().current('expenses.*')"
                        >
                            Dépenses
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('incomes.index')"
                            :active="route().current('incomes.*')"
                        >
                            Entrées
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('comparison')"
                            :active="route().current('comparison')"
                        >
                            Comparaison
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="border-t border-line pb-1 pt-4">
                        <div class="flex items-center gap-3 px-4">
                            <Avatar :name="$page.props.auth.user.name" />
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-base font-medium text-ink">
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div class="truncate text-sm font-medium text-muted">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>
                            <PrivacyToggle />
                            <ThemeToggle />
                        </div>

                        <div class="mt-3 space-y-1 px-2">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
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
        </div>

        <FeedbackBubble v-if="$page.props.auth.user.is_admin" />
    </div>
</template>
