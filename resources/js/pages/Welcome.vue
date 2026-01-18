<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ListVideo, Sparkles, Video } from 'lucide-vue-next';

import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';

const page = usePage();
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div
        class="flex min-h-screen flex-col items-center bg-background p-6 lg:justify-center lg:p-8"
    >
        <header class="mb-6 w-full max-w-4xl">
            <nav class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <AppLogoIcon
                        class="size-8 fill-current text-foreground"
                    />
                    <span class="font-semibold">Video Requests</span>
                </div>
                <div class="flex items-center gap-4">
                    <Link
                        v-if="page.props.auth.user"
                        href="/dashboard"
                        class="inline-block rounded-md border border-border px-5 py-2 text-sm font-medium hover:bg-muted"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            href="/login"
                            class="inline-block rounded-md px-5 py-2 text-sm font-medium hover:bg-muted"
                        >
                            Log in
                        </Link>
                    </template>
                </div>
            </nav>
        </header>

        <main class="flex w-full max-w-4xl flex-1 flex-col items-center justify-center text-center">
            <div class="mb-8">
                <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-primary/10">
                    <Video class="h-10 w-10 text-primary" />
                </div>
                <h1 class="mb-4 text-4xl font-bold tracking-tight sm:text-5xl">
                    Request Videos for Reactions
                </h1>
                <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
                    Patreon subscribers can request YouTube videos for me to react to.
                    Videos are watched in the order they're requested.
                </p>
            </div>

            <div class="flex flex-col gap-4 sm:flex-row">
                <Button v-if="page.props.auth.user" as-child size="lg">
                    <Link href="/dashboard">
                        Go to Dashboard
                    </Link>
                </Button>
                <Button v-else as="a" href="/auth/patreon" size="lg" class="gap-2">
                    <svg
                        viewBox="0 0 24 24"
                        class="h-5 w-5"
                        fill="currentColor"
                    >
                        <path
                            d="M15.386.524c-4.764 0-8.64 3.876-8.64 8.64 0 4.75 3.876 8.613 8.64 8.613 4.75 0 8.614-3.864 8.614-8.613C24 4.4 20.136.524 15.386.524M.003 23.537h4.22V.524H.003"
                        />
                    </svg>
                    Login with Patreon
                </Button>
            </div>

            <!-- Features -->
            <div class="mt-16 grid gap-8 sm:grid-cols-3">
                <div class="text-center">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-muted">
                        <Sparkles class="h-6 w-6 text-muted-foreground" />
                    </div>
                    <h3 class="mb-2 font-semibold">Tier-Based Limits</h3>
                    <p class="text-sm text-muted-foreground">
                        Higher tiers get more monthly requests
                    </p>
                </div>
                <div class="text-center">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-muted">
                        <ListVideo class="h-6 w-6 text-muted-foreground" />
                    </div>
                    <h3 class="mb-2 font-semibold">Fair Queue System</h3>
                    <p class="text-sm text-muted-foreground">
                        Videos watched in chronological order (FIFO)
                    </p>
                </div>
                <div class="text-center">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-muted">
                        <Video class="h-6 w-6 text-muted-foreground" />
                    </div>
                    <h3 class="mb-2 font-semibold">Track Progress</h3>
                    <p class="text-sm text-muted-foreground">
                        See your request status in real-time
                    </p>
                </div>
            </div>
        </main>

        <footer class="mt-12 text-center text-sm text-muted-foreground">
            <p>Subscribe on Patreon to start requesting videos</p>
        </footer>
    </div>
</template>
