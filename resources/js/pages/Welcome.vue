<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { VideoCamera } from '@element-plus/icons-vue';

import AppLogoIcon from '@/components/AppLogoIcon.vue';

const page = usePage();
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div
        class="flex min-h-screen flex-col items-center bg-[var(--el-bg-color)] p-6 lg:justify-center lg:p-8"
    >
        <header class="mb-6 w-full max-w-4xl">
            <nav class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <AppLogoIcon class="size-8 fill-current" />
                    <span class="font-semibold">Request Manager</span>
                </div>
                <div class="flex items-center gap-4">
                    <Link
                        v-if="page.props.auth.user"
                        href="/dashboard"
                    >
                        <el-button>Dashboard</el-button>
                    </Link>
                    <template v-else>
                        <Link href="/login">
                            <el-button text>Log in</el-button>
                        </Link>
                    </template>
                </div>
            </nav>
        </header>

        <main class="flex w-full max-w-4xl flex-1 flex-col items-center justify-center text-center">
            <div class="mb-8">
                <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-[var(--el-color-primary-light-9)]">
                    <el-icon :size="40" class="text-[var(--el-color-primary)]">
                        <VideoCamera />
                    </el-icon>
                </div>
                <h1 class="mb-4 text-4xl font-bold tracking-tight sm:text-5xl">
                    Request Videos for Reactions
                </h1>
                <p class="mx-auto max-w-2xl text-lg text-[var(--el-text-color-secondary)]">
                    Patreon subscribers can request YouTube videos for me to react to.
                    Videos are watched in the order they're requested.
                </p>
            </div>

            <div class="flex flex-col gap-4 sm:flex-row">
                <el-button v-if="page.props.auth.user" type="primary" size="large">
                    <Link href="/dashboard">
                        Go to Dashboard
                    </Link>
                </el-button>
                <el-button v-else type="primary" size="large" tag="a" href="/auth/patreon">
                    <svg
                        viewBox="0 0 24 24"
                        class="h-5 w-5 mr-2"
                        fill="currentColor"
                    >
                        <path
                            d="M15.386.524c-4.764 0-8.64 3.876-8.64 8.64 0 4.75 3.876 8.613 8.64 8.613 4.75 0 8.614-3.864 8.614-8.613C24 4.4 20.136.524 15.386.524M.003 23.537h4.22V.524H.003"
                        />
                    </svg>
                    Login with Patreon
                </el-button>
            </div>

            <!-- Features -->
            <div class="mt-16 grid gap-8 sm:grid-cols-3">
                <div class="text-center">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-[var(--el-fill-color)]">
                        <el-icon :size="24" class="text-[var(--el-text-color-secondary)]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/></svg>
                        </el-icon>
                    </div>
                    <h3 class="mb-2 font-semibold">Tier-Based Limits</h3>
                    <p class="text-sm text-[var(--el-text-color-secondary)]">
                        Higher tiers get more monthly requests
                    </p>
                </div>
                <div class="text-center">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-[var(--el-fill-color)]">
                        <el-icon :size="24" class="text-[var(--el-text-color-secondary)]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 12H3"/><path d="M16 6H3"/><path d="M12 18H3"/><path d="m16 12 5 3-5 3v-6Z"/></svg>
                        </el-icon>
                    </div>
                    <h3 class="mb-2 font-semibold">Fair Queue System</h3>
                    <p class="text-sm text-[var(--el-text-color-secondary)]">
                        Videos watched in chronological order (FIFO)
                    </p>
                </div>
                <div class="text-center">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-[var(--el-fill-color)]">
                        <el-icon :size="24" class="text-[var(--el-text-color-secondary)]">
                            <VideoCamera />
                        </el-icon>
                    </div>
                    <h3 class="mb-2 font-semibold">Track Progress</h3>
                    <p class="text-sm text-[var(--el-text-color-secondary)]">
                        See your request status in real-time
                    </p>
                </div>
            </div>
        </main>

        <footer class="mt-12 text-center text-sm text-[var(--el-text-color-secondary)]">
            <p>Subscribe on Patreon to start requesting videos</p>
        </footer>
    </div>
</template>
