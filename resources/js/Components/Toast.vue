<script setup>
import { ref } from 'vue';

const toasts = ref([]);
let nextId = 0;

function push(message) {
    const id = nextId++;
    toasts.value.push({ id, message });
    setTimeout(() => {
        toasts.value = toasts.value.filter((t) => t.id !== id);
    }, 5000);
}

defineExpose({ push });
</script>

<template>
    <Teleport to="body">
        <div class="pointer-events-none fixed bottom-4 right-4 z-50 flex flex-col gap-2">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="pointer-events-auto max-w-sm rounded-lg bg-gray-900 px-4 py-3 text-sm text-white shadow-lg dark:bg-gray-700"
            >
                {{ toast.message }}
            </div>
        </div>
    </Teleport>
</template>
