<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { CheckCircle, Clock, Edit2, ExternalLink, Plus, Save, Video, X } from 'lucide-vue-next';
import { ref } from 'vue';

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
import { Textarea } from '@/components/ui/textarea';
import { type BreadcrumbItem } from '@/types';

interface VideoRequest {
    id: number;
    title: string | null;
    thumbnail: string | null;
    youtube_url: string;
    youtube_video_id: string;
    status: 'pending' | 'done';
    context: string | null;
    queue_position: number | null;
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

const editingContext = ref<number | null>(null);
const contextForms = ref<Record<number, ReturnType<typeof useForm>>>({});

const startEditContext = (request: VideoRequest) => {
    editingContext.value = request.id;
    if (!contextForms.value[request.id]) {
        contextForms.value[request.id] = useForm({
            context: request.context || '',
        });
    }
};

const cancelEditContext = () => {
    editingContext.value = null;
};

const saveContext = (requestId: number) => {
    const form = contextForms.value[requestId];
    if (!form) return;

    form.patch(`/requests/${requestId}/context`, {
        preserveScroll: true,
        onSuccess: () => {
            editingContext.value = null;
        },
    });
};
</script>

<template>
    <Head title="My Requests" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl sm:text-2xl font-semibold tracking-tight">My Requests</h1>
                    <p class="text-sm sm:text-base text-muted-foreground">
                        {{ remainingRequests }} {{ remainingRequests === 1 ? 'credit' : 'credits' }} remaining
                    </p>
                </div>
                <Button as-child :disabled="remainingRequests === 0" class="w-full sm:w-auto">
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
                            class="rounded-lg border p-3 sm:p-4"
                        >
                            <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
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
                                        class="h-40 w-full sm:h-20 sm:w-36 rounded object-cover"
                                    />
                                    <div
                                        v-else
                                        class="flex h-40 w-full sm:h-20 sm:w-36 items-center justify-center rounded bg-muted"
                                    >
                                        <Video class="h-12 w-12 sm:h-8 sm:w-8 text-muted-foreground" />
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
                                        class="font-medium hover:underline line-clamp-2 text-sm sm:text-base"
                                    >
                                        {{ request.title || 'Untitled Video' }}
                                    </a>
                                    <p class="text-xs sm:text-sm text-muted-foreground mt-1">
                                        Requested {{ formatDate(request.requested_at) }}
                                    </p>
                                    <p
                                        v-if="request.status === 'pending' && request.queue_position"
                                        class="text-xs sm:text-sm text-muted-foreground"
                                    >
                                        Position in queue: #{{ request.queue_position }}
                                    </p>
                                    <p
                                        v-if="request.completed_at"
                                        class="text-xs sm:text-sm text-muted-foreground"
                                    >
                                        Completed {{ formatDate(request.completed_at) }}
                                    </p>
                                </div>
                                <Badge
                                    :variant="request.status === 'done' ? 'default' : 'secondary'"
                                    class="flex items-center gap-1 self-start sm:self-center text-xs whitespace-nowrap"
                                >
                                    <CheckCircle
                                        v-if="request.status === 'done'"
                                        class="h-3 w-3"
                                    />
                                    <Clock v-else class="h-3 w-3" />
                                    {{ request.status === 'done' ? 'Completed' : 'Pending' }}
                                </Badge>
                            </div>

                            <!-- Context section for pending requests -->
                            <div v-if="request.status === 'pending'" class="mt-4 pt-4 border-t">
                                <div v-if="editingContext === request.id" class="space-y-2">
                                    <label class="text-sm font-medium">Context</label>
                                    <Textarea
                                        v-model="contextForms[request.id].context"
                                        placeholder="Add any context or specific instructions for this request..."
                                        class="min-h-[100px]"
                                        :maxlength="500"
                                    />
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs text-muted-foreground">
                                            {{ contextForms[request.id].context?.length || 0 }}/500 characters
                                        </p>
                                        <div class="flex gap-2">
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                @click="cancelEditContext"
                                                :disabled="contextForms[request.id].processing"
                                            >
                                                <X class="h-4 w-4 mr-1" />
                                                Cancel
                                            </Button>
                                            <Button
                                                type="button"
                                                size="sm"
                                                @click="saveContext(request.id)"
                                                :disabled="contextForms[request.id].processing"
                                            >
                                                <Save class="h-4 w-4 mr-1" />
                                                Save
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                                <div v-else>
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium mb-1">Context</p>
                                            <p v-if="request.context" class="text-sm text-muted-foreground whitespace-pre-wrap">
                                                {{ request.context }}
                                            </p>
                                            <p v-else class="text-sm text-muted-foreground italic">
                                                No context provided
                                            </p>
                                        </div>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            @click="startEditContext(request)"
                                        >
                                            <Edit2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
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
