<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ExternalLink, Sparkles } from 'lucide-vue-next';

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

defineProps<{
    tiers: TierInfo[];
}>();

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
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
                            <Sparkles class="h-8 w-8 text-primary" />
                        </div>
                        <CardTitle class="text-2xl">Subscribe to Request Videos</CardTitle>
                        <CardDescription class="text-base">
                            Become a Patreon supporter to request YouTube videos for me to react to!
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <!-- Tier Information -->
                        <div class="grid gap-4 md:grid-cols-2" v-if="tiers.length > 0">
                            <div
                                v-for="(tier, index) in tiers"
                                :key="index"
                                class="rounded-lg border p-4 text-center"
                            >
                                <div class="text-2xl font-bold text-primary">
                                    {{ tier.amount }}
                                </div>
                                <div class="text-sm text-muted-foreground">per month</div>
                                <div class="mt-2 font-medium">
                                    {{ tier.requests_per_month }} request{{ tier.requests_per_month > 1 ? 's' : '' }}/month
                                </div>
                            </div>
                        </div>

                        <!-- Benefits -->
                        <div class="space-y-3">
                            <h3 class="font-medium">Benefits include:</h3>
                            <ul class="space-y-2 text-muted-foreground">
                                <li class="flex items-center gap-2">
                                    <span class="h-1.5 w-1.5 rounded-full bg-primary" />
                                    Request YouTube videos for reactions
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="h-1.5 w-1.5 rounded-full bg-primary" />
                                    Videos watched in chronological order (FIFO)
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="h-1.5 w-1.5 rounded-full bg-primary" />
                                    Track your request status
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="h-1.5 w-1.5 rounded-full bg-primary" />
                                    View the public video queue
                                </li>
                            </ul>
                        </div>

                        <!-- CTA -->
                        <div class="flex flex-col gap-3 pt-4">
                            <Button
                                as="a"
                                href="https://www.patreon.com"
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
            </div>
        </div>
    </AppLayout>
</template>
