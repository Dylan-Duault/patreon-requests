<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { VideoCamera, WarningFilled } from '@element-plus/icons-vue';

import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
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
                <el-card shadow="never">
                    <template #header>
                        <div class="text-center">
                            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[var(--el-color-primary-light-9)]">
                                <el-icon :size="32" class="text-[var(--el-color-primary)]">
                                    <VideoCamera />
                                </el-icon>
                            </div>
                            <h2 class="text-2xl font-semibold">Request a Video</h2>
                            <p class="text-[var(--el-text-color-secondary)]">
                                Submit a YouTube video for me to react to
                            </p>
                        </div>
                    </template>

                    <!-- Remaining Requests Info -->
                    <el-alert
                        type="info"
                        :closable="false"
                        class="mb-6"
                    >
                        <template #title>
                            <span>
                                You have <strong>{{ remainingRequests }}</strong> of
                                <strong>{{ monthlyLimit }}</strong> requests remaining this month.
                            </span>
                        </template>
                    </el-alert>

                    <el-form @submit.prevent="submit" label-position="top">
                        <el-form-item label="YouTube URL">
                            <el-input
                                v-model="form.youtube_url"
                                type="url"
                                placeholder="https://www.youtube.com/watch?v=..."
                                :disabled="form.processing"
                                autofocus
                                size="large"
                            />
                            <InputError :message="form.errors.youtube_url" />
                            <p class="text-xs text-[var(--el-text-color-secondary)] mt-1">
                                Supported formats: youtube.com/watch, youtu.be, youtube.com/shorts
                            </p>
                        </el-form-item>

                        <el-form-item>
                            <el-button
                                type="primary"
                                native-type="submit"
                                :loading="form.processing"
                                :disabled="!form.youtube_url"
                                class="w-full"
                                size="large"
                            >
                                Submit Request
                            </el-button>
                        </el-form-item>
                    </el-form>

                    <!-- Guidelines -->
                    <el-divider />
                    <div>
                        <h4 class="text-sm font-medium mb-2">Guidelines</h4>
                        <ul class="text-xs text-[var(--el-text-color-secondary)] space-y-1">
                            <li>- Only YouTube videos are accepted</li>
                            <li>- Videos already in the queue cannot be requested again</li>
                            <li>- Requests are processed in chronological order (FIFO)</li>
                            <li>- Your monthly limit resets on the 1st of each month</li>
                        </ul>
                    </div>
                </el-card>
            </div>
        </div>
    </AppLayout>
</template>
