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
    <div
        class="relative flex min-h-screen flex-col items-center overflow-hidden bg-background p-6 lg:justify-center lg:p-8"
    >
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

        <header class="relative z-10 mb-6 w-full max-w-4xl">
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

        <main class="relative z-10 flex w-full max-w-4xl flex-1 flex-col items-center justify-center text-center">
            <div class="mb-8">
                <h1 class="mb-6 text-4xl font-bold tracking-tight sm:text-5xl">
                    Ask D for video reactions
                </h1>
                <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
                    Plus c'est con, plus c'est bon.
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
        </main>

        <footer class="relative z-10 mt-12 text-center text-sm text-muted-foreground">
            <a :href="page.props.patreonSubscribeUrl" target="_blank">Subscribe on Patreon to start requesting videos</a>
        </footer>
    </div>
</template>

<style scoped>
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
</style>
