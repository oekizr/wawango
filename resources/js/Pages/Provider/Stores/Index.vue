<script setup>
import ProviderLayout from '@/Layouts/ProviderLayout.vue';
import Badge from '@/Components/Badge.vue';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    stores: { type: Array, required: true },
});

const confirmingDelete = ref(null);

function destroy() {
    router.delete(route('provider.stores.destroy', confirmingDelete.value.id), {
        onFinish: () => (confirmingDelete.value = null),
    });
}
</script>

<template>
    <Head title="Toko Saya" />

    <ProviderLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Toko Saya</h1>
                <Link :href="route('provider.stores.create')">
                    <PrimaryButton>Tambah Toko</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="space-y-3">
            <div
                v-for="store in stores"
                :key="store.id"
                class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-semibold text-gray-800 dark:text-gray-100">{{ store.nama_toko }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ store.lokasi }}</p>
                        <p class="mt-1 text-xs text-gray-400">
                            Biaya jasa Rp{{ new Intl.NumberFormat('id-ID').format(store.service_fee) }} ·
                            {{ store.menus_count }} menu
                        </p>
                    </div>
                    <Badge :tone="store.status === 'aktif' ? 'green' : 'gray'">
                        {{ store.status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                    </Badge>
                </div>
                <div class="mt-3 flex gap-3 text-sm">
                    <Link :href="route('provider.stores.edit', store.id)" class="text-primary-600 hover:underline">
                        Edit
                    </Link>
                    <button type="button" class="text-red-600 hover:underline" @click="confirmingDelete = store">
                        Hapus
                    </button>
                </div>
            </div>

            <p v-if="stores.length === 0" class="py-8 text-center text-gray-400">Belum ada toko.</p>
        </div>

        <Modal :show="!!confirmingDelete" @close="confirmingDelete = null">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Hapus toko?</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    "{{ confirmingDelete?.nama_toko }}" akan dihapus dari daftar toko aktif.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="confirmingDelete = null">Batal</SecondaryButton>
                    <DangerButton @click="destroy">Hapus</DangerButton>
                </div>
            </div>
        </Modal>
    </ProviderLayout>
</template>
