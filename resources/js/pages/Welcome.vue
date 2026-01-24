<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';

// Constants
const ANIMATION_IMAGES = [
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
] as const;

const FLAKE_CONFIG = {
    initialCount: 8,
    spawnDelay: 800,
    spawnInterval: 2000,
    minDuration: 8,
    maxDuration: 14,
    minSize: 30,
    maxSize: 60,
} as const;

// Types
interface Flake {
    id: number;
    src: string;
    left: number;
    duration: number;
    size: number;
}

// Page props
const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth.user);
const patreonSubscribeUrl = computed(() => page.props.patreonSubscribeUrl as string);

// Flake animation state
const flakes = ref<Flake[]>([]);
let flakeIdCounter = 0;
let spawnInterval: ReturnType<typeof setInterval> | null = null;
const flakeTimeouts = new Set<ReturnType<typeof setTimeout>>();

function getRandomImage(): string {
    return ANIMATION_IMAGES[Math.floor(Math.random() * ANIMATION_IMAGES.length)];
}

function createFlake(): void {
    const duration = FLAKE_CONFIG.minDuration + Math.random() * (FLAKE_CONFIG.maxDuration - FLAKE_CONFIG.minDuration);

    const flake: Flake = {
        id: flakeIdCounter++,
        src: getRandomImage(),
        left: Math.random() * 100,
        duration,
        size: FLAKE_CONFIG.minSize + Math.random() * (FLAKE_CONFIG.maxSize - FLAKE_CONFIG.minSize),
    };

    flakes.value.push(flake);

    const timeout = setTimeout(() => {
        flakes.value = flakes.value.filter((f) => f.id !== flake.id);
        flakeTimeouts.delete(timeout);
    }, duration * 1000);

    flakeTimeouts.add(timeout);
}

function cleanup(): void {
    if (spawnInterval) {
        clearInterval(spawnInterval);
        spawnInterval = null;
    }
    flakeTimeouts.forEach((timeout) => clearTimeout(timeout));
    flakeTimeouts.clear();
}

onMounted(() => {
    // Stagger initial flakes
    for (let i = 0; i < FLAKE_CONFIG.initialCount; i++) {
        const timeout = setTimeout(() => createFlake(), i * FLAKE_CONFIG.spawnDelay);
        flakeTimeouts.add(timeout);
    }
    spawnInterval = setInterval(createFlake, FLAKE_CONFIG.spawnInterval);
});

onUnmounted(cleanup);
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="flex h-screen flex-col overflow-hidden">
        <!-- Hero section -->
        <div class="hero-gradient relative flex flex-1 flex-col overflow-hidden text-white">
            <!-- Falling animations -->
            <div
                class="pointer-events-none absolute inset-0 z-0"
                aria-hidden="true"
            >
                <img
                    v-for="flake in flakes"
                    :key="flake.id"
                    :src="flake.src"
                    alt=""
                    class="falling-flake absolute object-contain"
                    :style="{
                        left: `${flake.left}%`,
                        width: `${flake.size}px`,
                        height: `${flake.size}px`,
                        animationDuration: `${flake.duration}s`,
                    }"
                />
            </div>

            <!-- Navigation -->
            <header class="relative z-10 mx-auto w-full max-w-4xl px-6 pt-6">
                <nav class="flex items-center justify-between">
                    <Link href="/" class="flex items-center gap-2">
                        <AppLogoIcon class="size-8 fill-current" />
                        <span class="font-semibold">Video Requests</span>
                    </Link>

                    <div class="flex items-center gap-4">
                        <Link
                            v-if="isAuthenticated"
                            href="/dashboard"
                            class="rounded-md border border-white/30 px-5 py-2 text-sm font-medium transition-colors hover:bg-white/10"
                        >
                            Dashboard
                        </Link>
                        <Link
                            v-else
                            href="/login"
                            class="rounded-md px-5 py-2 text-sm font-medium transition-colors hover:bg-white/10"
                        >
                            Log in
                        </Link>
                    </div>
                </nav>
            </header>

            <!-- Hero content -->
            <main class="relative z-10 flex flex-1 w-full items-center justify-center px-6">
                <div class="text-center">
                    <h1 class="mb-6 text-4xl font-bold tracking-tight sm:text-5xl">
                        Ask D for video reactions
                    </h1>
                    <p class="mx-auto mb-8 max-w-2xl text-lg text-white/80">
                        Plus c'est con, plus c'est bon.
                    </p>

                    <div class="flex flex-col justify-center gap-4 sm:flex-row">
                        <Button
                            v-if="isAuthenticated"
                            as-child
                            size="lg"
                            variant="secondary"
                        >
                            <Link href="/dashboard">Go to Dashboard</Link>
                        </Button>
                        <Button
                            v-else
                            as="a"
                            href="/auth/patreon"
                            size="lg"
                            variant="secondary"
                            class="gap-2"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                class="size-5"
                                fill="currentColor"
                                aria-hidden="true"
                            >
                                <path d="M15.386.524c-4.764 0-8.64 3.876-8.64 8.64 0 4.75 3.876 8.613 8.64 8.613 4.75 0 8.614-3.864 8.614-8.613C24 4.4 20.136.524 15.386.524M.003 23.537h4.22V.524H.003" />
                            </svg>
                            Login with Patreon
                        </Button>
                    </div>
                </div>
            </main>

            <!-- Waves -->
            <svg
                class="waves"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 24 150 28"
                preserveAspectRatio="none"
                aria-hidden="true"
            >
                <defs>
                    <path
                        id="wave-path"
                        d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z"
                    />
                </defs>
                <g class="parallax">
                    <use href="#wave-path" x="48" y="0" class="wave wave-1" />
                    <use href="#wave-path" x="48" y="3" class="wave wave-2" />
                    <use href="#wave-path" x="48" y="5" class="wave wave-3" />
                    <use href="#wave-path" x="48" y="7" class="wave wave-4" />
                </g>
            </svg>
        </div>

        <!-- Footer section -->
        <footer class="flex shrink-0 items-center justify-center bg-background px-6 py-8 text-center">
            <p class="text-sm text-muted-foreground">
                <a
                    :href="patreonSubscribeUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="transition-colors hover:text-foreground hover:underline"
                >
                    Subscribe on Patreon to start requesting videos
                </a>
            </p>
        </footer>
    </div>
</template>

<style scoped>
/* Hero gradient - uses CSS custom properties for dark mode */
.hero-gradient {
    background: linear-gradient(
        60deg,
        var(--hero-gradient-start, rgb(84, 58, 183)) 0%,
        var(--hero-gradient-end, rgb(0, 172, 193)) 100%
    );
}

/* Waves */
.waves {
    position: relative;
    width: 100%;
    height: 15vh;
    min-height: 100px;
    max-height: 150px;
    margin-bottom: -7px;
}

.wave {
    fill: var(--wave-color, white);
}

.wave-1 {
    --wave-color: rgba(255, 255, 255, 0.7);
}

.wave-2 {
    --wave-color: rgba(255, 255, 255, 0.5);
}

.wave-3 {
    --wave-color: rgba(255, 255, 255, 0.3);
}

.wave-4 {
    --wave-color: white;
}

/* Wave animation */
.parallax > use {
    animation: wave-move 25s cubic-bezier(0.55, 0.5, 0.45, 0.5) infinite;
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

@keyframes wave-move {
    0% {
        transform: translate3d(-90px, 0, 0);
    }
    100% {
        transform: translate3d(85px, 0, 0);
    }
}

/* Falling flakes animation */
.falling-flake {
    top: -100px;
    animation:
        flake-fall linear forwards,
        flake-spin linear infinite;
    animation-duration: inherit, 3s;
}

@keyframes flake-fall {
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

@keyframes flake-spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .waves {
        height: 40px;
        min-height: 40px;
    }
}
</style>

<style>
/* Dark mode - unscoped for global .dark class compatibility */
.dark .hero-gradient {
    --hero-gradient-start: rgb(49, 33, 110);
    --hero-gradient-end: rgb(0, 100, 115);
}

.dark .wave-1 {
    --wave-color: rgba(10, 10, 10, 0.7);
}

.dark .wave-2 {
    --wave-color: rgba(10, 10, 10, 0.5);
}

.dark .wave-3 {
    --wave-color: rgba(10, 10, 10, 0.3);
}

.dark .wave-4 {
    --wave-color: hsl(0 0% 3.9%);
}
</style>
