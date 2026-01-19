<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    status?: string;
    error?: string;
}>();
</script>

<template>
    <Head title="Log in" />
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-[var(--el-bg-color)] p-6 md:p-10"
    >
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link
                        :href="home()"
                        class="flex flex-col items-center gap-2 font-medium"
                    >
                        <div
                            class="mb-1 flex h-9 w-9 items-center justify-center rounded-md"
                        >
                            <AppLogoIcon class="size-9 fill-current" />
                        </div>
                        <span class="sr-only">Video Request Platform</span>
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="text-xl font-medium">
                            Welcome Back
                        </h1>
                        <p class="text-center text-sm text-[var(--el-text-color-secondary)]">
                            Sign in with your Patreon account to request videos
                        </p>
                    </div>
                </div>

                <el-alert
                    v-if="status"
                    :title="status"
                    type="success"
                    :closable="false"
                    class="mb-4"
                />

                <el-alert
                    v-if="error"
                    :title="error"
                    type="error"
                    :closable="false"
                    class="mb-4"
                />

                <el-button
                    type="primary"
                    size="large"
                    tag="a"
                    href="/auth/patreon"
                    class="w-full"
                >
                    <svg
                        viewBox="0 0 24 24"
                        class="h-5 w-5 mr-2"
                        fill="currentColor"
                    >
                        <path
                            d="M15.386.524c-4.764 0-8.64 3.876-8.64 8.64 0 4.75 3.876 8.613 8.64 8.613 4.75 0 8.614-3.864 8.614-8.613C24 4.4 20.136.524 15.386.524M.003 23.537h4.22V.524H.003"
                        />
                    </svg>
                    Continue with Patreon
                </el-button>

                <p class="text-center text-xs text-[var(--el-text-color-secondary)]">
                    By continuing, you agree to authenticate via Patreon. Only
                    Patreon subscribers can request videos.
                </p>
            </div>
        </div>
    </div>
</template>
