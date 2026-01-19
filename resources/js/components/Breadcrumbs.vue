<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface BreadcrumbItemType {
    title: string;
    href?: string;
}

defineProps<{
    breadcrumbs: BreadcrumbItemType[];
}>();
</script>

<template>
    <el-breadcrumb separator="/">
        <el-breadcrumb-item
            v-for="(item, index) in breadcrumbs"
            :key="index"
        >
            <Link
                v-if="index < breadcrumbs.length - 1 && item.href"
                :href="item.href"
                class="text-[var(--el-text-color-secondary)] hover:text-[var(--el-color-primary)]"
            >
                {{ item.title }}
            </Link>
            <span v-else class="text-[var(--el-text-color-primary)]">
                {{ item.title }}
            </span>
        </el-breadcrumb-item>
    </el-breadcrumb>
</template>
