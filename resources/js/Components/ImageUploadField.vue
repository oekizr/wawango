<script setup>
import { ref } from 'vue';

const props = defineProps({
    label: { type: String, required: true },
    existingUrl: { type: String, default: null },
    error: { type: String, default: null },
});

const file = defineModel({ default: null });
const preview = ref(props.existingUrl);

function onChange(e) {
    const selected = e.target.files[0];
    file.value = selected ?? null;
    preview.value = selected ? URL.createObjectURL(selected) : props.existingUrl;
}
</script>

<template>
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
            {{ label }}
        </label>
        <div class="flex items-center gap-4">
            <img
                v-if="preview"
                :src="preview"
                class="h-16 w-16 rounded-lg border border-gray-200 object-cover dark:border-gray-700"
            />
            <div
                v-else
                class="flex h-16 w-16 items-center justify-center rounded-lg bg-gray-100 text-xs text-gray-400 dark:bg-gray-800"
            >
                Belum ada
            </div>
            <input
                type="file"
                accept="image/*"
                class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-primary-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-primary-700 hover:file:bg-primary-100 dark:text-gray-300"
                @change="onChange"
            />
        </div>
        <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    </div>
</template>
