<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    CircleCheck,
    Clock,
    Link as LinkIcon,
    VideoCamera,
    Plus,
} from '@element-plus/icons-vue';

import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface VideoRequest {
    id: number;
    title: string | null;
    thumbnail: string | null;
    youtube_url: string;
    status: 'pending' | 'done';
    requested_at: string;
}

defineProps<{
    isActivePatron: boolean;
    patronStatus: string | null;
    tierCents: number;
    monthlyLimit: number;
    remainingRequests: number;
    recentRequests: VideoRequest[];
}>();

const page = usePage();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const formatTier = (cents: number) => {
    return `$${(cents / 100).toFixed(2)}`;
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Welcome Section -->
            <div class="flex flex-col gap-2">
                <h1 class="text-2xl font-semibold tracking-tight">
                    Welcome back, {{ page.props.auth.user?.name }}!
                </h1>
                <p class="text-[var(--el-text-color-secondary)]">
                    <template v-if="isActivePatron">
                        You're an active patron at the {{ formatTier(tierCents) }} tier.
                    </template>
                    <template v-else>
                        Subscribe to Patreon to request videos.
                    </template>
                </p>
            </div>

            <!-- Stats Cards -->
            <el-row :gutter="16">
                <el-col :xs="24" :md="8">
                    <el-card shadow="never">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-[var(--el-text-color-secondary)]">
                                Patron Status
                            </span>
                            <el-icon v-if="isActivePatron" class="text-[var(--el-color-success)]">
                                <CircleCheck />
                            </el-icon>
                            <el-icon v-else class="text-[var(--el-text-color-secondary)]">
                                <Clock />
                            </el-icon>
                        </div>
                        <div class="text-2xl font-bold mb-1">
                            <el-tag :type="isActivePatron ? 'success' : 'info'">
                                {{ isActivePatron ? 'Active' : 'Inactive' }}
                            </el-tag>
                        </div>
                        <p class="text-xs text-[var(--el-text-color-secondary)]">
                            {{ tierCents > 0 ? formatTier(tierCents) + '/month' : 'No active tier' }}
                        </p>
                    </el-card>
                </el-col>

                <el-col :xs="24" :md="8">
                    <el-card shadow="never">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-[var(--el-text-color-secondary)]">
                                Monthly Limit
                            </span>
                            <el-icon class="text-[var(--el-text-color-secondary)]">
                                <VideoCamera />
                            </el-icon>
                        </div>
                        <div class="text-2xl font-bold mb-1">
                            {{ monthlyLimit }}
                        </div>
                        <p class="text-xs text-[var(--el-text-color-secondary)]">
                            requests per month
                        </p>
                    </el-card>
                </el-col>

                <el-col :xs="24" :md="8">
                    <el-card shadow="never">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-[var(--el-text-color-secondary)]">
                                Remaining This Month
                            </span>
                            <el-icon class="text-[var(--el-text-color-secondary)]">
                                <VideoCamera />
                            </el-icon>
                        </div>
                        <div class="text-2xl font-bold mb-1">
                            {{ remainingRequests }}
                        </div>
                        <p class="text-xs text-[var(--el-text-color-secondary)]">
                            requests available
                        </p>
                    </el-card>
                </el-col>
            </el-row>

            <!-- Quick Actions -->
            <div class="flex gap-3">
                <el-button v-if="isActivePatron && remainingRequests > 0" type="primary">
                    <Link href="/requests/new" class="flex items-center gap-2">
                        <el-icon><Plus /></el-icon>
                        Request a Video
                    </Link>
                </el-button>
                <el-button v-else-if="!isActivePatron" type="primary">
                    <Link href="/subscribe">
                        Subscribe to Request
                    </Link>
                </el-button>
                <el-button v-if="isActivePatron">
                    <Link href="/requests">
                        View Queue
                    </Link>
                </el-button>
            </div>

            <!-- Recent Requests -->
            <el-card v-if="recentRequests.length > 0" shadow="never">
                <template #header>
                    <div>
                        <h3 class="text-lg font-semibold">Your Recent Requests</h3>
                        <p class="text-sm text-[var(--el-text-color-secondary)]">
                            Your latest video requests and their status
                        </p>
                    </div>
                </template>

                <div class="space-y-4">
                    <div
                        v-for="request in recentRequests"
                        :key="request.id"
                        class="flex items-center gap-4"
                    >
                        <img
                            v-if="request.thumbnail"
                            :src="request.thumbnail"
                            :alt="request.title || 'Video thumbnail'"
                            class="h-16 w-28 rounded object-cover"
                        />
                        <div
                            v-else
                            class="flex h-16 w-28 items-center justify-center rounded bg-[var(--el-fill-color)]"
                        >
                            <el-icon :size="24" class="text-[var(--el-text-color-secondary)]">
                                <VideoCamera />
                            </el-icon>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium truncate">
                                {{ request.title || 'Untitled Video' }}
                            </p>
                            <p class="text-sm text-[var(--el-text-color-secondary)]">
                                Requested {{ formatDate(request.requested_at) }}
                            </p>
                        </div>
                        <el-tag :type="request.status === 'done' ? 'success' : 'info'">
                            {{ request.status === 'done' ? 'Completed' : 'Pending' }}
                        </el-tag>
                        <a
                            :href="request.youtube_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-[var(--el-text-color-secondary)] hover:text-[var(--el-color-primary)]"
                        >
                            <el-icon><LinkIcon /></el-icon>
                        </a>
                    </div>
                </div>

                <el-divider />

                <el-button text class="w-full">
                    <Link href="/my-requests">
                        View All Requests
                    </Link>
                </el-button>
            </el-card>

            <!-- Empty State -->
            <el-card v-else-if="isActivePatron" shadow="never">
                <el-empty description="No requests yet">
                    <template #image>
                        <el-icon :size="48" class="text-[var(--el-text-color-secondary)]">
                            <VideoCamera />
                        </el-icon>
                    </template>
                    <p class="text-[var(--el-text-color-secondary)] mb-4">
                        You haven't requested any videos yet. Start by requesting your first video!
                    </p>
                    <el-button type="primary">
                        <Link href="/requests/new" class="flex items-center gap-2">
                            <el-icon><Plus /></el-icon>
                            Request a Video
                        </Link>
                    </el-button>
                </el-empty>
            </el-card>
        </div>
    </AppLayout>
</template>
