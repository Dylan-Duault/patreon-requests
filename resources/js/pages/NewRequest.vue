<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { AlertCircle, Video } from 'lucide-vue-next';

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
import { type BreadcrumbItem } from '@/types';

defineProps<{
    remainingRequests: number;
    monthlyLimit: number;
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
});

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
                                You have <strong>{{ remainingRequests }}</strong> of
                                <strong>{{ monthlyLimit }}</strong> requests remaining this month.
                            </AlertDescription>
                        </Alert>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="space-y-2">
                                <Label for="youtube_url">YouTube URL</Label>
                                <Input
                                    id="youtube_url"
                                    v-model="form.youtube_url"
                                    type="url"
                                    placeholder="https://www.youtube.com/watch?v=..."
                                    required
                                    :disabled="form.processing"
                                    autofocus
                                />
                                <InputError :message="form.errors.youtube_url" />
                                <p class="text-xs text-muted-foreground">
                                    Supported formats: youtube.com/watch, youtu.be, youtube.com/shorts
                                </p>
                            </div>

                            <div class="flex gap-3">
                                <Button
                                    type="submit"
                                    class="flex-1"
                                    :disabled="form.processing || !form.youtube_url"
                                >
                                    <Spinner v-if="form.processing" class="mr-2" />
                                    Submit Request
                                </Button>
                            </div>
                        </form>

                        <!-- Guidelines -->
                        <div class="mt-6 pt-6 border-t">
                            <h4 class="text-sm font-medium mb-2">Guidelines</h4>
                            <ul class="text-xs text-muted-foreground space-y-1">
                                <li>- Only YouTube videos are accepted</li>
                                <li>- Videos already in the queue cannot be requested again</li>
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
