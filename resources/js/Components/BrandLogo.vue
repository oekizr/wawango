<script setup>
import logoUrl from '@/assets/wawango-logo.png';

defineProps({
    size: { type: String, default: 'md' }, // sm | md | lg
});

// Logo image already includes the wordmark + tagline baked in, so size
// scales the whole lockup rather than just an icon.
const widths = { sm: 120, md: 176, lg: 240 };
</script>

<template>
    <img :src="logoUrl" :width="widths[size]" alt="WawanGo" />
</template>
