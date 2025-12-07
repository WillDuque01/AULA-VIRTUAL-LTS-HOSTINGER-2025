## [TURNO 2] GEMINI: ESPECIFICACIÓN DE UI

**Fecha**: 06-dic-2025 16:20 UTC
**Agente**: Gemini 3 Pro (Arquitecto UX/UI)

---

### 1. Refactorización: Filtros de Prácticas (Browser)

**Problema:** El componente `discord-practice-browser.blade.php` usa un `<select>` nativo que rompe la estética "Glassmorphism" del Player. La lista de lecciones es plana y difícil de escanear.

**Especificación Técnica (Para GPT-5.1):**

1.  **Componente Reutilizable:** Crear `resources/views/components/ui/select-grouped.blade.php`.
2.  **HTML/Tailwind Estructura:**
    ```html
    <div class="relative" x-data="{ open: false, selected: @entangle($attributes->wire('model')) }">
        <!-- Trigger -->
        <button @click="open = !open" type="button" class="flex w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm hover:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
            <span x-text="selectedLabel || '{{ $placeholder ?? 'Seleccionar...' }}'" class="truncate text-slate-700"></span>
            <span class="text-slate-400">▼</span>
        </button>

        <!-- Dropdown -->
        <div x-show="open" @click.away="open = false" class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-xl border border-slate-100 bg-white py-1 shadow-xl ring-1 ring-black/5" x-cloak>
            @foreach($groups as $group => $items)
                <div class="px-3 py-2 text-xs font-bold uppercase tracking-wider text-slate-400 bg-slate-50/50">
                    {{ $group }}
                </div>
                @foreach($items as $value => $label)
                    <div @click="selected = '{{ $value }}'; open = false" class="cursor-pointer px-4 py-2 text-sm text-slate-700 hover:bg-indigo-50 hover:text-indigo-700">
                        {{ $label }}
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>
    ```
3.  **Integración:** En `DiscordPracticeBrowser.php`, agrupar las lecciones por `course.slug` antes de pasarlas a la vista.

---

### 2. Sistema de Feedback Visual (Toasts)

**Problema:** Acciones críticas (reservar, cancelar) no dan feedback inmediato.

**Especificación Técnica (Para GPT-5.1):**

1.  **Listener Global (`resources/js/app.js`):**
    ```javascript
    window.addEventListener('notify', event => {
        const { message, style = 'success' } = event.detail;
        // Crear elemento DOM temporal
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 flex items-center gap-3 rounded-2xl px-4 py-3 shadow-xl transform transition-all duration-300 translate-y-[-10px] opacity-0 ${
            style === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-100' : 
            style === 'error' ? 'bg-rose-50 text-rose-800 border border-rose-100' : 
            'bg-slate-800 text-white'
        }`;
        toast.innerHTML = `<span class="text-xl">${style === 'success' ? '✅' : '⚠️'}</span><p class="text-sm font-medium">${message}</p>`;
        
        document.body.appendChild(toast);
        
        // Animar entrada
        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-[-10px]', 'opacity-0');
        });

        // Auto-eliminar
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    });
    ```
2.  **Uso en Livewire:**
    ```php
    $this->dispatch('notify', message: 'Cupo reservado correctamente', style: 'success');
    ```

---

### 3. Extracción de Lógica JS (Refactor)

**Problema:** `animatedCount` está duplicado en múltiples vistas Blade.

**Especificación Técnica (Para GPT-5.1):**

1.  **Archivo Nuevo:** `resources/js/animations.js`
    ```javascript
    export const animatedCount = (value, duration = 1000) => ({
        current: 0,
        target: value,
        init() {
            if (this.target === 0) return;
            const step = this.target / (duration / 16);
            const timer = setInterval(() => {
                this.current += step;
                if (this.current >= this.target) {
                    this.current = this.target;
                    clearInterval(timer);
                }
            }, 16);
        }
    });
    // Registrar en Alpine globalmente en app.js
    // Alpine.data('animatedCount', animatedCount);
    ```
2.  **Limpieza:** Borrar los `<script>` inline de `dashboard.blade.php` y `player.blade.php` y usar `x-data="animatedCount(100)"`.

---

[TURNO-GEMINI-FINALIZADO]

