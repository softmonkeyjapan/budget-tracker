import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
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
                peach: '#FFD2C4',
                coral: '#FF8A66',
                app: '#F3F5FA',
                ink: '#172033',
                muted: '#8A90A2',
                line: '#E6E9F0',
                nav: '#2F80ED',
                income: '#23C48E',
                expense: '#FF5B62',
                category: '#8A5CF6',
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
