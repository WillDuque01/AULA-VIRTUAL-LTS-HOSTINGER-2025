# Checkpoint de avances — 16 nov 2025

Este documento captura el estado global del proyecto LMS al 16-nov-2025 y define los bloques de implementación necesarios para llevar cada frente al 100 %.

## Resumen ejecutivo
- **Pruebas automáticas:** 159 tests verdes (`php artisan test --env=testing`).
- **Integraciones y CI/CD:** Workflows CI, Deploy y Smoke operativos; faltan ajustes de `workflow_dispatch` y encadenamiento.
- **Foco actual:** cerrar brechas UIX (builder/player), planner Discord/packs y documentación operacional.

## Status por frente

| Frente                                        | % aprox. | Comentarios clave |
|----------------------------------------------|---------:|-------------------|
| Backend & dominio (modelos, integraciones)   | ~93 %    | Nuevo modelo `Product` con observer automático, carrito unificado por producto y base lista para cohortes/servicios. |
| UI/UX general (dashboard, browser, builder)   | ~95 %    | Builder con métricas + hotkeys reales (N, Ctrl/⌘+S, Shift+?) y filtro de estado, microinteracciones accesibles y paneles responsivos; player UIX 2030 solo requiere pulido final. |
| Player avanzado (heatmap, CTAs, celebraciones)| ~100 %   | Panel insights ahora muestra racha/XP/último logro, CTA inteligente, celebraciones con fallback motion-safe y documentación QA (`docs/player_signals_playbook.md`). |
| HelpHub contextual                           | ~100 %   | Nuevo sistema de guías: wizard, dashboards y layout consumen `config/integration_guides.php` + `experience_guides.php`, con paneles interactivos y botón flotante por ruta (player, builder, planner, DataPorter). |
| Planner Discord & Packs                       | ~100 %   | Planner con plantillas multi-slot, presets de cohorte, checklist Make y guía operativa (`docs/planner_operativa_make.md`) completada. |
| DataPorter & Telemetría                       | ~100 %   | Hub con datasets nuevos, snapshots de consumo/asistencia/cancelaciones y monitoreo con alertas (`telemetry:monitor-backlog`, `practices:sync-attendance`) + historial `telemetry_sync_logs`. |
| Perfiles completos                            | ~100 %   | Checklist docente (headline, bio, idiomas, especialidades, certificaciones, LinkedIn/notas) + recordatorios automáticos `profile:remind-incomplete` (cooldown 7 d) corriendo a diario. |
| Operación docente (Teacher Admin & Teacher)   | ~100 %   | Dashboard docente + reporte de desempeño y ahora catálogo centralizado (Admin/Teacher Admin) para editar/destacar productos y cohortes. |
| Documentación & Playbooks                     | ~100 %   | Blueprint + checkpoints al día, `player_signals_playbook`, `integration_playbook.md`, nuevo `ui-playbook.md`, guía de cohortes, checklist smoke Hostinger y base del page builder documentada. |
| CI/CD extendido                               | ~90 %    | Workflows OK; falta `workflow_dispatch` manual y reporte integrado de smoke tests. |

## Bloques de implementación pendientes
1. **UIX Course Builder (100 %)**  
   Completado: hotkeys contextuales, panel de atajos (`Shift+?`), filtro dinámico de estado, microinteracciones accesibles y documentación actualizada en blueprint/checkpoint.

2. **Planner Discord & Packs (100 % → 100 %)**  
   Completo: presets de cohorte, duplicación masiva y guía operativa Make/Discord (`docs/planner_operativa_make.md`).

3. **Player UIX 2030 (100 %)**  
   Completado con panel de insights (racha, XP, último logro), CTA contextual ampliado y efectos celebratorios con fallback `prefers-reduced-motion`.

4. **DataPorter & Telemetría (100 %)**  
   Cerrado: nuevos snapshots (`practice_attendance`, `practice_cancellation`), comando `practices:sync-attendance`, alertas `telemetry:monitor-backlog` y documentación actualizada.

5. **Perfiles completos (100 %)**  
   Completado: campos profesionales Teacher Admin, banner con formulario in-place y automatización `profile:remind-incomplete` (cron diario 09:00, configurable con `PROFILE_REMINDER_COOLDOWN_DAYS`).

6. **Documentación & Playbooks (100 %)**  
   Entregados: `docs/ui-playbook.md`, `docs/guia_cohortes_discord.md`, `docs/hostinger_smoke_checklist.md` y blueprint actualizado. ADRs UIX se documentan en el playbook y blueprint.
7. **HelpHub contextual (100 %)**  
   Configuración centralizada de credenciales (`integration_guides.php`) + Experience Guides (`experience_guides.php`) integrada en wizard, dashboards y botón flotante global. Nuevos componentes `x-help.contextual-panel` y `x-help.floating`.

8. **Operación docente (100 %)**  
   Cerrado: historial de aprobación por propuesta, filtros de estado en Course Builder, métricas de desempeño (tiempos y tasas) y dashboard dedicado `teacher-performance`.

9. **CI/CD extendido (90 % → 100 %)**  
   `workflow_dispatch` en Deploy, encadenar smoke.yml y publicar resultados (Slack/Discord) con documentación del procedimiento.

10. **Landing/Page Builder (60 % → 85 %)**  
    Drag & drop real con `wire:sortable`, preview responsivo (desktop/tablet/móvil), kits extendidos (Galería, Equipo, FAQ), widget destacado configurable (categoría/IDs), render público de todos los bloques y módulo `PageManager` para crear/duplicar páginas antes de abrir el builder. Falta drag and drop visual completo y variables globales.

## Próximas acciones inmediatas
- Desarrollar el **Page/Landing Builder** con kits responsivos y bloque de productos destacados conectado al catálogo.
- Extender el catálogo a cohortes/productos personalizados y enlazarlo al checkout generalizado.
- Activar `workflow_dispatch` + smoke tests encadenados en CI/CD y documentar el runbook Hostinger.
- Tras completar cada bloque, actualizar este checkpoint y comunicar cuando el frente llegue al 100 %.

