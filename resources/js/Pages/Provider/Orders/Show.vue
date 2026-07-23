<script setup>
import ProviderLayout from '@/Layouts/ProviderLayout.vue';
import Badge from '@/Components/Badge.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import WhatsAppLink from '@/Components/WhatsAppLink.vue';
import ChatThread from '@/Components/Chat/ChatThread.vue';
import Echo from '@/echo';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

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
    toko_tutup: 'Toko Tutup',
    menu_habis: 'Menu Habis',
    barang_tidak_ada: 'Barang Tidak Ada',
    cuaca: 'Cuaca',
    tidak_dikonfirmasi: 'Tidak Dikonfirmasi',
    lainnya: 'Lainnya',
};

// "Tidak Dikonfirmasi" is set automatically by the system (10-minute
// confirmation timeout) — not a reason the provider picks manually.
const selectableReasons = {
    toko_tutup: 'Toko Tutup',
    menu_habis: 'Menu Habis',
    barang_tidak_ada: 'Barang Tidak Ada',
    cuaca: 'Cuaca',
    lainnya: 'Lainnya',
};

const advanceLabel = computed(() => ({
    menunggu: 'Konfirmasi Pesanan',
    diproses: 'Tandai Sudah Dibelikan',
    dibelikan: 'Tandai Dalam Pengantaran',
    diantar: 'Selesaikan Pesanan',
}[props.order.status]));

const isTerminal = computed(() => ['selesai', 'dibatalkan'].includes(props.order.status));
const rupiah = (v) => 'Rp'.concat(new Intl.NumberFormat('id-ID').format(v));

const advanceForm = useForm({ note: '' });

function submitAdvance() {
    advanceForm.patch(route('provider.orders.advance', props.order.id), {
        preserveScroll: true,
        onSuccess: () => (advanceForm.note = ''),
    });
}

const showIssueModal = ref(false);
const issueForm = useForm({ reason: '', note: '' });

function submitIssue() {
    issueForm.patch(route('provider.orders.reportIssue', props.order.id), {
        preserveScroll: true,
        onSuccess: () => (showIssueModal.value = false),
    });
}

const paymentStatusTones = { pending: 'yellow', diterima: 'green', ditolak: 'red' };
const paymentStatusLabels = { pending: 'Menunggu Verifikasi', diterima: 'Diterima', ditolak: 'Ditolak' };

const verifyForm = useForm({ status: '', note: '' });

function verifyPayment(status) {
    verifyForm.status = status;
    verifyForm.patch(route('provider.orders.payment.verify', props.order.id), { preserveScroll: true });
}
</script>

<template>
    <Head :title="`Order ${order.kode_order}`" />

    <ProviderLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <Link :href="route('provider.orders.index')" class="text-sm text-gray-500 hover:underline dark:text-gray-400">
                        ← Kembali ke daftar pesanan
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
                <h2 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Detail Pemesan</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Nama</dt>
                        <dd class="text-gray-800 dark:text-gray-100">{{ order.pemesan }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Divisi / Lantai</dt>
                        <dd class="text-gray-800 dark:text-gray-100">{{ order.divisi }} / Lt.{{ order.lantai }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-400">No. HP</dt>
                        <dd><WhatsAppLink :phone="order.pemesan_no_hp" /></dd>
                    </div>
                    <div v-if="order.notes" class="flex justify-between">
                        <dt class="text-gray-400">Catatan</dt>
                        <dd class="text-gray-800 dark:text-gray-100">{{ order.notes }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
                <h2 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Daftar Menu</h2>
                <ul class="divide-y divide-gray-50 dark:divide-gray-800/50">
                    <li v-for="item in order.items" :key="item.id" class="flex justify-between py-2 text-sm">
                        <div>
                            <p class="text-gray-800 dark:text-gray-100">{{ item.nama_menu }} x{{ item.qty }}</p>
                            <p v-if="item.note" class="text-xs text-gray-400">{{ item.note }}</p>
                        </div>
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
                    <p class="text-gray-500 dark:text-gray-400">
                        Metode: <span class="font-medium uppercase">{{ order.payment_method }}</span>
                    </p>
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
                    Metode: <span class="font-medium uppercase">{{ order.payment.method }}</span> ·
                    {{ rupiah(order.payment.amount) }}
                </p>

                <img
                    v-if="order.payment.proof_url"
                    :src="order.payment.proof_url"
                    class="mt-3 max-h-64 rounded-lg border border-gray-100 object-contain dark:border-gray-800"
                />
                <p v-else-if="order.payment.method !== 'cash'" class="mt-2 text-xs text-gray-400">
                    Pemesan belum mengunggah bukti pembayaran.
                </p>

                <div v-if="order.payment.status === 'pending'" class="mt-3 flex gap-2">
                    <PrimaryButton :disabled="verifyForm.processing" @click="verifyPayment('diterima')">
                        Terima Pembayaran
                    </PrimaryButton>
                    <DangerButton :disabled="verifyForm.processing" @click="verifyPayment('ditolak')">
                        Tolak Pembayaran
                    </DangerButton>
                </div>
                <InputError class="mt-1" :message="verifyForm.errors.status" />
            </div>

            <div
                v-if="order.status === 'menunggu' && order.confirm_deadline"
                class="rounded-lg bg-yellow-50 p-3 text-sm text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200"
            >
                ⏱ Konfirmasi sebelum {{ new Date(order.confirm_deadline).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }},
                atau order otomatis dibatalkan.
            </div>

            <div v-if="!isTerminal" class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
                <h2 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Aksi</h2>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <PrimaryButton class="justify-center" :disabled="advanceForm.processing" @click="submitAdvance">
                        {{ advanceLabel }}
                    </PrimaryButton>
                    <SecondaryButton class="justify-center" @click="showIssueModal = true">
                        Laporkan Kendala
                    </SecondaryButton>
                </div>
            </div>

            <div v-if="order.issues?.length" class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
                <h2 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Kendala Dilaporkan</h2>
                <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-300">
                    <li v-for="(issue, i) in order.issues" :key="i">
                        <span class="font-medium">{{ reasonLabels[issue.reason] ?? issue.reason }}</span>
                        <span v-if="issue.note"> — {{ issue.note }}</span>
                    </li>
                </ul>
            </div>

            <div>
                <h2 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Chat dengan Pemesan</h2>
                <ChatThread
                    :order-id="order.id"
                    :messages="order.messages ?? []"
                    send-route-name="provider.orders.messages.store"
                />
            </div>
        </div>

        <Modal :show="showIssueModal" @close="showIssueModal = false">
            <form class="p-6" @submit.prevent="submitIssue">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Laporkan Kendala</h2>
                <div class="mt-4">
                    <InputLabel for="reason" value="Alasan" />
                    <select
                        id="reason"
                        v-model="issueForm.reason"
                        class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                        required
                    >
                        <option value="">— Pilih alasan —</option>
                        <option v-for="(label, key) in selectableReasons" :key="key" :value="key">{{ label }}</option>
                    </select>
                </div>
                <div class="mt-4">
                    <InputLabel for="issue_note" value="Catatan" />
                    <textarea
                        id="issue_note"
                        v-model="issueForm.note"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    />
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showIssueModal = false">Batal</SecondaryButton>
                    <DangerButton :disabled="issueForm.processing">Laporkan & Batalkan Order</DangerButton>
                </div>
            </form>
        </Modal>
    </ProviderLayout>
</template>
