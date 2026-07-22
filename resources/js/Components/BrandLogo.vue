<script setup>
defineProps({
    size: { type: String, default: 'md' }, // sm | md | lg
    withTagline: { type: Boolean, default: false },
});

const iconSizes = { sm: 32, md: 48, lg: 72 };
const textSizes = { sm: 'text-lg', md: 'text-2xl', lg: 'text-4xl' };
</script>

<template>
    <div class="flex flex-col items-center gap-2">
        <svg
            :width="iconSizes[size]"
            :height="iconSizes[size]"
            viewBox="0 0 100 100"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <!-- hand: fanned fingers -->
            <g fill="#f97316">
                <rect x="21" y="8" width="11" height="34" rx="5.5" transform="rotate(-14 26 8)" />
                <rect x="34" y="2" width="11" height="38" rx="5.5" />
                <rect x="47" y="4" width="11" height="36" rx="5.5" transform="rotate(10 52 4)" />
            </g>
            <path
                d="M14 40c0-4 3-7 7-7h4l6 6 6-6h9c4 0 7 3 7 7v10c0 12-9 22-21 22h-3c-8 0-15-6-15-14V40z"
                fill="#f97316"
            />

            <!-- cloche / serving dish -->
            <path
                d="M20 62a20 12 0 0 1 40 0z"
                fill="#fff7ed"
                stroke="#f97316"
                stroke-width="3"
            />
            <rect x="16" y="62" width="48" height="5" rx="2.5" fill="#f97316" />
            <circle cx="40" cy="46" r="2.5" fill="#f97316" />

            <!-- go arrow badge -->
            <circle cx="76" cy="70" r="16" fill="#22c55e" />
            <path d="M71 62l11 8-11 8V62z" fill="#ffffff" />
            <rect x="94" y="61" width="10" height="4" rx="2" fill="#84cc16" />
            <rect x="94" y="69" width="10" height="4" rx="2" fill="#84cc16" />
            <rect x="94" y="77" width="7" height="4" rx="2" fill="#84cc16" />
        </svg>

        <div class="text-center leading-tight">
            <span class="font-extrabold tracking-tight" :class="textSizes[size]">
                <span class="text-green-800 dark:text-green-300">Wawan</span><span
                    class="text-primary-500"
                >GO</span>
            </span>
            <p
                v-if="withTagline"
                class="mt-1 text-xs font-semibold tracking-[0.2em] text-green-700 dark:text-green-400"
            >
                PESAN &bull; BELI &bull; ANTAR
            </p>
        </div>
    </div>
</template>
