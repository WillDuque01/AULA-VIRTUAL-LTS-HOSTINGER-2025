import './bootstrap';

import Alpine from 'alpinejs';
import registerCelebrations from './modules/celebrations.js';
import { animatedCount } from './animations.js'; // [AGENTE: GPT-5.1 CODEX] - Extraemos la lógica compartida de contadores

window.Alpine = Alpine;

Alpine.data('animatedCount', animatedCount); // [AGENTE: GPT-5.1 CODEX] - Registra el helper globalmente

registerCelebrations();

Alpine.start();

window.addEventListener('notify', (event) => { // [AGENTE: GPT-5.1 CODEX] - Listener global para los toasts UIX 2030
    const { message, style = 'success' } = event.detail ?? {};

    const icons = {
        success: '✅',
        error: '⚠️',
        info: 'ℹ️',
    }; // [AGENTE: GPT-5.1 CODEX] - Iconografía consistente

    const palettes = {
        success: 'bg-emerald-50 text-emerald-800 border border-emerald-100',
        error: 'bg-rose-50 text-rose-800 border border-rose-100',
        info: 'bg-slate-800 text-white border border-slate-700',
    }; // [AGENTE: GPT-5.1 CODEX] - Paleta por estado

    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-[9999] flex items-center gap-3 rounded-2xl px-4 py-3 shadow-xl transform transition-all duration-300 translate-y-[-10px] opacity-0 pointer-events-auto ${palettes[style] ?? palettes.info}`; // [AGENTE: GPT-5.1 CODEX] - Estilos base propuestos por Gemini
    toast.innerHTML = `<span class="text-xl">${icons[style] ?? icons.info}</span><p class="text-sm font-semibold">${message ?? 'Acción completada'}</p>`;

    document.body.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-[-10px]', 'opacity-0');
    }); // [AGENTE: GPT-5.1 CODEX] - Animación de entrada

    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-x-full');
        toast.addEventListener('transitionend', () => toast.remove());
    }, 4000); // [AGENTE: GPT-5.1 CODEX] - Auto cierre tras 4 segundos
});
