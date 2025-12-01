import './bootstrap';

import Alpine from 'alpinejs';
import registerCelebrations from './modules/celebrations.js';

window.Alpine = Alpine;

Alpine.start();

registerCelebrations();

const toastHostId = 'app-toast-host'; // [AGENTE: GPT-5.1 CODEX] - Identificador único para el contenedor de toasts
const toastVariants = { // [AGENTE: GPT-5.1 CODEX] - Definimos los estilos base por variante
    success: 'bg-emerald-600 text-white shadow-emerald-500/40', // [AGENTE: GPT-5.1 CODEX] - Toast para operaciones exitosas
    error: 'bg-rose-600 text-white shadow-rose-500/30', // [AGENTE: GPT-5.1 CODEX] - Toast para mensajes de error
    info: 'bg-slate-900 text-white shadow-slate-900/30', // [AGENTE: GPT-5.1 CODEX] - Toast neutro
}; // [AGENTE: GPT-5.1 CODEX] - Cierre del mapa de variantes

const ensureToastHost = () => { // [AGENTE: GPT-5.1 CODEX] - Garantiza que exista un contenedor para los toasts
    let host = document.getElementById(toastHostId); // [AGENTE: GPT-5.1 CODEX] - Buscamos el host en el DOM
    if (!host) { // [AGENTE: GPT-5.1 CODEX] - Si no existe se crea
        host = document.createElement('div'); // [AGENTE: GPT-5.1 CODEX] - Creamos el contenedor
        host.id = toastHostId; // [AGENTE: GPT-5.1 CODEX] - Asignamos el ID consistente
        host.className = 'fixed right-4 bottom-4 z-[9999] flex flex-col gap-2'; // [AGENTE: GPT-5.1 CODEX] - Posicionamos el host
        document.body.appendChild(host); // [AGENTE: GPT-5.1 CODEX] - Inyectamos el host en el body
    }
    return host; // [AGENTE: GPT-5.1 CODEX] - Devolvemos el host para su reutilización
}; // [AGENTE: GPT-5.1 CODEX] - Fin del helper ensureToastHost

const spawnToast = ({ message, variant }) => { // [AGENTE: GPT-5.1 CODEX] - Fabrica cada toast individual
    const host = ensureToastHost(); // [AGENTE: GPT-5.1 CODEX] - Nos aseguramos de tener host listo
    const toast = document.createElement('div'); // [AGENTE: GPT-5.1 CODEX] - Creamos el elemento toast
    toast.className = `pointer-events-auto rounded-2xl px-4 py-3 text-sm font-semibold shadow-lg transition-all duration-200 ${toastVariants[variant] ?? toastVariants.info}`; // [AGENTE: GPT-5.1 CODEX] - Aplicamos estilos según variante
    toast.textContent = message || 'Acción completada'; // [AGENTE: GPT-5.1 CODEX] - Mensaje a mostrar
    host.appendChild(toast); // [AGENTE: GPT-5.1 CODEX] - Agregamos el toast al contenedor
    setTimeout(() => { // [AGENTE: GPT-5.1 CODEX] - Temporizador para ocultar el toast
        toast.classList.add('opacity-0', 'translate-y-2'); // [AGENTE: GPT-5.1 CODEX] - Animación de salida
        setTimeout(() => toast.remove(), 220); // [AGENTE: GPT-5.1 CODEX] - Eliminamos el nodo tras la animación
    }, 3200); // [AGENTE: GPT-5.1 CODEX] - Duración visible del toast
}; // [AGENTE: GPT-5.1 CODEX] - Fin del helper spawnToast

window.addEventListener('notify', (event) => { // [AGENTE: GPT-5.1 CODEX] - Listener global para los eventos Alpine/Livewire
    const detail = event.detail ?? {}; // [AGENTE: GPT-5.1 CODEX] - Extraemos el payload recibido
    spawnToast({ message: detail.message, variant: detail.style || 'success' }); // [AGENTE: GPT-5.1 CODEX] - Disparamos el toast en función de la data
}); // [AGENTE: GPT-5.1 CODEX] - Fin del listener de notificaciones
