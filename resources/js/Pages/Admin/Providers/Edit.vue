<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ScheduleEditor from '@/Components/ScheduleEditor.vue';
import ImageUploadField from '@/Components/ImageUploadField.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    provider: { type: Object, required: true },
});

const form = useForm({
    _method: 'put',
    name: props.provider.name,
    email: props.provider.email,
    password: '',
    password_confirmation: '',
    divisi: props.provider.divisi,
    lantai: props.provider.lantai,
    no_hp: props.provider.no_hp,
    foto_profil: null,
    qris_image: null,
    nama_bank: props.provider.nama_bank,
    no_rekening: props.provider.no_rekening,
    nama_pemilik_rekening: props.provider.nama_pemilik_rekening,
    is_active: props.provider.is_active,
    schedules: props.provider.schedules,
});

function submit() {
    form.post(route('admin.providers.update', props.provider.id), {
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="Edit Penyedia Jasa" />

    <AdminLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
                Edit Penyedia Jasa — {{ provider.name }}
            </h1>
        </template>

        <form
            class="max-w-3xl space-y-6 rounded-xl bg-white p-6 shadow-sm dark:bg-surface-darkMuted"
            @submit.prevent="submit"
        >
            <section>
                <h2 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Akun</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel for="name" value="Nama" />
                        <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.name" />
                    </div>
                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.email" />
                    </div>
                    <div>
                        <InputLabel for="password" value="Password baru (opsional)" />
                        <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full" />
                        <InputError class="mt-1" :message="form.errors.password" />
                    </div>
                    <div>
                        <InputLabel for="password_confirmation" value="Konfirmasi Password" />
                        <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" class="mt-1 block w-full" />
                    </div>
                </div>
            </section>

            <section>
                <h2 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Profil Provider</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <InputLabel for="divisi" value="Divisi" />
                        <TextInput id="divisi" v-model="form.divisi" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.divisi" />
                    </div>
                    <div>
                        <InputLabel for="lantai" value="Lantai" />
                        <TextInput id="lantai" v-model="form.lantai" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.lantai" />
                    </div>
                    <div>
                        <InputLabel for="no_hp" value="Nomor HP" />
                        <TextInput id="no_hp" v-model="form.no_hp" class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.no_hp" />
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <ImageUploadField
                        v-model="form.foto_profil"
                        label="Foto Profil"
                        :existing-url="provider.foto_profil_url"
                        :error="form.errors.foto_profil"
                    />
                    <ImageUploadField
                        v-model="form.qris_image"
                        label="QRIS"
                        :existing-url="provider.qris_image_url"
                        :error="form.errors.qris_image"
                    />
                </div>

                <label class="mt-4 flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                    <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
                    Status akun aktif (bisa login & menerima order)
                </label>
            </section>

            <section>
                <h2 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Rekening & QRIS</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <InputLabel for="nama_bank" value="Nama Bank" />
                        <TextInput id="nama_bank" v-model="form.nama_bank" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <InputLabel for="no_rekening" value="Nomor Rekening" />
                        <TextInput id="no_rekening" v-model="form.no_rekening" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <InputLabel for="nama_pemilik_rekening" value="Nama Pemilik Rekening" />
                        <TextInput id="nama_pemilik_rekening" v-model="form.nama_pemilik_rekening" class="mt-1 block w-full" />
                    </div>
                </div>
            </section>

            <section>
                <h2 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Jadwal Layanan</h2>
                <ScheduleEditor v-model="form.schedules" />
                <InputError class="mt-1" :message="form.errors.schedules" />
            </section>

            <div class="flex justify-end gap-3">
                <PrimaryButton :disabled="form.processing">Simpan Perubahan</PrimaryButton>
            </div>
        </form>
    </AdminLayout>
</template>
