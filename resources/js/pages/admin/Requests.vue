<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    CircleCheck,
    Clock,
    Link as LinkIcon,
    List,
    VideoCamera,
    RefreshLeft,
} from '@element-plus/icons-vue';

import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface VideoRequest {
    id: number;
    title: string | null;
    thumbnail: string | null;
    youtube_url: string;
    youtube_video_id: string;
    status: 'pending' | 'done';
    requested_at: string;
    completed_at: string | null;
    user: {
        id: number;
        name: string;
        email: string;
        avatar: string | null;
        tier_cents: number;
    };
}

interface Stats {
    total: number;
    pending: number;
    done: number;
}

defineProps<{
    requests: VideoRequest[];
    stats: Stats;
    currentFilter: string;
}>();

const page = usePage();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Admin',
        href: '/admin/requests',
    },
    {
        title: 'Requests',
        href: '/admin/requests',
    },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
};

const formatTier = (cents: number) => {
    return `$${(cents / 100).toFixed(2)}`;
};

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const markAsDone = (requestId: number) => {
    router.patch(`/admin/requests/${requestId}/done`, {}, {
        preserveScroll: true,
    });
};

const markAsPending = (requestId: number) => {
    router.patch(`/admin/requests/${requestId}/pending`, {}, {
        preserveScroll: true,
    });
};

const filterUrl = (status: string) => {
    return status === 'all' ? '/admin/requests' : `/admin/requests?status=${status}`;
};
</script>

<template>
    <Head title="Admin - Requests" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Manage Requests</h1>
                <p class="text-[var(--el-text-color-secondary)]">
                    View and manage all video requests
                </p>
            </div>

            <!-- Stats -->
            <el-row :gutter="16">
                <el-col :xs="24" :md="8">
                    <el-card shadow="never">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-[var(--el-text-color-secondary)]">
                                Total Requests
                            </span>
                            <el-icon class="text-[var(--el-text-color-secondary)]">
                                <List />
                            </el-icon>
                        </div>
                        <div class="text-2xl font-bold">{{ stats.total }}</div>
                    </el-card>
                </el-col>
                <el-col :xs="24" :md="8">
                    <el-card shadow="never">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-[var(--el-text-color-secondary)]">
                                Pending
                            </span>
                            <el-icon class="text-[var(--el-color-warning)]">
                                <Clock />
                            </el-icon>
                        </div>
                        <div class="text-2xl font-bold">{{ stats.pending }}</div>
                    </el-card>
                </el-col>
                <el-col :xs="24" :md="8">
                    <el-card shadow="never">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-[var(--el-text-color-secondary)]">
                                Completed
                            </span>
                            <el-icon class="text-[var(--el-color-success)]">
                                <CircleCheck />
                            </el-icon>
                        </div>
                        <div class="text-2xl font-bold">{{ stats.done }}</div>
                    </el-card>
                </el-col>
            </el-row>

            <!-- Filters -->
            <div class="flex gap-2">
                <el-button :type="currentFilter === 'all' ? 'primary' : 'default'" size="small">
                    <Link :href="filterUrl('all')">All</Link>
                </el-button>
                <el-button :type="currentFilter === 'pending' ? 'primary' : 'default'" size="small">
                    <Link :href="filterUrl('pending')">Pending</Link>
                </el-button>
                <el-button :type="currentFilter === 'done' ? 'primary' : 'default'" size="small">
                    <Link :href="filterUrl('done')">Completed</Link>
                </el-button>
            </div>

            <!-- Flash Messages -->
            <el-alert
                v-if="page.props.flash?.success"
                :title="page.props.flash.success"
                type="success"
                :closable="true"
            />

            <!-- Requests Table -->
            <el-card v-if="requests.length > 0" shadow="never">
                <template #header>
                    <div>
                        <h3 class="text-lg font-semibold">Video Requests</h3>
                        <p class="text-sm text-[var(--el-text-color-secondary)]">
                            {{ requests.length }} request{{ requests.length !== 1 ? 's' : '' }}
                        </p>
                    </div>
                </template>

                <div class="space-y-4">
                    <div
                        v-for="request in requests"
                        :key="request.id"
                        class="flex items-start gap-4 rounded-lg border border-[var(--el-border-color)] p-4"
                    >
                        <!-- Thumbnail -->
                        <a
                            :href="request.youtube_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="relative flex-shrink-0 group"
                        >
                            <img
                                v-if="request.thumbnail"
                                :src="request.thumbnail"
                                :alt="request.title || 'Video thumbnail'"
                                class="h-24 w-44 rounded object-cover"
                            />
                            <div
                                v-else
                                class="flex h-24 w-44 items-center justify-center rounded bg-[var(--el-fill-color)]"
                            >
                                <el-icon :size="32" class="text-[var(--el-text-color-secondary)]">
                                    <VideoCamera />
                                </el-icon>
                            </div>
                            <div class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded">
                                <el-icon :size="24" class="text-white">
                                    <LinkIcon />
                                </el-icon>
                            </div>
                        </a>

                        <!-- Content -->
                        <div class="flex-1 min-w-0 space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <a
                                        :href="request.youtube_url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="font-medium hover:underline line-clamp-1"
                                    >
                                        {{ request.title || 'Untitled Video' }}
                                    </a>
                                    <p class="text-sm text-[var(--el-text-color-secondary)]">
                                        Requested {{ formatDate(request.requested_at) }}
                                    </p>
                                </div>
                                <el-tag :type="request.status === 'done' ? 'success' : 'info'">
                                    {{ request.status === 'done' ? 'Completed' : 'Pending' }}
                                </el-tag>
                            </div>

                            <!-- User Info -->
                            <div class="flex items-center gap-2 text-sm">
                                <el-avatar
                                    :size="24"
                                    :src="request.user.avatar || undefined"
                                >
                                    {{ getInitials(request.user.name) }}
                                </el-avatar>
                                <span>{{ request.user.name }}</span>
                                <span class="text-[var(--el-text-color-secondary)]">({{ request.user.email }})</span>
                                <el-tag class="ml-auto" size="small">
                                    {{ formatTier(request.user.tier_cents) }}/month
                                </el-tag>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2 pt-2">
                                <el-button
                                    v-if="request.status === 'pending'"
                                    type="primary"
                                    size="small"
                                    @click="markAsDone(request.id)"
                                >
                                    <el-icon class="mr-1"><CircleCheck /></el-icon>
                                    Mark Done
                                </el-button>
                                <el-button
                                    v-else
                                    size="small"
                                    @click="markAsPending(request.id)"
                                >
                                    <el-icon class="mr-1"><RefreshLeft /></el-icon>
                                    Revert to Pending
                                </el-button>
                                <el-button
                                    size="small"
                                    text
                                    tag="a"
                                    :href="request.youtube_url"
                                    target="_blank"
                                >
                                    <el-icon class="mr-1"><LinkIcon /></el-icon>
                                    Watch Video
                                </el-button>
                            </div>
                        </div>
                    </div>
                </div>
            </el-card>

            <!-- Empty State -->
            <el-card v-else shadow="never">
                <el-empty description="No requests found">
                    <template #image>
                        <el-icon :size="48" class="text-[var(--el-text-color-secondary)]">
                            <List />
                        </el-icon>
                    </template>
                    <p class="text-[var(--el-text-color-secondary)]">
                        <template v-if="currentFilter !== 'all'">
                            No {{ currentFilter }} requests. Try a different filter.
                        </template>
                        <template v-else>
                            No video requests have been submitted yet.
                        </template>
                    </p>
                </el-empty>
            </el-card>
        </div>
    </AppLayout>
</template>
