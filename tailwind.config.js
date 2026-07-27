import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                forest: '#1B4332',
                khaki: '#A98753',
                parchment: '#F4EFE1',
                ink: '#241F16',
                gold: '#C9971F',
                emas: '#C9971F',
                perak: '#9CA3AF',
                perunggu: '#B5651D',
            },
            fontFamily: {
                display: ['Oswald', 'sans-serif'],
                body: ['Inter', 'sans-serif'],
                mono: ['IBM Plex Mono', 'monospace'],
            },
        },
    },

    plugins: [forms, typography],
};
