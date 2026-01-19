<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ExternalLink, ListVideo, Plus, Video } from 'lucide-vue-next';

const page = usePage();

import AppLayout from '@/layouts/AppLayout.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
                    <p class="text-muted-foreground">
                        Pending videos in chronological order (FIFO)
                    </p>
                </div>
                <Button as-child>
                    <Link href="/requests/new">
                        <Plus class="mr-2 h-4 w-4" />
                        Request Video
                    </Link>
                </Button>
            </div>

            <!-- Flash Messages -->
            <div
                v-if="page.props.flash?.error"
                class="rounded-md bg-red-50 p-3 text-sm font-medium text-red-600 dark:bg-red-900/20 dark:text-red-400"
            >
                {{ page.props.flash.error }}
            </div>
            <div
                v-if="page.props.flash?.success"
                class="rounded-md bg-green-50 p-3 text-sm font-medium text-green-600 dark:bg-green-900/20 dark:text-green-400"
            >
                {{ page.props.flash.success }}
            </div>

            <!-- Queue List -->
            <Card v-if="requests.length > 0">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <ListVideo class="h-5 w-5" />
                        Pending Requests ({{ requests.length }})
                    </CardTitle>
                    <CardDescription>
                        Videos are watched in the order they were requested
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            v-for="(request, index) in requests"
                            :key="request.id"
                            class="flex items-center gap-4 rounded-lg border p-4"
                        >
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-muted text-sm font-medium">
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
                                    class="flex h-20 w-36 items-center justify-center rounded bg-muted"
                                >
                                    <Video class="h-8 w-8 text-muted-foreground" />
                                </div>
                                <div class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded">
                                    <ExternalLink class="h-6 w-6 text-white" />
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
                                <p class="text-sm text-muted-foreground mt-1">
                                    Requested {{ formatDate(request.requested_at) }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <Avatar class="h-8 w-8">
                                    <AvatarImage
                                        v-if="request.user.avatar"
                                        :src="request.user.avatar"
                                        :alt="request.user.name"
                                    />
                                    <AvatarFallback>
                                        {{ getInitials(request.user.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <span class="text-sm text-muted-foreground hidden sm:inline">
                                    {{ request.user.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Empty State -->
            <Card v-else>
                <CardContent class="flex flex-col items-center justify-center py-12">
                    <ListVideo class="h-12 w-12 text-muted-foreground mb-4" />
                    <h3 class="text-lg font-medium mb-2">Queue is empty</h3>
                    <p class="text-muted-foreground text-center mb-4">
                        No pending video requests. Be the first to request a video!
                    </p>
                    <Button as-child>
                        <Link href="/requests/new">
                            <Plus class="mr-2 h-4 w-4" />
                            Request a Video
                        </Link>
                    </Button>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
