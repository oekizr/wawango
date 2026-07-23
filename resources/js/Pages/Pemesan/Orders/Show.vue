<script setup>
import PemesanLayout from '@/Layouts/PemesanLayout.vue';
import Badge from '@/Components/Badge.vue';
import ChatThread from '@/Components/Chat/ChatThread.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    order: { type: Object, required: true },
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
    <Head :title="`Order ${order.kode_order}`" />

    <PemesanLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <Link :href="route('pemesan.orders.index')" class="text-sm text-gray-500 hover:underline dark:text-gray-400">
                        ← Kembali ke Order Saya
                    </Link>
                    <h1 class="mt-1 text-xl font-semibold text-gray-800 dark:text-gray-100">
                        Order {{ order.kode_order }}
                    </h1>
                </div>
                <Badge :tone="statusTones[order.status]">{{ statusLabels[order.status] }}</Badge>
            </div>
        </template>

        <div class="space-y-6">
            <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
                <h2 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Timeline</h2>
                <ol class="space-y-3">
                    <li v-for="(h, i) in order.status_histories" :key="i" class="flex gap-3 text-sm">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-primary-500" />
                        <div>
                            <div class="font-medium text-gray-800 dark:text-gray-100">
                                {{ statusLabels[h.status] ?? h.status }}
                            </div>
                            <div class="text-xs text-gray-400">
                                {{ new Date(h.created_at).toLocaleString('id-ID') }}
                            </div>
                        </div>
                    </li>
                </ol>
            </div>

            <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
                <h2 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Detail Pesanan</h2>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    {{ order.toko }} · {{ order.provider }}
                </p>
                <ul class="mt-2 divide-y divide-gray-50 dark:divide-gray-800/50">
                    <li v-for="item in order.items" :key="item.id" class="flex justify-between py-2 text-sm">
                        <p class="text-gray-800 dark:text-gray-100">{{ item.nama_menu }} x{{ item.qty }}</p>
                        <p class="tabular-nums text-gray-700 dark:text-gray-200">{{ rupiah(item.subtotal) }}</p>
                    </li>
                </ul>
                <div class="mt-3 space-y-1 border-t border-gray-100 pt-3 text-sm dark:border-gray-800">
                    <div class="flex justify-between text-gray-500 dark:text-gray-400">
                        <span>Subtotal</span><span class="tabular-nums">{{ rupiah(order.subtotal) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-500 dark:text-gray-400">
                        <span>Biaya Jasa</span><span class="tabular-nums">{{ rupiah(order.service_fee) }}</span>
                    </div>
                    <div class="flex justify-between text-base font-semibold text-gray-800 dark:text-gray-100">
                        <span>Total Bayar</span><span class="tabular-nums">{{ rupiah(order.total) }}</span>
                    </div>
                </div>
            </div>

            <div v-if="order.issues?.length" class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
                <h2 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Kendala</h2>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Pesanan mengalami kendala, silakan cek pesan dari penyedia jasa di bawah.
                </p>
            </div>

            <div>
                <h2 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Chat dengan Penyedia Jasa</h2>
                <ChatThread
                    :order-id="order.id"
                    :messages="order.messages ?? []"
                    send-route-name="pemesan.orders.messages.store"
                />
            </div>
        </div>
    </PemesanLayout>
</template>
