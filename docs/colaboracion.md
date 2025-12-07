# Protocolo de Colaboración - Proyecto Aula Virtual LTS

Este documento centraliza el estado, las señales y el roadmap de implementación entre los agentes.

---

## ESTADO DEL PROYECTO
| Agente | Estado | Última Acción |
| :--- | :--- | :--- |
| Opus 4.5 | **COMPLETADO** | Auditoría de Infraestructura y Fixes Backend. |
| Gemini 3 Pro | **EN PROGRESO** | Auditoría Visual y Plan de Unificación UI (Mensajería). |
| GPT-5.1 | **PENDIENTE** | Esperando instrucciones para refactorización visual final. |

---

## [ROADMAP DE IMPLEMENTACIÓN]

### Fase 1: Fundamentos Visuales (UIX 2030) ✅
*   Configuración Tailwind (`Inter`/`Onest`).
*   Limpieza Blade Player.
*   Sistema Feedback (Toast).

### Fase 2: Refactorización Estructural (Player & Builder) ✅
*   Drawer Móvil.
*   Microinteracciones.
*   Tabs escritorio.

### Fase 3: Pruebas Funcionales Obligatorias ✅
*   Prueba e2e de CTA.
*   Responsive Check.
*   Toast Check.

### Fase 4: Dashboards y Experiencia de Profesor ✅
*   Dashboard Profesor UIX 2030.
*   Planner Responsivo.
*   Optimizaciones de Carga.

### Fase 5: Experiencia Estudiante y Correcciones Finales (Turno Actual)
*Objetivo: Eliminar inconsistencias visuales críticas detectadas en auditoría.*
1.  **Unificación Mensajería**: Migrar `student/message-center` al tema claro para coincidir con `admin/message-center`.
2.  **Validación Final**: Asegurar que no queden componentes "oscuros" fuera de lugar.

---

## [GEMINI] Plan de Unificación UI (Instrucciones para GPT-5.1 - Turno Final)

**Contexto:** Se detectó que el Centro de Mensajes del Estudiante sigue usando un tema oscuro (`bg-slate-900`) que rompe la consistencia con el resto de la plataforma (tema claro `bg-gray-100`).

### TAREA ÚNICA: Refactorizar `student/message-center.blade.php`

**Archivo Objetivo:** `resources/views/livewire/student/message-center.blade.php`

**Instrucciones de Diseño:**
Reemplazar la estructura actual "Dark Mode" por el patrón "Light Mode" (Card UI) que ya usaste en el panel de Admin.

1.  **Contenedor Principal:**
    *   Cambiar `bg-slate-900/70 border-slate-800` por `bg-white border-slate-200 shadow-sm`.
2.  **Sidebar (Lista de Mensajes):**
    *   Fondo: `bg-white`.
    *   Texto: `text-slate-900` (títulos), `text-slate-500` (meta).
    *   Hover: `hover:bg-slate-50`.
    *   Activo: `bg-sky-50`.
3.  **Área de Lectura/Composición:**
    *   Fondo: `bg-white`.
    *   Inputs/Textareas: `bg-white border-slate-200 text-slate-900 focus:ring-sky-500`.
    *   Tipografía: Asegurar legibilidad oscura sobre claro.

**Código de Referencia (Admin):**
Usa `resources/views/livewire/admin/message-center.blade.php` como guía visual para mantener la paridad total entre roles.

---

*Firmado por: Gemini 3 Pro (Arquitecto UX/UI)*

[TURNO-COMPLETADO: UI-UNIFICATION-READY]

---

## ESTADO: Turno 33 (Opus Despliegue Final) En Progreso.

**Fecha**: 07-dic-2025

[LINK] Ver Reporte en 33_OPUS_FINAL_QA_REPORT.md

### Ejecutado:
- ✅ Archivos GPT-5.1 desplegados al VPS (emails UIX 2030, Message Center, QR fix)
- ✅ Caché limpiada y permisos corregidos
- ✅ 6/7 notificaciones de email enviadas y recibidas
- ✅ Contraseñas QA actualizadas para 9 usuarios
- ⚠️ Problema de autenticación en navegador pendiente

### Problema Crítico Pendiente:
El login no redirige al dashboard después de enviar el formulario. Requiere investigación de:
- `config/session.php`
- `config/fortify.php`
- Middlewares de autenticación

### Scripts Creados:
- `scripts/update_qa_passwords.php` - Actualiza contraseñas de usuarios QA

---

[DEPLOYMENT-COMPLETE-AUTH-PENDING]
