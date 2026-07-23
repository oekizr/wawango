<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    orderId: { type: [Number, String], required: true },
    messages: { type: Array, default: () => [] },
    sendRouteName: { type: String, required: true }, // e.g. 'provider.orders.messages.store'
    pollSeconds: { type: Number, default: 5 },
});

const form = useForm({ body: '' });
const scrollArea = ref(null);
let pollTimer = null;

function scrollToBottom() {
    nextTick(() => {
        if (scrollArea.value) {
            scrollArea.value.scrollTop = scrollArea.value.scrollHeight;
        }
    });
}

function send() {
    if (!form.body.trim()) return;

    form.post(route(props.sendRouteName, props.orderId), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('body');
            scrollToBottom();
        },
    });
}

onMounted(() => {
    scrollToBottom();

    pollTimer = setInterval(() => {
        router.reload({ only: ['order'], preserveScroll: true });
    }, props.pollSeconds * 1000);
});

onUnmounted(() => {
    if (pollTimer) clearInterval(pollTimer);
});

watch(
    () => props.messages.length,
    () => scrollToBottom(),
);
</script>

<template>
    <div class="flex h-96 flex-col rounded-xl bg-white shadow-sm dark:bg-surface-darkMuted">
        <div ref="scrollArea" class="flex-1 space-y-2 overflow-y-auto p-4">
            <p v-if="messages.length === 0" class="text-center text-sm text-gray-400">
                Belum ada pesan. Mulai percakapan di bawah.
            </p>
            <div
                v-for="message in messages"
                :key="message.id"
                class="flex"
                :class="message.is_mine ? 'justify-end' : 'justify-start'"
            >
                <div
                    class="max-w-[75%] rounded-2xl px-3 py-2 text-sm"
                    :class="
                        message.is_mine
                            ? 'bg-primary-600 text-white'
                            : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100'
                    "
                >
                    <p v-if="!message.is_mine" class="mb-0.5 text-xs font-semibold opacity-70">
                        {{ message.sender_name }}
                    </p>
                    <p class="whitespace-pre-wrap break-words">{{ message.body }}</p>
                    <p class="mt-1 text-right text-[10px] opacity-60">
                        {{ new Date(message.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                    </p>
                </div>
            </div>
        </div>

        <form
            class="flex items-center gap-2 border-t border-gray-100 p-3 dark:border-gray-800"
            @submit.prevent="send"
        >
            <input
                v-model="form.body"
                type="text"
                placeholder="Tulis pesan..."
                class="flex-1 rounded-full border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
            />
            <button
                type="submit"
                :disabled="form.processing || !form.body.trim()"
                class="rounded-full bg-primary-600 p-2 text-white hover:bg-primary-700 disabled:opacity-40"
            >
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2.94 2.94a1.5 1.5 0 011.6-.34l13 5a1.5 1.5 0 010 2.8l-13 5a1.5 1.5 0 01-1.99-1.83L4.5 10 2.55 4.77a1.5 1.5 0 01.39-1.83z" />
                </svg>
            </button>
        </form>
    </div>
</template>
