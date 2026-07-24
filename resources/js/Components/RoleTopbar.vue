<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import Toast from '@/Components/Toast.vue';
import Echo from '@/echo';
import { useAuthStore } from '@/stores/auth';
import { useNotificationsStore } from '@/stores/notifications';
import { useUiStore } from '@/stores/ui';
import { computed, onMounted, onUnmounted, ref } from 'vue';

defineProps({
    roleLabel: { type: String, required: true },
});

const auth = useAuthStore();
const ui = useUiStore();
const notificationsStore = useNotificationsStore();
const toastRef = ref(null);

const notifications = computed(() => ({
    unread_count: notificationsStore.unreadCount,
    items: notificationsStore.items,
}));

function resolveUrl(orderId) {
    if (!orderId) return null;

    return {
        admin: route('admin.orders.show', orderId),
        penyedia_jasa: route('provider.orders.show', orderId),
        pemesan: route('pemesan.orders.show', orderId),
    }[auth.role] ?? null;
}

function playBeep() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = ctx.createOscillator();
        const gain = ctx.createGain();
        oscillator.type = 'sine';
        oscillator.frequency.value = 880;
        gain.gain.setValueAtTime(0.15, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
        oscillator.connect(gain);
        gain.connect(ctx.destination);
        oscillator.start();
        oscillator.stop(ctx.currentTime + 0.3);
    } catch {
        // sound is a nice-to-have, never block on it
    }
}

onMounted(() => {
    notificationsStore.hydrate(usePage().props.notifications);

    if (auth.user?.id) {
        Echo.private(`App.Models.User.${auth.user.id}`).notification((notification) => {
            notificationsStore.pushLive({ ...notification, url: resolveUrl(notification.order_id) });
            playBeep();
            toastRef.value?.push(notification.message);
        });
    }
});

onUnmounted(() => {
    if (auth.user?.id) {
        Echo.leave(`App.Models.User.${auth.user.id}`);
    }
});

function openNotification(notification) {
    router.post(
        route('notifications.read', notification.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                if (notification.url) {
                    router.visit(notification.url);
                }
            },
        },
    );
}
</script>

<template>
    <header
        class="border-b border-gray-100 bg-white dark:border-gray-800 dark:bg-surface-darkMuted"
    >
        <div
            class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8"
        >
            <div class="flex items-center gap-3">
                <ApplicationLogo />
                <span
                    class="hidden rounded-full bg-primary-100 px-2.5 py-0.5 text-xs font-medium text-primary-700 dark:bg-primary-900 dark:text-primary-200 sm:inline-block"
                >
                    {{ roleLabel }}
                </span>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="rounded-full p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                    @click="ui.toggleDarkMode"
                >
                    <svg
                        v-if="ui.darkMode"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
                        />
                    </svg>
                    <svg
                        v-else
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                        />
                    </svg>
                </button>

                <Dropdown align="right" width="80" content-classes="py-1 bg-white dark:bg-gray-800">
                    <template #trigger>
                        <button
                            type="button"
                            class="relative rounded-full p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                                />
                            </svg>
                            <span
                                v-if="notifications.unread_count > 0"
                                class="absolute right-1 top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-semibold text-white"
                            >
                                {{ notifications.unread_count > 9 ? '9+' : notifications.unread_count }}
                            </span>
                        </button>
                    </template>
                    <template #content>
                        <div class="max-h-80 overflow-y-auto">
                            <p
                                v-if="notifications.items.length === 0"
                                class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400"
                            >
                                Tidak ada notifikasi baru.
                            </p>
                            <button
                                v-for="item in notifications.items"
                                :key="item.id"
                                type="button"
                                class="block w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800"
                                @click="openNotification(item)"
                            >
                                <p :class="item.is_read ? 'font-normal' : 'font-semibold'">{{ item.message }}</p>
                                <p class="mt-0.5 text-xs text-gray-400">
                                    {{ new Date(item.created_at).toLocaleString('id-ID') }}
                                </p>
                            </button>
                        </div>
                    </template>
                </Dropdown>

                <Dropdown align="right" width="48">
                    <template #trigger>
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none dark:bg-surface-darkMuted dark:text-gray-300"
                        >
                            {{ auth.user?.name }}
                            <svg
                                class="-me-0.5 ms-2 h-4 w-4"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>
                    </template>
                    <template #content>
                        <DropdownLink :href="route('profile.edit')">
                            Profile
                        </DropdownLink>
                        <DropdownLink
                            :href="route('logout')"
                            method="post"
                            as="button"
                        >
                            Log Out
                        </DropdownLink>
                    </template>
                </Dropdown>
            </div>
        </div>
    </header>

    <Toast ref="toastRef" />
</template>
