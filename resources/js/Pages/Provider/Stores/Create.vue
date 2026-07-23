<script setup>
import ProviderLayout from '@/Layouts/ProviderLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    nama_toko: '',
    lokasi: '',
    deskripsi: '',
    service_fee: 10000,
    status: 'aktif',
});

function submit() {
    form.post(route('provider.stores.store'));
}
</script>

<template>
    <Head title="Tambah Toko" />

    <ProviderLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Tambah Toko</h1>
        </template>

        <form
            class="max-w-xl space-y-4 rounded-xl bg-white p-6 shadow-sm dark:bg-surface-darkMuted"
            @submit.prevent="submit"
        >
            <div>
                <InputLabel for="nama_toko" value="Nama Toko" />
                <TextInput id="nama_toko" v-model="form.nama_toko" class="mt-1 block w-full" required autofocus />
                <InputError class="mt-1" :message="form.errors.nama_toko" />
            </div>

            <div>
                <InputLabel for="lokasi" value="Lokasi" />
                <TextInput id="lokasi" v-model="form.lokasi" class="mt-1 block w-full" />
                <InputError class="mt-1" :message="form.errors.lokasi" />
            </div>

            <div>
                <InputLabel for="deskripsi" value="Deskripsi" />
                <textarea
                    id="deskripsi"
                    v-model="form.deskripsi"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                />
                <InputError class="mt-1" :message="form.errors.deskripsi" />
            </div>

            <div>
                <InputLabel for="service_fee" value="Biaya Jasa (Rp)" />
                <TextInput id="service_fee" v-model.number="form.service_fee" type="number" min="0" class="mt-1 block w-full" required />
                <InputError class="mt-1" :message="form.errors.service_fee" />
            </div>

            <div>
                <InputLabel for="status" value="Status" />
                <select
                    id="status"
                    v-model="form.status"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                >
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>

            <div class="flex justify-end">
                <PrimaryButton :disabled="form.processing">Simpan</PrimaryButton>
            </div>
        </form>
    </ProviderLayout>
</template>
