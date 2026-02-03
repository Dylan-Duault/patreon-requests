<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { CheckCircle, Clock, ExternalLink, Film, Plus, Video } from 'lucide-vue-next';

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
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { type BreadcrumbItem } from '@/types';

interface VideoRequest {
    id: number;
    title: string | null;
    thumbnail: string | null;
    youtube_url: string;
    status: 'pending' | 'done';
    queue_position: number | null;
    requested_at: string;
    completed_at: string | null;
}

defineProps<{
    isActivePatron: boolean;
    patronStatus: string | null;
    tierCents: number;
    monthlyLimit: number;
    remainingRequests: number;
    showRequestList: boolean;
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
                <p class="text-muted-foreground">
                    <template v-if="isActivePatron">
                        You're an active patron, thank you for supporting the channel
                    </template>
                    <template v-else>
                        Subscribe to Patreon to request videos.
                    </template>
                </p>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Patron Status
                        </CardTitle>
                        <CheckCircle
                            v-if="isActivePatron"
                            class="h-4 w-4 text-green-500"
                        />
                        <Clock v-else class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            <Badge
                                :variant="isActivePatron ? 'default' : 'secondary'"
                            >
                                {{ isActivePatron ? 'Active' : 'Inactive' }}
                            </Badge>
                        </div>
                        <p class="text-xs text-muted-foreground mt-1">
                            {{ tierCents > 0 ? formatTier(tierCents) + '/month' : 'No active tier' }}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Credits Per Month
                        </CardTitle>
                        <Video class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ monthlyLimit }}
                        </div>
                        <p class="text-xs text-muted-foreground mt-1">
                            credits per renewal
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Credits Remaining
                        </CardTitle>
                        <Film class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ remainingRequests }}
                        </div>
                        <p class="text-xs text-muted-foreground mt-1">
                            credits available
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions -->
            <div class="flex flex-col sm:flex-row gap-3">
                <Button v-if="isActivePatron && remainingRequests > 0" as-child class="w-full sm:w-auto">
                    <Link href="/requests/new">
                        <Plus class="mr-2 h-4 w-4" />
                        Request a Video
                    </Link>
                </Button>
                <Button v-else-if="!isActivePatron" as-child class="w-full sm:w-auto">
                    <Link href="/subscribe">
                        Subscribe to Request
                    </Link>
                </Button>
                <Button variant="outline" as-child v-if="isActivePatron && showRequestList" class="w-full sm:w-auto">
                    <Link href="/requests">
                        View Queue
                    </Link>
                </Button>
            </div>

            <!-- Recent Requests -->
            <Card v-if="recentRequests.length > 0">
                <CardHeader>
                    <CardTitle>Your Recent Requests</CardTitle>
                    <CardDescription>
                        Your latest video requests and their status
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            v-for="request in recentRequests"
                            :key="request.id"
                            class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4"
                        >
                            <div class="flex items-center gap-3 sm:gap-4">
                                <TooltipProvider>
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <div class="flex flex-col items-center justify-center w-10 sm:w-12 h-14 sm:h-16 flex-shrink-0">
                                                <template v-if="request.status === 'pending' && request.queue_position">
                                                    <Clock class="h-4 w-4 sm:h-5 sm:w-5 mb-1 text-muted-foreground" />
                                                    <span class="text-xs font-medium text-muted-foreground">#{{ request.queue_position }}</span>
                                                </template>
                                                <template v-else-if="request.status === 'done'">
                                                    <CheckCircle class="h-4 w-4 sm:h-5 sm:w-5 text-green-500" />
                                                </template>
                                            </div>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <p v-if="request.status === 'pending' && request.queue_position">
                                                Position #{{ request.queue_position }} in queue
                                            </p>
                                            <p v-else-if="request.status === 'done'">
                                                Completed on {{ request.completed_at ? formatDate(request.completed_at) : 'N/A' }}
                                            </p>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                                <img
                                    v-if="request.thumbnail"
                                    :src="request.thumbnail"
                                    :alt="request.title || 'Video thumbnail'"
                                    class="h-14 w-24 sm:h-16 sm:w-28 rounded object-cover flex-shrink-0"
                                />
                                <div
                                    v-else
                                    class="flex h-14 w-24 sm:h-16 sm:w-28 items-center justify-center rounded bg-muted flex-shrink-0"
                                >
                                    <Video class="h-5 w-5 sm:h-6 sm:w-6 text-muted-foreground" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium truncate text-sm sm:text-base">
                                        {{ request.title || 'Untitled Video' }}
                                    </p>
                                    <p class="text-xs sm:text-sm text-muted-foreground">
                                        Requested {{ formatDate(request.requested_at) }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 sm:gap-3 ml-auto sm:ml-0">
                                <Badge
                                    :variant="request.status === 'done' ? 'default' : 'secondary'"
                                    class="text-xs"
                                >
                                    {{ request.status === 'done' ? 'Completed' : 'Pending' }}
                                </Badge>
                                <a
                                    :href="request.youtube_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-muted-foreground hover:text-foreground"
                                >
                                    <ExternalLink class="h-4 w-4" />
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t">
                        <Button variant="ghost" as-child class="w-full">
                            <Link href="/my-requests">
                                View all my requests
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Empty State -->
            <Card v-else-if="isActivePatron">
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
