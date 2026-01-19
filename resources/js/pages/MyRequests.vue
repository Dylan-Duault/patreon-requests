<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    CircleCheck,
    Clock,
    Link as LinkIcon,
    Plus,
    VideoCamera,
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
}

defineProps<{
    requests: VideoRequest[];
    remainingRequests: number;
    monthlyLimit: number;
}>();

const page = usePage();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'My Requests',
        href: '/my-requests',
    },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="My Requests" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">My Requests</h1>
                    <p class="text-[var(--el-text-color-secondary)]">
                        {{ remainingRequests }} of {{ monthlyLimit }} requests remaining this month
                    </p>
                </div>
                <el-button type="primary" :disabled="remainingRequests === 0">
                    <Link href="/requests/new" class="flex items-center gap-2">
                        <el-icon><Plus /></el-icon>
                        New Request
                    </Link>
                </el-button>
            </div>

            <!-- Flash Messages -->
            <el-alert
                v-if="page.props.flash?.success"
                :title="page.props.flash.success"
                type="success"
                :closable="true"
            />

            <!-- Requests List -->
            <el-card v-if="requests.length > 0" shadow="never">
                <template #header>
                    <div>
                        <h3 class="text-lg font-semibold">Your Video Requests</h3>
                        <p class="text-sm text-[var(--el-text-color-secondary)]">
                            Track the status of your requested videos
                        </p>
                    </div>
                </template>

                <div class="space-y-4">
                    <div
                        v-for="request in requests"
                        :key="request.id"
                        class="flex items-center gap-4 rounded-lg border border-[var(--el-border-color)] p-4"
                    >
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
                                class="h-20 w-36 rounded object-cover"
                            />
                            <div
                                v-else
                                class="flex h-20 w-36 items-center justify-center rounded bg-[var(--el-fill-color)]"
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
                        <div class="flex-1 min-w-0">
                            <a
                                :href="request.youtube_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="font-medium hover:underline line-clamp-2"
                            >
                                {{ request.title || 'Untitled Video' }}
                            </a>
                            <p class="text-sm text-[var(--el-text-color-secondary)] mt-1">
                                Requested {{ formatDate(request.requested_at) }}
                            </p>
                            <p
                                v-if="request.completed_at"
                                class="text-sm text-[var(--el-text-color-secondary)]"
                            >
                                Completed {{ formatDate(request.completed_at) }}
                            </p>
                        </div>
                        <el-tag
                            :type="request.status === 'done' ? 'success' : 'info'"
                            class="flex items-center gap-1"
                        >
                            <el-icon v-if="request.status === 'done'"><CircleCheck /></el-icon>
                            <el-icon v-else><Clock /></el-icon>
                            {{ request.status === 'done' ? 'Completed' : 'Pending' }}
                        </el-tag>
                    </div>
                </div>
            </el-card>

            <!-- Empty State -->
            <el-card v-else shadow="never">
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
