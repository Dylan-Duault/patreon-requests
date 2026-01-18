<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    CheckCircle,
    Clock,
    ExternalLink,
    ListVideo,
    RotateCcw,
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
            <div class="flex gap-2">
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
                            class="flex items-start gap-4 rounded-lg border p-4"
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
                                    class="flex h-24 w-44 items-center justify-center rounded bg-muted"
                                >
                                    <Video class="h-8 w-8 text-muted-foreground" />
                                </div>
                                <div class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded">
                                    <ExternalLink class="h-6 w-6 text-white" />
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
                                        <p class="text-sm text-muted-foreground">
                                            Requested {{ formatDate(request.requested_at) }}
                                        </p>
                                    </div>
                                    <Badge
                                        :variant="request.status === 'done' ? 'default' : 'secondary'"
                                    >
                                        {{ request.status === 'done' ? 'Completed' : 'Pending' }}
                                    </Badge>
                                </div>

                                <!-- User Info -->
                                <div class="flex items-center gap-2 text-sm">
                                    <Avatar class="h-6 w-6">
                                        <AvatarImage
                                            v-if="request.user.avatar"
                                            :src="request.user.avatar"
                                            :alt="request.user.name"
                                        />
                                        <AvatarFallback class="text-xs">
                                            {{ getInitials(request.user.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <span>{{ request.user.name }}</span>
                                    <span class="text-muted-foreground">({{ request.user.email }})</span>
                                    <Badge variant="outline" class="ml-auto">
                                        {{ formatTier(request.user.tier_cents) }}/month
                                    </Badge>
                                </div>

                                <!-- Actions -->
                                <div class="flex gap-2 pt-2">
                                    <Button
                                        v-if="request.status === 'pending'"
                                        size="sm"
                                        @click="markAsDone(request.id)"
                                    >
                                        <CheckCircle class="mr-2 h-4 w-4" />
                                        Mark Done
                                    </Button>
                                    <Button
                                        v-else
                                        variant="outline"
                                        size="sm"
                                        @click="markAsPending(request.id)"
                                    >
                                        <RotateCcw class="mr-2 h-4 w-4" />
                                        Revert to Pending
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        as="a"
                                        :href="request.youtube_url"
                                        target="_blank"
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
