# Bitácora de fallos y endurecimiento Hostinger

Este documento resume los errores encontrados durante los despliegues en Hostinger Cloud Startup y la acción correctiva aplicada en el código/base de empaquetado. Sirve como checklist previo a futuros releases.

## 1. Componentes Livewire fuera del namespace esperado
- **Síntoma**: `Unable to find component: [setup.setup-wizard]` al cargar `/setup`.
- **Causa raíz**: `SetupWizard` vivía en `App\Http\Livewire\Setup`, fuera del namespace que Livewire autodescubre (`App\Livewire`).
- **Fix**: mover el componente a `app/Livewire/Setup/SetupWizard.php` y actualizar las referencias/tests. Desde ahora cualquier componente que se use vía `<livewire:*>` debe residir directamente en `App\Livewire`.

## 2. Caches compiladas con proveedores de desarrollo
- **Síntoma**: `Class "Laravel\Pail\PailServiceProvider" not found` al ejecutar `composer install --no-dev` en Hostinger.
- **Causa**: `bootstrap/cache/*.php` estaba versionado y contenía providers de paquetes dev.
- **Fix**: eliminar `bootstrap/cache/services.php` y `packages.php`, añadir `.gitignore` dedicada y limpiar caches antes de empaquetar. El instalador ejecuta `optimize:clear` antes de cualquier cacheo.

## 3. Laravel Telescope intentando leer tablas inexistentes
- **Síntoma**: `SQLSTATE[42S02]: Table '...telescope_entries' doesn't exist` provocando HTTP 500.
- **Causa**: Telescope se registraba siempre; en producción la tabla no se había migrado.
- **Fix**: `config/telescope.php` ahora viene deshabilitado por defecto y `AppServiceProvider` sólo registra `TelescopeServiceProvider` cuando el entorno es `local` o `TELESCOPE_ENABLED=true`. En producción basta con dejar la variable en `false`.

## 4. `route:cache` incompatible con Fortify
- **Síntoma**: `Unable to prepare route [{locale}/register]... name [register] duplicado` rompiendo instalaciones automatizadas.
- **Causa**: Fortify define rutas con el mismo nombre en diferentes prefixes.
- **Fix**: el instalador ya no ejecuta `php artisan route:cache` (lo reemplazamos por `optimize:clear` + `config:cache`). La guía de despliegue documenta expresamente evitar `route:cache` hasta que eliminemos los duplicados.

## 5. Assets `public/build/` ausentes
- **Síntoma**: pantalla en blanco con HTTP 200; consola mostraba que no existían los bundles de Vite.
- **Causa**: el paquete que se subió a Hostinger no incluía `npm run build`.
- **Fix**: el script `build_hostinger_package.php` parte de `dist/validate_build` (que ya debe tener `npm run build` ejecutado). La guía recalca subir `public/build/` o usar el paquete validado.

## 6. `public/index.php` con rutas absolutas incorrectas
- **Síntoma**: `Failed opening required '/home/.../public/../home/.../vendor/autoload.php'`.
- **Fix**: restablecimos las rutas relativas estándar (`__DIR__.'/../vendor/autoload.php'`).

## 7. Versiones PHP dispares
- **Síntoma**: en SSH seguía usándose PHP 8.1 a pesar de configurar 8.2 en hPanel.
- **Fix**: documentamos el binario correcto (`/opt/alt/php82/usr/bin/php`) y lo usamos tanto en las instrucciones manuales como en los comandos del wizard.

## 8. Wizard empaquetado incompleto
- **Síntoma**: la extracción manual dejaba fuera la carpeta `installer/` o el ZIP del LMS, causando confusión.
- **Fix**: el script de empaquetado ahora genera `hostinger_bundle.zip` con dos elementos en la raíz: `installer/` y `hostinger_payload.zip`. Basta con subir/unzip y acceder a `installer/web/index.php`.

## 9. Layout guest incompatible con vistas `@extends`
- **Síntoma**: HTTP 500 en `/setup` con `Undefined variable $slot` al renderizar `layouts/guest`.
- **Causa**: el layout se usaba como componente (`<x-guest-layout>`), pero los blades del wizard lo extendían con `@extends('layouts.guest')`, por lo que `$slot` no existía.
- **Fix**: `resources/views/layouts/guest.blade.php` ahora soporta ambos flujos: si hay `@section('title')` y `@section('content')` los renderiza vía `@yield`, y si llega `$slot` desde un componente lo imprime sin romper compatibilidad.

## 10. Wizard sin responsive en móviles
- **Síntoma**: el asistente se “cortaba” en pantallas pequeñas y requería desplazar horizontalmente para ver los formularios.
- **Causa**: la grilla principal forzaba dos columnas (`md:grid-cols`) sin declarar el fallback `grid-cols-1`, por lo que el sidebar ocupaba un ancho fijo incluso en móviles.
- **Fix**: `resources/views/setup/index.blade.php` define clases personalizadas para el layout guest y `resources/views/livewire/setup/setup-wizard.blade.php` usa `grid grid-cols-1 gap-8 md:grid-cols-[260px,1fr]` más `overflow-x-hidden`, asegurando una sola columna en dispositivos pequeños.

Mantener esta lista actualizada evita reintroducir regresiones en futuros despliegues y sirve como referencia rápida cuando un síntoma reaparece.


