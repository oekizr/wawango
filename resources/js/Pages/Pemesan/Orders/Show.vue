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
    paymentInfo: { type: Object, default: null },
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

// Payment method is only asked once the provider has confirmed the order —
// no point picking one if the store turns out to be closed or the menu
// unavailable and the order gets cancelled instead.
const needsPaymentMethod = computed(
    () => !props.order.payment && !['menunggu', 'dibatalkan'].includes(props.order.status),
);

const methodForm = useForm({ method: 'cash' });

function submitPaymentMethod() {
    methodForm.post(route('pemesan.orders.paymentMethod.store', props.order.id));
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

            <div
                v-if="order.status === 'menunggu'"
                class="rounded-lg bg-yellow-50 p-3 text-sm text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200"
            >
                Menunggu konfirmasi penyedia jasa. Anda bisa memilih metode pembayaran setelah pesanan dikonfirmasi.
            </div>

            <div v-if="needsPaymentMethod" class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
                <h2 class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-200">Pilih Metode Pembayaran</h2>
                <p class="mb-3 text-sm text-gray-500 dark:text-gray-400">
                    Penyedia jasa sudah menerima pesanan Anda, silahkan lakukan pembayaran melalui metode berikut.
                </p>

                <form @submit.prevent="submitPaymentMethod">
                    <div class="space-y-2 text-sm">
                        <label class="flex items-center gap-2">
                            <input v-model="methodForm.method" type="radio" value="cash" class="text-primary-600 focus:ring-primary-500" />
                            Cash (bayar langsung)
                        </label>
                        <label class="flex items-center gap-2">
                            <input v-model="methodForm.method" type="radio" value="transfer" class="text-primary-600 focus:ring-primary-500" />
                            Transfer Bank
                        </label>
                        <label class="flex items-center gap-2">
                            <input v-model="methodForm.method" type="radio" value="qris" class="text-primary-600 focus:ring-primary-500" />
                            QRIS
                        </label>
                    </div>
                    <InputError class="mt-2" :message="methodForm.errors.method" />

                    <div
                        v-if="methodForm.method === 'transfer' && paymentInfo"
                        class="mt-3 rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-800"
                    >
                        <p class="font-medium text-gray-700 dark:text-gray-200">{{ paymentInfo.nama_bank ?? '-' }}</p>
                        <p class="text-gray-600 dark:text-gray-300">{{ paymentInfo.no_rekening ?? '-' }}</p>
                        <p class="text-xs text-gray-400">a.n. {{ paymentInfo.nama_pemilik_rekening ?? '-' }}</p>
                    </div>

                    <div
                        v-if="methodForm.method === 'qris' && paymentInfo"
                        class="mt-3 rounded-lg bg-gray-50 p-3 text-center text-sm dark:bg-gray-800"
                    >
                        <img
                            v-if="paymentInfo.qris_image_url"
                            :src="paymentInfo.qris_image_url"
                            class="mx-auto h-40 w-40 object-contain"
                        />
                        <p v-else class="text-gray-400">Provider belum mengunggah QRIS.</p>
                    </div>

                    <PrimaryButton class="mt-3" :disabled="methodForm.processing">
                        Pilih Metode Ini
                    </PrimaryButton>
                </form>
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

            <div
                v-if="order.status === 'selesai'"
                class="rounded-xl bg-primary-50 p-6 text-center dark:bg-primary-900/20"
            >
                <p class="text-base font-semibold text-primary-700 dark:text-primary-300">Pesanan Sudah Selesai</p>
                <Link
                    :href="route('pemesan.dashboard')"
                    class="mt-3 inline-block rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700"
                >
                    Kembali ke Halaman Utama
                </Link>
            </div>
        </div>
    </PemesanLayout>
</template>
