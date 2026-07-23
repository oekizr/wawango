<script setup>
import PemesanLayout from '@/Layouts/PemesanLayout.vue';
import Badge from '@/Components/Badge.vue';
import ChatThread from '@/Components/Chat/ChatThread.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Echo from '@/echo';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    order: { type: Object, required: true },
});

onMounted(() => {
    Echo.private(`orders.${props.order.id}`).listen('.status.changed', () => {
        router.reload({ only: ['order'], preserveScroll: true });
    });
});

onUnmounted(() => {
    Echo.leave(`orders.${props.order.id}`);
});

const paymentStatusTones = { pending: 'yellow', diterima: 'green', ditolak: 'red' };
const paymentStatusLabels = { pending: 'Menunggu Verifikasi', diterima: 'Diterima', ditolak: 'Ditolak' };

const canUploadProof = computed(() => {
    const payment = props.order.payment;
    return payment && payment.method !== 'cash' && ['pending', 'ditolak'].includes(payment.status);
});

const proofForm = useForm({ bukti: null });

function submitProof() {
    proofForm.post(route('pemesan.orders.paymentProof.store', props.order.id), {
        forceFormData: true,
        onSuccess: () => (proofForm.bukti = null),
    });
}

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

const reasonLabels = {
    toko_tutup: 'Toko sedang tutup',
    menu_habis: 'Menu habis',
    barang_tidak_ada: 'Barang tidak ada',
    cuaca: 'Kendala cuaca',
    tidak_dikonfirmasi: 'Penyedia jasa tidak mengkonfirmasi pesanan',
    lainnya: 'Kendala lain',
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

            <div v-if="order.payment" class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Pembayaran</h2>
                    <Badge :tone="paymentStatusTones[order.payment.status]">
                        {{ paymentStatusLabels[order.payment.status] }}
                    </Badge>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Metode: <span class="font-medium uppercase">{{ order.payment.method }}</span>
                </p>

                <img
                    v-if="order.payment.proof_url"
                    :src="order.payment.proof_url"
                    class="mt-3 max-h-64 rounded-lg border border-gray-100 object-contain dark:border-gray-800"
                />

                <form v-if="canUploadProof" class="mt-3" @submit.prevent="submitProof">
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                        {{ order.payment.proof_url ? 'Upload ulang bukti' : 'Upload bukti pembayaran' }}
                    </label>
                    <input
                        type="file"
                        accept="image/*"
                        class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-primary-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-primary-700 hover:file:bg-primary-100 dark:text-gray-300"
                        @change="proofForm.bukti = $event.target.files[0]"
                    />
                    <InputError class="mt-1" :message="proofForm.errors.bukti" />
                    <PrimaryButton class="mt-2" :disabled="proofForm.processing || !proofForm.bukti">
                        Kirim Bukti
                    </PrimaryButton>
                </form>
            </div>

            <div v-if="order.issues?.length" class="rounded-xl bg-red-50 p-4 dark:bg-red-900/20">
                <h2 class="mb-2 text-sm font-semibold text-red-800 dark:text-red-200">Kendala Pesanan</h2>
                <ul class="space-y-1 text-sm text-red-700 dark:text-red-300">
                    <li v-for="(issue, i) in order.issues" :key="i">
                        <span class="font-medium">{{ reasonLabels[issue.reason] ?? issue.reason }}</span>
                        <span v-if="issue.reason === 'tidak_dikonfirmasi'"> ({{ order.provider }})</span>
                        <span v-if="issue.note"> — {{ issue.note }}</span>
                    </li>
                </ul>
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
