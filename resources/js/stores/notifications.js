import { defineStore } from 'pinia';

export const useNotificationsStore = defineStore('notifications', {
    state: () => ({
        unreadCount: 0,
        items: [],
    }),

    actions: {
        hydrate(payload) {
            this.unreadCount = payload?.unread_count ?? 0;
            this.items = payload?.items ?? [];
        },

        pushLive(notification) {
            this.unreadCount += 1;
            this.items = [
                {
                    id: notification.id,
                    message: notification.message,
                    created_at: notification.created_at ?? new Date().toISOString(),
                    url: notification.url ?? null,
                },
                ...this.items,
            ].slice(0, 10);
        },
    },
});
