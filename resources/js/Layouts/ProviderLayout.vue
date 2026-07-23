<script setup>
import { Link } from '@inertiajs/vue3';
import RoleTopbar from '@/Components/RoleTopbar.vue';

const navigation = [
    { name: 'Dashboard', href: 'provider.dashboard', active: 'provider.dashboard' },
    { name: 'Toko', href: 'provider.stores.index', active: 'provider.stores.*' },
    { name: 'Menu', href: 'provider.menus.index', active: 'provider.menus.*' },
    { name: 'Pesanan', href: 'provider.orders.index', active: 'provider.orders.*' },
];
</script>

<template>
    <div class="min-h-screen bg-surface-muted pb-16 dark:bg-surface-dark sm:pb-0">
        <RoleTopbar role-label="Penyedia Jasa" />

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
                    route().current(item.active)
                        ? 'text-primary-600'
                        : 'text-gray-400'
                "
            >
                {{ item.name }}
            </Link>
        </nav>

        <!-- Nav desktop -->
        <nav
            class="mx-auto hidden max-w-3xl gap-1 px-4 pb-4 sm:flex sm:px-6"
        >
            <Link
                v-for="item in navigation"
                :key="item.name"
                :href="route(item.href)"
                class="rounded-lg px-3 py-1.5 text-sm font-medium"
                :class="
                    route().current(item.active)
                        ? 'bg-primary-50 text-primary-700 dark:bg-gray-800'
                        : 'text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800'
                "
            >
                {{ item.name }}
            </Link>
        </nav>
    </div>
</template>
