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

## 5. Próximos pasos

- Construir el lienzo drag & drop (Livewire) que consuma `PageBuilderService`.
- Definir kits/bloques (`hero`, `cta`, `pricing`, etc.) que se serialicen dentro de `layout`.
- Añadir endpoint público para renderizar la versión publicada (`Page::published()` + `publishedRevision`).
- Integrar bloque de productos destacados reutilizando `Product`.

Con esta base podemos iterar el constructor visual sin tocar repetidamente la capa de datos.

