## Turno 15 · Fixes Finales (DT-01 / DT-02)

### 1. Escalabilidad · Índice `discord_practices.start_at` (DT-01)
- **Migración:** `2025_12_06_190000_add_index_to_discord_practices.php` agrega el índice `idx_start_at` sobre la columna `start_at`, reduciendo el `FULL SCAN` detectado por Opus en las consultas de prácticas futuras.
- **Ejecución:** `php artisan migrate` (salida registrada en consola) para asegurar que el índice quede aplicado en la base de datos.

### 2. Localización del Course Builder (DT-02)
- **Vista:** `resources/views/livewire/builder/course-builder.blade.php` → ~25 textos hardcodeados migrados a `__()`, incluyendo encabezados, métricas, atajos, filtros, botones de acción, estados vacíos y el panel de enfoque/avanzado.
- **Nuevas traducciones:** Archivos `resources/lang/en/builder.php` y `resources/lang/es/builder.php` con todas las claves `builder.*` utilizadas (encabezados, métricas, acciones, atajos, filtros, capítulos, lecciones, panel de enfoque y sección avanzada).
- **Ejemplos:**
  - `{{ __('builder.heading', ['slug' => $course->slug]) }}`
  - `{{ __('builder.metrics.locks_hint', ['hours' => number_format(...)]) }}`
  - `{{ __('builder.actions.add_chapter') }}` / `{{ __('builder.actions.remove') }}`
  - `{{ __('builder.focus.panel_label') }}` y pestañas `builder.focus.tabs.*`

### 3. Prueba de humo
- `php artisan test --filter=PracticePackageOrderServiceTest` (2 pruebas / 9 aserciones OK). Esto valida que las modificaciones de Blade y los nuevos archivos de idioma no rompen la compilación de vistas ni el flujo de pagos usado como check rápido.

### Resumen
- Índice de `start_at` creado y migrado.
- Course Builder ahora se apoya en ~25 claves L10N nuevas, eliminando los literales reportados en DT-02.
- Tests de humo exitosos.

[FIXES-GPT-APPLIED]

