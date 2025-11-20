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

## 3. Cohortes monetizables

- Migraciones:
  - `2025_11_22_120000_add_commerce_fields_to_cohort_templates` añade `price_amount`, `price_currency`, `status`, `is_featured` y la tabla `cohort_registrations`.
  - `2025_11_20_202517_add_enrolled_count_to_cohort_templates` persiste `enrolled_count` (cacheado) para exponer inventario restante.
- `CohortTemplateObserver` mantiene sincronizado el `Product`:
  - `type = cohort`, slug `cohort-{slug}`.
  - `category` se alimenta con `cohort_label`.
  - `inventory` refleja `remainingSlots()` (capacidad – inscritos pagados).
  - `meta` incluye `duration_minutes`, `capacity`, `enrolled_count`, `available_slots` y los `slots`.
- Servicio `CohortEnrollmentService`:
  - Enroll hace `lockForUpdate`, recalcula métricas (`refreshEnrollmentMetrics`) y lanza `CohortSoldOutException` si ya no quedan cupos.
  - Reutiliza la misma inscripción si el estudiante ya estaba marcado como `paid/confirmed`.
- `CohortRegistrationObserver` sincroniza inventario cada vez que un registro se crea/actualiza/elimina.
- `PracticeCheckout` reconoce ambos tipos (`PracticePackage`, `CohortTemplate`) y captura la excepción de cupos agotados para devolver al usuario al carrito con un mensaje claro.

## 4. Carrito/Checkout unificados

- Helper `App\Support\Practice\PracticeCart` guarda IDs de `Product` en sesión (`commerce_cart`) y evita duplicados.
- Métodos públicos:
  - `ids()`, `products()`, `addProduct($id)`, `remove($id)`, `clear()`, `count()`, `subtotal()`.
- Validaciones:
  - `ProductGallery` y `PracticePackagesCatalog` bloquean el agregado al carrito si `inventory <= 0`.
  - El checkout captura `CohortSoldOutException` para mostrar el mensaje “Ya no hay cupos disponibles”.
- `PracticeCartPage` y `PracticeCheckout` muestran productos (packs y cohortes). Cada item despacha su propio servicio (`PracticePackageOrderService`, `CohortEnrollmentService`) y comparten registro de conversiones (`PageConversion`).

## 5. Catálogo público (`/es/catalogo`)

- Livewire `App\Livewire\Shop\ProductGallery`:
  - Filtros: categoría, tipo (`practice_pack`, `cohort`, etc.) y solo destacados.
  - Tarjetas de cohortes muestran duración, cupos, inscritos y el estado “Agotado” con CTA deshabilitado cuando `inventory = 0`.
  - Botón “Añadir” guarda en el carrito (no requiere login; el checkout sí).
  - Usa los tokens UI definidos en `docs/ui-playbook.md`.
- Ruta: `/{locale}/catalogo` (sin middleware `auth`).
- Los bloques del Page Builder (`page/blocks/featured-products.blade.php`) consumen la misma data y marcan los productos agotados antes de redirigir al catálogo.

## 6. Panel Teacher Admin/Admin (`/es/admin/products`)

- Livewire `App\Livewire\Admin\ProductCatalog`.
- Funcionalidades:
  - Lista paginada con búsqueda, filtros por estado/tipo y toggle “Solo destacados”.
  - Botón “Destacar” → `toggleFeatured`.
  - Editor rápido (modal) para título, resumen, precio, moneda, estado, categoría.
  - Enlaces de origen cuando el product proviene de un Practice Pack.
- Requiere rol `Admin` o `teacher_admin`.

## 7. Tests y QA

- `tests/Feature/Product/ProductSyncTest.php`: valida creación automática del product.
- `tests/Feature/Admin/ProductCatalogTest.php`: edición y toggle destacados.
- `tests/Feature/Shop/ProductGalleryTest.php`: render Livewire y almacenamiento en carrito como invitado.
- `tests/Feature/Shop/PracticeCheckoutTest.php`: checkout usando IDs de producto (packs y cohortes).
- `tests/Feature/Catalog/CohortProductTest.php`: sincronización template → product + registro pagado.
- `tests/Feature/Catalog/CohortSoldOutTest.php` (nuevo): asegura que el checkout lance `CohortSoldOutException` al intentar comprar una cohorte sin cupos.

## 8. Próximos pasos (fases posteriores)

- Publicar cohortes directamente desde el planner (elige plantilla + abre el checkout con el `Product` correspondiente).
- Widgets dinámicos en el futuro constructor de páginas (Hero, Pricing, Bloque destacado).
- Widgets dinámicos en el futuro constructor de páginas (Hero, Pricing, Bloque destacado).
- Telemetría (`product_purchase`, `landing_performance`) integrada en DataPorter.

Con esta base, cualquier producto de pago comparte el mismo pipeline (catálogo → carrito → checkout → DataPorter), lo que simplifica la futura integración con el constructor de Home/Landings y con nuevos tipos de oferta.

