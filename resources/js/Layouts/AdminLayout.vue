<script setup>
import { Link } from '@inertiajs/vue3';
import RoleTopbar from '@/Components/RoleTopbar.vue';

const navigation = [
    { name: 'Dashboard', href: 'admin.dashboard', active: 'admin.dashboard', enabled: true },
    { name: 'Penyedia Jasa', href: 'admin.providers.index', active: 'admin.providers.*', enabled: true },
    { name: 'User', href: 'admin.users.index', active: 'admin.users.*', enabled: true },
    { name: 'Order', href: 'admin.orders.index', active: 'admin.orders.*', enabled: true },
];
</script>

<template>
    <div class="min-h-screen bg-surface-muted dark:bg-surface-dark">
        <RoleTopbar role-label="Admin" />

        <div class="mx-auto flex max-w-7xl">
            <aside
                class="hidden w-56 shrink-0 border-r border-gray-100 bg-white px-3 py-6 dark:border-gray-800 dark:bg-surface-darkMuted md:block"
            >
                <nav class="space-y-1">
                    <Link
                        v-for="item in navigation.filter((i) => i.enabled)"
                        :key="item.name"
                        :href="route(item.href)"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-primary-50 hover:text-primary-700 dark:text-gray-300 dark:hover:bg-gray-800"
                        :class="{
                            'bg-primary-50 text-primary-700 dark:bg-gray-800':
                                route().current(item.active),
                        }"
                    >
                        {{ item.name }}
                    </Link>
                    <span
                        v-for="item in navigation.filter((i) => !i.enabled)"
                        :key="item.name"
                        class="flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-gray-400 dark:text-gray-600"
                    >
                        {{ item.name }}
                        <span class="text-xs">Segera</span>
                    </span>
                </nav>
            </aside>

            <main class="min-w-0 flex-1 px-4 py-6 sm:px-6 lg:px-8">
                <header v-if="$slots.header" class="mb-6">
                    <slot name="header" />
                </header>
                <slot />
            </main>
        </div>
    </div>
</template>
