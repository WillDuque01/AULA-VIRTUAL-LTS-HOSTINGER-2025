# Guía operativa — Planner Discord & Make

Este documento describe el flujo recomendado para profesores/admin al crear prácticas de cohorte y sincronizarlas con Discord/Make.

## 1. Plantillas de cohorte
- Ubicadas en `config/practice.php`. Cada preset incluye:
  - `name`, `description`
  - `type` (`cohort` o `global`), `cohort_label`
  - `duration_minutes`, `capacity`, `requires_package`
  - Bloques `slots` (weekday + hh:mm)
- Pueden editarse sin despliegue chequeando cache (`php artisan config:clear`).

## 2. Flujo en el planner
1. **Selecciona plantilla** desde tarjetas “Plantillas de cohorte”.
2. Ajusta solo aquello necesario (lección, descripción específica).
3. Usa “Bloques recurrentes” para cambios puntuales.
4. Programa sesión individual o ejecuta “Duplicación masiva”.

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


