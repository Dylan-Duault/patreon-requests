<script setup lang="ts">
import { computed } from 'vue';

import { useInitials } from '@/composables/useInitials';
import type { User } from '@/types';

interface Props {
    user: User;
    showEmail?: boolean;
    collapsed?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
    collapsed: false,
});

const { getInitials } = useInitials();

const showAvatar = computed(
    () => props.user.avatar && props.user.avatar !== '',
);
</script>

<template>
    <el-avatar
        :size="32"
        :src="showAvatar ? user.avatar! : undefined"
        class="flex-shrink-0"
    >
        {{ getInitials(user.name) }}
    </el-avatar>

    <div v-if="!collapsed" class="grid flex-1 text-left text-sm leading-tight min-w-0">
        <span class="truncate font-medium">{{ user.name }}</span>
        <span v-if="showEmail" class="truncate text-xs text-[var(--el-text-color-secondary)]">
            {{ user.email }}
        </span>
    </div>
</template>
