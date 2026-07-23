<script setup>
import PemesanLayout from '@/Layouts/PemesanLayout.vue';
import Badge from '@/Components/Badge.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    orders: { type: Object, required: true },
});

const statusTones = {
    menunggu: 'yellow',
    diproses: 'blue',
    dibelikan: 'blue',
    diantar: 'blue',
    selesai: 'green',
    dibatalkan: 'red',
};

const statusLabels = {
    menunggu: 'Menunggu',
    diproses: 'Diproses',
    dibelikan: 'Dibelikan',
    diantar: 'Dalam Pengantaran',
    selesai: 'Selesai',
    dibatalkan: 'Dibatalkan',
};

const rupiah = (v) => 'Rp'.concat(new Intl.NumberFormat('id-ID').format(v));
</script>

<template>
    <Head title="Order Saya" />

    <PemesanLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Order Saya</h1>
        </template>

        <div class="space-y-3">
            <Link
                v-for="order in orders.data"
                :key="order.id"
                :href="route('pemesan.orders.show', order.id)"
                class="block rounded-xl bg-white p-4 shadow-sm hover:bg-gray-50 dark:bg-surface-darkMuted dark:hover:bg-gray-800"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-mono text-xs text-gray-400">{{ order.kode_order }}</p>
                        <p class="font-semibold text-gray-800 dark:text-gray-100">{{ order.toko }}</p>
                        <p class="text-xs text-gray-400">{{ order.provider }}</p>
                    </div>
                    <Badge :tone="statusTones[order.status]">{{ statusLabels[order.status] }}</Badge>
                </div>
                <p class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-200">{{ rupiah(order.total) }}</p>
            </Link>

            <p v-if="orders.data.length === 0" class="py-8 text-center text-gray-400">
                Belum ada order. Fitur pesan makanan akan hadir di tahap berikutnya.
            </p>
        </div>

        <Pagination :meta="orders.meta" />
    </PemesanLayout>
</template>
