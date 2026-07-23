<script setup>
import PemesanLayout from '@/Layouts/PemesanLayout.vue';
import Badge from '@/Components/Badge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    providers: { type: Array, required: true },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');

function applySearch() {
    router.get(route('pemesan.providers.index'), { search: search.value }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Cari Penyedia Jasa" />

    <PemesanLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Cari Penyedia Jasa</h1>
        </template>

        <input
            v-model="search"
            type="text"
            placeholder="Cari nama penyedia jasa..."
            class="mb-4 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
            @keyup.enter="applySearch"
        />

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <component
                :is="provider.is_open ? Link : 'div'"
                v-for="provider in providers"
                :key="provider.id"
                :href="provider.is_open ? route('pemesan.providers.show', provider.id) : undefined"
                class="rounded-xl bg-white p-4 shadow-sm dark:bg-surface-darkMuted"
                :class="provider.is_open ? 'hover:bg-gray-50 dark:hover:bg-gray-800' : 'opacity-60'"
            >
                <div class="flex items-center justify-between">
                    <p class="font-semibold text-gray-800 dark:text-gray-100">{{ provider.name }}</p>
                    <Badge :tone="provider.is_open ? 'green' : 'gray'">
                        {{ provider.is_open ? 'Buka' : 'Tutup' }}
                    </Badge>
                </div>
                <ul class="mt-2 space-y-0.5 text-xs text-gray-400">
                    <li v-for="(s, i) in provider.schedules" :key="i">
                        {{ s.day }}: {{ s.open_time }}–{{ s.close_time }}
                    </li>
                </ul>
            </component>

            <p v-if="providers.length === 0" class="col-span-2 py-8 text-center text-gray-400">
                Belum ada penyedia jasa yang aktif.
            </p>
        </div>
    </PemesanLayout>
</template>
