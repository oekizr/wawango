import { defineStore } from 'pinia';

const STORAGE_KEY = 'wawango-cart';

function loadCart() {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        return raw ? JSON.parse(raw) : null;
    } catch {
        return null;
    }
}

function emptyCart() {
    return {
        providerId: null,
        storeId: null,
        storeName: null,
        serviceFee: 0,
        items: [], // { menuId, nama, harga, qty, note }
    };
}

export const useCartStore = defineStore('cart', {
    state: () => ({ ...emptyCart(), ...(loadCart() ?? {}) }),

    getters: {
        totalQty: (state) => state.items.reduce((sum, item) => sum + item.qty, 0),
        subtotal: (state) => state.items.reduce((sum, item) => sum + item.harga * item.qty, 0),
        grandTotal() {
            return this.subtotal + this.serviceFee;
        },
    },

    actions: {
        persist() {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(this.$state));
        },

        addItem(store, menu) {
            if (this.storeId && this.storeId !== store.id) {
                this.items = [];
            }

            this.providerId = store.provider_id;
            this.storeId = store.id;
            this.storeName = store.nama_toko;
            this.serviceFee = store.service_fee;

            const existing = this.items.find((item) => item.menuId === menu.id);

            if (existing) {
                existing.qty += 1;
            } else {
                this.items.push({ menuId: menu.id, nama: menu.nama, harga: menu.harga, qty: 1, note: '' });
            }

            this.persist();
        },

        updateQty(menuId, qty) {
            const item = this.items.find((i) => i.menuId === menuId);
            if (!item) return;

            if (qty <= 0) {
                this.items = this.items.filter((i) => i.menuId !== menuId);
            } else {
                item.qty = qty;
            }

            this.persist();
        },

        updateNote(menuId, note) {
            const item = this.items.find((i) => i.menuId === menuId);
            if (item) {
                item.note = note;
                this.persist();
            }
        },

        clear() {
            Object.assign(this, emptyCart());
            localStorage.removeItem(STORAGE_KEY);
        },
    },
});
