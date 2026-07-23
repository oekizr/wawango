import { usePage } from '@inertiajs/vue3';
import { defineStore } from 'pinia';
import { computed } from 'vue';

export const useAuthStore = defineStore('auth', () => {
    const user = computed(() => usePage().props.auth.user);
    const role = computed(() => usePage().props.auth.role);
    const providerId = computed(() => usePage().props.auth.provider_id);

    const isAdmin = computed(() => role.value === 'admin');
    const isProvider = computed(() => role.value === 'penyedia_jasa');
    const isPemesan = computed(() => role.value === 'pemesan');

    return { user, role, providerId, isAdmin, isProvider, isPemesan };
});
