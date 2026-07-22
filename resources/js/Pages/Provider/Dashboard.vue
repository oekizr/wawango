<script setup>
import ProviderLayout from '@/Layouts/ProviderLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
</script>

<template>
    <Head title="Dashboard Penyedia Jasa" />

    <ProviderLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
                Halo, {{ auth.user?.name }}
            </h1>
        </template>

        <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Status Layanan
                    </p>
                    <p class="mt-1 flex items-center gap-2 text-lg font-semibold text-gray-400">
                        <span class="h-2.5 w-2.5 rounded-full bg-gray-300" />
                        Belum diatur
                    </p>
                </div>
                <button
                    disabled
                    class="cursor-not-allowed rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400 dark:bg-gray-800"
                >
                    Atur Jadwal
                </button>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-4">
            <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
                <p class="text-sm text-gray-500 dark:text-gray-400">Order Aktif</p>
                <p class="mt-1 text-2xl font-semibold text-gray-800 dark:text-gray-100">-</p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
                <p class="text-sm text-gray-500 dark:text-gray-400">Pendapatan Hari Ini</p>
                <p class="mt-1 text-2xl font-semibold text-gray-800 dark:text-gray-100">-</p>
            </div>
        </div>

        <div
            class="mt-6 rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:bg-surface-darkMuted dark:text-gray-400"
        >
            Kelola toko, menu, jadwal layanan, dan pesanan masuk akan dibangun
            di Stage 3 (Modul Penyedia Jasa).
        </div>
    </ProviderLayout>
</template>
