<script setup>
import { computed } from 'vue';

const props = defineProps({
    phone: { type: String, default: null },
});

// Normalisasi nomor HP Indonesia ke format wa.me (628xxxx, tanpa spasi/simbol).
const waHref = computed(() => {
    if (!props.phone) return null;

    let digits = props.phone.replace(/\D/g, '');

    if (digits.startsWith('0')) {
        digits = '62' + digits.slice(1);
    } else if (!digits.startsWith('62')) {
        digits = '62' + digits;
    }

    return `https://wa.me/${digits}`;
});
</script>

<template>
    <a
        v-if="waHref"
        :href="waHref"
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex items-center gap-1.5 text-primary-600 hover:underline dark:text-primary-400"
    >
        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
            <path
                d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.44 1.32 4.94L2 22l5.29-1.39a9.9 9.9 0 004.75 1.21h.01c5.46 0 9.9-4.45 9.9-9.91C21.96 6.45 17.51 2 12.04 2zm5.8 14.03c-.24.68-1.4 1.3-1.93 1.38-.5.08-1.12.11-1.8-.11-.42-.13-.95-.31-1.64-.6-2.88-1.24-4.76-4.14-4.9-4.33-.14-.19-1.17-1.56-1.17-2.98s.73-2.11 1-2.4c.24-.27.53-.33.7-.33.18 0 .35 0 .5.01.16.01.38-.06.6.45.24.56.8 1.95.87 2.09.07.14.12.31.02.5-.1.19-.15.31-.29.48-.14.17-.3.37-.43.5-.14.14-.29.29-.13.57.17.28.75 1.24 1.61 2 1.11.98 2.04 1.29 2.33 1.43.29.14.46.12.63-.07.17-.19.72-.83.91-1.12.19-.29.38-.24.63-.14.26.1 1.64.77 1.92.91.29.14.48.21.55.33.07.12.07.68-.17 1.36z"
            />
        </svg>
        {{ phone }}
    </a>
    <span v-else class="text-gray-400">-</span>
</template>
