<script setup>
import ProviderLayout from '@/Layouts/ProviderLayout.vue';
import ScheduleEditor from '@/Components/ScheduleEditor.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    schedules: { type: Array, required: true },
});

const form = useForm({
    schedules: props.schedules,
});

function submit() {
    form.put(route('provider.schedule.update'));
}
</script>

<template>
    <Head title="Jadwal Layanan" />

    <ProviderLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Jadwal Layanan</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Di luar jadwal ini, status otomatis Tutup dan pemesan tidak bisa checkout.
            </p>
        </template>

        <form
            class="max-w-xl space-y-4 rounded-xl bg-white p-6 shadow-sm dark:bg-surface-darkMuted"
            @submit.prevent="submit"
        >
            <ScheduleEditor v-model="form.schedules" />
            <InputError :message="form.errors.schedules" />

            <div class="flex justify-end">
                <PrimaryButton :disabled="form.processing">Simpan Jadwal</PrimaryButton>
            </div>
        </form>
    </ProviderLayout>
</template>
