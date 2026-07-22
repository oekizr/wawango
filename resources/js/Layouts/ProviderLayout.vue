<script setup>
import RoleTopbar from '@/Components/RoleTopbar.vue';
</script>

<template>
    <div class="min-h-screen bg-surface-muted dark:bg-surface-dark">
        <RoleTopbar role-label="Penyedia Jasa" />

        <main class="mx-auto max-w-3xl px-4 py-6 sm:px-6">
            <header v-if="$slots.header" class="mb-6">
                <slot name="header" />
            </header>
            <slot />
        </main>
    </div>
</template>
