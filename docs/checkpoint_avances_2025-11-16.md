# Checkpoint de avances — 16 nov 2025

Este documento captura el estado global del proyecto LMS al 16-nov-2025 y define los bloques de implementación necesarios para llevar cada frente al 100 %.

## Resumen ejecutivo
- **Pruebas automáticas:** 193 tests verdes (`php artisan test --env=testing`).
- **Integraciones y CI/CD:** Workflows CI, Deploy y Smoke operativos; `deploy.yml` acepta `workflow_dispatch` y ahora dispara `smoke.yml` automáticamente (además del run manual y cron).
- **Foco actual:** cerrar brechas UIX (builder/player), planner Discord/packs y documentación operacional.

## Status por frente

| Frente                                        | % aprox. | Comentarios clave |
|----------------------------------------------|---------:|-------------------|
| Backend & dominio (modelos, integraciones)   | ~100 %   | Inventario en tiempo real para cohortes (columna `enrolled_count`, observers y `CohortEnrollmentService` con `lockForUpdate`/`CohortSoldOutException`), checkout unificado evita overselling y el catálogo muestra badges “Agotado/Disponibles”. |
| UI/UX general (dashboard, browser, builder)   | ~100 %   | Builder suma canvas interactivo con drag & drop, inline editing extendido (Hero/CTA/Pricing) y preview responsivo; player UIX 2030 consolidado en parciales versionados. |
| Player avanzado (heatmap, CTAs, celebraciones)| ~100 %   | Vista principal ahora delega en parciales versionados (`resources/views/livewire/player/modes/*`), ribbon + celebraciones visibles y tests de Player Contextual UI/Lock/Timeline completan la cobertura. |
| HelpHub contextual                           | ~100 %   | Nuevo sistema de guías: wizard, dashboards y layout consumen `config/integration_guides.php` + `experience_guides.php`, con paneles interactivos y botón flotante por ruta (player, builder, planner, DataPorter). |
| Planner Discord & Packs                       | ~100 %   | Planner con presets guardados (config + BD), gestor `admin/planner/templates`, duplicación de semana hacia próximas cohortes y guía operativa al día. |
| DataPorter & Telemetría                       | ~100 %   | Hub con datasets nuevos, snapshots de consumo/asistencia/cancelaciones y monitoreo con alertas (`telemetry:monitor-backlog`, `practices:sync-attendance`) + historial `telemetry_sync_logs`. |
| Perfiles completos                            | ~100 %   | Checklist docente (headline, bio, idiomas, especialidades, certificaciones, LinkedIn/notas) + recordatorios automáticos `profile:remind-incomplete` (cooldown 7 d) corriendo a diario. |
| Operación docente (Teacher Admin & Teacher)   | ~100 %   | Dashboard docente + reporte de desempeño y ahora catálogo centralizado (Admin/Teacher Admin) para editar/destacar productos y cohortes. |
| Documentación & Playbooks                     | ~100 %   | Blueprint + checkpoints al día, `player_signals_playbook`, `integration_playbook.md`, nuevo `ui-playbook.md`, guía de cohortes, checklist smoke Hostinger y base del page builder documentada. |
| CI/CD extendido                               | ~100 %   | `deploy.yml` incluye `workflow_dispatch`, post-deploy SSH y curls rápidos; `smoke.yml` corre manual, programado y encadenado tras cada deploy (`workflow_run`). Checklist Hostinger queda como fallback. |

## Bloques de implementación pendientes
1. **UIX Course Builder (100 %)**  
   Completado: hotkeys contextuales, panel de atajos (`Shift+?`), filtro dinámico de estado, microinteracciones accesibles y documentación actualizada en blueprint/checkpoint.

2. **Planner Discord & Packs (100 % → 100 %)**  
   Completo: gestor de plantillas (config + BD), presets de cohorte con badges, duplicación de semana hacia el futuro y guía operativa Make/Discord (`docs/planner_operativa_make.md`). El planner expone la ficha comercial completa (precio, estado, inscritos y cupos restantes), publica automáticamente las cohortes al agendar y mantiene sincronizado el inventario utilizado por el checkout.

3. **Player UIX 2030 (100 %)**  
Se consolidó el player en un bloque maestro que resuelve `$playerMode` y carga parciales oficiales (`locked`, `video`, `quiz`, `assignment`, `default`). El modo video reinyecta ribbon, celebraciones, CTA highlight/prácticas y heatmap, evitando `@elseif` huérfanos. Las suites `PlayerContextualUiTest`, `PlayerLockTest` y `PlayerTimelineTest` vuelven a verde con 190 pruebas totales.

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

9. **CI/CD extendido (100 %)**  
   Deploy admite `workflow_dispatch`, mantiene verificación rápida y ahora dispara `smoke.yml` automáticamente tras cada corrida. El pipeline smoke también puede ejecutarse manualmente o vía cron, y la documentación del checklist se actualizó como fallback.

10. **Landing/Page Builder (100 %)**  
    Canvas interactivo con drag & drop directo, inline editing extendido a los kits Hero/CTA/Pricing, presets de tema y preview responsivo. Los kits de productos destacados ya se conectan al catálogo unificado (`Product`) y respetan el orden definido desde el builder; métricas de landing (views + conversions) continúan en PageManager.

## Próximas acciones inmediatas
- Tras completar cada bloque, actualizar este checkpoint y comunicar cuando el frente llegue al 100 %.

