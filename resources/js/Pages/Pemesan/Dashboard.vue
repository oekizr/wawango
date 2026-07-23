<script setup>
import PemesanLayout from '@/Layouts/PemesanLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/auth';

defineProps({
    stats: { type: Object, required: true },
});

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
            :href="route('pemesan.providers.index')"
            class="block rounded-xl bg-primary-600 p-4 text-white shadow-sm hover:bg-primary-700"
        >
            <p class="font-semibold">🔍 Cari Penyedia Jasa</p>
            <p class="text-sm text-primary-100">Pesan makanan, minuman, atau titip beli</p>
        </Link>

        <div class="mt-4 grid grid-cols-2 gap-4">
            <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
                <p class="text-sm text-gray-500 dark:text-gray-400">Order Aktif</p>
                <p class="mt-1 text-2xl font-semibold text-gray-800 dark:text-gray-100">{{ stats.orders_active }}</p>
            </div>
            <Link
                :href="route('pemesan.orders.index')"
                class="rounded-xl bg-white p-4 shadow-sm hover:bg-gray-50 dark:bg-surface-darkMuted dark:hover:bg-gray-800"
            >
                <p class="font-medium text-gray-800 dark:text-gray-100">Order Saya</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Riwayat & chat</p>
            </Link>
        </div>
    </PemesanLayout>
</template>
