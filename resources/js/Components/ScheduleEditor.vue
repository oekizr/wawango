<script setup>
const schedules = defineModel({ type: Array, required: true });

const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
</script>

<template>
    <div class="space-y-2">
        <div
            v-for="schedule in schedules"
            :key="schedule.day_of_week"
            class="flex flex-wrap items-center gap-3 rounded-lg border border-gray-100 p-3 dark:border-gray-800"
        >
            <label class="flex w-28 items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                <input
                    v-model="schedule.is_active"
                    type="checkbox"
                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                />
                {{ dayNames[schedule.day_of_week] }}
            </label>

            <template v-if="schedule.is_active">
                <input
                    v-model="schedule.open_time"
                    type="time"
                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                />
                <span class="text-gray-400">—</span>
                <input
                    v-model="schedule.close_time"
                    type="time"
                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                />
            </template>
            <span v-else class="text-sm text-gray-400">Tutup</span>
        </div>
    </div>
</template>
