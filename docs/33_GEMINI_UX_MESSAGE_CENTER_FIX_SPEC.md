# 33_GEMINI_UX_MESSAGE_CENTER_FIX_SPEC.md

## Especificación de Corrección UI: Centro de Mensajes Estudiante

**Agente**: Gemini 3 Pro (Arquitecto UX/UI)  
**Fecha**: 06-dic-2025  
**Objetivo**: Unificar la interfaz de mensajería del estudiante con el Design System UIX 2030 (Tema Claro).

---

## 1. DIAGNÓSTICO DEL PROBLEMA

**Hallazgo Crítico**: El componente `resources/views/livewire/student/message-center.blade.php` conserva estilos de un "Dark Mode" forzado (`bg-slate-900`, `text-slate-100`) que eran parte de un diseño anterior.

**Impacto UX**:
1.  **Incoherencia**: El resto del Dashboard de Estudiante usa tarjetas blancas sobre fondo gris claro. Al entrar a Mensajes, el cambio brusco de contraste rompe la continuidad.
2.  **Legibilidad**: Los inputs y áreas de texto oscuras pueden ser menos legibles en contextos diurnos si no están calibrados (contraste forzado).
3.  **Mantenimiento**: Tener dos versiones de la UI de mensajería (Admin Claro vs Estudiante Oscuro) duplica el esfuerzo de mantenimiento CSS.

---

## 2. ESPECIFICACIÓN TÉCNICA (PARA GPT-5.1)

La refactorización debe replicar **exactamente** la estructura visual de `admin/message-center.blade.php` pero manteniendo la lógica de negocio del estudiante (filtros simplificados, permisos).

### 2.1 Contenedor Principal y Layout
*   **Antes**: `<div class="grid gap-6 lg:grid-cols-[320px_1fr]">` (con fondos oscuros internos).
*   **Ahora**: Mantener grid, pero usar tarjetas blancas independientes.

### 2.2 Sidebar (Lista de Mensajes)
*   **Contenedor**: `bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden`.
*   **Header**: `border-b border-slate-100`. Título `text-slate-900`.
*   **Lista**:
    *   Item normal: `hover:bg-slate-50`.
    *   Item no leído: `bg-slate-50`.
    *   Texto Remitente: `text-slate-900`.
    *   Texto Meta/Preview: `text-slate-500` / `text-slate-400`.

### 2.3 Área Principal (Lectura/Redacción)
*   **Contenedor**: `bg-white border border-slate-200 rounded-2xl p-6 shadow-sm`.
*   **Formulario Redacción**:
    *   Inputs: `bg-white border-slate-200 text-slate-900 focus:border-sky-500 focus:ring-sky-500`.
    *   Radio Cards: `border-slate-200 hover:border-slate-300 bg-white` (Seleccionado: `border-sky-200 bg-sky-50`).
*   **Lectura Mensaje**:
    *   Header: `border-b border-slate-100`.
    *   Cuerpo: `prose-slate` (no invert).
    *   Footer: `border-t border-slate-100`. Chips de destinatarios `bg-slate-100 text-slate-700`.

---

## 3. REFERENCIA DE CÓDIGO (SNIPPET)

El Agente Implementador puede usar este bloque como base para la sección de filtros (radio buttons), que suele ser la más compleja de adaptar:

```html
<!-- Ejemplo de Radio Card en Tema Claro -->
<label class="flex items-center gap-2 p-3 rounded-xl border cursor-pointer transition {{ $target === 'teacher_team' ? 'border-sky-200 bg-sky-50' : 'border-slate-200 bg-white hover:border-slate-300' }}">
    <input type="radio" class="text-sky-500 focus:ring-sky-500" value="teacher_team" wire:model="target">
    <div>
        <p class="text-sm font-semibold text-slate-900">{{ __('Enviar al equipo docente') }}</p>
        <p class="text-xs text-slate-500">{{ __('Llega a los profesores responsables del programa.') }}</p>
    </div>
</label>
```

---

## 4. CRITERIOS DE ACEPTACIÓN

1.  **Visual**: El componente se ve indistinguible en estilo del Dashboard Admin.
2.  **Funcional**: Los mensajes se envían y leen correctamente.
3.  **Responsive**: En móvil, la lista y el detalle se comportan adecuadamente (stack vertical o navegación fluida).

---

[SPEC-READY-FOR-IMPLEMENTATION]

