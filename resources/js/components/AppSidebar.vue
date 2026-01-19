<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Grid,
    List,
    Plus,
    VideoCamera,
    Setting,
} from '@element-plus/icons-vue';
import { computed, inject, type Ref } from 'vue';

import AppLogo from './AppLogo.vue';
import NavUser from '@/components/NavUser.vue';
import { dashboard } from '@/routes';

const page = usePage();

const sidebarCollapsed = inject<Ref<boolean>>('sidebarCollapsed');

const isActivePatron = computed(() => page.props.auth.user?.is_active_patron);
const isAdmin = computed(() => page.props.auth.user?.is_admin);

const getActiveIndex = () => {
    const path = window.location.pathname;
    if (path === '/dashboard') return '/dashboard';
    if (path === '/requests') return '/requests';
    if (path === '/requests/new') return '/requests/new';
    if (path === '/my-requests') return '/my-requests';
    if (path === '/admin/requests') return '/admin/requests';
    return '/dashboard';
};
</script>

<template>
    <el-aside
        :width="sidebarCollapsed ? '64px' : '240px'"
        class="transition-all duration-300 border-r border-[var(--el-border-color)]"
    >
        <div class="flex h-full flex-col">
            <!-- Logo -->
            <div class="flex h-16 items-center justify-center border-b border-[var(--el-border-color)] px-4">
                <Link :href="dashboard()" class="flex items-center gap-2">
                    <AppLogo v-if="!sidebarCollapsed" />
                    <span v-else class="text-xl font-bold">RM</span>
                </Link>
            </div>

            <!-- Navigation -->
            <el-menu
                :default-active="getActiveIndex()"
                :collapse="sidebarCollapsed"
                :collapse-transition="false"
                class="flex-1 border-none"
            >
                <!-- Main Nav -->
                <el-menu-item index="/dashboard">
                    <Link :href="dashboard()" class="flex items-center w-full">
                        <el-icon><Grid /></el-icon>
                        <span>Dashboard</span>
                    </Link>
                </el-menu-item>

                <!-- Patron Navigation -->
                <template v-if="isActivePatron">
                    <el-menu-item-group v-if="!sidebarCollapsed">
                        <template #title>Video Requests</template>
                    </el-menu-item-group>
                    <el-divider v-else class="my-2" />

                    <el-menu-item index="/requests">
                        <Link href="/requests" class="flex items-center w-full">
                            <el-icon><List /></el-icon>
                            <span>Video Queue</span>
                        </Link>
                    </el-menu-item>

                    <el-menu-item index="/requests/new">
                        <Link href="/requests/new" class="flex items-center w-full">
                            <el-icon><Plus /></el-icon>
                            <span>New Request</span>
                        </Link>
                    </el-menu-item>

                    <el-menu-item index="/my-requests">
                        <Link href="/my-requests" class="flex items-center w-full">
                            <el-icon><VideoCamera /></el-icon>
                            <span>My Requests</span>
                        </Link>
                    </el-menu-item>
                </template>

                <!-- Admin Navigation -->
                <template v-if="isAdmin">
                    <el-menu-item-group v-if="!sidebarCollapsed">
                        <template #title>Admin</template>
                    </el-menu-item-group>
                    <el-divider v-else class="my-2" />

                    <el-menu-item index="/admin/requests">
                        <Link href="/admin/requests" class="flex items-center w-full">
                            <el-icon><Setting /></el-icon>
                            <span>Manage Requests</span>
                        </Link>
                    </el-menu-item>
                </template>
            </el-menu>

            <!-- User Menu -->
            <div class="border-t border-[var(--el-border-color)] p-2">
                <NavUser :collapsed="sidebarCollapsed" />
            </div>
        </div>
    </el-aside>
</template>

<style scoped>
.el-menu {
    --el-menu-bg-color: transparent;
}

.el-menu-item {
    height: auto;
    line-height: normal;
    padding: 0 !important;
}

.el-menu-item a {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    width: 100%;
    color: inherit;
    text-decoration: none;
}

.el-menu--collapse .el-menu-item a {
    justify-content: center;
    padding: 12px;
}

.el-menu--collapse .el-menu-item a span {
    display: none;
}
</style>
