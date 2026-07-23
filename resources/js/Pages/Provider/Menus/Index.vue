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
    menus: { type: Array, required: true },
});

const confirmingDelete = ref(null);

function destroy() {
    router.delete(route('provider.menus.destroy', confirmingDelete.value.id), {
        onFinish: () => (confirmingDelete.value = null),
    });
}
</script>

<template>
    <Head title="Menu Saya" />

    <ProviderLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Menu Saya</h1>
                <Link :href="route('provider.menus.create')">
                    <PrimaryButton>Tambah Menu</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            <div
                v-for="menu in menus"
                :key="menu.id"
                class="rounded-xl bg-white p-3 shadow-sm dark:bg-surface-darkMuted"
            >
                <img
                    v-if="menu.foto_url"
                    :src="menu.foto_url"
                    class="mb-2 h-24 w-full rounded-lg object-cover"
                />
                <div v-else class="mb-2 flex h-24 w-full items-center justify-center rounded-lg bg-gray-100 text-xs text-gray-400 dark:bg-gray-800">
                    Belum ada foto
                </div>
                <p class="font-medium text-gray-800 dark:text-gray-100">{{ menu.nama }}</p>
                <p class="text-xs text-gray-400">{{ menu.toko }}</p>
                <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-200">
                    Rp{{ new Intl.NumberFormat('id-ID').format(menu.harga) }}
                </p>
                <Badge class="mt-1" :tone="menu.status === 'tersedia' ? 'green' : 'gray'">
                    {{ menu.status === 'tersedia' ? 'Tersedia' : 'Habis' }}
                </Badge>
                <div class="mt-2 flex gap-3 text-sm">
                    <Link :href="route('provider.menus.edit', menu.id)" class="text-primary-600 hover:underline">Edit</Link>
                    <button type="button" class="text-red-600 hover:underline" @click="confirmingDelete = menu">Hapus</button>
                </div>
            </div>
        </div>

        <p v-if="menus.length === 0" class="py-8 text-center text-gray-400">
            Belum ada menu. Tambahkan toko terlebih dahulu sebelum menambah menu.
        </p>

        <Modal :show="!!confirmingDelete" @close="confirmingDelete = null">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Hapus menu?</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    "{{ confirmingDelete?.nama }}" akan dihapus dari daftar menu.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="confirmingDelete = null">Batal</SecondaryButton>
                    <DangerButton @click="destroy">Hapus</DangerButton>
                </div>
            </div>
        </Modal>
    </ProviderLayout>
</template>
