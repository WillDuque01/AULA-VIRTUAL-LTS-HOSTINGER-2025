# Operativa del catálogo y productos

Este documento explica la nueva capa de productos unificados que alimenta el carrito/checkout, el panel de Teacher Admin/Admin y el catálogo público.

---

## 1. Modelo `Product`

- Tabla `products` (ver migración `2025_11_20_170000_create_products_table.php`).
- Campos clave: `type`, `title`, `slug`, `category`, `price_amount/currency`, `compare_at_amount`, `status`, `is_featured`, `thumbnail_path`, `productable_type/id`, `meta`.
- Scopes:
  - `Product::published()`
  - `Product::featured()`
- Cada `PracticePackage` crea/actualiza su `Product` mediante `PracticePackageObserver`.
  - Slug: `Str::slug(title).'-pack-{id}'`.
  - `meta.sessions_count`, `meta.practice_package_id`.

## 2. Sincronización con Practice Packs

- Observer `PracticePackageObserver`:
  - `created/updated` → `product()->updateOrCreate([...])`.
  - `deleted` → elimina el product asociado.
- Campos heredados:
  - `status`: `published` si el pack está publicado, caso contrario `draft`.
  - `category`: `global` (packs abiertos) o `cohort`.
  - `price_amount/currency` y `compare_at_amount` (si viene en meta).
- `PracticePackage` expone `product()` (`MorphOne`) para consultas directas.

## 3. Carrito/Checkout unificados

- Helper `App\Support\Practice\PracticeCart` ahora guarda IDs de `Product` en sesión (`commerce_cart`).
- Métodos públicos:
  - `ids()`, `products()`, `addProduct($id)`, `remove($id)`, `clear()`, `count()`, `subtotal()`.
- `PracticeCartPage` y `PracticeCheckout` muestran productos (no solo packs). Para ahora, el checkout procesa `PracticePackage`; otros tipos quedarán para fases futuras.

## 4. Catálogo público (`/es/catalogo`)

- Livewire `App\Livewire\Shop\ProductGallery`:
  - Filtros: categoría, solo destacados.
  - Botón “Añadir” guarda en el carrito (no requiere login; el checkout sí).
  - Usa los tokens UI definidos en `docs/ui-playbook.md`.
- Ruta: `/{locale}/catalogo` (sin middleware `auth`).

## 5. Panel Teacher Admin/Admin (`/es/admin/products`)

- Livewire `App\Livewire\Admin\ProductCatalog`.
- Funcionalidades:
  - Lista paginada con búsqueda, filtros por estado/tipo y toggle “Solo destacados”.
  - Botón “Destacar” → `toggleFeatured`.
  - Editor rápido (modal) para título, resumen, precio, moneda, estado, categoría.
  - Enlaces de origen cuando el product proviene de un Practice Pack.
- Requiere rol `Admin` o `teacher_admin`.

## 6. Tests y QA

- `tests/Feature/Product/ProductSyncTest.php`: valida creación automática del product.
- `tests/Feature/Admin/ProductCatalogTest.php`: edición y toggle destacados.
- `tests/Feature/Shop/ProductGalleryTest.php`: render Livewire y almacenamiento en carrito como invitado.
- `tests/Feature/Shop/PracticeCheckoutTest.php`: checkout usando IDs de producto.

## 7. Próximos pasos (fases posteriores)

- Asociar cohortes y servicios al mismo `Product`.
- Widgets dinámicos en el futuro constructor de páginas (Hero, Pricing, Bloque destacado).
- Telemetría (`product_purchase`, `landing_performance`) integrada en DataPorter.

Con esta base, cualquier producto de pago comparte el mismo pipeline (catálogo → carrito → checkout → DataPorter), lo que simplifica la futura integración con el constructor de Home/Landings y con nuevos tipos de oferta.

