import { defineStore } from 'pinia';

export const useUiStore = defineStore('ui', {
    state: () => ({
        darkMode: localStorage.getItem('wawango-dark-mode') === 'true',
    }),

    actions: {
        init() {
            document.documentElement.classList.toggle('dark', this.darkMode);
        },

        toggleDarkMode() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('wawango-dark-mode', this.darkMode);
            document.documentElement.classList.toggle('dark', this.darkMode);
        },
    },
});
