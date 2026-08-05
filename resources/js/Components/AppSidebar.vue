<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Avatar from '@/Components/Avatar.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import PrivacyToggle from '@/Components/PrivacyToggle.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/Components/ui/sidebar';
import { Link } from '@inertiajs/vue3';

const navItems = [
    { label: 'Dashboard', route: 'dashboard', pattern: 'dashboard' },
    { label: 'Catégories', route: 'categories.index', pattern: 'categories.*' },
    { label: 'Dépenses', route: 'expenses.index', pattern: 'expenses.*' },
    { label: 'Entrées', route: 'incomes.index', pattern: 'incomes.*' },
    { label: 'Comparaison', route: 'comparison', pattern: 'comparison' },
];
</script>

<template>
    <Sidebar>
        <SidebarHeader class="p-6">
            <Link :href="route('dashboard')" class="flex items-center gap-3">
                <ApplicationLogo class="h-9 w-9 shrink-0" />
                <span class="text-lg font-extrabold leading-tight text-ink group-data-[collapsible=icon]:hidden">
                    Budget Tracker
                </span>
            </Link>
        </SidebarHeader>

        <SidebarContent>
            <SidebarMenu class="px-4">
                <SidebarMenuItem v-for="item in navItems" :key="item.route">
                    <SidebarMenuButton
                        as-child
                        :is-active="route().current(item.pattern)"
                        :tooltip="item.label"
                        class="data-[active=true]:bg-nav/10 data-[active=true]:text-nav data-[active=true]:font-semibold"
                    >
                        <Link :href="route(item.route)">
                            <span>{{ item.label }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarContent>

        <SidebarFooter class="p-4">
            <div class="flex items-center gap-2">
                <Dropdown align="left" direction="up" width="48" class="min-w-0 flex-1">
                    <template #trigger>
                        <button
                            type="button"
                            class="flex w-full items-center gap-3 rounded-control p-2 text-start transition duration-150 ease-in-out hover:bg-app"
                        >
                            <Avatar :name="$page.props.auth.user.name" />
                            <span class="min-w-0 flex-1 group-data-[collapsible=icon]:hidden">
                                <span class="block truncate text-sm font-semibold text-ink">{{ $page.props.auth.user.name }}</span>
                                <span class="block truncate text-xs text-muted">{{ $page.props.auth.user.email }}</span>
                            </span>
                        </button>
                    </template>
                    <template #content>
                        <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                        <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                    </template>
                </Dropdown>

                <PrivacyToggle class="group-data-[collapsible=icon]:hidden" />
                <ThemeToggle class="group-data-[collapsible=icon]:hidden" />
            </div>
        </SidebarFooter>
    </Sidebar>
</template>
