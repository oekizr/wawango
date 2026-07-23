<script setup>
import PemesanLayout from '@/Layouts/PemesanLayout.vue';
import Badge from '@/Components/Badge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useCartStore } from '@/stores/cart';

const props = defineProps({
    store: { type: Object, required: true },
    menus: { type: Array, required: true },
});

const cart = useCartStore();

const isCurrentStore = computed(() => cart.storeId === props.store.id);

function qtyFor(menuId) {
    if (!isCurrentStore.value) return 0;
    return cart.items.find((i) => i.menuId === menuId)?.qty ?? 0;
}

function add(menu) {
    cart.addItem(props.store, menu);
}

function decrease(menu) {
    const qty = qtyFor(menu.id);
    cart.updateQty(menu.id, qty - 1);
}

const rupiah = (v) => 'Rp'.concat(new Intl.NumberFormat('id-ID').format(v));
</script>

<template>
    <Head :title="store.nama_toko" />

    <PemesanLayout>
        <template #header>
            <Link :href="route('pemesan.providers.show', store.provider_id)" class="text-sm text-gray-500 hover:underline dark:text-gray-400">
                ← Kembali
            </Link>
            <div class="mt-1 flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">{{ store.nama_toko }}</h1>
                    <p class="text-xs text-gray-400">{{ store.provider_name }} · Biaya jasa {{ rupiah(store.service_fee) }}</p>
                </div>
                <Badge :tone="store.is_open ? 'green' : 'gray'">{{ store.is_open ? 'Buka' : 'Tutup' }}</Badge>
            </div>
        </template>

        <p v-if="!store.is_open" class="mb-4 rounded-lg bg-yellow-50 p-3 text-sm text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300">
            Toko sedang tutup, belum bisa menerima pesanan.
        </p>

        <div class="space-y-3 pb-24">
            <div
                v-for="menu in menus"
                :key="menu.id"
                class="flex items-center gap-3 rounded-xl bg-white p-3 shadow-sm dark:bg-surface-darkMuted"
            >
                <img
                    v-if="menu.foto_url"
                    :src="menu.foto_url"
                    class="h-16 w-16 shrink-0 rounded-lg object-cover"
                />
                <div v-else class="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-[10px] text-gray-400 dark:bg-gray-800">
                    Tanpa foto
                </div>

                <div class="min-w-0 flex-1">
                    <p class="truncate font-medium text-gray-800 dark:text-gray-100">{{ menu.nama }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ rupiah(menu.harga) }}</p>
                    <Badge v-if="menu.status === 'habis'" tone="red" class="mt-1">Habis</Badge>
                </div>

                <div v-if="menu.status === 'tersedia' && store.is_open" class="flex items-center gap-2">
                    <button
                        v-if="qtyFor(menu.id) > 0"
                        type="button"
                        class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                        @click="decrease(menu)"
                    >
                        −
                    </button>
                    <span v-if="qtyFor(menu.id) > 0" class="w-4 text-center text-sm font-medium">{{ qtyFor(menu.id) }}</span>
                    <button
                        type="button"
                        class="flex h-7 w-7 items-center justify-center rounded-full bg-primary-600 text-white"
                        @click="add(menu)"
                    >
                        +
                    </button>
                </div>
            </div>

            <p v-if="menus.length === 0" class="py-8 text-center text-gray-400">Belum ada menu.</p>
        </div>

        <div
            v-if="isCurrentStore && cart.items.length > 0"
            class="fixed inset-x-0 bottom-16 z-30 px-4 sm:bottom-4"
        >
            <button
                type="button"
                class="mx-auto flex w-full max-w-3xl items-center justify-between rounded-xl bg-primary-600 px-4 py-3 text-white shadow-lg hover:bg-primary-700"
                @click="router.visit(route('pemesan.checkout.show', { store_id: store.id }))"
            >
                <span>Keranjang ({{ cart.totalQty }})</span>
                <span class="font-semibold">{{ rupiah(cart.subtotal) }}</span>
            </button>
        </div>
    </PemesanLayout>
</template>
