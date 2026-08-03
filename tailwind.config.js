import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: ['selector', '[data-theme="dark"]'],

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito Sans', 'Poppins', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // No documented dark-mode override — unchanged in both themes.
                peach: '#FFD2C4',
                category: '#8A5CF6',
                // Theme-aware tokens, driven by CSS variables (see resources/css/app.css).
                coral: 'rgb(var(--color-coral) / <alpha-value>)',
                app: 'rgb(var(--color-app) / <alpha-value>)',
                surface: 'rgb(var(--color-surface) / <alpha-value>)',
                'surface-secondary': 'rgb(var(--color-surface-secondary) / <alpha-value>)',
                ink: 'rgb(var(--color-ink) / <alpha-value>)',
                muted: 'rgb(var(--color-muted) / <alpha-value>)',
                line: 'rgb(var(--color-line) / <alpha-value>)',
                nav: 'rgb(var(--color-nav) / <alpha-value>)',
                income: 'rgb(var(--color-income) / <alpha-value>)',
                expense: 'rgb(var(--color-expense) / <alpha-value>)',
            },
            borderRadius: {
                shell: '26px',
                card: '16px',
                control: '12px',
            },
            boxShadow: {
                soft: '0 8px 24px rgb(40 52 77 / 0.07)',
                shell: '0 28px 70px rgb(74 41 31 / 0.16)',
                primary: '0 8px 18px rgb(47 128 237 / 0.22)',
                pill: '0 4px 14px rgb(40 52 77 / 0.05)',
            },
            backgroundImage: {
                canvas: 'linear-gradient(135deg, #ffd7ca, #ffcabc)',
            },
        },
    },

    plugins: [forms],
};
