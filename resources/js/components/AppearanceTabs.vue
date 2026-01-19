<script setup lang="ts">
import { Sunny, Moon, Monitor } from '@element-plus/icons-vue';

import { useAppearance } from '@/composables/useAppearance';

const { appearance, updateAppearance } = useAppearance();

const tabs = [
    { value: 'light', Icon: Sunny, label: 'Light' },
    { value: 'dark', Icon: Moon, label: 'Dark' },
    { value: 'system', Icon: Monitor, label: 'System' },
] as const;
</script>

<template>
    <el-radio-group v-model="appearance" @change="updateAppearance">
        <el-radio-button
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            :value="value"
        >
            <el-icon class="mr-1"><component :is="Icon" /></el-icon>
            {{ label }}
        </el-radio-button>
    </el-radio-group>
</template>
