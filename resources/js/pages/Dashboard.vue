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
                        You're an active patron at the {{ formatTier(tierCents) }} tier.
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
            <div class="flex gap-3">
                <Button v-if="isActivePatron && remainingRequests > 0" as-child>
                    <Link href="/requests/new">
                        <Plus class="mr-2 h-4 w-4" />
                        Request a Video
                    </Link>
                </Button>
                <Button v-else-if="!isActivePatron" as-child>
                    <Link href="/subscribe">
                        Subscribe to Request
                    </Link>
                </Button>
                <Button variant="outline" as-child v-if="isActivePatron && showRequestList">
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
                                class="flex h-16 w-28 items-center justify-center rounded bg-muted"
                            >
                                <Video class="h-6 w-6 text-muted-foreground" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium truncate">
                                    {{ request.title || 'Untitled Video' }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    Requested {{ formatDate(request.requested_at) }}
                                </p>
                            </div>
                            <Badge
                                :variant="request.status === 'done' ? 'default' : 'secondary'"
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
