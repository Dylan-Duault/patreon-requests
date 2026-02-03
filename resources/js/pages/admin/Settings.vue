<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';
import { CheckboxIndicator, CheckboxRoot, ToastProvider, ToastRoot, ToastTitle, ToastViewport } from 'reka-ui';
import { ref } from 'vue';

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

interface Props {
    settings: {
        show_request_list: boolean;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Admin',
        href: '/admin/requests',
    },
    {
        title: 'Settings',
        href: '/admin/settings',
    },
];

const form = useForm({
    show_request_list: props.settings.show_request_list,
});

const toastOpen = ref(false);

const saveSettings = () => {
    form.patch('/admin/settings', {
        preserveScroll: true,
        onSuccess: () => {
            toastOpen.value = true;
        },
    });
};
</script>

<template>
    <ToastProvider>
        <AppLayout :breadcrumbs="breadcrumbs">
            <Head title="Admin Settings" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Settings</h1>
                <p class="text-muted-foreground">
                    Configure system-wide settings and features
                </p>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>General Settings</CardTitle>
                    <CardDescription>
                        Configure system-wide settings and features
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <CheckboxRoot
                            v-model="form.show_request_list"
                            class="peer border-input data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground data-[state=checked]:border-primary focus-visible:border-ring focus-visible:ring-ring/50 size-4 shrink-0 rounded-[4px] border shadow-xs transition-shadow outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <CheckboxIndicator class="grid place-content-center text-current transition-none">
                                <Check class="size-3.5" />
                            </CheckboxIndicator>
                        </CheckboxRoot>
                        <div class="grid gap-1.5 leading-none">
                            <span class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Show request list to members
                            </span>
                            <p class="text-muted-foreground text-sm">
                                When enabled, patrons can view the video queue
                                at /requests. When disabled, the page is not
                                accessible and the link is hidden from the
                                sidebar.
                            </p>
                        </div>
                    </label>

                    <div class="flex gap-3">
                        <Button
                            @click="saveSettings"
                            :disabled="form.processing"
                        >
                            Save Settings
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>

        <ToastRoot
            v-model:open="toastOpen"
            class="bg-background text-foreground rounded-lg shadow-lg border border-border p-4 grid grid-cols-[auto_max-content] gap-x-4 items-center data-[state=open]:animate-in data-[state=closed]:animate-out data-[swipe=end]:animate-out data-[state=closed]:fade-out-80 data-[state=closed]:slide-out-to-right-full data-[state=open]:slide-in-from-top-full data-[state=open]:sm:slide-in-from-bottom-full"
        >
            <ToastTitle class="font-medium text-sm text-foreground">
                Settings saved successfully
            </ToastTitle>
        </ToastRoot>
        <ToastViewport class="fixed bottom-0 right-0 flex flex-col p-6 gap-2 w-[390px] max-w-[100vw] m-0 list-none z-[2147483647] outline-none" />
    </AppLayout>
    </ToastProvider>
</template>
