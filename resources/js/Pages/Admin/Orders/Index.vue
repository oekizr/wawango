<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/Badge.vue';
import Pagination from '@/Components/Pagination.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    orders: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    divisions: { type: Array, default: () => [] },
    providers: { type: Array, default: () => [] },
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

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const divisi = ref(props.filters.divisi ?? '');
const providerId = ref(props.filters.provider_id ?? '');
const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');

function applyFilters() {
    router.get(
        route('admin.orders.index'),
        {
            search: search.value,
            status: status.value,
            divisi: divisi.value,
            provider_id: providerId.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
        },
        { preserveState: true, replace: true },
    );
}

const rupiah = (v) => 'Rp'.concat(new Intl.NumberFormat('id-ID').format(v));
</script>

<template>
    <Head title="Manajemen Order" />

    <AdminLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Manajemen Order</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Pantau & kelola seluruh pesanan.</p>
        </template>

        <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
            <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Kode order / nama..."
                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    @keyup.enter="applyFilters"
                />
                <select
                    v-model="status"
                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    @change="applyFilters"
                >
                    <option value="">Semua Status</option>
                    <option v-for="(label, key) in statusLabels" :key="key" :value="key">{{ label }}</option>
                </select>
                <select
                    v-model="divisi"
                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    @change="applyFilters"
                >
                    <option value="">Semua Divisi</option>
                    <option v-for="d in divisions" :key="d" :value="d">{{ d }}</option>
                </select>
                <select
                    v-model="providerId"
                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    @change="applyFilters"
                >
                    <option value="">Semua Provider</option>
                    <option v-for="p in providers" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
                <input
                    v-model="dateFrom"
                    type="date"
                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    @change="applyFilters"
                />
                <input
                    v-model="dateTo"
                    type="date"
                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    @change="applyFilters"
                />
            </div>
            <SecondaryButton class="mb-4" @click="applyFilters">Terapkan Filter</SecondaryButton>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-500 dark:border-gray-800 dark:text-gray-400">
                            <th class="py-2 pr-4">Kode Order</th>
                            <th class="py-2 pr-4">Pemesan</th>
                            <th class="py-2 pr-4">Toko / Provider</th>
                            <th class="py-2 pr-4">Total</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 pr-4">Tanggal</th>
                            <th class="py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="order in orders.data"
                            :key="order.id"
                            class="border-b border-gray-50 dark:border-gray-800/50"
                        >
                            <td class="py-2 pr-4 font-mono text-xs text-gray-700 dark:text-gray-300">
                                {{ order.kode_order }}
                            </td>
                            <td class="py-2 pr-4">
                                <div class="font-medium text-gray-800 dark:text-gray-100">{{ order.pemesan }}</div>
                                <div class="text-xs text-gray-400">{{ order.divisi }} / Lt.{{ order.lantai }}</div>
                            </td>
                            <td class="py-2 pr-4 text-gray-600 dark:text-gray-300">
                                <div>{{ order.toko }}</div>
                                <div class="text-xs text-gray-400">{{ order.provider }}</div>
                            </td>
                            <td class="py-2 pr-4 tabular-nums text-gray-700 dark:text-gray-200">
                                {{ rupiah(order.total) }}
                            </td>
                            <td class="py-2 pr-4">
                                <Badge :tone="statusTones[order.status]">{{ statusLabels[order.status] }}</Badge>
                            </td>
                            <td class="py-2 pr-4 text-gray-500 dark:text-gray-400">
                                {{ new Date(order.ordered_at).toLocaleDateString('id-ID') }}
                            </td>
                            <td class="py-2 text-right">
                                <Link
                                    :href="route('admin.orders.show', order.id)"
                                    class="text-sm text-primary-600 hover:underline"
                                >
                                    Lihat Detail
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="orders.data.length === 0">
                            <td colspan="7" class="py-8 text-center text-gray-400">Belum ada order.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :meta="orders.meta" />
        </div>
    </AdminLayout>
</template>
