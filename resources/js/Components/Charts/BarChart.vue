<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    title: { type: String, required: true },
    labels: { type: Array, required: true }, // string[]
    values: { type: Array, required: true }, // number[]
    formatValue: { type: Function, default: (v) => new Intl.NumberFormat('id-ID').format(v) },
});

const showTable = ref(false);
const hovered = ref(null);

const max = computed(() => Math.max(1, ...props.values));
const maxIndex = computed(() => props.values.indexOf(max.value));

// viewBox coordinate space
const width = 600;
const height = 220;
const paddingLeft = 8;
const paddingRight = 8;
const paddingTop = 28;
const paddingBottom = 28;
const plotWidth = width - paddingLeft - paddingRight;
const plotHeight = height - paddingTop - paddingBottom;

const barSlot = computed(() => plotWidth / props.values.length);
const barWidth = computed(() => Math.min(24, barSlot.value * 0.6));
const gap = 2;

function barX(i) {
    return paddingLeft + i * barSlot.value + (barSlot.value - barWidth.value) / 2;
}

function barHeight(v) {
    return max.value === 0 ? 0 : (v / max.value) * plotHeight;
}

function barY(v) {
    return paddingTop + (plotHeight - barHeight(v));
}

const gridLines = [0, 0.25, 0.5, 0.75, 1];
</script>

<template>
    <div class="viz-root rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
        <div class="mb-2 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                {{ title }}
            </h3>
            <button
                type="button"
                class="text-xs font-medium text-gray-400 underline hover:text-gray-600 dark:hover:text-gray-200"
                @click="showTable = !showTable"
            >
                {{ showTable ? 'Lihat grafik' : 'Lihat sebagai tabel' }}
            </button>
        </div>

        <div v-if="!showTable" class="relative">
            <svg :viewBox="`0 0 ${width} ${height}`" class="w-full" role="img" :aria-label="title">
                <!-- gridlines -->
                <line
                    v-for="g in gridLines"
                    :key="g"
                    :x1="paddingLeft"
                    :x2="width - paddingRight"
                    :y1="paddingTop + plotHeight * (1 - g)"
                    :y2="paddingTop + plotHeight * (1 - g)"
                    class="grid-line"
                    stroke-width="1"
                />

                <!-- baseline -->
                <line
                    :x1="paddingLeft"
                    :x2="width - paddingRight"
                    :y1="paddingTop + plotHeight"
                    :y2="paddingTop + plotHeight"
                    class="baseline"
                    stroke-width="1"
                />

                <!-- bars -->
                <g v-for="(v, i) in values" :key="i">
                    <rect
                        :x="barX(i)"
                        :y="barY(v)"
                        :width="barWidth"
                        :height="Math.max(barHeight(v), 1)"
                        rx="4"
                        class="bar"
                        :class="{ 'bar-hover': hovered === i }"
                        @pointerenter="hovered = i"
                        @pointerleave="hovered = null"
                        @focus="hovered = i"
                        @blur="hovered = null"
                        tabindex="0"
                        role="img"
                        :aria-label="`${labels[i]}: ${formatValue(v)}`"
                    />

                    <!-- direct label on the max bar only -->
                    <text
                        v-if="i === maxIndex"
                        :x="barX(i) + barWidth / 2"
                        :y="barY(v) - 6"
                        text-anchor="middle"
                        class="value-label"
                    >
                        {{ formatValue(v) }}
                    </text>

                    <text
                        :x="barX(i) + barWidth / 2"
                        :y="height - 8"
                        text-anchor="middle"
                        class="axis-label"
                    >
                        {{ labels[i] }}
                    </text>
                </g>
            </svg>

            <!-- hover tooltip -->
            <div
                v-if="hovered !== null"
                class="pointer-events-none absolute rounded-md bg-gray-900 px-2 py-1 text-xs text-white shadow-lg dark:bg-black"
                :style="{
                    left: `${((barX(hovered) + barWidth / 2) / width) * 100}%`,
                    top: `${(barY(values[hovered]) / height) * 100}%`,
                    transform: 'translate(-50%, -130%)',
                }"
            >
                <div class="font-semibold">{{ formatValue(values[hovered]) }}</div>
                <div class="text-gray-300">{{ labels[hovered] }}</div>
            </div>
        </div>

        <table v-else class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 text-left text-gray-500 dark:border-gray-800 dark:text-gray-400">
                    <th class="py-1">Tanggal</th>
                    <th class="py-1 text-right">Nilai</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(v, i) in values" :key="i" class="border-b border-gray-50 dark:border-gray-800/50">
                    <td class="py-1 text-gray-600 dark:text-gray-300">{{ labels[i] }}</td>
                    <td class="py-1 text-right font-medium tabular-nums text-gray-800 dark:text-gray-100">
                        {{ formatValue(v) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
.viz-root {
    --bar-color: #22c55e;
    --grid-color: #e5e7eb;
    --baseline-color: #d1d5db;
    --text-muted: #9ca3af;
}

:global(.dark) .viz-root {
    --bar-color: #16a34a;
    --grid-color: #374151;
    --baseline-color: #4b5563;
    --text-muted: #9ca3af;
}

.bar {
    fill: var(--bar-color);
    cursor: pointer;
    outline: none;
    transition: opacity 0.15s ease;
}

.bar-hover {
    opacity: 0.85;
}

.grid-line {
    stroke: var(--grid-color);
}

.baseline {
    stroke: var(--baseline-color);
}

.value-label {
    font-size: 11px;
    font-weight: 600;
    fill: var(--text-muted);
}

.axis-label {
    font-size: 10px;
    fill: var(--text-muted);
}
</style>
