<script setup>
import PemesanLayout from '@/Layouts/PemesanLayout.vue';
import Badge from '@/Components/Badge.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    provider: { type: Object, required: true },
    stores: { type: Array, required: true },
});
</script>

<template>
    <Head :title="provider.name" />

    <PemesanLayout>
        <template #header>
            <Link :href="route('pemesan.providers.index')" class="text-sm text-gray-500 hover:underline dark:text-gray-400">
                ← Kembali
            </Link>
            <div class="mt-1 flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">{{ provider.name }}</h1>
                <Badge :tone="provider.is_open ? 'green' : 'gray'">
                    {{ provider.is_open ? 'Buka' : 'Tutup' }}
                </Badge>
            </div>
        </template>

        <div class="mb-4 rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-200">Jam Layanan</p>
            <ul class="mt-1 space-y-0.5 text-sm text-gray-500 dark:text-gray-400">
                <li v-for="(s, i) in provider.schedules" :key="i">{{ s.day }}: {{ s.open_time }}–{{ s.close_time }}</li>
            </ul>
        </div>

        <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-200">Toko</p>
        <div class="space-y-3">
            <Link
                v-for="store in stores"
                :key="store.id"
                :href="route('pemesan.stores.show', store.id)"
                class="block rounded-xl bg-white p-4 shadow-sm hover:bg-gray-50 dark:bg-surface-darkMuted dark:hover:bg-gray-800"
            >
                <p class="font-semibold text-gray-800 dark:text-gray-100">{{ store.nama_toko }}</p>
                <p class="text-xs text-gray-400">{{ store.lokasi }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Biaya jasa Rp{{ new Intl.NumberFormat('id-ID').format(store.service_fee) }} · {{ store.menus_count }} menu
                </p>
            </Link>

            <p v-if="stores.length === 0" class="py-8 text-center text-gray-400">Belum ada toko.</p>
        </div>
    </PemesanLayout>
</template>
