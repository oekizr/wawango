<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/Badge.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    users: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

function applyFilters() {
    router.get(
        route('admin.users.index'),
        { search: search.value, status: status.value },
        { preserveState: true, replace: true },
    );
}

const confirmingDelete = ref(null);

function destroy() {
    router.delete(route('admin.users.destroy', confirmingDelete.value.id), {
        onFinish: () => (confirmingDelete.value = null),
    });
}
</script>

<template>
    <Head title="Manajemen User" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
                        Manajemen User
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Kelola akun pemesan.
                    </p>
                </div>
                <Link :href="route('admin.users.create')">
                    <PrimaryButton>Tambah User</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari nama atau email..."
                    class="flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    @keyup.enter="applyFilters"
                />
                <select
                    v-model="status"
                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    @change="applyFilters"
                >
                    <option value="">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
                <SecondaryButton @click="applyFilters">Cari</SecondaryButton>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-500 dark:border-gray-800 dark:text-gray-400">
                            <th class="py-2 pr-4">Nama</th>
                            <th class="py-2 pr-4">Divisi / Lantai</th>
                            <th class="py-2 pr-4">No. HP</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="user in users.data"
                            :key="user.id"
                            class="border-b border-gray-50 dark:border-gray-800/50"
                        >
                            <td class="py-2 pr-4">
                                <div class="font-medium text-gray-800 dark:text-gray-100">{{ user.name }}</div>
                                <div class="text-xs text-gray-400">{{ user.email }}</div>
                            </td>
                            <td class="py-2 pr-4 text-gray-600 dark:text-gray-300">
                                {{ user.divisi }} / Lt.{{ user.lantai }}
                            </td>
                            <td class="py-2 pr-4 text-gray-600 dark:text-gray-300">{{ user.no_hp }}</td>
                            <td class="py-2 pr-4">
                                <Badge :tone="user.status === 'aktif' ? 'green' : 'red'">
                                    {{ user.status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                                </Badge>
                            </td>
                            <td class="py-2 text-right">
                                <Link
                                    :href="route('admin.users.edit', user.id)"
                                    class="mr-3 text-sm text-primary-600 hover:underline"
                                >
                                    Edit
                                </Link>
                                <button
                                    type="button"
                                    class="text-sm text-red-600 hover:underline"
                                    @click="confirmingDelete = user"
                                >
                                    Hapus
                                </button>
                            </td>
                        </tr>
                        <tr v-if="users.data.length === 0">
                            <td colspan="5" class="py-8 text-center text-gray-400">Belum ada user.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :meta="users.meta" />
        </div>

        <Modal :show="!!confirmingDelete" @close="confirmingDelete = null">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Hapus user?</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    "{{ confirmingDelete?.name }}" akan dinonaktifkan dan tidak bisa login lagi.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="confirmingDelete = null">Batal</SecondaryButton>
                    <DangerButton @click="destroy">Hapus</DangerButton>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>
