# Memoria del proyecto LMS — Noviembre 2025

Esta memoria condensa el estado actual del LMS, los bloques implementados y los aprendizajes clave necesarios para operar y evolucionar la plataforma sin perder contexto operativo.

---

## 1. Contexto general
- **Objetivo**: entregar un LMS modular con constructor de cursos/páginas, planner Discord, player 2030 y catálogo unificado de productos/cohortes listo para venta directa.
- **Estado**: 100 % de los bloques comprometidos están implementados y documentados. El backlog activo se centra en monitoreo y ajustes finos post-entrega.
- **Pruebas**: 190+ tests `php artisan test` cubren builder, planner, player, catálogo, checkout y pipelines de datos.
- **Infra / CI-CD**: Workflows `ci.yml`, `deploy.yml` y `smoke.yml` orquestan validaciones + despliegues Hostinger con `workflow_dispatch`, `workflow_run` y alertas Slack.

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
- **Catálogo**: `Product` centraliza pricing/estado/inventario. Observers sincronizan packs (`PracticePackageObserver`) y cohortes (`CohortTemplateObserver`) con metadatos para UI y checkout.
- **Cohortes pagadas**:
  - Migraciones añaden `price_*`, `status`, `is_featured`, `enrolled_count` y tabla `cohort_registrations`.
  - `CohortEnrollmentService` usa `lockForUpdate`, recalcula métricas y lanza `CohortSoldOutException` al agotar cupos.
  - `CohortRegistrationObserver` mantiene inventario en `Product` y `CohortTemplate`.
- **Carrito/Checkout**: `PracticeCart` guarda IDs de producto; `PracticeCheckout` recorre items y despacha el servicio correspondiente (packs/cohortes) dentro de una transacción global, registrando conversiones (`PageConversion`).
- **Planner**: Plantillas DB/config, autopublicación, consumo de datos comerciales para el staff y duplicaciones masivas + semana base.
- **Builder/Page**: Editor Livewire `PageBuilderEditor` guarda revisiones, soporta inline editing y arrastre directo, y alimenta bloques públicos conectados al catálogo.
- **Player**: Vista `livewire/player.blade.php` resuelve `playerMode` y carga parciales; las integraciones (heatmap, CTAs, celebraciones) se documentan en su playbook.

## 4. Operación y documentación
- **Runbooks**:  
  - `planner_operativa_make.md` (Discord + Make).  
  - `products_catalog_operativa.md` (catálogo/checkout).  
  - `hostinger_smoke_checklist.md` (fallback manual).  
  - `player_signals_playbook.md` + `ui-playbook.md`.
- **Guías rápidas**: Experience Guides (HelpHub) configuran botones flotantes y panel contextual en builder/planner/player.

## 5. Pruebas y monitoreo
- `tests/Feature/Catalog/CohortProductTest.php` y `CohortSoldOutTest.php` aseguran sincronización e inventario.
- Suites adicionales cubren builder (`PageBuilderTest`), planner (`PlannerCohortTemplateTest`), player (`PlayerContextualUiTest`), operaciones Discord y DataPorter.
- Smoke Hostinger automatizado (`smoke.yml`) + checklist manual como respaldo.

## 6. Seguimiento y próximos pasos
- **Monitoreo**: validar métricas `enrolled_count` tras cada ciclo de ventas y supervisar `CohortSoldOutException` en logs (alerta Sentry/Slack).
- **Evoluciones sugeridas**:
  - Integrar proveedor de pago real (Stripe/PayPal) reusando `CohortEnrollmentService`.
  - Extender builder con drag & drop para secciones completas y kits dinámicos.
  - Añadir reportes de cohortes pagadas en DataPorter para marketing/cohort ops.

Con esta memoria, cualquier integrante puede retomar el proyecto entendiendo arquitectura, flujos críticos, documentación disponible y focos de evolución inmediata.


