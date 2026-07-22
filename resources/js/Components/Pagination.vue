<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    meta: { type: Object, required: true }, // Laravel paginator meta (current_page, last_page, links, total, from, to)
});
</script>

<template>
    <div
        v-if="meta.last_page > 1"
        class="mt-4 flex flex-col items-center justify-between gap-3 sm:flex-row"
    >
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Menampilkan {{ meta.from }}-{{ meta.to }} dari {{ meta.total }} data
        </p>
        <div class="flex flex-wrap gap-1">
            <template v-for="(link, i) in meta.links" :key="i">
                <span
                    v-if="!link.url"
                    class="rounded-md px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600"
                    v-html="link.label"
                />
                <Link
                    v-else
                    :href="link.url"
                    preserve-scroll
                    class="rounded-md px-3 py-1.5 text-sm"
                    :class="
                        link.active
                            ? 'bg-primary-600 text-white'
                            : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'
                    "
                    v-html="link.label"
                />
            </template>
        </div>
    </div>
</template>
