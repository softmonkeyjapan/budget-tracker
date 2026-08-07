<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Avatar from '@/Components/Avatar.vue';
import PrivacyToggle from '@/Components/PrivacyToggle.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarRail,
    useSidebar,
} from '@/Components/ui/sidebar';
import { Link } from '@inertiajs/vue3';
import { ChevronsUpDown } from '@lucide/vue';

const { isMobile } = useSidebar();

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
                <span class="text-lg font-heading font-extrabold leading-tight text-foreground">
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
                        class="data-[active=true]:bg-primary/10 data-[active=true]:text-primary data-[active=true]:font-semibold"
                    >
                        <Link :href="route(item.route)">
                            <span>{{ item.label }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarContent>

        <SidebarFooter class="gap-2 p-2">
            <div class="flex items-center justify-center gap-2">
                <PrivacyToggle />
                <ThemeToggle />
            </div>

            <SidebarMenu>
                <SidebarMenuItem>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <SidebarMenuButton
                                size="lg"
                                class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                            >
                                <Avatar :name="$page.props.auth.user.name" size="h-8 w-8 rounded-lg text-xs" />
                                <span class="grid min-w-0 flex-1 text-left text-sm leading-tight">
                                    <span class="truncate font-semibold text-foreground">{{ $page.props.auth.user.name }}</span>
                                    <span class="truncate text-xs text-muted-foreground">{{ $page.props.auth.user.email }}</span>
                                </span>
                                <ChevronsUpDown class="ml-auto size-4 text-muted-foreground" />
                            </SidebarMenuButton>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent
                            class="w-(--reka-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                            :side="isMobile ? 'bottom' : 'right'"
                            align="end"
                            :side-offset="4"
                        >
                            <DropdownMenuLabel class="p-0 font-normal">
                                <span class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                    <Avatar :name="$page.props.auth.user.name" size="h-8 w-8 rounded-lg text-xs" />
                                    <span class="grid min-w-0 flex-1 text-left text-sm leading-tight">
                                        <span class="truncate font-semibold text-foreground">{{ $page.props.auth.user.name }}</span>
                                        <span class="truncate text-xs text-muted-foreground">{{ $page.props.auth.user.email }}</span>
                                    </span>
                                </span>
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem as-child>
                                <Link :href="route('profile.edit')" class="w-full">Profile</Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem as-child>
                                <Link :href="route('logout')" method="post" as="button" class="w-full text-start">Log Out</Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarFooter>

        <SidebarRail />
    </Sidebar>
</template>
