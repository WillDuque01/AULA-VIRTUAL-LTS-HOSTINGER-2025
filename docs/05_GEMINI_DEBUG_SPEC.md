## [TURNO 5] GEMINI: PLANIFICACIÓN DE DEBUGGING

**Fecha**: 06-dic-2025 17:25 UTC
**Agente**: Gemini 3 Pro (Planificador de Debugging)

---

### 1. Análisis de Errores Frontend Pendientes

El reporte QA (`04_OPUS_QA_REPORT.md`) confirma que la infraestructura base (Nginx, permisos, CSP) está **SANA**. Ahora los errores son puramente de código Frontend.

#### BUG-A: "Detected multiple instances of Alpine running"
**Causa**: Estamos importando `alpinejs` en `app.js` pero Livewire 3 ya lo incluye automáticamente.
**Solución**: Eliminar la importación manual de Alpine en `resources/js/app.js`.

#### BUG-B: "Cannot read properties of undefined (reading 'entangle')"
**Causa**: El script inline del componente `x-ui.select-grouped` intenta usar `@entangle` fuera del contexto de inicialización de Livewire, o antes de que Alpine.js esté listo.
**Solución**: Envolver la inicialización en `document.addEventListener('livewire:navigated', ...)` o usar la directiva `x-data` de forma más robusta.

---

### 2. Especificación de Correcciones (Para GPT-5.1)

#### Fix 1: Limpieza de `app.js`
**Archivo**: `resources/js/app.js`
**Acción**: Eliminar o comentar las líneas de importación de Alpine.
```javascript
// resources/js/app.js

// ELIMINAR ESTAS LÍNEAS:
// import Alpine from 'alpinejs';
// window.Alpine = Alpine;
// Alpine.start();

// MANTENER:
import './bootstrap';
// ... lógica de Toasts ...
```

#### Fix 2: Robustez en `select-grouped.blade.php`
**Archivo**: `resources/views/components/ui/select-grouped.blade.php`
**Acción**: Asegurar que `@entangle` se procese correctamente dentro de un componente Livewire.
```html
<div class="relative" 
     x-data="{ 
         open: false, 
         selected: @entangle($attributes->wire('model')).live 
     }" 
     x-cloak>
    <!-- ... resto del componente ... -->
</div>
```
*Nota: Añadir `.live` si se requiere reactividad inmediata, o asegurar que el componente padre sea Livewire.*

---

### 3. Instrucciones de Verificación (Smoke Test)

GPT-5.1 debe ejecutar estas pruebas tras aplicar los fixes:

1.  **Consola Limpia**: Cargar `/es/student/practices` y verificar que NO aparezca el warning de "multiple instances of Alpine".
2.  **Dropdown Funcional**: Abrir el filtro de "Lección" en el browser de prácticas. Debe desplegarse y permitir selección sin errores de consola.
3.  **Toast Test**: Ejecutar `window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Test OK' } }))` en consola y confirmar visualización.

---

[TURNO-GEMINI-DEBUG-FINALIZADO]

