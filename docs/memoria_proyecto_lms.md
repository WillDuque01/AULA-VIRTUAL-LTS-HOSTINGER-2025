# Memoria del proyecto LMS — Noviembre 2025

Esta memoria condensa el estado actual del LMS, los bloques implementados y los aprendizajes clave necesarios para operar y evolucionar la plataforma sin perder contexto operativo.

---

## 1. Contexto general
- **Objetivo**: entregar un LMS modular con constructor de cursos/páginas, planner Discord, player 2030 y catálogo unificado de productos/cohortes listo para venta directa.
- **Estado**: 100 % de los bloques comprometidos están implementados y documentados. El backlog activo se centra en monitoreo y ajustes finos post-entrega (limpieza de logs, health-check de pipelines y smoke encadenado).
- **Pruebas**: 193 tests (`php artisan test`) cubren builder, planner, player, catálogo, checkout, integraciones y pipelines de datos. La suite completa corre en ~35 s en local.
- **Infra / CI-CD**: Workflows `ci.yml`, `deploy.yml` y `smoke.yml` orquestan validaciones + despliegues Hostinger con `workflow_dispatch`, `workflow_run` y alertas Slack. El smoke se dispara manual, cron y post-deploy.

## 2. Bloques implementados
1. **UIX Course Builder (100 %)**  
   - Canvas interactivo, drag & drop Sortable, inline editing (Hero/CTA/Pricing), preview multi-dispositivo y kits conectados al catálogo (`featured-products`).
2. **Planner Discord & Practice Packs (100 %)**  
   - Gestor de plantillas BD/config, duplicación semanal, autopublicación de cohortes y snapshot comercial en el planner (precio, estado, cupos, inscritos).
3. **Player UIX 2030 (100 %)**  
   - Arquitectura basada en parciales por modo, ribbon/celebraciones, CTAs contextuales y microinteracciones auditadas en `player_signals_playbook`.
4. **Catálogo & Checkout unificados (100 %)**  
   - Modelo `Product` polimórfico (`PracticePackage`, `CohortTemplate`), carrito común, checkout con `PracticePackageOrderService` + `CohortEnrollmentService`, y widgets públicos conectados.
5. **Documentación & Playbooks (100 %)**  
   - Blueprint actualizado, `ui-playbook.md`, `products_catalog_operativa.md`, `planner_operativa_make.md`, `hostinger_smoke_checklist.md`.
6. **CI/CD extendido (100 %)**  
   - Deploy manual/automático, smoke encadenado y notificaciones Slack; scripts listos para `workflow_dispatch` y cron.

## 3. Arquitectura y flujos clave
- **Catálogo**: `Product` centraliza pricing/estado/inventario. Observers sincronizan packs (`PracticePackageObserver`) y cohortes (`CohortTemplateObserver`) con metadatos para UI, disponibilidad y checkout. Los bloques públicos (`featured-products`) consumen la misma fuente.
- **Cohortes pagadas**:
  - Migraciones añaden `price_*`, `status`, `is_featured`, `enrolled_count` y tabla `cohort_registrations`.
  - `CohortEnrollmentService` usa `lockForUpdate`, recalcula métricas y lanza `CohortSoldOutException` al agotar cupos; soporta inventario compartido con `PracticeCart`.
  - `CohortRegistrationObserver` mantiene inventario e índices en `Product`/`CohortTemplate`.
- **Carrito/Checkout**: `PracticeCart` guarda IDs de producto; `PracticeCheckout` recorre items, despacha el servicio correspondiente (packs/cohortes) dentro de una transacción global, registra conversiones (`PageConversion`) y devuelve messaging amigable si la cohorte está agotada.
- **Planner**: Plantillas DB/config, autopublicación al crear prácticas o duplicar series, snapshot comercial en UI (precio, cupos, inscritos) y duplicación semanal para Discord.
- **Builder/Page**: Editor Livewire `PageBuilderEditor` guarda revisiones, soporta inline editing y arrastre directo, y alimenta bloques públicos conectados al catálogo; incluye kits Hero/CTA/Pricing y featured-products.
- **Player**: Vista `livewire/player.blade.php` resuelve `playerMode` y carga parciales (`modes/*`); integra heatmap, CTAs contextuales, celebraciones y ribbon documentados en `player_signals_playbook`.

## 4. Operación y documentación
- **Runbooks**:  
  - `planner_operativa_make.md` (Discord + Make).  
  - `products_catalog_operativa.md` (catálogo/checkout).  
  - `hostinger_smoke_checklist.md` (fallback manual).  
  - `player_signals_playbook.md` + `ui-playbook.md`.
- **Guías rápidas**: Experience Guides (HelpHub) configuran botones flotantes y panel contextual en builder/planner/player.

## 5. Pruebas y monitoreo
- `tests/Feature/Catalog/CohortProductTest.php` y `CohortSoldOutTest.php` aseguran sincronización/inventario; `PlannerCohortTemplateTest.php` valida que el planner publique las cohortes DB al planificar.
- Suites adicionales cubren builder (`PageBuilderTest`), player (`PlayerContextualUiTest`/`PlayerLockTest`), operaciones Discord y DataPorter.
- Smoke Hostinger automatizado (`smoke.yml`) + checklist manual (`docs/hostinger_smoke_checklist.md`) como respaldo.

## 6. Seguimiento y próximos pasos
- **Monitoreo**: validar métricas `enrolled_count` tras cada ciclo de ventas, supervisar `CohortSoldOutException` en logs (Sentry/Slack) y vigilar que `Product.inventory` refleje los cupos disponibles.
- **Evoluciones sugeridas**:
  - Integrar proveedor de pago real (Stripe/PayPal) reutilizando `CohortEnrollmentService`.
  - Extender builder con drag & drop para secciones completas y kits dinámicos.
  - Añadir reportes de cohortes pagadas en DataPorter para marketing/cohort ops.

## 7. Estado operativo (22-nov-2025)
- Suite local verde (193 pruebas) y `php artisan test --env=testing` alineado con CI. Los workflows `ci.yml`, `deploy.yml` y `smoke.yml` están en verde después de la corrección del orden de llaves foráneas en snapshots y el fix del planner; el run “Fix planner publish flake #92” tomó 2m31s.
- Último push (`803f54f`) corrige el flake del planner (`PlannerCohortTemplateTest`) asegurando que las cohortes aplicadas desde la BD se publiquen aun cuando requieran pack. Los dumps de depuración fueron removidos y se añadió cache del ID seleccionado.
- Monitoreo activo: verificar métricas `enrolled_count`/`Product.inventory`, revisar los runs encadenados (`deploy` + `smoke`) tras cada push y mantener actualizado `hostinger_smoke_checklist.md` si surge algún hallazgo durante el despliegue.

Con esta memoria, cualquier integrante puede retomar el proyecto entendiendo arquitectura, flujos críticos, documentación disponible y focos de evolución inmediata, además del estado de monitoreo post-entrega.
