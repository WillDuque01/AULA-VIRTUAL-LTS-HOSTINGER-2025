# Checkpoint de avances — 16 nov 2025

Este documento captura el estado global del proyecto LMS al 16-nov-2025 y define los bloques de implementación necesarios para llevar cada frente al 100 %.

## Resumen ejecutivo
- **Pruebas automáticas:** 159 tests verdes (`php artisan test --env=testing`).
- **Integraciones y CI/CD:** Workflows CI, Deploy y Smoke operativos; faltan ajustes de `workflow_dispatch` y encadenamiento.
- **Foco actual:** cerrar brechas UIX (builder/player), planner Discord/packs y documentación operacional.

## Status por frente

| Frente                                        | % aprox. | Comentarios clave |
|----------------------------------------------|---------:|-------------------|
| Backend & dominio (modelos, integraciones)   | ~90 %    | Discord Practices, packs, notificaciones, outbox y now pivotes `course_teacher` + submissions docentes. |
| UI/UX general (dashboard, browser, builder)   | ~80 %    | Builder con métricas, chips y nuevo focus panel tabulado con quick actions; restan microinteracciones UIX 2030 del player. |
| Player avanzado (heatmap, CTAs, celebraciones)| ~100 %   | Panel insights ahora muestra racha/XP/último logro, CTA inteligente, celebraciones con fallback motion-safe y documentación QA (`docs/player_signals_playbook.md`). |
| HelpHub contextual                           | ~100 %   | Nuevo sistema de guías: wizard, dashboards y layout consumen `config/integration_guides.php` + `experience_guides.php`, con paneles interactivos y botón flotante por ruta (player, builder, planner, DataPorter). |
| Planner Discord & Packs                       | ~100 %   | Planner con plantillas multi-slot, presets de cohorte, checklist Make y guía operativa (`docs/planner_operativa_make.md`) completada. |
| DataPorter & Telemetría                       | ~100 %   | Hub con datasets nuevos, snapshots de consumo/asistencia/cancelaciones y monitoreo con alertas (`telemetry:monitor-backlog`, `practices:sync-attendance`) + historial `telemetry_sync_logs`. |
| Perfiles completos                            | ~100 %   | Checklist docente (headline, bio, idiomas, especialidades, certificaciones, LinkedIn/notas) + recordatorios automáticos `profile:remind-incomplete` (cooldown 7 d) corriendo a diario. |
| Operación docente (Teacher Admin & Teacher)   | ~100 %   | Dashboard docente con historial de aprobación, filtros por estado en builder y nuevo reporte de desempeño (tiempos, tasas, backlog) disponible para Admin/Teacher Admin. |
| Documentación & Playbooks                     | ~85 %    | Blueprint + checkpoints al día, `player_signals_playbook`, `integration_playbook.md` y actualización roles; faltan UI playbook completo, guía de cohortes y checklists Hostinger. |
| CI/CD extendido                               | ~90 %    | Workflows OK; falta `workflow_dispatch` manual y reporte integrado de smoke tests. |

## Bloques de implementación pendientes
1. **UIX Course Builder (80 % → 100 %)**  
   Focus panel tabulado y quick actions listos; pendiente completar microinteracciones UIX 2030 restantes (hotkeys contextuales, tooltips accesibles), ajustes responsive y documentación final.

2. **Planner Discord & Packs (100 % → 100 %)**  
   Completo: presets de cohorte, duplicación masiva y guía operativa Make/Discord (`docs/planner_operativa_make.md`).

3. **Player UIX 2030 (100 %)**  
   Completado con panel de insights (racha, XP, último logro), CTA contextual ampliado y efectos celebratorios con fallback `prefers-reduced-motion`.

4. **DataPorter & Telemetría (100 %)**  
   Cerrado: nuevos snapshots (`practice_attendance`, `practice_cancellation`), comando `practices:sync-attendance`, alertas `telemetry:monitor-backlog` y documentación actualizada.

5. **Perfiles completos (100 %)**  
   Completado: campos profesionales Teacher Admin, banner con formulario in-place y automatización `profile:remind-incomplete` (cron diario 09:00, configurable con `PROFILE_REMINDER_COOLDOWN_DAYS`).

6. **Documentación & Playbooks (75 % → 100 %)**  
   `docs/ui-playbook.md`, guía de cohortes, checklists de smoke Hostinger y ADRs UIX 2030.
7. **HelpHub contextual (100 %)**  
   Configuración centralizada de credenciales (`integration_guides.php`) + Experience Guides (`experience_guides.php`) integrada en wizard, dashboards y botón flotante global. Nuevos componentes `x-help.contextual-panel` y `x-help.floating`.

8. **Operación docente (100 %)**  
   Cerrado: historial de aprobación por propuesta, filtros de estado en Course Builder, métricas de desempeño (tiempos y tasas) y dashboard dedicado `teacher-performance`.

9. **CI/CD extendido (90 % → 100 %)**  
   `workflow_dispatch` en Deploy, encadenar smoke.yml y publicar resultados (Slack/Discord) con documentación del procedimiento.

## Próximas acciones inmediatas
- Completar **UIX Course Builder 100 %**: hotkeys contextuales, microinteracciones y documentación del focus panel.
- Mantener guías y playbooks al día (`experience_guides.php`, UI playbook 2030 y guía de cohortes).
- Activar `workflow_dispatch` + smoke tests encadenados en CI/CD y documentar el runbook Hostinger.
- Tras completar cada bloque, actualizar este checkpoint y comunicar cuando el frente llegue al 100 %.

