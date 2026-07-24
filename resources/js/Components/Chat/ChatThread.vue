<script setup>
import { router, useForm } from '@inertiajs/vue3';
import Echo from '@/echo';
import { nextTick, onBeforeUnmount, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    orderId: { type: [Number, String], required: true },
    messages: { type: Array, default: () => [] },
    sendRouteName: { type: String, required: true }, // e.g. 'provider.orders.messages.store'
    // Realtime push (see onMounted below) is the primary delivery path; this
    // poll is just a slow fallback in case the socket connection drops.
    pollSeconds: { type: Number, default: 30 },
});

const form = useForm({ body: '', image: null });
const scrollArea = ref(null);
const fileInput = ref(null);
const cameraInput = ref(null);
const imagePreviewUrl = ref(null);
let pollTimer = null;

function scrollToBottom() {
    nextTick(() => {
        if (scrollArea.value) {
            scrollArea.value.scrollTop = scrollArea.value.scrollHeight;
        }
    });
}

function pickImage(input) {
    input.value?.click();
}

function onImageSelected(event) {
    const file = event.target.files[0];
    event.target.value = ''; // allow re-selecting the same file later

    if (!file) return;

    if (imagePreviewUrl.value) URL.revokeObjectURL(imagePreviewUrl.value);
    form.image = file;
    imagePreviewUrl.value = URL.createObjectURL(file);
}

function removeImage() {
    if (imagePreviewUrl.value) URL.revokeObjectURL(imagePreviewUrl.value);
    form.image = null;
    imagePreviewUrl.value = null;
}

function send() {
    if (!form.body.trim() && !form.image) return;

    form.post(route(props.sendRouteName, props.orderId), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset('body', 'image');
            removeImage();
            scrollToBottom();
        },
    });
}

onMounted(() => {
    scrollToBottom();

    Echo.private(`orders.${props.orderId}`).listen('.message.posted', () => {
        router.reload({ only: ['order'], preserveScroll: true });
    });

    pollTimer = setInterval(() => {
        router.reload({ only: ['order'], preserveScroll: true });
    }, props.pollSeconds * 1000);
});

onUnmounted(() => {
    if (pollTimer) clearInterval(pollTimer);
    Echo.leave(`orders.${props.orderId}`);
});

onBeforeUnmount(() => {
    if (imagePreviewUrl.value) URL.revokeObjectURL(imagePreviewUrl.value);
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
                    <a v-if="message.image_url" :href="message.image_url" target="_blank" rel="noopener">
                        <img
                            :src="message.image_url"
                            alt="Gambar terkirim"
                            class="mb-1 max-h-48 w-full rounded-lg object-cover"
                        />
                    </a>
                    <p v-if="message.body" class="whitespace-pre-wrap break-words">{{ message.body }}</p>
                    <p class="mt-1 text-right text-[10px] opacity-60">
                        {{ new Date(message.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                    </p>
                </div>
            </div>
        </div>

        <div v-if="imagePreviewUrl" class="flex items-center gap-2 border-t border-gray-100 px-3 pt-3 dark:border-gray-800">
            <div class="relative">
                <img :src="imagePreviewUrl" alt="Pratinjau gambar" class="h-16 w-16 rounded-lg object-cover" />
                <button
                    type="button"
                    class="absolute -right-1.5 -top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-gray-900 text-white shadow"
                    @click="removeImage"
                >
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <p class="text-xs text-gray-400">Gambar siap dikirim</p>
        </div>
        <p v-if="form.errors.image" class="px-3 pt-2 text-xs text-red-500">{{ form.errors.image }}</p>

        <form
            class="flex items-center gap-2 border-t border-gray-100 p-3 dark:border-gray-800"
            @submit.prevent="send"
        >
            <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="onImageSelected" />
            <input ref="cameraInput" type="file" accept="image/*" capture="environment" class="hidden" @change="onImageSelected" />

            <button
                type="button"
                title="Kirim gambar dari galeri"
                class="shrink-0 rounded-full p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                @click="pickImage(fileInput)"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M3 16.5V6a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2H8m-5-3.5l4.5-4.5a2 2 0 012.8 0L15 17M3 16.5V18a2 2 0 002 2h13M14 10a2 2 0 100-4 2 2 0 000 4z"
                    />
                </svg>
            </button>
            <button
                type="button"
                title="Ambil foto dengan kamera"
                class="shrink-0 rounded-full p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                @click="pickImage(cameraInput)"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"
                    />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </button>

            <input
                v-model="form.body"
                type="text"
                placeholder="Tulis pesan..."
                class="flex-1 rounded-full border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
            />
            <button
                type="submit"
                :disabled="form.processing || (!form.body.trim() && !form.image)"
                class="shrink-0 rounded-full bg-primary-600 p-2 text-white hover:bg-primary-700 disabled:opacity-40"
            >
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2.94 2.94a1.5 1.5 0 011.6-.34l13 5a1.5 1.5 0 010 2.8l-13 5a1.5 1.5 0 01-1.99-1.83L4.5 10 2.55 4.77a1.5 1.5 0 01.39-1.83z" />
                </svg>
            </button>
        </form>
    </div>
</template>
