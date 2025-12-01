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
            fontFamily: { // [AGENTE: GPT-5.1 CODEX] - Se redefine el mapa de fuentes base para alinearlo con UIX 2030
                sans: ['Inter', 'Onest', ...defaultTheme.fontFamily.sans], // [AGENTE: GPT-5.1 CODEX] - Inter/Onest sustituyen a Figtree según el plan de Gemini
            },
        },
    },

    plugins: [forms],
};
