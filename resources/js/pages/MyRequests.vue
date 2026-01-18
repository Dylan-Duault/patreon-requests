<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { CheckCircle, Clock, ExternalLink, Plus, Video } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
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
                    <p class="text-muted-foreground">
                        {{ remainingRequests }} of {{ monthlyLimit }} requests remaining this month
                    </p>
                </div>
                <Button as-child :disabled="remainingRequests === 0">
                    <Link href="/requests/new">
                        <Plus class="mr-2 h-4 w-4" />
                        New Request
                    </Link>
                </Button>
            </div>

            <!-- Flash Messages -->
            <div
                v-if="page.props.flash?.success"
                class="rounded-md bg-green-50 p-3 text-sm font-medium text-green-600 dark:bg-green-900/20 dark:text-green-400"
            >
                {{ page.props.flash.success }}
            </div>

            <!-- Requests List -->
            <Card v-if="requests.length > 0">
                <CardHeader>
                    <CardTitle>Your Video Requests</CardTitle>
                    <CardDescription>
                        Track the status of your requested videos
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            v-for="request in requests"
                            :key="request.id"
                            class="flex items-center gap-4 rounded-lg border p-4"
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
                                <p
                                    v-if="request.completed_at"
                                    class="text-sm text-muted-foreground"
                                >
                                    Completed {{ formatDate(request.completed_at) }}
                                </p>
                            </div>
                            <Badge
                                :variant="request.status === 'done' ? 'default' : 'secondary'"
                                class="flex items-center gap-1"
                            >
                                <CheckCircle
                                    v-if="request.status === 'done'"
                                    class="h-3 w-3"
                                />
                                <Clock v-else class="h-3 w-3" />
                                {{ request.status === 'done' ? 'Completed' : 'Pending' }}
                            </Badge>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Empty State -->
            <Card v-else>
                <CardContent class="flex flex-col items-center justify-center py-12">
                    <Video class="h-12 w-12 text-muted-foreground mb-4" />
                    <h3 class="text-lg font-medium mb-2">No requests yet</h3>
                    <p class="text-muted-foreground text-center mb-4">
                        You haven't requested any videos yet. Start by requesting your first video!
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
