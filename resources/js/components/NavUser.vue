<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Setting, SwitchButton } from '@element-plus/icons-vue';

import UserInfo from '@/components/UserInfo.vue';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';

interface Props {
    collapsed?: boolean;
}

defineProps<Props>();

const page = usePage();
const user = page.props.auth.user;

const handleLogout = () => {
    router.flushAll();
};
</script>

<template>
    <el-dropdown trigger="click" :hide-on-click="true">
        <div class="flex w-full cursor-pointer items-center gap-2 rounded-lg p-2 hover:bg-[var(--el-fill-color-light)]">
            <UserInfo :user="user" :show-email="!collapsed" :collapsed="collapsed" />
        </div>
        <template #dropdown>
            <el-dropdown-menu>
                <div class="px-3 py-2 border-b border-[var(--el-border-color)]">
                    <UserInfo :user="user" :show-email="true" />
                </div>
                <el-dropdown-item>
                    <Link :href="edit()" class="flex items-center gap-2 w-full" prefetch>
                        <el-icon><Setting /></el-icon>
                        Settings
                    </Link>
                </el-dropdown-item>
                <el-dropdown-item divided>
                    <Link
                        :href="logout()"
                        @click="handleLogout"
                        as="button"
                        class="flex items-center gap-2 w-full"
                        data-test="logout-button"
                    >
                        <el-icon><SwitchButton /></el-icon>
                        Log out
                    </Link>
                </el-dropdown-item>
            </el-dropdown-menu>
        </template>
    </el-dropdown>
</template>

<style scoped>
.el-dropdown {
    width: 100%;
}
</style>
