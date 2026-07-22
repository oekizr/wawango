<script setup>
import PemesanLayout from '@/Layouts/PemesanLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
</script>

<template>
    <Head title="Dashboard Pemesan" />

    <PemesanLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
                Halo, {{ auth.user?.name }} 👋
            </h1>
        </template>

        <div
            class="rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:bg-surface-darkMuted dark:text-gray-400"
        >
            Pencarian penyedia jasa, order aktif, dan riwayat order akan
            dibangun di Stage 4 (Modul Pemesan).
        </div>
    </PemesanLayout>
</template>
