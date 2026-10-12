import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
            'theme-primary': '#A084DC',
            'theme-secondary': '#B983FF',
            'theme-accent': '#F9B2D7',
            'theme-background': '#FFF4F4',
            'theme-text': '#612D53',
            }
        },
    },

    plugins: [forms],
};
