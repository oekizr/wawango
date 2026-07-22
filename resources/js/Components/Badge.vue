<script setup>
import { computed } from 'vue';

const props = defineProps({
    tone: {
        type: String,
        default: 'gray', // gray | green | red | yellow | blue
    },
});

const classes = computed(() => ({
    gray: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
    green: 'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-200',
    red: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
    yellow: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
    blue: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
}[props.tone]));
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
        :class="classes"
    >
        <slot />
    </span>
</template>
