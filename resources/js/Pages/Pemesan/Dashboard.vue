<script setup>
import PemesanLayout from '@/Layouts/PemesanLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
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

        <Link
            :href="route('pemesan.orders.index')"
            class="block rounded-xl bg-white p-4 shadow-sm hover:bg-gray-50 dark:bg-surface-darkMuted dark:hover:bg-gray-800"
        >
            <p class="font-medium text-gray-800 dark:text-gray-100">Order Saya</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Lihat status & chat pesanan Anda</p>
        </Link>

        <div
            class="mt-4 rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:bg-surface-darkMuted dark:text-gray-400"
        >
            Cari penyedia jasa, pilih toko & menu, dan checkout akan dibangun
            di Stage 4 (Modul Pemesan).
        </div>
    </PemesanLayout>
</template>
