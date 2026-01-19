<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface TierInfo {
    amount: string;
    requests_per_month: number;
}

defineProps<{
    tiers: TierInfo[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Subscribe',
        href: '/subscribe',
    },
];
</script>

<template>
    <Head title="Subscribe" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col items-center justify-center gap-6 p-4 md:p-6">
            <div class="max-w-2xl w-full">
                <el-card shadow="never">
                    <template #header>
                        <div class="text-center">
                            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[var(--el-color-primary-light-9)]">
                                <el-icon :size="32" class="text-[var(--el-color-primary)]">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/></svg>
                                </el-icon>
                            </div>
                            <h2 class="text-2xl font-semibold">Subscribe to Request Videos</h2>
                            <p class="text-[var(--el-text-color-secondary)]">
                                Become a Patreon supporter to request YouTube videos for me to react to!
                            </p>
                        </div>
                    </template>

                    <div class="space-y-6">
                        <!-- Tier Information -->
                        <el-row :gutter="16" v-if="tiers.length > 0">
                            <el-col
                                v-for="(tier, index) in tiers"
                                :key="index"
                                :xs="24"
                                :sm="12"
                            >
                                <el-card shadow="hover" class="text-center">
                                    <div class="text-2xl font-bold text-[var(--el-color-primary)]">
                                        {{ tier.amount }}
                                    </div>
                                    <div class="text-sm text-[var(--el-text-color-secondary)]">per month</div>
                                    <div class="mt-2 font-medium">
                                        {{ tier.requests_per_month }} request{{ tier.requests_per_month > 1 ? 's' : '' }}/month
                                    </div>
                                </el-card>
                            </el-col>
                        </el-row>

                        <!-- Benefits -->
                        <div class="space-y-3">
                            <h3 class="font-medium">Benefits include:</h3>
                            <ul class="space-y-2 text-[var(--el-text-color-secondary)]">
                                <li class="flex items-center gap-2">
                                    <span class="h-1.5 w-1.5 rounded-full bg-[var(--el-color-primary)]" />
                                    Request YouTube videos for reactions
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="h-1.5 w-1.5 rounded-full bg-[var(--el-color-primary)]" />
                                    Videos watched in chronological order (FIFO)
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="h-1.5 w-1.5 rounded-full bg-[var(--el-color-primary)]" />
                                    Track your request status
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="h-1.5 w-1.5 rounded-full bg-[var(--el-color-primary)]" />
                                    View the public video queue
                                </li>
                            </ul>
                        </div>

                        <!-- CTA -->
                        <div class="flex flex-col gap-3 pt-4">
                            <el-button
                                type="primary"
                                size="large"
                                tag="a"
                                href="https://www.patreon.com"
                                target="_blank"
                                class="w-full"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    class="h-5 w-5 mr-2"
                                    fill="currentColor"
                                >
                                    <path
                                        d="M15.386.524c-4.764 0-8.64 3.876-8.64 8.64 0 4.75 3.876 8.613 8.64 8.613 4.75 0 8.614-3.864 8.614-8.613C24 4.4 20.136.524 15.386.524M.003 23.537h4.22V.524H.003"
                                    />
                                </svg>
                                Subscribe on Patreon
                                <el-icon class="ml-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/></svg>
                                </el-icon>
                            </el-button>
                            <el-button size="large">
                                <Link href="/dashboard">
                                    Back to Dashboard
                                </Link>
                            </el-button>
                        </div>
                    </div>
                </el-card>
            </div>
        </div>
    </AppLayout>
</template>
