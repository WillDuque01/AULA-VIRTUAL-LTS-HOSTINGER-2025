# Guía operativa — Planner Discord & Make

Este documento describe el flujo recomendado para profesores/admin al crear prácticas de cohorte y sincronizarlas con Discord/Make.

## 1. Plantillas de cohorte
- Ubicadas en `config/practice.php` **y** en el panel `Admin → Planner → Plantillas`.
- Cada preset incluye:
  - `name`, `description`
  - `type` (`cohort` o `global`), `cohort_label`
  - `duration_minutes`, `capacity`, `requires_package`
  - Bloques `slots` (weekday + hh:mm)
- Las del panel se guardan en BD (`cohort_templates`) y pueden editarse sin despliegue; las de config siguen disponibles como fallback. Las plantillas DB ahora guardan precio, moneda, estado (`draft/published/archived`) y un flag “destacado” que alimenta el catálogo público.
- Botón “Gestionar presets” (visible para Admin / Teacher Admin) abre el manager para crear, duplicar o eliminar colecciones reutilizables.

## 2. Flujo en el planner
1. **Selecciona plantilla** desde las tarjetas “Plantillas de cohorte”. Ahora se visualizan precio, estado, cupos totales, inscritos y cupos disponibles (badge “Agotado” cuando `available_slots = 0`). Las plantillas provenientes de BD también ofrecen accesos rápidos al catálogo/Admin.
2. Ajusta solo aquello necesario (lección, descripción específica).
3. Usa “Bloques recurrentes” para cambios puntuales.
4. Programa sesión individual o ejecuta “Duplicación masiva” (por semanas) desde el formulario principal. Cuando usas una plantilla DB, al crear la primera sesión se marca automáticamente como `published`, asegurando que el `Product` asociado quede activo en `/catalogo`.
5. **Nuevo:** Duplica toda la semana visible a futuras semanas con el panel “Duplicar semana al futuro” (elige offset y repeticiones).

## 3. Cupos pagados y disponibilidad
- Cada práctica creada desde plantilla DB publica automáticamente la cohorte y mantiene `enrolled_count` sincronizado.
- `CohortEnrollmentService` bloquea nuevas ventas cuando no quedan cupos y devuelve el mensaje “Ya no hay cupos disponibles”.
- Si necesitas liberar un cupo (cancelación), quita el registro desde `Admin → Planner → Plantillas` (sección “Inscripciones”) o vía base de datos; el observador actualiza inventario al vuelo.
- Cuando dupliques semanas, el planner fuerza estado `published` y refresca los contadores para que el catálogo siempre muestre inventario real.

## 4. Checklist Make/Discord
- Confirmar webhook activo (`DISCORD_WEBHOOK_URL`).
- Escenario Make debe escuchar `discord.practice.scheduled`.
- Actualiza timezone en Make antes de duplicar series.
- Revisa el outbox (`/admin/integrations/outbox`) tras cada corrida.

## 5. Métricas y exportes
- Cada práctica queda disponible en `DataPorter` (planner dataset) para exportar CSV/JSON.
- Columna `cohort_template` indica si proviene de un preset.
- Usa filtros por `creator_id`, `lesson_id` o `cohort_label`.

## 6. Problemas comunes
| Situación | Solución |
|-----------|----------|
| Horario duplicado con desfase | Revisa timezone en Make y en el formulario antes de duplicar. |
| No se dispara Discord | Verifica que el preset tenga `requires_package` según corresponda y que el evento `DiscordPracticeScheduled` aparezca en logs. |
| Plantilla editada no se refleja | Limpiar config cache y recargar el planner (`php artisan config:clear`). |


