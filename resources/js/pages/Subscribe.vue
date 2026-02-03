<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ExternalLink, RefreshCw, Sparkles } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted } from 'vue';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';

interface TierInfo {
    amount: string;
    requests_per_month: number;
}

const props = defineProps<{
    tiers: TierInfo[];
    subscribeUrl: string;
    cooldownRemaining: number;
}>();

const page = usePage();
const cooldown = ref(props.cooldownRemaining);
let cooldownInterval: ReturnType<typeof setInterval> | null = null;

const form = useForm({});

const refreshSubscription = () => {
    if (cooldown.value > 0) return;

    form.post('/subscribe/refresh', {
        preserveScroll: true,
        onSuccess: () => {
            cooldown.value = 15;
            startCooldownTimer();
        },
    });
};

const startCooldownTimer = () => {
    if (cooldownInterval) {
        clearInterval(cooldownInterval);
    }
    cooldownInterval = setInterval(() => {
        if (cooldown.value > 0) {
            cooldown.value--;
        } else if (cooldownInterval) {
            clearInterval(cooldownInterval);
            cooldownInterval = null;
        }
    }, 1000);
};

onMounted(() => {
    if (cooldown.value > 0) {
        startCooldownTimer();
    }
});

onUnmounted(() => {
    if (cooldownInterval) {
        clearInterval(cooldownInterval);
    }
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Subscribe',
        href: '/subscribe',
    },
];
</script>

<template>
    <Head title="Subscribe" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col items-center justify-center gap-6 p-4 md:p-6">
            <div class="max-w-2xl w-full">
                <Card>
                    <CardHeader class="text-center">
                        <div class="mx-auto mb-4 flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-full bg-primary/10">
                            <Sparkles class="h-7 w-7 sm:h-8 sm:w-8 text-primary" />
                        </div>
                        <CardTitle class="text-xl sm:text-2xl">Subscribe to Request Videos</CardTitle>
                        <CardDescription class="text-sm sm:text-base">
                            Become a Patreon supporter to request YouTube videos for me to react to!
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="flex flex-col gap-3">
                            <Button
                                as="a"
                                :href="subscribeUrl"
                                target="_blank"
                                rel="noopener noreferrer"
                                size="lg"
                                class="w-full gap-2"
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
                                Subscribe on Patreon
                                <ExternalLink class="h-4 w-4" />
                            </Button>
                            <Button variant="outline" as-child>
                                <Link href="/dashboard">
                                    Back to Dashboard
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Just Subscribed Card -->
                <Card class="mt-4">
                    <CardHeader class="pb-3">
                        <CardTitle class="text-lg">Just subscribed?</CardTitle>
                        <CardDescription>
                            If you just completed your subscription, click below to refresh your status.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <!-- Flash Messages -->
                        <div
                            v-if="page.props.flash?.error"
                            class="mb-4 rounded-md bg-red-50 p-3 text-sm font-medium text-red-600 dark:bg-red-900/20 dark:text-red-400"
                        >
                            {{ page.props.flash.error }}
                        </div>

                        <Button
                            @click="refreshSubscription"
                            :disabled="form.processing || cooldown > 0"
                            variant="outline"
                            class="w-full gap-2"
                        >
                            <RefreshCw
                                class="h-4 w-4"
                                :class="{ 'animate-spin': form.processing }"
                            />
                            <span v-if="form.processing">Checking...</span>
                            <span v-else-if="cooldown > 0">Wait {{ cooldown }}s</span>
                            <span v-else>Refresh subscription status</span>
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
