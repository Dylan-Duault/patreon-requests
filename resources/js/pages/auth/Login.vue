<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { home } from '@/routes';

defineProps<{
    status?: string;
    error?: string;
}>();
</script>

<template>
    <Head title="Log in" />
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10"
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
                            <AppLogoIcon
                                class="size-9 fill-current text-[var(--foreground)] dark:text-white"
                            />
                        </div>
                        <span class="sr-only">Video Request Platform</span>
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="text-xl font-medium">
                            Welcome Back
                        </h1>
                        <p class="text-center text-sm text-muted-foreground">
                            Sign in with your Patreon account to request videos
                        </p>
                    </div>
                </div>

                <div
                    v-if="status"
                    class="mb-4 rounded-md bg-green-50 p-3 text-center text-sm font-medium text-green-600 dark:bg-green-900/20 dark:text-green-400"
                >
                    {{ status }}
                </div>

                <div
                    v-if="error"
                    class="mb-4 rounded-md bg-red-50 p-3 text-center text-sm font-medium text-red-600 dark:bg-red-900/20 dark:text-red-400"
                >
                    {{ error }}
                </div>

                <Button
                    as="a"
                    href="/auth/patreon"
                    class="w-full gap-2"
                    size="lg"
                >
                    <svg
                        viewBox="0 0 24 24"
                        class="h-5 w-5"
                        fill="currentColor"
                    >
                        <path
                            d="M15.386.524c-4.764 0-8.64 3.876-8.64 8.64 0 4.75 3.876 8.613 8.64 8.613 4.75 0 8.614-3.864 8.614-8.613C24 4.4 20.136.524 15.386.524M.003 23.537h4.22V.524H.003"
                        />
                    </svg>
                    Continue with Patreon
                </Button>

                <p class="text-center text-xs text-muted-foreground">
                    By continuing, you agree to authenticate via Patreon. Only
                    Patreon subscribers can request videos.
                </p>
            </div>
        </div>
    </div>
</template>
