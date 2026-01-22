<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { AlertCircle, Clock, Video } from 'lucide-vue-next';
import { ref, watch } from 'vue';

import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { type BreadcrumbItem } from '@/types';

const props = defineProps<{
    remainingRequests: number;
    monthlyLimit: number;
    maxDurationMinutes: number;
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
    {
        title: 'New Request',
        href: '/requests/new',
    },
];

const form = useForm({
    youtube_url: '',
    context: '',
});

interface VideoInfo {
    video_id: string;
    title: string | null;
    thumbnail: string | null;
    duration_seconds: number | null;
    request_cost: number;
    max_duration_minutes: number;
}

const videoInfo = ref<VideoInfo | null>(null);
const videoError = ref<string | null>(null);
const checkingVideo = ref(false);

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

function formatDuration(seconds: number): string {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;

    if (hours > 0) {
        return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }
    return `${minutes}:${secs.toString().padStart(2, '0')}`;
}

async function checkVideo(url: string) {
    if (!url) {
        videoInfo.value = null;
        videoError.value = null;
        return;
    }

    checkingVideo.value = true;
    videoError.value = null;
    videoInfo.value = null;

    try {
        const response = await axios.post('/requests/check', { youtube_url: url });
        videoInfo.value = response.data;
    } catch (error: unknown) {
        if (axios.isAxiosError(error) && error.response?.data?.error) {
            videoError.value = error.response.data.error;
        } else {
            videoError.value = 'Could not check video';
        }
    } finally {
        checkingVideo.value = false;
    }
}

watch(
    () => form.youtube_url,
    (newUrl) => {
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }
        debounceTimer = setTimeout(() => {
            checkVideo(newUrl);
        }, 500);
    },
);

const canSubmit = () => {
    if (!form.youtube_url || form.processing || checkingVideo.value) return false;
    if (videoError.value) return false;
    if (videoInfo.value && videoInfo.value.request_cost > props.remainingRequests) return false;
    return true;
};

const submit = () => {
    form.post('/requests', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Request Video" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col items-center justify-center gap-6 p-4 md:p-6">
            <div class="max-w-lg w-full">
                <Card>
                    <CardHeader class="text-center">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
                            <Video class="h-8 w-8 text-primary" />
                        </div>
                        <CardTitle class="text-2xl">Request a Video</CardTitle>
                        <CardDescription class="text-base">
                            Submit a YouTube video for me to react to
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <!-- Remaining Requests Info -->
                        <Alert class="mb-6">
                            <AlertCircle class="h-4 w-4" />
                            <AlertDescription>
                                You have <strong>{{ remainingRequests }}</strong> {{ remainingRequests === 1 ? 'request' : 'requests' }} remaining this month.
                            </AlertDescription>
                        </Alert>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="space-y-2">
                                <Label for="youtube_url">YouTube URL</Label>
                                <div class="relative">
                                    <Input
                                        id="youtube_url"
                                        v-model="form.youtube_url"
                                        type="url"
                                        placeholder="https://www.youtube.com/watch?v=..."
                                        required
                                        :disabled="form.processing"
                                        autofocus
                                    />
                                    <div v-if="checkingVideo" class="absolute right-3 top-1/2 -translate-y-1/2">
                                        <Spinner class="h-4 w-4" />
                                    </div>
                                </div>
                                <InputError :message="form.errors.youtube_url" />
                                <p v-if="videoError" class="text-xs text-destructive">
                                    {{ videoError }}
                                </p>
                                <p v-else class="text-xs text-muted-foreground">
                                    Supported formats: youtube.com/watch, youtu.be, youtube.com/shorts
                                </p>

                                <!-- Video Preview -->
                                <div
                                    v-if="videoInfo"
                                    class="mt-3 rounded-lg border bg-muted/50 p-3"
                                >
                                    <div class="flex gap-3">
                                        <img
                                            v-if="videoInfo.thumbnail"
                                            :src="videoInfo.thumbnail"
                                            :alt="videoInfo.title || 'Video thumbnail'"
                                            class="h-16 w-28 flex-shrink-0 rounded object-cover"
                                        />
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium truncate">
                                                {{ videoInfo.title || 'Unknown title' }}
                                            </p>
                                            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                                <span v-if="videoInfo.duration_seconds" class="flex items-center gap-1">
                                                    <Clock class="h-3 w-3" />
                                                    {{ formatDuration(videoInfo.duration_seconds) }}
                                                </span>
                                            </div>
                                            <div class="mt-2">
                                                <span
                                                    v-if="videoInfo.request_cost === 1"
                                                    class="inline-flex items-center rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary"
                                                >
                                                    1 request
                                                </span>
                                                <span
                                                    v-else
                                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                                    :class="videoInfo.request_cost > remainingRequests ? 'bg-destructive/10 text-destructive' : 'bg-amber-500/10 text-amber-600'"
                                                >
                                                    {{ videoInfo.request_cost }} requests
                                                    <span class="ml-1 font-normal">(video > {{ maxDurationMinutes }} min)</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <p
                                        v-if="videoInfo.request_cost > remainingRequests"
                                        class="mt-2 text-xs text-destructive"
                                    >
                                        You don't have enough requests remaining. This video requires {{ videoInfo.request_cost }} {{ videoInfo.request_cost === 1 ? 'request' : 'requests' }}.
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="context">Context <span class="text-muted-foreground font-normal">(optional)</span></Label>
                                <Textarea
                                    id="context"
                                    v-model="form.context"
                                    placeholder="What is this video about? Any specific part you want me to react to?"
                                    :disabled="form.processing"
                                    class="min-h-[100px]"
                                />
                                <InputError :message="form.errors.context" />
                                <p class="text-xs text-muted-foreground">
                                    Add any helpful information about the video (max 500 characters)
                                </p>
                            </div>

                            <div class="flex gap-3">
                                <Button
                                    type="submit"
                                    class="flex-1"
                                    :disabled="!canSubmit()"
                                >
                                    <Spinner v-if="form.processing" class="mr-2" />
                                    <template v-if="videoInfo && videoInfo.request_cost > 1">
                                        Submit Request ({{ videoInfo.request_cost }} credits)
                                    </template>
                                    <template v-else>
                                        Submit Request
                                    </template>
                                </Button>
                            </div>
                        </form>

                        <!-- Guidelines -->
                        <div class="mt-6 pt-6 border-t">
                            <h4 class="text-sm font-medium mb-2">Guidelines</h4>
                            <ul class="text-xs text-muted-foreground space-y-1">
                                <li>- Only YouTube videos are accepted</li>
                                <li>- Videos already in the queue cannot be requested again</li>
                                <li>- Videos over {{ maxDurationMinutes }} minutes count as multiple requests</li>
                                <li>- Requests are processed in chronological order (FIFO)</li>
                                <li>- Your monthly limit resets on the 1st of each month</li>
                            </ul>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
