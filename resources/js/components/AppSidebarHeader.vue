<script setup lang="ts">
import { Fold, Expand } from '@element-plus/icons-vue';
import { inject, type Ref } from 'vue';

import Breadcrumbs from '@/components/Breadcrumbs.vue';
import type { BreadcrumbItemType } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const sidebarCollapsed = inject<Ref<boolean>>('sidebarCollapsed');
const toggleSidebar = inject<() => void>('toggleSidebar');
</script>

<template>
    <el-header class="flex items-center gap-4 border-b border-[var(--el-border-color)] h-16 px-4">
        <el-button
            text
            :icon="sidebarCollapsed ? Expand : Fold"
            @click="toggleSidebar"
            class="!p-2"
        />
        <Breadcrumbs v-if="breadcrumbs && breadcrumbs.length > 0" :breadcrumbs="breadcrumbs" />
    </el-header>
</template>
