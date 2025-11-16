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

- Nuevos kits: `gallery_masonry`, `team_grid`, `faq_list` y `featured_products` ampliado (categoría + ids personalizados).
- Editor incorpora preview responsivo (desktop/tablet/mobile) y reordenamiento con `wire:sortable`.
- Bloques renderizados en `resources/views/page/blocks` y pueden añadirse sin tocar código backend.
- Panel `livewire:admin.page-manager` gestiona creación/duplicado de Home/Landings y enlaza al builder.

## 7. Próximos pasos

- Añadir drag & drop visual con arrastre directo en el canvas (actualmente botones/handles).
- Variables globales de estilo (paleta por página) y más bloques (FAQ avanzado, testimonios con video).
- Integrar analítica directa (vista previa de conversiones por landing).

Con esto el constructor ya cubre la experiencia mínima viable para crear/publish Homes y Landings desde el dashboard.

