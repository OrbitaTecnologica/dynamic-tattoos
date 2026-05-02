import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Outfit', ...defaultTheme.fontFamily.sans],
            },
            keyframes: {
                'gradient-x': {
                    '0%, 100%': { backgroundPosition: '0% 50%' },
                    '50%': { backgroundPosition: '100% 50%' },
                },
                'scan-once': {
                    '0%': { transform: 'translateY(-120%)', opacity: '0' },
                    '10%': { opacity: '.8' },
                    '100%': { transform: 'translateY(120vh)', opacity: '0' },
                },
                float: {
                    '0%, 100%': { transform: 'translateY(0px)' },
                    '50%': { transform: 'translateY(-10px)' },
                },
                'pulse-soft': {
                    '0%, 100%': { boxShadow: '0 0 0 0 rgba(34, 211, 238, 0.0)' },
                    '50%': { boxShadow: '0 0 35px 6px rgba(34, 211, 238, 0.25)' },
                },
                shimmer: {
                    '0%': { transform: 'translateX(-120%)' },
                    '100%': { transform: 'translateX(220%)' },
                },
                'fade-in-up': {
                    '0%': { opacity: '0', transform: 'translateY(12px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
            },
            animation: {
                'gradient-x': 'gradient-x 12s ease infinite',
                'scan-once': 'scan-once 1.6s cubic-bezier(0.22, 1, 0.36, 1) 1',
                float: 'float 5s ease-in-out infinite',
                'pulse-soft': 'pulse-soft 2.6s ease-in-out infinite',
                shimmer: 'shimmer 1.8s ease-in-out infinite',
                'fade-in-up': 'fade-in-up .7s ease-out forwards',
            },
        },
    },

    plugins: [forms],
};
