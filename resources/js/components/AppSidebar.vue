<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, ListVideo, Plus, Settings, Users, Video } from 'lucide-vue-next';
import { computed } from 'vue';

import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useActiveUrl } from '@/composables/useActiveUrl';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';

import AppLogo from './AppLogo.vue';

const page = usePage();
const { urlIsActive } = useActiveUrl();

const isActivePatron = computed(() => page.props.auth.user?.is_active_patron);
const isAdmin = computed(() => page.props.auth.user?.is_admin);

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

const patronNavItems = computed<NavItem[]>(() => {
    if (!isActivePatron.value) return [];
    return [
        {
            title: 'Video Queue',
            href: '/requests',
            icon: ListVideo,
        },
        {
            title: 'New Request',
            href: '/requests/new',
            icon: Plus,
        },
        {
            title: 'My Requests',
            href: '/my-requests',
            icon: Video,
        },
    ];
});

const adminNavItems = computed<NavItem[]>(() => {
    if (!isAdmin.value) return [];
    return [
        {
            title: 'Manage Requests',
            href: '/admin/requests',
            icon: Settings,
        },
        {
            title: 'Manage Users',
            href: '/admin/users',
            icon: Users,
        },
    ];
});

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />

            <!-- Patron Navigation -->
            <SidebarGroup v-if="patronNavItems.length > 0" class="px-2 py-0">
                <SidebarGroupLabel>The world with D</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem v-for="item in patronNavItems" :key="item.title">
                        <SidebarMenuButton
                            as-child
                            :is-active="urlIsActive(item.href)"
                            :tooltip="item.title"
                        >
                            <Link :href="item.href">
                                <component :is="item.icon" />
                                <span>{{ item.title }}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>

            <!-- Admin Navigation -->
            <SidebarGroup v-if="adminNavItems.length > 0" class="px-2 py-0">
                <SidebarGroupLabel>Admin</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem v-for="item in adminNavItems" :key="item.title">
                        <SidebarMenuButton
                            as-child
                            :is-active="urlIsActive(item.href)"
                            :tooltip="item.title"
                        >
                            <Link :href="item.href">
                                <component :is="item.icon" />
                                <span>{{ item.title }}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
