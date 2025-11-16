# Fundamentos del Page Builder

Este documento cubre la base de datos y servicios recién creados para soportar el constructor tipo Elementor solicitado.

---

## 1. Estructura de datos

### Tabla `pages`
- `title`, `slug`, `type` (`home`, `landing`, `custom`), `locale`, `status`.
- `published_revision_id`: referencia a la versión publicada (nullable).
- `meta`: JSON para parámetros globales (SEO, hero default, etc.).

### Tabla `page_revisions`
- `page_id`: relación directa.
- `label`: referencia humana del borrador (“Hero v2”).
- `layout`: JSON con arreglo de bloques en orden.
- `settings`: JSON para configuraciones globales (paleta, tipografías locales).
- `author_id`: usuario que hizo el cambio.
- timestamps permiten controlar historial.

> Nota: `pages.published_revision_id` se enlaza tras crear la tabla `page_revisions`, por eso la migración hace un `Schema::table` posterior.

## 2. Modelos

- `App\Models\Page`:
  - `revisions()` (hasMany).
  - `publishedRevision()` (belongsTo).
  - Scope `published()`.

- `App\Models\PageRevision`:
  - `page()` y `author()` (belongsTo).
  - Casts JSON para `layout` y `settings`.

## 3. Servicio `PageBuilderService`

Ubicación: `app/Services/PageBuilderService.php`

Métodos clave:
- `createPage($attributes, $layout = [], $settings = [], $authorId = null)`: crea la página y genera un primer draft.
- `saveDraft(Page $page, array $payload, ?int $authorId = null)`: guarda nuevas revisiones.
- `publish(Page $page, PageRevision $revision = null)`: promueve una revisión a publicada y actualiza `published_revision_id`.

Este servicio se usará por el futuro constructor Livewire para encapsular la lógica de guardado/publish/rollback.

## 4. Tests

- `tests/Feature/Page/PageBuilderServiceTest.php` verifica:
  - Creación de página con revisión inicial.
  - Publicación de una revisión y actualización del estado.
- `tests/Feature/Admin/PageBuilderEditorTest.php` valida el flujo Livewire (agregar bloque + guardar borrador).

## 5. UI del builder (fase 2)

- `livewire:admin.page-builder-editor`:
  - Panel de kits definido en `config/page_builder.php`.
  - Acciones por bloque: mover, duplicar, eliminar.
  - Formularios especializados en `resources/views/livewire/admin/page-builder/blocks`.
  - Botones “Guardar borrador” y “Publicar” conectados al servicio.
- Render público:
  - Vistas `resources/views/page/blocks/*` (Hero, CTA, Pricing, Testimonials, Featured Products).
  - Controlador `PageController@show` + rutas `/landing/{slug}` y Home dinámico.

## 6. Kit UI y fase 3

- Nuevos kits: `gallery_masonry`, `team_grid`, `faq_list`, `timeline_steps` y `featured_products` ampliado (categoría + IDs concretos).
- Editor incorpora preview responsivo (desktop/tablet/móvil), variables globales de tema (colores, tipografía) y reordenamiento `wire:sortable`.
- Bloques renderizados en `resources/views/page/blocks` respetan las variables del tema.
- Panel `livewire:admin.page-manager` gestiona creación/duplicado y muestra conteo de vistas (`page_views`).

## 7. Analítica básica

- Tabla `page_views` + modelo `PageView`.
- `PageController@show` registra visitas (session, referer, user agent) antes de renderizar.
- `Page` expone `views()`/`views_count` y el manager los despliega para priorizar landings.

## 8. Fase 5 (inline + presets + conversions)

- **Inline editing**: bloques como Hero soportan edición directa (`contenteditable`) sincronizada con Livewire (`inlineUpdate`). Se mantiene el formulario clásico para ajustes finos.
- **Theme presets**: `config/page_builder.php` expone `theme_presets` (Noir/Sunset/Matcha). El sidebar permite aplicarlos y luego ajustar colores/tipografía.
- **Nuevos kits**: `lead-form`, `video-testimonial`, `countdown` añaden formularios ligeros, testimonios en video y contadores con CTA.
- **Landing analytics**: tabla/modelo `page_conversions`, hook en `PracticeCheckout` que toma `session('landing_ref')` (set por `PageController`) y registra monto/productos vendidos por landing. `PageManager` muestra vistas, conversiones y top productos por página.

## 9. Próximos pasos

- Extender inline editing a más bloques (CTA, Pricing) + drag & drop directo en canvas.
- Presets avanzados (grid, tamaños tipográficos) y guardado como "Tema" reutilizable entre landings.
- Dashboard consolidado con funnels (views → clicks → checkout) e integración DataPorter/telemetría externa.

Con esto el constructor ya permite crear/publish Homes y Landings, ajustar estilos en caliente y medir impacto (vistas + conversiones) directamente desde el dashboard.

