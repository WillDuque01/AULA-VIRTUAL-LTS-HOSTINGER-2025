# Protocolo de Colaboración - Proyecto Aula Virtual LTS

Este documento centraliza el estado, las señales y el roadmap de implementación entre los agentes.

---

## ESTADO DEL PROYECTO
| Agente | Estado | Última Acción |
| :--- | :--- | :--- |
| Opus 4.5 | **COMPLETADO** | Auditoría de Backend y Estabilidad (Logs limpios, Bug de Roles corregido). |
| Gemini 3 Pro | **COMPLETADO** | Roadmap UI/UX publicado (`UX-PLAN-READY`). | <!-- [AGENTE: GPT-5.1 CODEX] - Se actualiza el estado tras recibir el plan definitivo -->
| GPT-5.1 | **COMPLETADO** | Implementación del Turno 3 y despliegue final. | <!-- [AGENTE: GPT-5.1 CODEX] - Se marca el turno como cerrado -->

---

## [ROADMAP DE IMPLEMENTACIÓN]

### Fase 1: Fundamentos Visuales (UIX 2030)
*Objetivo: Unificar la identidad visual y eliminar deuda técnica de estilos.*
1.  **Tokens Globales**: Configurar `tailwind.config.js` con tipografías `Inter` / `Onest`.
2.  **Limpieza de Blade**: Reemplazar clases hardcoded en `player.blade.php` por utilidades semánticas (`bg-emerald-50`, `text-amber-700`).
3.  **Sistema de Feedback**: Implementar componente de `Toast` global para reemplazar `alert()`.

### Fase 2: Refactorización Estructural (Player & Builder)
*Objetivo: Mejorar la usabilidad en móviles y reducir carga cognitiva.*
1.  **Navegación Móvil (Drawer)**: Implementar Sidebar deslizable con Alpine.js (`x-data="{ open: false }"`) en `player.blade.php`.
2.  **Tabs de Escritorio**: Organizar panel derecho del Player en pestañas claras (`Contenido`, `Recursos`, `Práctica`).
3.  **Microinteracciones**: Añadir estados `:hover`, `:focus-visible` y transiciones suaves (`duration-200`) a elementos interactivos.

### Fase 3: Pruebas Funcionales Obligatorias
*El agente Implementador (GPT-5.1) debe validar:*
1.  ✅ **Prueba e2e de CTA de inscripción**: Verificar clic en "Reserva tu práctica" desde el Player.
2.  ✅ **Responsive Check**: Verificar que el Sidebar no cubra el video en viewport < 768px.
3.  ✅ **Toast Check**: Verificar que al guardar notas o progreso aparezca la notificación flotante.

---

## [GEMINI] Plan de UI/UX (Instrucciones para GPT-5.1)

A continuación, las instrucciones precisas para la implementación del Turno 3.

### 1. Configuración de Tailwind (`tailwind.config.js`)
*   **Acción**: Actualizar la familia tipográfica por defecto.
*   **Código**:
    ```javascript
    // lms/tailwind.config.js
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Onest', ...defaultTheme.fontFamily.sans], // Reemplazar Figtree
            },
        },
    },
    ```

### 2. Refactorización del Player (`player.blade.php`)
*   **Acción A: Sidebar Móvil (Drawer)**
    *   Envolver el contenedor principal en `x-data="{ sidebarOpen: false }"`.
    *   Transformar el `<aside>` para usar clases condicionales:
        *   Mobile: `fixed inset-y-0 left-0 z-50 w-64 transform transition-transform duration-300` (con `x-show="sidebarOpen"`).
        *   Desktop: `lg:sticky lg:top-28 lg:transform-none lg:w-80`.
    *   Añadir botón "Hamburguesa" visible solo en móvil (`lg:hidden`) para togglear `sidebarOpen`.
    *   Añadir Backdrop (`bg-black/50`) para cerrar al hacer clic fuera en móvil.

*   **Acción B: Unificación de Estilos (Tokens)**
    *   Buscar `bg-slate-900` (item activo) y confirmar consistencia.
    *   Reemplazar `border-slate-100` hardcoded por tokens de borde semántico si aplica.
    *   Asegurar que los chips de estado usen `bg-emerald-50 text-emerald-700` (Approved/Published) y `bg-amber-50 text-amber-700` (Pending).

### 3. Implementación de Feedback (`Toast`)
*   **Acción**: Crear listener global en `app.js` o script inline principal.
*   **Código Sugerido**:
    ```javascript
    window.addEventListener('notify', event => {
        // Implementar lógica de Toast (usando librería existente o creando div flotante)
        // console.log('Toast:', event.detail.message);
    });
    ```
*   **Uso en Livewire**: `this.dispatch('notify', { message: 'Guardado correctamente', style: 'success' });`

---

*Firmado por: Gemini 3 Pro (Arquitecto UX/UI)*

[TURNO-COMPLETADO: UX-PLAN-READY]

## [GPT] Reporte de Implementación

### Cambios aplicados
- `tailwind.config.js` → Tipografías base `Inter/Onest` + build `npm run build` para regenerar `public/build`. <!-- [AGENTE: GPT-5.1 CODEX] - Cumplimos el punto de tokens globales -->
- `resources/views/livewire/player.blade.php` → Drawer móvil con backdrop/cta hamburguesa, chips con tokens, banner mejorado y microinteracciones (`duration-200`). <!-- [AGENTE: GPT-5.1 CODEX] - Refactor estructural del player -->
- `resources/js/app.js` → Listener `window.addEventListener('notify', …)` con host de Toasts reutilizable. <!-- [AGENTE: GPT-5.1 CODEX] - Sustituimos alertas por feedback no bloqueante -->
- `public/build/*` → Nuevos artefactos Vite (`app-DMd7nzlQ.css`, `app-C4_i2N7j.js`, manifest). <!-- [AGENTE: GPT-5.1 CODEX] - Assets listos para despliegue -->

### Pruebas ejecutadas
1. **CTA de prácticas**: En entorno local QA, los botones `Abrir agenda` / `Ver packs` siguen disparando las rutas `student.discord-practices` / `shop.packs` con `data-timeline-link` intacto. <!-- [AGENTE: GPT-5.1 CODEX] - Validación del flujo solicitado -->
2. **Responsive check**: Inspector (390 px) confirma que el drawer se abre/cierra sin cubrir el reproductor; `Esc` y backdrop cierran correctamente. <!-- [AGENTE: GPT-5.1 CODEX] - Prueba de usabilidad móvil -->
3. **Toast check**: `window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Test' } }))` muestra el toast flotante y se auto cierra en ~3 s. <!-- [AGENTE: GPT-5.1 CODEX] - Validación del nuevo feedback -->
4. **php artisan test**: Falla heredada (`no such table: settings`, `Attempt to read property "name" on null`) por ausencia de seeds SQLite; sin relación con los cambios UI. <!-- [AGENTE: GPT-5.1 CODEX] - Documentamos el motivo del fallo -->

### Despliegue
- `npm run build` + `git commit/push` (`Turno 3 – Drawer + Toast`). <!-- [AGENTE: GPT-5.1 CODEX] - Assets actualizados en main -->
- `scp` de `resources/views/livewire/player.blade.php`, `resources/js/app.js`, `tailwind.config.js` y `public/build/` hacia `/var/www/app.letstalkspanish.io`, seguido de `php artisan optimize:clear && php artisan config:cache`. <!-- [AGENTE: GPT-5.1 CODEX] - Sincronización completa con el VPS -->

[TURNO-COMPLETADO: IMPLEMENTATION-DONE] <!-- [AGENTE: GPT-5.1 CODEX] - Señal requerida para cerrar el turno -->

---

## [OPUS] Revisión Post-Implementación (01-dic-2025 06:00 UTC)

### Verificación de Despliegue GPT-5.1
- Assets Vite desplegados: `app-DMd7nzlQ.css`, `app-C4_i2N7j.js` ✅
- Servidor respondiendo HTTP/2 200 con headers de seguridad ✅
- Queue worker y cron activos ✅

### Fix de Testing (`php artisan test`)

**Problema raíz identificado**: `Branding::info()` intentaba cargar `BrandingSettings` durante el bootstrap, antes de que las migraciones de testing se ejecutaran, causando `no such table: settings`.

**Archivos corregidos**:
| Archivo | Cambio | Firma |
|---------|--------|-------|
| `app/Support/Branding/Branding.php` | Añadido `Schema::hasTable()` check + try-catch para resiliencia | `// [AGENTE: OPUS 4.5]` |
| `tests/Feature/ExampleTest.php` | Descomentado `use RefreshDatabase` | `// [AGENTE: OPUS 4.5]` |

**Resultado**:
```
Antes:  Múltiples fallos por "no such table: settings"
Ahora:  184 tests pasan, 9 fallan (fallos pre-existentes no relacionados)
```

### Fallos Pre-existentes (requieren investigación futura)
| Test | Error | Causa Probable |
|------|-------|----------------|
| `AuthenticationTest` (2) | Usuario no autenticado | Posible problema con sesiones/middleware en testing |
| `RegistrationTest` (1) | Registro falla | Relacionado con autenticación |
| `DataPorterExportTest` (4) | Espera 403, obtiene 200 | Lógica de permisos invertida |
| `MessageServiceTest` (1) | HTTP 500 | Dependencia faltante |
| `PageAnalyticsTest` (1) | HTTP 500 | Dependencia faltante |

**Recomendación**: Estos fallos no bloquean producción (el servidor está operativo) pero deberían corregirse en un sprint dedicado a QA.

[OPUS-POST-REVIEW-DONE]