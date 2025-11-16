# Guía operativa — Planner Discord & Make

Este documento describe el flujo recomendado para profesores/admin al crear prácticas de cohorte y sincronizarlas con Discord/Make.

## 1. Plantillas de cohorte
- Ubicadas en `config/practice.php` **y** en el panel `Admin → Planner → Plantillas`.
- Cada preset incluye:
  - `name`, `description`
  - `type` (`cohort` o `global`), `cohort_label`
  - `duration_minutes`, `capacity`, `requires_package`
  - Bloques `slots` (weekday + hh:mm)
- Las del panel se guardan en BD (`cohort_templates`) y pueden editarse sin despliegue; las de config siguen disponibles como fallback.
- Botón “Gestionar presets” (visible para Admin / Teacher Admin) abre el manager para crear, duplicar o eliminar colecciones reutilizables.

## 2. Flujo en el planner
1. **Selecciona plantilla** desde tarjetas “Plantillas de cohorte” (puedes mezclar presets del equipo y config).
2. Ajusta solo aquello necesario (lección, descripción específica).
3. Usa “Bloques recurrentes” para cambios puntuales.
4. Programa sesión individual o ejecuta “Duplicación masiva” (por semanas) desde el formulario principal.
5. **Nuevo:** Duplica toda la semana visible a futuras semanas con el panel “Duplicar semana al futuro” (elige offset y repeticiones).

## 3. Checklist Make/Discord
- Confirmar webhook activo (`DISCORD_WEBHOOK_URL`).
- Escenario Make debe escuchar `discord.practice.scheduled`.
- Actualiza timezone en Make antes de duplicar series.
- Revisa el outbox (`/admin/integrations/outbox`) tras cada corrida.

## 4. Métricas y exportes
- Cada práctica queda disponible en `DataPorter` (planner dataset) para exportar CSV/JSON.
- Columna `cohort_template` indica si proviene de un preset.
- Usa filtros por `creator_id`, `lesson_id` o `cohort_label`.

## 5. Problemas comunes
| Situación | Solución |
|-----------|----------|
| Horario duplicado con desfase | Revisa timezone en Make y en el formulario antes de duplicar. |
| No se dispara Discord | Verifica que el preset tenga `requires_package` según corresponda y que el evento `DiscordPracticeScheduled` aparezca en logs. |
| Plantilla editada no se refleja | Limpiar config cache y recargar el planner (`php artisan config:clear`). |


