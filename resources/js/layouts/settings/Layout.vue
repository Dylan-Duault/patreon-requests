<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import Heading from '@/components/Heading.vue';
import { useActiveUrl } from '@/composables/useActiveUrl';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { type NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: editProfile(),
    },
    {
        title: 'Appearance',
        href: editAppearance(),
    },
];

const { urlIsActive } = useActiveUrl();
</script>

<template>
    <div class="px-4 py-6">
        <Heading
            title="Settings"
            description="Manage your profile and account settings"
        />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <el-menu
                    class="settings-menu border-none"
                    :default-active="sidebarNavItems.find(item => urlIsActive(item.href))?.title"
                >
                    <el-menu-item
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        :index="item.title"
                    >
                        <Link :href="item.href" class="w-full">
                            {{ item.title }}
                        </Link>
                    </el-menu-item>
                </el-menu>
            </aside>

            <el-divider class="my-6 lg:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>

<style scoped>
.settings-menu {
    --el-menu-bg-color: transparent;
}

.settings-menu .el-menu-item {
    height: auto;
    line-height: normal;
    padding: 0 !important;
}

.settings-menu .el-menu-item a {
    display: block;
    padding: 10px 16px;
}
</style>
