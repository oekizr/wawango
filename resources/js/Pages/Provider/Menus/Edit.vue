<script setup>
import ProviderLayout from '@/Layouts/ProviderLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ImageUploadField from '@/Components/ImageUploadField.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    menu: { type: Object, required: true },
    stores: { type: Array, required: true },
});

const form = useForm({
    _method: 'put',
    store_id: props.menu.store_id,
    nama: props.menu.nama,
    harga: props.menu.harga,
    foto: null,
    status: props.menu.status,
});

function submit() {
    form.post(route('provider.menus.update', props.menu.id), { forceFormData: true });
}
</script>

<template>
    <Head title="Edit Menu" />

    <ProviderLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Edit Menu — {{ menu.nama }}</h1>
        </template>

        <form
            class="max-w-xl space-y-4 rounded-xl bg-white p-6 shadow-sm dark:bg-surface-darkMuted"
            @submit.prevent="submit"
        >
            <div>
                <InputLabel for="store_id" value="Toko" />
                <select
                    id="store_id"
                    v-model="form.store_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    disabled
                >
                    <option v-for="store in stores" :key="store.id" :value="store.id">{{ store.nama_toko }}</option>
                </select>
            </div>

            <div>
                <InputLabel for="nama" value="Nama Menu" />
                <TextInput id="nama" v-model="form.nama" class="mt-1 block w-full" required autofocus />
                <InputError class="mt-1" :message="form.errors.nama" />
            </div>

            <div>
                <InputLabel for="harga" value="Harga (Rp)" />
                <TextInput id="harga" v-model.number="form.harga" type="number" min="0" class="mt-1 block w-full" required />
                <InputError class="mt-1" :message="form.errors.harga" />
            </div>

            <ImageUploadField v-model="form.foto" label="Foto Menu" :existing-url="menu.foto_url" :error="form.errors.foto" />

            <div>
                <InputLabel for="status" value="Status" />
                <select
                    id="status"
                    v-model="form.status"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                >
                    <option value="tersedia">Tersedia</option>
                    <option value="habis">Habis</option>
                </select>
            </div>

            <div class="flex justify-end">
                <PrimaryButton :disabled="form.processing">Simpan Perubahan</PrimaryButton>
            </div>
        </form>
    </ProviderLayout>
</template>
