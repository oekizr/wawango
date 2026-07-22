<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();

const stats = [
    { label: 'Total Penyedia Jasa', value: '-' },
    { label: 'Total User', value: '-' },
    { label: 'Order Hari Ini', value: '-' },
    { label: 'Order Berjalan', value: '-' },
];
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

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div
                v-for="stat in stats"
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

        <div
            class="mt-6 rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:bg-surface-darkMuted dark:text-gray-400"
        >
            Manajemen provider, user, order, dan grafik transaksi akan dibangun
            di Stage 2 (Modul Admin).
        </div>
    </AdminLayout>
</template>
