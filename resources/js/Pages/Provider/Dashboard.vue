<script setup>
import ProviderLayout from '@/Layouts/ProviderLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/auth';

const props = defineProps({
    stats: { type: Object, required: true },
});

const auth = useAuthStore();

const rupiah = (v) => 'Rp'.concat(new Intl.NumberFormat('id-ID').format(v));

function toggleStatus() {
    router.post(route('provider.status.toggle'), {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Dashboard Penyedia Jasa" />

    <ProviderLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
                Halo, {{ auth.user?.name }}
            </h1>
        </template>

        <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Status Layanan
                    </p>
                    <p
                        class="mt-1 flex items-center gap-2 text-lg font-semibold"
                        :class="stats.is_open ? 'text-primary-600' : 'text-gray-400'"
                    >
                        <span
                            class="h-2.5 w-2.5 rounded-full"
                            :class="stats.is_open ? 'bg-primary-500' : 'bg-gray-300'"
                        />
                        {{ stats.is_open ? 'Buka' : 'Tutup' }}
                    </p>
                    <p v-if="!stats.is_within_schedule" class="mt-1 text-xs text-gray-400">
                        Di luar jadwal layanan hari ini.
                    </p>
                </div>
                <button
                    v-if="stats.is_manually_closed || stats.is_within_schedule"
                    class="rounded-lg px-4 py-2 text-sm font-medium text-white"
                    :class="stats.is_open ? 'bg-red-600 hover:bg-red-700' : 'bg-primary-600 hover:bg-primary-700'"
                    @click="toggleStatus"
                >
                    {{ stats.is_open ? 'Tutup' : 'Buka' }}
                </button>
            </div>
            <Link
                :href="route('provider.schedule.edit')"
                class="mt-3 inline-block text-sm text-primary-600 hover:underline"
            >
                Atur jadwal layanan →
            </Link>
        </div>

        <div class="mt-4 grid grid-cols-3 gap-4">
            <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
                <p class="text-sm text-gray-500 dark:text-gray-400">Order Aktif</p>
                <p class="mt-1 text-2xl font-semibold text-gray-800 dark:text-gray-100">{{ stats.orders_active }}</p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
                <p class="text-sm text-gray-500 dark:text-gray-400">Pendapatan Hari Ini</p>
                <p class="mt-1 text-xl font-semibold text-gray-800 dark:text-gray-100">{{ rupiah(stats.revenue_today) }}</p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted">
                <p class="text-sm text-gray-500 dark:text-gray-400">Pendapatan Bulan Ini</p>
                <p class="mt-1 text-xl font-semibold text-gray-800 dark:text-gray-100">{{ rupiah(stats.revenue_month) }}</p>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-4">
            <Link
                :href="route('provider.stores.index')"
                class="rounded-xl bg-white p-4 text-center shadow-sm hover:bg-gray-50 dark:bg-surface-darkMuted dark:hover:bg-gray-800"
            >
                <p class="font-medium text-gray-800 dark:text-gray-100">Kelola Toko & Menu</p>
            </Link>
            <Link
                :href="route('provider.orders.index')"
                class="rounded-xl bg-white p-4 text-center shadow-sm hover:bg-gray-50 dark:bg-surface-darkMuted dark:hover:bg-gray-800"
            >
                <p class="font-medium text-gray-800 dark:text-gray-100">Kelola Pesanan</p>
            </Link>
        </div>
    </ProviderLayout>
</template>
