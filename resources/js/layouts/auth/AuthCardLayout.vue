<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-[var(--el-fill-color-light)] p-6 md:p-10"
    >
        <div class="flex w-full max-w-md flex-col gap-6">
            <Link
                :href="home()"
                class="flex items-center gap-2 self-center font-medium"
            >
                <div class="flex h-9 w-9 items-center justify-center">
                    <AppLogoIcon class="size-9 fill-current" />
                </div>
            </Link>

            <div class="flex flex-col gap-6">
                <el-card class="rounded-xl">
                    <template #header>
                        <div class="text-center">
                            <h2 class="text-xl font-semibold">{{ title }}</h2>
                            <p class="text-[var(--el-text-color-secondary)]">
                                {{ description }}
                            </p>
                        </div>
                    </template>
                    <slot />
                </el-card>
            </div>
        </div>
    </div>
</template>
