<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    variant?: 'header' | 'sidebar';
    class?: string;
}

const props = defineProps<Props>();
const className = computed(() => props.class);
</script>

<template>
    <el-main :class="className" class="p-0">
        <slot />
    </el-main>
</template>
