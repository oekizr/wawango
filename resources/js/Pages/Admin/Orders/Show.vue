<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/Badge.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import WhatsAppLink from '@/Components/WhatsAppLink.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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

const reasonLabels = {
    toko_tutup: 'Toko Tutup',
    menu_habis: 'Menu Habis',
    barang_tidak_ada: 'Barang Tidak Ada',
    cuaca: 'Cuaca',
    tidak_dikonfirmasi: 'Tidak Dikonfirmasi',
    lainnya: 'Lainnya',
};

// "Tidak Dikonfirmasi" is set automatically by the system (10-minute
// provider timeout) — not something an admin picks when cancelling manually.
const selectableReasons = {
    toko_tutup: 'Toko Tutup',
    menu_habis: 'Menu Habis',
    barang_tidak_ada: 'Barang Tidak Ada',
    cuaca: 'Cuaca',
    lainnya: 'Lainnya',
};

const isTerminal = computed(() => ['selesai', 'dibatalkan'].includes(props.order.status));
const rupiah = (v) => 'Rp'.concat(new Intl.NumberFormat('id-ID').format(v));

const statusForm = useForm({ status: props.order.status, note: '' });

function submitStatus() {
    statusForm.patch(route('admin.orders.updateStatus', props.order.id), {
        preserveScroll: true,
        onSuccess: () => (statusForm.note = ''),
    });
}

const showCancelModal = ref(false);
const cancelForm = useForm({ reason: '', note: '' });

function submitCancel() {
    cancelForm.patch(route('admin.orders.cancel', props.order.id), {
        preserveScroll: true,
        onSuccess: () => (showCancelModal.value = false),
    });
}
</script>

<template>
    <Head :title="`Order ${order.kode_order}`" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <Link :href="route('admin.orders.index')" class="text-sm text-gray-500 hover:underline dark:text-gray-400">
                        ← Kembali ke daftar order
                    </Link>
                    <h1 class="mt-1 text-xl font-semibold text-gray-800 dark:text-gray-100">
                        Order {{ order.kode_order }}
                    </h1>
                </div>
                <Badge :tone="statusTones[order.status]">{{ statusLabels[order.status] }}</Badge>
            </div>
        </template>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
                    <h2 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Detail Pesanan</h2>
                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <dt class="text-gray-400">Pemesan</dt>
                            <dd class="text-gray-800 dark:text-gray-100">{{ order.pemesan }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-400">Divisi / Lantai</dt>
                            <dd class="text-gray-800 dark:text-gray-100">{{ order.divisi }} / Lt.{{ order.lantai }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-400">No. HP Pemesan</dt>
                            <dd><WhatsAppLink :phone="order.pemesan_no_hp" /></dd>
                        </div>
                        <div>
                            <dt class="text-gray-400">Toko</dt>
                            <dd class="text-gray-800 dark:text-gray-100">{{ order.toko }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-400">Penyedia Jasa</dt>
                            <dd class="text-gray-800 dark:text-gray-100">{{ order.provider }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-gray-400">Catatan</dt>
                            <dd class="text-gray-800 dark:text-gray-100">{{ order.notes || '-' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
                    <h2 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Item Pesanan</h2>
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                <th class="py-1">Menu</th>
                                <th class="py-1 text-center">Qty</th>
                                <th class="py-1 text-right">Harga</th>
                                <th class="py-1 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in order.items" :key="item.id" class="border-b border-gray-50 dark:border-gray-800/50">
                                <td class="py-1.5">
                                    {{ item.nama_menu }}
                                    <div v-if="item.note" class="text-xs text-gray-400">{{ item.note }}</div>
                                </td>
                                <td class="py-1.5 text-center">{{ item.qty }}</td>
                                <td class="py-1.5 text-right tabular-nums">{{ rupiah(item.price) }}</td>
                                <td class="py-1.5 text-right tabular-nums">{{ rupiah(item.subtotal) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mt-3 space-y-1 text-sm">
                        <div class="flex justify-between text-gray-500 dark:text-gray-400">
                            <span>Subtotal</span><span class="tabular-nums">{{ rupiah(order.subtotal) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-500 dark:text-gray-400">
                            <span>Biaya Jasa</span><span class="tabular-nums">{{ rupiah(order.service_fee) }}</span>
                        </div>
                        <div class="flex justify-between text-base font-semibold text-gray-800 dark:text-gray-100">
                            <span>Total</span><span class="tabular-nums">{{ rupiah(order.total) }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
                    <h2 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Pembayaran</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        Metode: <span class="font-medium uppercase">{{ order.payment_method }}</span>
                        <span v-if="order.payment"> — Status: {{ order.payment.status }}</span>
                    </p>
                </div>

                <div v-if="order.issues?.length" class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
                    <h2 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Kendala Pesanan</h2>
                    <ul class="space-y-2 text-sm">
                        <li v-for="(issue, i) in order.issues" :key="i" class="text-gray-600 dark:text-gray-300">
                            <span class="font-medium">{{ reasonLabels[issue.reason] ?? issue.reason }}</span>
                            <span v-if="issue.note"> — {{ issue.note }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
                    <h2 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Timeline Status</h2>
                    <ol class="space-y-3">
                        <li v-for="(h, i) in order.status_histories" :key="i" class="flex gap-3 text-sm">
                            <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-primary-500" />
                            <div>
                                <div class="font-medium text-gray-800 dark:text-gray-100">
                                    {{ statusLabels[h.status] ?? h.status }}
                                </div>
                                <div class="text-xs text-gray-400">
                                    {{ new Date(h.created_at).toLocaleString('id-ID') }}
                                    <span v-if="h.changed_by"> · {{ h.changed_by }}</span>
                                </div>
                                <div v-if="h.note" class="text-xs text-gray-500 dark:text-gray-400">{{ h.note }}</div>
                            </div>
                        </li>
                    </ol>
                </div>

                <div v-if="!isTerminal" class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
                    <h2 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Ubah Status</h2>
                    <form @submit.prevent="submitStatus" class="space-y-3">
                        <select
                            v-model="statusForm.status"
                            class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                        >
                            <option value="menunggu">Menunggu</option>
                            <option value="diproses">Diproses</option>
                            <option value="dibelikan">Dibelikan</option>
                            <option value="diantar">Dalam Pengantaran</option>
                            <option value="selesai">Selesai</option>
                        </select>
                        <textarea
                            v-model="statusForm.note"
                            rows="2"
                            placeholder="Catatan (opsional)"
                            class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                        />
                        <PrimaryButton class="w-full justify-center" :disabled="statusForm.processing">
                            Simpan Status
                        </PrimaryButton>
                    </form>

                    <SecondaryButton class="mt-3 w-full justify-center" @click="showCancelModal = true">
                        Batalkan Order
                    </SecondaryButton>
                </div>
            </div>
        </div>

        <Modal :show="showCancelModal" @close="showCancelModal = false">
            <form class="p-6" @submit.prevent="submitCancel">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Batalkan Order</h2>
                <div class="mt-4">
                    <InputLabel for="reason" value="Alasan" />
                    <select
                        id="reason"
                        v-model="cancelForm.reason"
                        class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    >
                        <option value="">— Pilih alasan (opsional) —</option>
                        <option v-for="(label, key) in selectableReasons" :key="key" :value="key">{{ label }}</option>
                    </select>
                </div>
                <div class="mt-4">
                    <InputLabel for="cancel_note" value="Catatan" />
                    <textarea
                        id="cancel_note"
                        v-model="cancelForm.note"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    />
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showCancelModal = false">Batal</SecondaryButton>
                    <DangerButton :disabled="cancelForm.processing">Ya, Batalkan Order</DangerButton>
                </div>
            </form>
        </Modal>
    </AdminLayout>
</template>
