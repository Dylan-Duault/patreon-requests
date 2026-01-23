<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { AlertTriangle, ArrowLeft, Calendar } from 'lucide-vue-next';

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

defineProps<{
    monthlyLimit: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Request Limit Reached',
        href: '/requests/new',
    },
];
</script>

<template>
    <Head title="Request Limit Reached" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col items-center justify-center gap-6 p-4 md:p-6">
            <div class="max-w-lg w-full">
                <Card>
                    <CardHeader class="text-center">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                            <AlertTriangle class="h-8 w-8 text-amber-600 dark:text-amber-400" />
                        </div>
                        <CardTitle class="text-2xl">No Credits Remaining</CardTitle>
                        <CardDescription class="text-base">
                            You've used all your available credits
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="rounded-lg bg-muted p-4 text-center">
                            <p class="text-sm text-muted-foreground mb-1">Credit balance</p>
                            <p class="text-3xl font-bold">0</p>
                            <p class="text-sm text-muted-foreground mt-1">credits remaining</p>
                        </div>

                        <div class="flex items-start gap-3 rounded-lg border p-4">
                            <Calendar class="h-5 w-5 text-muted-foreground mt-0.5" />
                            <div>
                                <p class="font-medium">When do I get more credits?</p>
                                <p class="text-sm text-muted-foreground">
                                    New credits are added each month on subscription renewal.
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3">
                            <Button as-child variant="default">
                                <Link href="/requests">
                                    View Video Queue
                                </Link>
                            </Button>
                            <Button as-child variant="outline">
                                <Link href="/my-requests">
                                    <ArrowLeft class="mr-2 h-4 w-4" />
                                    View My Requests
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
