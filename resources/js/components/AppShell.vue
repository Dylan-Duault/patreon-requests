<script setup lang="ts">
import { provide, ref } from 'vue';

interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();

const sidebarCollapsed = ref(false);
const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
};

provide('sidebarCollapsed', sidebarCollapsed);
provide('toggleSidebar', toggleSidebar);
</script>

<template>
    <el-container class="min-h-screen">
        <slot />
    </el-container>
</template>
