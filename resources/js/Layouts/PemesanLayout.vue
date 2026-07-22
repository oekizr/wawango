<script setup>
import { Link } from '@inertiajs/vue3';
import RoleTopbar from '@/Components/RoleTopbar.vue';

const navigation = [
    { name: 'Beranda', href: 'pemesan.dashboard' },
];
</script>

<template>
    <div class="min-h-screen bg-surface-muted pb-16 dark:bg-surface-dark sm:pb-0">
        <RoleTopbar role-label="Pemesan" />

        <main class="mx-auto max-w-3xl px-4 py-6 sm:px-6">
            <header v-if="$slots.header" class="mb-6">
                <slot name="header" />
            </header>
            <slot />
        </main>

        <!-- Bottom nav mobile -->
        <nav
            class="fixed inset-x-0 bottom-0 flex border-t border-gray-100 bg-white dark:border-gray-800 dark:bg-surface-darkMuted sm:hidden"
        >
            <Link
                v-for="item in navigation"
                :key="item.name"
                :href="route(item.href)"
                class="flex flex-1 flex-col items-center gap-1 py-2 text-xs font-medium"
                :class="
                    route().current(item.href)
                        ? 'text-primary-600'
                        : 'text-gray-400'
                "
            >
                {{ item.name }}
            </Link>
        </nav>
    </div>
</template>
