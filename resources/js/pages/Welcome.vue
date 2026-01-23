<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';

const page = usePage();

const animationImages = [
    '/images/animations/Screenshot_20260122_195048-removebg-preview.png',
    '/images/animations/Screenshot_20260122_195145-removebg-preview.png',
    '/images/animations/Screenshot_20260122_195227-removebg-preview.png',
    '/images/animations/Screenshot_20260122_195250-removebg-preview.png',
    '/images/animations/Screenshot_20260122_195303-removebg-preview.png',
    '/images/animations/Screenshot_20260122_195327-removebg-preview.png',
    '/images/animations/Screenshot_20260122_200706-removebg-preview.png',
    '/images/animations/Screenshot_20260122_200756-removebg-preview.png',
    '/images/animations/Screenshot_20260122_200823-removebg-preview.png',
    '/images/animations/Screenshot_20260122_200954-removebg-preview.png',
    '/images/animations/Screenshot_20260122_201005-removebg-preview.png',
    '/images/animations/Screenshot_20260122_201045-removebg-preview.png',
];

interface Flake {
    id: number;
    src: string;
    left: number;
    animationDuration: number;
    delay: number;
    size: number;
}

const flakes = ref<Flake[]>([]);
let flakeId = 0;
let spawnInterval: ReturnType<typeof setInterval> | null = null;

function createFlake() {
    const flake: Flake = {
        id: flakeId++,
        src: animationImages[Math.floor(Math.random() * animationImages.length)],
        left: Math.random() * 100,
        animationDuration: 8 + Math.random() * 6,
        delay: 0,
        size: 40 + Math.random() * 40,
    };
    flakes.value.push(flake);

    setTimeout(() => {
        flakes.value = flakes.value.filter((f) => f.id !== flake.id);
    }, flake.animationDuration * 1000);
}

onMounted(() => {
    for (let i = 0; i < 6; i++) {
        setTimeout(() => createFlake(), i * 800);
    }
    spawnInterval = setInterval(createFlake, 2000);
});

onUnmounted(() => {
    if (spawnInterval) {
        clearInterval(spawnInterval);
    }
});
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="welcome-page">
        <!-- Header section with gradient background -->
        <div class="header">
            <!-- Falling animations -->
            <div class="pointer-events-none fixed inset-0 z-0 overflow-hidden">
                <img
                    v-for="flake in flakes"
                    :key="flake.id"
                    :src="flake.src"
                    class="falling-flake absolute"
                    :style="{
                        left: `${flake.left}%`,
                        width: `${flake.size}px`,
                        height: `${flake.size}px`,
                        animationDuration: `${flake.animationDuration}s`,
                    }"
                />
            </div>

            <header class="relative z-10 w-full max-w-4xl mx-auto px-6 pt-6">
                <nav class="nav-header">
                    <div class="flex items-center gap-2">
                        <AppLogoIcon
                            class="size-8 fill-current text-white"
                        />
                        <span class="font-semibold text-white">Video Requests</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <Link
                            v-if="page.props.auth.user"
                            href="/dashboard"
                            class="inline-block rounded-md border border-white/30 px-5 py-2 text-sm font-medium text-white hover:bg-white/10"
                        >
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                href="/login"
                                class="inline-block rounded-md px-5 py-2 text-sm font-medium text-white hover:bg-white/10"
                            >
                                Log in
                            </Link>
                        </template>
                    </div>
                </nav>
            </header>

            <main class="inner-header flex-center relative z-10">
                <div class="text-center">
                    <h1 class="mb-6 text-4xl font-bold tracking-tight sm:text-5xl text-white">
                        Ask D for video reactions
                    </h1>
                    <p class="mx-auto max-w-2xl text-lg text-white/80 mb-8">
                        Plus c'est con, plus c'est bon.
                    </p>

                    <div class="flex flex-col gap-4 sm:flex-row justify-center">
                        <Button v-if="page.props.auth.user" as-child size="lg" variant="secondary">
                            <Link href="/dashboard">
                                Go to Dashboard
                            </Link>
                        </Button>
                        <Button v-else as="a" href="/auth/patreon" size="lg" variant="secondary" class="gap-2">
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
                </div>
            </main>

            <!-- Waves -->
            <div>
                <svg class="waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                    viewBox="0 24 150 28" preserveAspectRatio="none" shape-rendering="auto">
                    <defs>
                        <path id="gentle-wave" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z" />
                    </defs>
                    <g class="parallax">
                        <use xlink:href="#gentle-wave" x="48" y="0" class="wave-1" />
                        <use xlink:href="#gentle-wave" x="48" y="3" class="wave-2" />
                        <use xlink:href="#gentle-wave" x="48" y="5" class="wave-3" />
                        <use xlink:href="#gentle-wave" x="48" y="7" class="wave-4" />
                    </g>
                </svg>
            </div>
        </div>

        <!-- Content after waves -->
        <div class="content flex-center">
            <footer class="text-center text-sm text-muted-foreground">
                <a :href="page.props.patreonSubscribeUrl" target="_blank" class="hover:underline">Subscribe on Patreon to start requesting videos</a>
            </footer>
        </div>
    </div>
</template>

<style scoped>
.welcome-page {
    min-height: 100vh;
}

.header {
    position: relative;
    text-align: center;
    background: linear-gradient(60deg, rgba(84,58,183,1) 0%, rgba(0,172,193,1) 100%);
    color: white;
}

.inner-header {
    height: 65vh;
    width: 100%;
    margin: 0;
    padding: 0;
}

.flex-center {
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
}

.nav-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.waves {
    position: relative;
    width: 100%;
    height: 15vh;
    margin-bottom: -7px;
    min-height: 100px;
    max-height: 150px;
}

.content {
    position: relative;
    height: 20vh;
    text-align: center;
    background-color: hsl(0 0% 100%);
}

/* Wave fills - Light mode */
.wave-1 {
    fill: rgba(255,255,255,0.7);
}
.wave-2 {
    fill: rgba(255,255,255,0.5);
}
.wave-3 {
    fill: rgba(255,255,255,0.3);
}
.wave-4 {
    fill: hsl(0 0% 100%);
}

/* Wave Animation */
.parallax > use {
    animation: move-forever 25s cubic-bezier(.55,.5,.45,.5) infinite;
}

.parallax > use:nth-child(1) {
    animation-delay: -2s;
    animation-duration: 7s;
}

.parallax > use:nth-child(2) {
    animation-delay: -3s;
    animation-duration: 10s;
}

.parallax > use:nth-child(3) {
    animation-delay: -4s;
    animation-duration: 13s;
}

.parallax > use:nth-child(4) {
    animation-delay: -5s;
    animation-duration: 20s;
}

@keyframes move-forever {
    0% {
        transform: translate3d(-90px,0,0);
    }
    100% {
        transform: translate3d(85px,0,0);
    }
}

/* Falling flakes animation */
.falling-flake {
    top: -100px;
    animation: fall linear forwards, spin linear infinite;
    animation-duration: inherit, 3s;
    object-fit: contain;
}

@keyframes fall {
    0% {
        top: -100px;
        opacity: 0;
    }
    5% {
        opacity: 0.7;
    }
    90% {
        opacity: 0.7;
    }
    100% {
        top: 110vh;
        opacity: 0;
    }
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

/* Mobile responsive */
@media (max-width: 768px) {
    .waves {
        height: 40px;
        min-height: 40px;
    }
    .content {
        height: 30vh;
    }
    .inner-header {
        height: 55vh;
    }
}
</style>

<style>
/* Dark mode styles (unscoped to work with global .dark class) */
.dark .welcome-page .header {
    background: linear-gradient(60deg, rgba(49,33,110,1) 0%, rgba(0,100,115,1) 100%);
}

.dark .welcome-page .content {
    background-color: hsl(0 0% 3.9%);
}

.dark .welcome-page .wave-1 {
    fill: rgba(10,10,10,0.7);
}
.dark .welcome-page .wave-2 {
    fill: rgba(10,10,10,0.5);
}
.dark .welcome-page .wave-3 {
    fill: rgba(10,10,10,0.3);
}
.dark .welcome-page .wave-4 {
    fill: hsl(0 0% 3.9%);
}
</style>
