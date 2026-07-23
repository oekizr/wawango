<script setup>
import ProviderLayout from '@/Layouts/ProviderLayout.vue';
import Badge from '@/Components/Badge.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    orders: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
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

const status = ref(props.filters.status ?? '');

function applyFilter() {
    router.get(route('provider.orders.index'), { status: status.value }, { preserveState: true, replace: true });
}

const rupiah = (v) => 'Rp'.concat(new Intl.NumberFormat('id-ID').format(v));
</script>

<template>
    <Head title="Pesanan" />

    <ProviderLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Pesanan</h1>
        </template>

        <select
            v-model="status"
            class="mb-4 rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
            @change="applyFilter"
        >
            <option value="">Semua Status</option>
            <option v-for="(label, key) in statusLabels" :key="key" :value="key">{{ label }}</option>
        </select>

        <div class="space-y-3">
            <Link
                v-for="order in orders.data"
                :key="order.id"
                :href="route('provider.orders.show', order.id)"
                class="block rounded-xl bg-white p-4 shadow-sm hover:bg-gray-50 dark:bg-surface-darkMuted dark:hover:bg-gray-800"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-mono text-xs text-gray-400">{{ order.kode_order }}</p>
                        <p class="font-semibold text-gray-800 dark:text-gray-100">{{ order.pemesan }}</p>
                        <p class="text-xs text-gray-400">{{ order.divisi }} · Lt.{{ order.lantai }}</p>
                    </div>
                    <Badge :tone="statusTones[order.status]">{{ statusLabels[order.status] }}</Badge>
                </div>
                <p class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-200">{{ rupiah(order.total) }}</p>
                <p v-if="order.status === 'menunggu' && order.confirm_deadline" class="mt-1 text-xs text-yellow-600 dark:text-yellow-400">
                    ⏱ Konfirmasi sebelum {{ new Date(order.confirm_deadline).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                </p>
            </Link>

            <p v-if="orders.data.length === 0" class="py-8 text-center text-gray-400">Belum ada pesanan.</p>
        </div>

        <Pagination :meta="orders.meta" />
    </ProviderLayout>
</template>
