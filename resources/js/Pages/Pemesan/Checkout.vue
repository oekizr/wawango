<script setup>
import PemesanLayout from '@/Layouts/PemesanLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useCartStore } from '@/stores/cart';

const cart = useCartStore();

const form = useForm({
    store_id: cart.storeId,
    items: cart.items.map((item) => ({ menu_id: item.menuId, qty: item.qty, note: item.note })),
    notes: '',
});

function updateQty(menuId, qty) {
    cart.updateQty(menuId, qty);
    syncItems();
}

function updateNote(menuId, note) {
    cart.updateNote(menuId, note);
}

function syncItems() {
    form.items = cart.items.map((item) => ({ menu_id: item.menuId, qty: item.qty, note: item.note }));
}

function submit() {
    syncItems();
    form.post(route('pemesan.checkout.store'), {
        onSuccess: () => cart.clear(),
    });
}

const rupiah = (v) => 'Rp'.concat(new Intl.NumberFormat('id-ID').format(v));
</script>

<template>
    <Head title="Checkout" />

    <PemesanLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Checkout</h1>
        </template>

        <div v-if="cart.items.length === 0" class="rounded-xl bg-white p-8 text-center shadow-sm dark:bg-surface-darkMuted">
            <p class="text-gray-400">Keranjang Anda kosong.</p>
            <Link :href="route('pemesan.providers.index')" class="mt-2 inline-block text-primary-600 hover:underline">
                Cari penyedia jasa →
            </Link>
        </div>

        <form v-else class="space-y-4" @submit.prevent="submit">
            <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
                <p class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">{{ cart.storeName }}</p>
                <ul class="divide-y divide-gray-50 dark:divide-gray-800/50">
                    <li v-for="item in cart.items" :key="item.menuId" class="py-3">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-800 dark:text-gray-100">{{ item.nama }}</p>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                    @click="updateQty(item.menuId, item.qty - 1)"
                                >
                                    −
                                </button>
                                <span class="w-4 text-center text-sm">{{ item.qty }}</span>
                                <button
                                    type="button"
                                    class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                    @click="updateQty(item.menuId, item.qty + 1)"
                                >
                                    +
                                </button>
                            </div>
                        </div>
                        <input
                            :value="item.note"
                            type="text"
                            placeholder="Catatan (mis. tidak pedas)"
                            class="mt-1.5 block w-full rounded-md border-gray-200 text-xs shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            @input="updateNote(item.menuId, $event.target.value)"
                        />
                        <p class="mt-1 text-right text-xs text-gray-400">{{ rupiah(item.harga * item.qty) }}</p>
                    </li>
                </ul>
            </div>

            <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between text-gray-500 dark:text-gray-400">
                        <span>Subtotal Menu</span><span class="tabular-nums">{{ rupiah(cart.subtotal) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-500 dark:text-gray-400">
                        <span>Biaya Jasa</span><span class="tabular-nums">{{ rupiah(cart.serviceFee) }}</span>
                    </div>
                    <div class="flex justify-between text-base font-semibold text-gray-800 dark:text-gray-100">
                        <span>Grand Total</span><span class="tabular-nums">{{ rupiah(cart.grandTotal) }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
                <p class="rounded-lg bg-gray-50 p-3 text-xs text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    Metode pembayaran dipilih setelah penyedia jasa mengkonfirmasi pesanan Anda — toko bisa saja
                    sedang tutup atau menunya tidak tersedia, jadi belum perlu dipilih sekarang.
                </p>

                <div class="mt-4">
                    <InputLabel for="notes" value="Catatan untuk pesanan (opsional)" />
                    <textarea
                        id="notes"
                        v-model="form.notes"
                        rows="2"
                        class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    />
                </div>
            </div>

            <InputError :message="form.errors.items ?? form.errors.store_id" />

            <PrimaryButton class="w-full justify-center" :disabled="form.processing">
                Buat Pesanan
            </PrimaryButton>
        </form>
    </PemesanLayout>
</template>
