<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    user: { type: Object, required: true },
});

const form = useForm({
    name: props.user.name,
    divisi: props.user.divisi,
    lantai: props.user.lantai,
    no_hp: props.user.no_hp,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    status: props.user.status,
});

function submit() {
    form.put(route('admin.users.update', props.user.id));
}
</script>

<template>
    <Head title="Edit User" />

    <AdminLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
                Edit User — {{ user.name }}
            </h1>
        </template>

        <form
            class="max-w-2xl space-y-4 rounded-xl bg-white p-6 shadow-sm dark:bg-surface-darkMuted"
            @submit.prevent="submit"
        >
            <div>
                <InputLabel for="name" value="Nama" />
                <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required autofocus />
                <InputError class="mt-1" :message="form.errors.name" />
            </div>

            <div class="grid grid-cols-2 gap-4">
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
            </div>

            <div>
                <InputLabel for="no_hp" value="Nomor HP" />
                <TextInput id="no_hp" v-model="form.no_hp" class="mt-1 block w-full" required />
                <InputError class="mt-1" :message="form.errors.no_hp" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />
                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required />
                <InputError class="mt-1" :message="form.errors.email" />
            </div>

            <div class="grid grid-cols-2 gap-4">
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
                <PrimaryButton :disabled="form.processing">Simpan Perubahan</PrimaryButton>
            </div>
        </form>
    </AdminLayout>
</template>
