<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BarChart from '@/Components/Charts/BarChart.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

const props = defineProps({
    stats: { type: Object, required: true },
    chart: { type: Object, required: true },
});

const auth = useAuthStore();

const rupiah = (v) => 'Rp'.concat(new Intl.NumberFormat('id-ID').format(v));

const statCards = computed(() => [
    { label: 'Total Penyedia Jasa', value: props.stats.total_providers },
    { label: 'Total User (Pemesan)', value: props.stats.total_users },
    { label: 'Order Hari Ini', value: props.stats.orders_today },
    { label: 'Order Berjalan', value: props.stats.orders_active },
    { label: 'Order Selesai', value: props.stats.orders_selesai },
    { label: 'Total Pendapatan Jasa', value: rupiah(props.stats.total_pendapatan_jasa) },
]);
</script>

<template>
    <Head title="Dashboard Admin" />

    <AdminLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
                Selamat datang, {{ auth.user?.name }}
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Ringkasan operasional WawanGo.
            </p>
        </template>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
            <div
                v-for="stat in statCards"
                :key="stat.label"
                class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted"
            >
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ stat.label }}
                </p>
                <p class="mt-1 text-2xl font-semibold text-gray-800 dark:text-gray-100">
                    {{ stat.value }}
                </p>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
            <BarChart
                title="Order per Hari (14 hari terakhir)"
                :labels="chart.labels"
                :values="chart.orders"
            />
            <BarChart
                title="Pendapatan Jasa per Hari (14 hari terakhir)"
                :labels="chart.labels"
                :values="chart.revenue"
                :format-value="rupiah"
            />
        </div>
    </AdminLayout>
</template>
