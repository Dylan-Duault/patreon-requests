<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Link as LinkIcon, List, Plus, VideoCamera } from '@element-plus/icons-vue';

import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface VideoRequest {
    id: number;
    title: string | null;
    thumbnail: string | null;
    youtube_url: string;
    youtube_video_id: string;
    requested_at: string;
    user: {
        name: string;
        avatar: string | null;
    };
}

defineProps<{
    requests: VideoRequest[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Video Queue',
        href: '/requests',
    },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};
</script>

<template>
    <Head title="Video Queue" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">Video Queue</h1>
                    <p class="text-[var(--el-text-color-secondary)]">
                        Pending videos in chronological order (FIFO)
                    </p>
                </div>
                <el-button type="primary">
                    <Link href="/requests/new" class="flex items-center gap-2">
                        <el-icon><Plus /></el-icon>
                        Request Video
                    </Link>
                </el-button>
            </div>

            <!-- Queue List -->
            <el-card v-if="requests.length > 0" shadow="never">
                <template #header>
                    <div class="flex items-center gap-2">
                        <el-icon><List /></el-icon>
                        <h3 class="text-lg font-semibold">Pending Requests ({{ requests.length }})</h3>
                    </div>
                    <p class="text-sm text-[var(--el-text-color-secondary)]">
                        Videos are watched in the order they were requested
                    </p>
                </template>

                <div class="space-y-4">
                    <div
                        v-for="(request, index) in requests"
                        :key="request.id"
                        class="flex items-center gap-4 rounded-lg border border-[var(--el-border-color)] p-4"
                    >
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[var(--el-fill-color)] text-sm font-medium">
                            {{ index + 1 }}
                        </div>
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
                        </div>
                        <div class="flex items-center gap-2">
                            <el-avatar
                                :size="32"
                                :src="request.user.avatar || undefined"
                            >
                                {{ getInitials(request.user.name) }}
                            </el-avatar>
                            <span class="text-sm text-[var(--el-text-color-secondary)] hidden sm:inline">
                                {{ request.user.name }}
                            </span>
                        </div>
                    </div>
                </div>
            </el-card>

            <!-- Empty State -->
            <el-card v-else shadow="never">
                <el-empty description="Queue is empty">
                    <template #image>
                        <el-icon :size="48" class="text-[var(--el-text-color-secondary)]">
                            <List />
                        </el-icon>
                    </template>
                    <p class="text-[var(--el-text-color-secondary)] mb-4">
                        No pending video requests. Be the first to request a video!
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
