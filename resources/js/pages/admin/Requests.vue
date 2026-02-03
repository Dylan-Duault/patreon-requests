<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    CheckCircle,
    Clock,
    Coins,
    ExternalLink,
    ListVideo,
    RotateCcw,
    ThumbsDown,
    ThumbsUp,
    Video,
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
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
    context: string | null;
    duration_seconds: number | null;
    request_cost: number;
    status: 'pending' | 'done';
    rating: 'up' | 'down' | null;
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

const formatDuration = (seconds: number): string => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;

    if (hours > 0) {
        return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }
    return `${minutes}:${secs.toString().padStart(2, '0')}`;
};

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const markAsPending = (requestId: number) => {
    router.patch(`/admin/requests/${requestId}/pending`, {}, {
        preserveScroll: true,
    });
};

const rateRequest = (requestId: number, rating: 'up' | 'down') => {
    router.patch(`/admin/requests/${requestId}/rate`, { rating }, {
        preserveScroll: true,
    });
};

const filterUrl = (status: string) => {
    return status === 'pending' ? '/admin/requests' : `/admin/requests?status=${status}`;
};
</script>

<template>
    <Head title="Admin - Requests" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Manage Requests</h1>
                <p class="text-muted-foreground">
                    View and manage all video requests
                </p>
            </div>

            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Requests</CardTitle>
                        <ListVideo class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pending</CardTitle>
                        <Clock class="h-4 w-4 text-yellow-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.pending }}</div>
                    </CardContent>
                </Card>
                
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Completed</CardTitle>
                        <CheckCircle class="h-4 w-4 text-green-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.done }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-2">
                <Button
                    :variant="currentFilter === 'all' ? 'default' : 'outline'"
                    size="sm"
                    as-child
                >
                    <Link :href="filterUrl('all')">All</Link>
                </Button>
                <Button
                    :variant="currentFilter === 'pending' ? 'default' : 'outline'"
                    size="sm"
                    as-child
                >
                    <Link :href="filterUrl('pending')">Pending</Link>
                </Button>
                <Button
                    :variant="currentFilter === 'done' ? 'default' : 'outline'"
                    size="sm"
                    as-child
                >
                    <Link :href="filterUrl('done')">Completed</Link>
                </Button>
            </div>

            <!-- Flash Messages -->
            <div
                v-if="page.props.flash?.success"
                class="rounded-md bg-green-50 p-3 text-sm font-medium text-green-600 dark:bg-green-900/20 dark:text-green-400"
            >
                {{ page.props.flash.success }}
            </div>

            <!-- Requests Table -->
            <Card v-if="requests.length > 0">
                <CardHeader>
                    <CardTitle>Video Requests</CardTitle>
                    <CardDescription>
                        {{ requests.length }} request{{ requests.length !== 1 ? 's' : '' }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            v-for="request in requests"
                            :key="request.id"
                            class="flex flex-col sm:flex-row sm:items-start gap-3 sm:gap-4 rounded-lg border p-3 sm:p-4"
                        >
                            <!-- Thumbnail -->
                            <a
                                :href="request.youtube_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="relative flex-shrink-0 group w-full sm:w-auto"
                            >
                                <img
                                    v-if="request.thumbnail"
                                    :src="request.thumbnail"
                                    :alt="request.title || 'Video thumbnail'"
                                    class="h-48 w-full sm:h-24 sm:w-44 rounded object-cover"
                                />
                                <div
                                    v-else
                                    class="flex h-48 w-full sm:h-24 sm:w-44 items-center justify-center rounded bg-muted"
                                >
                                    <Video class="h-12 w-12 sm:h-8 sm:w-8 text-muted-foreground" />
                                </div>
                                <div class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded">
                                    <ExternalLink class="h-6 w-6 text-white" />
                                </div>
                            </a>

                            <!-- Content -->
                            <div class="flex-1 min-w-0 space-y-2">
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                                    <div class="flex-1 min-w-0">
                                        <a
                                            :href="request.youtube_url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="font-medium hover:underline line-clamp-2 text-sm sm:text-base"
                                        >
                                            {{ request.title || 'Untitled Video' }}
                                        </a>
                                        <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs sm:text-sm text-muted-foreground mt-1">
                                            <span class="whitespace-nowrap">{{ formatDate(request.requested_at) }}</span>
                                            <span v-if="request.duration_seconds" class="flex items-center gap-1 whitespace-nowrap">
                                                <Clock class="h-3 w-3" />
                                                {{ formatDuration(request.duration_seconds) }}
                                            </span>
                                            <span class="flex items-center gap-1 whitespace-nowrap">
                                                <Coins class="h-3 w-3" />
                                                {{ request.request_cost }} {{ request.request_cost === 1 ? 'credit' : 'credits' }}
                                            </span>
                                        </div>
                                    </div>
                                    <Badge
                                        :variant="request.status === 'done' ? 'default' : 'secondary'"
                                        class="self-start text-xs whitespace-nowrap"
                                    >
                                        {{ request.status === 'done' ? 'Completed' : 'Pending' }}
                                    </Badge>
                                </div>

                                <!-- User Info -->
                                <div class="flex flex-wrap items-center gap-2 text-xs sm:text-sm">
                                    <Avatar class="h-5 w-5 sm:h-6 sm:w-6">
                                        <AvatarImage
                                            v-if="request.user.avatar"
                                            :src="request.user.avatar"
                                            :alt="request.user.name"
                                        />
                                        <AvatarFallback class="text-xs">
                                            {{ getInitials(request.user.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <span class="font-medium">{{ request.user.name }}</span>
                                    <span class="text-muted-foreground hidden sm:inline">({{ request.user.email }})</span>
                                    <Badge variant="outline" class="ml-auto text-xs whitespace-nowrap">
                                        {{ formatTier(request.user.tier_cents) }}/month
                                    </Badge>
                                </div>

                                <!-- Context -->
                                <div
                                    v-if="request.context"
                                    class="rounded-md bg-muted/50 p-2 sm:p-3 text-xs sm:text-sm"
                                >
                                    <p class="text-xs font-medium text-muted-foreground mb-1">Context</p>
                                    <p class="whitespace-pre-wrap">{{ request.context }}</p>
                                </div>

                                <!-- Actions -->
                                <div class="flex flex-wrap items-center gap-2 pt-2">
                                    <!-- Rating buttons for pending requests -->
                                    <template v-if="request.status === 'pending'">
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            class="text-green-600 hover:bg-green-50 hover:text-green-700 dark:text-green-400 dark:hover:bg-green-950 dark:hover:text-green-300"
                                            @click="rateRequest(request.id, 'up')"
                                        >
                                            <ThumbsUp class="mr-1 sm:mr-2 h-3 w-3 sm:h-4 sm:w-4" />
                                            <span class="text-xs sm:text-sm">Good</span>
                                        </Button>
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            class="text-red-600 hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-950 dark:hover:text-red-300"
                                            @click="rateRequest(request.id, 'down')"
                                        >
                                            <ThumbsDown class="mr-1 sm:mr-2 h-3 w-3 sm:h-4 sm:w-4" />
                                            <span class="text-xs sm:text-sm">Bad</span>
                                        </Button>
                                    </template>
                                    <!-- Show rating and revert for completed requests -->
                                    <template v-else>
                                        <div v-if="request.rating" class="flex items-center gap-1 text-xs sm:text-sm">
                                            <ThumbsUp
                                                v-if="request.rating === 'up'"
                                                class="h-3 w-3 sm:h-4 sm:w-4 text-green-600 dark:text-green-400"
                                            />
                                            <ThumbsDown
                                                v-else
                                                class="h-3 w-3 sm:h-4 sm:w-4 text-red-600 dark:text-red-400"
                                            />
                                            <span
                                                :class="request.rating === 'up'
                                                    ? 'text-green-600 dark:text-green-400'
                                                    : 'text-red-600 dark:text-red-400'"
                                            >
                                                {{ request.rating === 'up' ? 'Good' : 'Bad' }}
                                            </span>
                                        </div>
                                        <span v-else class="text-xs sm:text-sm text-muted-foreground">No rating</span>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            @click="markAsPending(request.id)"
                                        >
                                            <RotateCcw class="mr-1 sm:mr-2 h-3 w-3 sm:h-4 sm:w-4" />
                                            <span class="text-xs sm:text-sm">Revert</span>
                                        </Button>
                                    </template>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        as="a"
                                        :href="request.youtube_url"
                                        target="_blank"
                                        class="hidden sm:inline-flex"
                                    >
                                        <ExternalLink class="mr-2 h-4 w-4" />
                                        Watch Video
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Empty State -->
            <Card v-else>
                <CardContent class="flex flex-col items-center justify-center py-12">
                    <ListVideo class="h-12 w-12 text-muted-foreground mb-4" />
                    <h3 class="text-lg font-medium mb-2">No requests found</h3>
                    <p class="text-muted-foreground text-center">
                        <template v-if="currentFilter !== 'all'">
                            No {{ currentFilter }} requests. Try a different filter.
                        </template>
                        <template v-else>
                            No video requests have been submitted yet.
                        </template>
                    </p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
