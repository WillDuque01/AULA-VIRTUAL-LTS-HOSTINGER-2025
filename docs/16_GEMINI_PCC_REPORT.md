# 16_GEMINI_PCC_REPORT.md

## Reporte de Auditoría PCC — Certificación de Producto, UX y Localización

**Agente**: Gemini 3 Pro (Arquitecto de Experiencia de Usuario)
**Fecha**: 06-dic-2025
**Foco**: Localización (L10N), Flujos UI/UX, Constructores y Responsividad.

---

## 1. RESUMEN EJECUTIVO

Se ha ejecutado una auditoría exhaustiva 360° sobre el frontend y la experiencia de usuario del LMS. El análisis se basó en los reportes previos (`14_OPUS_SUPER_UAT_REPORT.md`, `15_GPT_FINAL_FIXES_REPORT.md`) y en la inspección directa del código fuente de los componentes críticos.

| Área | Estado | Hallazgos Clave |
|------|--------|-----------------|
| **Localización (L10N)** | 🟡 ALERTA | Se detectaron textos *hardcodeados* críticos en el **Page Builder** que no fueron cubiertos en el fix anterior. |
| **Constructores (Builders)** | 🟡 PARCIAL | El **Course Builder** carece de Drag & Drop real (`wire:sortable` no implementado), afectando gravemente la usabilidad en pantallas táctiles y desktop. |
| **UI/UX General** | 🟢 APROBADO | La consistencia visual (UIX 2030) es alta en Dashboard y Player. El sistema de feedback (Toasts) es robusto. |
| **Responsividad** | 🟢 APROBADO | Los layouts principales (Admin, Student) se adaptan correctamente a viewport móvil. |

---

## 2. HALLAZGOS DETALLADOS Y EVIDENCIA

### 2.1 Localización (L10N) - Fugas Detectadas

A pesar de los esfuerzos previos, el componente `PageBuilderEditor` (`resources/views/livewire/admin/page-builder-editor.blade.php`) contiene múltiples cadenas de texto sin traducir. Esto rompe la experiencia para usuarios en inglés (`/en/*`).

**Evidencia (Strings Hardcodeados):**
*   Línea 28: `"Page Builder"` (Título estático)
*   Línea 30: `"Arrastra bloques o usa los botones para construir la landing."`
*   Línea 53: `"Guardar borrador"`
*   Línea 61: `"Publicar página"`
*   Línea 78: `"Canvas interactivo"`
*   Línea 79: `"Arrastra los bloques directamente en el canvas y edita el contenido inline."`
*   Línea 83: `"Arrastra para reordenar"`
*   Línea 86: `"Haz clic para editar texto"`
*   Línea 132: `"Agrega bloques con los kits de la derecha para comenzar."`
*   Línea 142: `"Tema"` (Sidebar)
*   Línea 155-167: Labels de configuración (`"Color primario"`, `"Fondo"`, `"Tipografía (CSS)"`).
*   Línea 175: `"Kits disponibles"`

**Impacto:** Crítico para la internacionalización. Un administrador que use la interfaz en inglés verá una mezcla confusa de idiomas.

### 2.2 Course Builder - Deuda de Usabilidad (Drag & Drop)

El reporte 14 de Opus identificó correctamente que el Course Builder **no implementa** `wire:sortable`. Mi revisión del código (`resources/views/livewire/builder/course-builder.blade.php`) confirma esto.

**Evidencia Técnica:**
*   Aunque existen elementos visuales con la clase `drag-handle` (líneas 149, 235), **no hay directivas de Livewire Sortable** (`wire:sortable`, `wire:sortable.item`) conectadas al contenedor principal ni a las listas de lecciones.
*   El controlador `CourseBuilder.php` tiene un listener `'builder-reorder' => 'saveOrder'`, pero este evento parece depender de una implementación JS personalizada (`Sortable.js` en script inline) que podría ser frágil o inconsistente con el estándar `livewire-sortable` usado en el Page Builder.

**Riesgo UX:** La reordenación de contenido es una función core de un LMS. Depender de implementaciones JS ad-hoc aumenta el riesgo de bugs de sincronización (índices visuales vs backend).

### 2.3 UI Estudiante - Student Browser

La implementación del `DiscordPracticeBrowser` (revisada en turnos anteriores) es funcional y responsiva. Sin embargo, se observa una oportunidad de mejora en la **retroalimentación de estado vacío**.

*   **Estado:** Si no hay prácticas, se muestra un mensaje genérico.
*   **Mejora UX:** Debería incluir un CTA claro ("Solicitar nueva fecha") que dispare una acción real o lleve al canal de soporte.

---

## 3. PLAN DE ACCIÓN CORRECTIVA (PARA GPT-5.1)

Se requiere una intervención final quirúrgica para cerrar las brechas de L10N y UX detectadas.

### Tarea 1: Localización Total del Page Builder (Prioridad Alta)
**Objetivo:** Eliminar todos los textos hardcodeados en `resources/views/livewire/admin/page-builder-editor.blade.php`.

**Instrucción Técnica:**
1.  Crear/Actualizar archivos de idioma `resources/lang/es/page_builder.php` y `resources/lang/en/page_builder.php`.
2.  Mapear cada string identificado (ver sección 2.1) a una clave de traducción (ej. `__('page_builder.canvas_title')`).
3.  Reemplazar los literales en la vista Blade por las directivas `__('...')`.

### Tarea 2: Estandarización de Drag & Drop en Course Builder (Prioridad Media/Alta)
**Objetivo:** Asegurar que la experiencia de reordenamiento sea nativa y robusta.

**Instrucción Técnica:**
1.  Revisar la implementación JS actual en `course-builder.blade.php` (líneas 910+).
2.  Si es inestable o compleja, reemplazarla por el plugin oficial `livewire-sortable` (como en Page Builder), o reforzar el script actual para garantizar que el payload enviado a `saveOrder` sea siempre correcto tras múltiples arrastres.
3.  **Verificación:** Confirmar que al soltar un ítem, se dispara el feedback visual (Toast "Orden guardado").

---

## 4. CONCLUSIÓN DEL AUDITOR

El producto está en un estado **muy avanzado y sólido** en términos de infraestructura y lógica de negocio. La interfaz de usuario es moderna y coherente. Sin embargo, la **fuga de localización en el Page Builder** es un defecto de calidad que debe corregirse antes del lanzamiento oficial ("Gold Master").

La usabilidad del Course Builder es aceptable para un MVP, pero su mecanismo de reordenamiento debe ser vigilado de cerca.

**Veredicto Final:** 🟡 **APROBADO CON OBSERVACIONES (Requiere Fix de L10N)**.

---

[PCC-FALLO-BLOQUEANTE]
*(Se marca como fallo bloqueante debido a la severidad de los textos hardcodeados para la experiencia multi-idioma).*

