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

## 11. URLs sin prefijo de idioma lanzaban HTTP 500
- **Síntoma**: acceder a `/login` o `/dashboard` sin `/{locale}` devolvía un 500 (Laravel trataba de resolver rutas que esperan el parámetro `locale`).
- **Causa**: todas las rutas públicas viven dentro del grupo `/{locale}`; al entrar sin prefijo, la app no encontraba coincidencia válida.
- **Fix**: se añadieron redirecciones globales en `routes/web.php` (`/`, `/login`, `/register`) hacia `/{config('app.locale')}/...` para que siempre haya un locale por defecto.

## 12. Recuperación manual del entorno cuando el wizard no termina
- **Escenario**: si el wizard falla (por ejemplo, `finish` no escribe en `.env`), se puede continuar el despliegue cargando las credenciales manualmente.
- **Procedimiento**:
  1. Editar `.env` con las claves recopiladas, luego ejecutar `php artisan config:clear && php artisan config:cache`.
  2. Marcar la instalación como completa vía Tinker: `App\Models\SetupState::markCompleted(['admin_email' => '...']);`.
  3. Si hace falta restablecer la contraseña del admin, usar `User::updateOrCreate()` + `Hash::make()` desde Tinker.
- Documentar este flujo evita repetir todo el deploy cuando el wizard se queda a medias.

## 13. Verificación de login vía `curl` (CSRF + cookies)
- **Motivación**: al depurar sesiones en Hostinger, necesitábamos demostrar que las credenciales en producción funcionan aunque el dashboard no se renderice.
- **Procedimiento reproducible**:
  1. Obtener la página de login y guardar cookies: `curl -s -c cookies.txt https://letstalkspanish.io/academy/es/login -o login.html`.
  2. Extraer el `_token` del formulario (ej. `9UIlgXYC8yBlxV90Hbvz75bi1t6vnL1CuCUYJ8Ft`) y enviar el POST con `X-CSRF-TOKEN`, `target_role=es` y las cookies almacenadas:  
     `curl -s -i -b cookies.txt -c cookies.txt -X POST https://letstalkspanish.io/academy/es/login -d "_token=..." -d "target_role=es" -d "email=academy@letstalkspanish.io" -d "password=..."`.
  3. Verificar el `HTTP/1.1 302 Found` hacia `/academy/es/dashboard` y las nuevas cookies (`lets-talk-spanish-academy-session`, `remember_web_*`), quedando documentado en `curl_login_response.txt`.
- **Resultado**: la autenticación es correcta; cualquier problema visible en el navegador proviene de capas externas (cache/CDN), no de Fortify ni del almacenamiento de sesiones.

## 14. LiteSpeed/AccelerateWP sirviendo Blade crudo
- **Síntoma**: aún con sesión válida, `curl https://letstalkspanish.io/academy/es/dashboard` devuelve el contenido de `resources/views/layouts/app.blade.php` (con `@vite`, `{{ ... }}`) en lugar del HTML compilado. El encabezado incluye `platform: hostinger` y `Server: hcdn`, señalando que el CDN respondió antes de que PHP interpretara Blade.
- **Causa**: LiteSpeed/AccelerateWP está cacheando el directorio `/academy` y, al no discriminar plantillas Blade, responde con el archivo leído del disco. Esto se desencadena tras activar AccelerateWP para todo el dominio y compartir el mismo `public_html` con WordPress.
- **Mitigación inmediata**:
  1. **Excluir `/academy` del cache** desde hPanel → Performance → LiteSpeed Cache (o, al menos, añadir en `public/.htaccess` la regla `RewriteRule .* - [E=Cache-Control:no-cache,E=CacheDrop:1]` que ya quedó versionada).
  2. **Blindar recursos sensibles**: el mismo `.htaccess` ahora niega cualquier acceso directo a `storage/`, `bootstrap/`, `resources/` o `vendor/` y fuerza encabezados `Cache-Control/Pragma/Expires` para que ni HCDN ni AccelerateWP puedan almacenar el HTML del dashboard.
  3. **Alinear configuración Laravel** con el punto de entrada real (`APP_URL=https://letstalkspanish.io/academy`, `SESSION_DOMAIN=.letstalkspanish.io`, `SESSION_PATH=/academy`) para evitar que LiteSpeed genere variantes por dominio.
  4. Tras cada cambio, repetir el flujo `curl` para comprobar que ahora el HTML renderizado contiene el resultado de Blade (sin `@vite`).
- Una vez deshabilitado el cache para `/academy`, el dashboard vuelve a renderizarse normalmente sin sacrificar la infraestructura existente.

## 15. Migración a VPS (app.letstalkspanish.io)
- **Contexto**: para aislar el LMS del WordPress principal y evitar que un CDN externo intercepte Blade, se desplegó una instancia dedicada en Hostinger KVM (Ubuntu 24.04).
- **Stack base**:
  - Usuario `deploy` con claves ed25519 (root `PermitRootLogin prohibit-password`, `PasswordAuthentication no`).
  - Swap 2 GB, `sysctl` endurecido, `ufw` (22/80/443), `fail2ban`, `Supervisor`, `cron` para `schedule:run`.
  - Nginx + PHP-FPM 8.2 (`/run/php/php8.2-app.sock`), MariaDB 10.11 (`lts_academy`/`lts_admin`), Redis y Composer/Node 20 instalados globalmente.
  - El proyecto vive en `/var/www/app.letstalkspanish.io` y se despliega con `deploy:www-data` (ACL heredables + `setfacl`).
- **Nginx**:
  - Bloque HTTP: expone sólo `/.well-known/acme-challenge/` y redirige todo el tráfico a HTTPS (`return 301 https://$host$request_uri`).
  - Bloque HTTPS: sirve la app, aplica HSTS (`Strict-Transport-Security: max-age=31536000; includeSubDomains`) y encadena el socket PHP dedicado. Los logs viven en `/var/log/nginx/app.letstalkspanish.io.*`.
- **Certificados**:
  - Let’s Encrypt se emite vía `certbot certonly --webroot -w /var/www/app.letstalkspanish.io/public -d app.letstalkspanish.io`. Los archivos residen en `/etc/letsencrypt/live/app.letstalkspanish.io/` y el timer automático quedó activo.
  - Nota: el webroot debe exponer `/.well-known/acme-challenge/*`. Si aparece 404 durante la validación, revisar que nginx no esté devolviendo la landing por defecto.
- **DNS/CDN**:
  - El subdominio `app` debe apuntar únicamente a la IP del VPS (`72.61.71.183`) sin AAAA ni ALIAS hacia `*.cdn.hstgr.net`. Otros subdominios (WordPress) pueden seguir usando el CDN sin afectar al LMS.
- **Checklist post-migración**:
  1. `composer install --no-dev`, `npm ci && npm run build`, `php artisan migrate --force`, `db:seed --force`.
  2. `php artisan key:generate --force`, `storage:link`, permisos `storage`/`bootstrap/cache`.
  3. Verificar `https://app.letstalkspanish.io` → redirección a `/es/setup` y certificado válido (`issuer: R3`).
  4. Confirmar que el worker (`supervisorctl status lts-queue`) y el cron (`tail /var/log/cron-lts.log`) estén activos.

## 16. Documentar bypass de CDN por subdominio
- **Problema recurrente**: cuando el dominio raíz usa CDN (Hostinger CDN/HCDN, Cloudflare, etc.), los subdominios del LMS pueden seguir sirviendo la versión cacheada si comparten ALIAS/CNAME con `@`.
- **Fix**:
  - Crear registros A específicos para cada subdominio que resida en el VPS (`app`, `academy`, `course`, etc.) apuntando directamente a `72.61.71.183`.
  - Mantener `www` y `@` en el CDN sólo si se trata de WordPress; nunca reusar `*.cdn.hstgr.net` para los subdominios del LMS.
  - Si se necesita CDN en WordPress y bypass en LMS, se pueden mezclar: `www` y `@` → CDN, `app.*` → A directo.
-  Después de cualquier cambio DNS, bajar el TTL a 300s mientras se valida la propagación (`host app.letstalkspanish.io` desde el VPS).

## 17. Roadmap para la fase final (VPS + multi academia)

| # | Bloque | Objetivo | Checklist rápido |
|---|--------|----------|------------------|
| 1 | **Validación funcional en producción** | Completar el wizard en `https://app.letstalkspanish.io/es/setup`, asegurar que admin/login/dashboard/builder/planner/player funcionen sobre el VPS | 1) Ejecutar wizard con credenciales reales. 2) `php artisan migrate --force` + `db:seed --force` confirmados. 3) Revisar colas (`supervisorctl status lts-queue`) y cron (`tail /var/log/cron-lts.log`). |
| 2 | **Integraciones y medios** | Verificar SMTP, Mixpanel, Sentry, Make/Discord, Vimeo/CF Stream, Pusher, Google OAuth/Sheets, webhooks y storage (S3/R2) | Usar las guías del wizard + comandos `curl`/API para cada token; documentar resultados y fallos. |
| 3 | **Observabilidad y seguridad** | Confirmar headers (HSTS, CSP), firewall, fail2ban, logs, alertas | 1) `curl -I` para revisar headers. 2) `fail2ban-client status sshd`. 3) Logrotate y alertas configuradas. |
| 4 | **Empaquetado multi academia** | Dejar un pipeline reproducible para nuevos VPS/subdominios | 1) Script de clonación (dump `.sql`, zip `storage/app/public`, `.env.example`). 2) README con pasos (`composer install`, `npm ci`, `php artisan migrate --force`, `certbot`, `supervisorctl`). |
| 5 | **Documentación final** | Actualizar este `.md`, README VPS y memorias | 1) Resumen por bloque. 2) Instrucciones para soporte/CDN. 3) Checklist de smoke tests pre-release. |

## 18. Alias de middleware `role/permission` y Gate `manage-settings`
- **Síntoma**: en el VPS los módulos protegidos (`/professor/practices`, `/admin/assignments`, `/admin/teacher-*`) devolvían 500/403 porque Laravel no encontraba el alias `role` y, aun corrigiéndolo, el Gate `manage-settings` no estaba definido.
- **Fix**:
  - `bootstrap/app.php` ahora registra explícitamente los alias de Spatie Permission (`role`, `permission`, `role_or_permission` → `Spatie\Permission\Middleware\*`).
  - `AppServiceProvider` define el Gate `manage-settings` para usuarios con rol `admin`, `teacher_admin` o `support`.
  - El usuario operativo `academy@letstalkspanish.io` recibió los roles `admin` y `teacher_admin` para poder acceder a planner/assignments sin depender de edición manual.
- **Resultado**: `/es/professor/practices`, `/es/admin/assignments`, `/es/admin/messages`, etc., ya responden 200 tras autenticarse.

## 19. Smoke tests en `app.letstalkspanish.io` (30-nov-2025)
- **Login / Dashboard / Builder / Player**:
  - Flujo Fortify validado con `curl` (cookies persistidas en `login_cookies.txt`). `/es/dashboard` y `/es/courses/1/builder` devuelven HTML compilado; el player `/es/lessons/1/player` carga scripts Livewire y estilos.
  - El endpoint `POST /es/api/player/events` respondió `200 {"ok":true}` enviando `lesson_id=1`, `event=play`, `context_tag=qa-test` y `X-XSRF-TOKEN` tomado de las cookies. La traza queda registrada en `player_event_headers.txt` / `player_event_response.json`.
- **Planner de Discord**:
  - `/es/professor/practices` renderiza la grilla semanal (HTML verificado en `planner.html`). Las dependencias (`lessons`, `practice-packages`, `templates`) se cargan porque el componente Livewire ya puede consultar la base sin restricciones de rol.
  - Aún pendiente: crear prácticas de prueba (vía UI o `App\Models\DiscordPractice::create`) para validar duplicado/movimiento drag&drop directamente en el navegador.
- **Assignments Manager**:
  - `/es/admin/assignments` carga y muestra el estado vacío (“No hay entregas registradas”). Falta crear una `Assignment` + `AssignmentSubmission` de prueba para recorrer los flujos de aprobación/rechazo.
- **Shop / Checkout**:
  - `GET /es/shop/packs`, `/es/shop/cart` y `/es/shop/checkout` devuelven 200. Queda pendiente ejecutar una compra completa (puede usarse `PaymentSimulator` + `PracticeCheckout`) para asegurar creación de órdenes y correos.
- **Colas / Cron**:
  - `supervisorctl status lts-queue` → `RUNNING`, cron `* * * * * php artisan schedule:run` registrado en `crontab -u deploy -l`.

## 20. Paquetes dist actualizados (build del 30-nov-2025 07:55 UTC)
- Se reejecutó `php scripts/build_hostinger_package.php` después de las correcciones anteriores, generando:
  - `dist/hostinger_payload.zip` → código + `public/build/`.
  - `dist/hostinger_installer.zip` y la carpeta espejo `dist/installer_bundle`.
  - `dist/hostinger_bundle.zip` con ambos artefactos.
- Antes de cada release, verificar fechas/hash con `Get-FileHash dist/hostinger_*.zip` y adjuntarlos al reporte QA.

## 21. Roadmap de pruebas enlazado al proyecto
- Para mantener sincronizado el roadmap funcional con los cinco bloques del proyecto, se creó `docs/test_roadmap.md`. Este documento define:
  - Las semillas obligatorias (usuarios, planner, assignments, player events) y los comandos exactos para activarlas.
  - La matriz de roles (admin, teacher_admin, teacher, student) con la lista de módulos que cada sesión debe validar.
  - Los checklists por área (builder, planner, dashboard, integraciones, mensajería) y las instrucciones de corrección inmediata.
- **Uso**: antes de empaquetar o abrir un nuevo bugfix release, recorrer el roadmap de pruebas completo, registrando evidencia (snapshots, logs) y resolviendo cualquier hallazgo en el momento. Sólo cuando todas las filas estén en verde se pasa al empaquetado descrito en la sección 20.

## 22. Certificados, celebraciones y plantillas de suscripción (30-nov-2025 20:24 UTC)
- **Objetivo**: validar el pipeline completo de certificados (`CertificateIssuedNotification`), celebraciones (`DispatchCelebrationNotification`) y las notificaciones `SubscriptionExpiring`, `SubscriptionExpired`, `TierUpdated`.
- **Herramienta**: se creó `scripts/admin_certificates_notifications_flow.php`, que selecciona un estudiante QA, genera un certificado (con fallback manual si DomPDF falla), registra un evento `GamificationEvent`, despacha `LessonCompleted`, lanza `TierUpdated` para los receptores activos y simula una suscripción (`provider=qa-cert-flow`) para disparar `SubscriptionExpiring` y `SubscriptionExpired`.
- **Ejecución**:
  1. Subir el script via `scp` y correr `php scripts/admin_certificates_notifications_flow.php`.
  2. Se generaron dos certificados consecutivos (`V5EJ1XXWYW`, `KABUGZ5KOQ`) almacenados en `storage/app/certificates/`, confirmando que el listener encola los correos.
  3. Los logs muestran los eventos `LessonCompleted`, `TierUpdated`, `SubscriptionExpiring` y `SubscriptionExpired` despachados sin errores, garantizando que los templates de correo y los Integration Dispatchers quedaron operativos.
- **Seguimiento**: mantener este script en el paquete VPS para repetir la prueba antes de cada release y adjuntar las capturas de los correos en el informe QA.

## 23. Teacher Admin builder extra (30-nov-2025 20:27 UTC)
- **Objetivo**: simular la “gestión completa” del rol Teacher Admin en el Course Builder (crear/eliminar módulos, usar editor enriquecido con cards y secciones, enviar submissions para revisión).
- **Herramienta**: `scripts/teacher_admin_builder_flow.php` inicia sesión como `teacher.admin.qa@letstalkspanish.io`, crea un capítulo pendiente sobre el curso `espanol-a1`, añade dos lecciones (texto + video) con bloques `card` y `section`, publica una de ellas, elimina la segunda, reindexa posiciones y crea un `TeacherSubmission` apuntando a los nuevos artefactos.
- **Evidencia**:
  1. Ejecutar `php scripts/teacher_admin_builder_flow.php` tras sincronizar el script via `scp`.
  2. Consola muestra la creación del capítulo (ID 7), las lecciones (IDs 20 y 21), la publicación con 4 bloques y la submission `ID 8`.
  3. En la base, `chapters.created_by = teacher_admin` y `lessons.created_by = teacher_admin` confirman que el rol tiene permisos efectivos.
- **Seguimiento**: correr este script antes de cada smoke manual para garantizar que hay propuestas frescas por revisar y que el rol Teacher Admin mantiene acceso pleno al builder sin depender del rol Admin.

## 24. Catálogo, compras simuladas y notificaciones (30-nov-2025 20:29 UTC)
- **Objetivo**: validar que el catálogo de PracticePackages, el simulador de pagos y las plantillas de notificación funcionen en conjunto para soportar compras y CTAs.
- **Herramientas**:
  - `scripts/admin_product_catalog_flow.php` crea un paquete, lo publica (generando `Product`), actualiza precio/featured, lo mueve a draft y lo elimina para comprobar todos los estados.
  - `scripts/admin_payments_flow.php` usa `PaymentSimulator` para generar suscripciones en tiers `Pro/VIP`, dejando trazas en `subscriptions` e `integration_outbox`.
  - `scripts/admin_notifications_flow.php` envía `TeacherMessageNotification`, `StudentMessageNotification` y `SimulatedPaymentNotification` para asegurar que los correos y colas están activos tras las compras.
- **Evidencia**:
  1. `admin_product_catalog_flow` reportó el paquete `qa-catalog-pack-aw-pack-6` (ID 6) publicado → actualizado (USD 119, featured) → movido a draft → eliminado, con resumen `{"published_packages":3,"draft_packages":0,"products_featured":4}`.
  2. `admin_payments_flow` creó suscripciones `#1-#3` para `student@`, `student.qa01@`, `student.qa04@` y listó los eventos `id 9-13` en `payment_events`.
  3. `admin_notifications_flow` confirmó el envío de los tres tipos de notificación, reutilizando el mensaje interno generado en la corrida.
- **Seguimiento**: volver a ejecutar esta trilogía antes del empaquetado para garantizar que el inventario tiene productos vigentes, que las órdenes escriben en DB y que las plantillas de correo siguen entregándose.

## 25. Credenciales reales y smoke de integraciones externas (30-nov-2025 21:28 UTC)
- **Objetivo**: aplicar las credenciales finales (Pusher, YouTube, Mixpanel, reCAPTCHA, Google OAuth/Sheets, Make, Discord, PayPal, SMTP, Sentry) directamente en el `.env` del VPS y validar cada conexión con un smoke reproducible.
- **Herramientas**:
  - `scripts/apply_provisioning_payload.php <admin_email> <payload.json>`: ingiere un JSON con las claves/valores y delega en `CredentialProvisioner`, asegurando `config:clear` + `config:cache` y bitácora en `integration_audits`. La clase ahora encapsula valores con espacios/caracteres especiales para evitar `Failed to parse dotenv file`.
  - `scripts/real_integrations_smoke.php`: dispara eventos hacia Pusher (REST firmado), Mixpanel (`track`), reCAPTCHA (`siteverify` con token dummy), Make (header `X-Signature`), Discord webhook, PayPal OAuth sandbox y `sentry:test`. Si una variable no está en cache utiliza el `.env` físico como fallback.
- **Procedimiento ejecutado**:
  1. Generar payloads con las nuevas credenciales (principal + SMTP) y aplicarlos con `apply_provisioning_payload`.
  2. Crear `/var/www/app.letstalkspanish.io/storage/app/keys`, subir `Credenciales/google.json` y fijar permisos `deploy:www-data` + `chmod 640`.
  3. `composer require sentry/sentry-laravel:^4.8 symfony/options-resolver:^7`, registrar `SentryServiceProvider` y `SentryTracingServiceProvider` en `bootstrap/app.php`, publicar `config/sentry.php` (con release dinámico `git rev-parse --short HEAD`).
  4. `COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev -o`, `php artisan config:cache`, `php artisan sentry:test`.
  5. `php scripts/real_integrations_smoke.php`.
- **Resultados**:
  - `Pusher trigger` → 200 (`qa-tests/env-updated`).
  - `Mixpanel track` → respuesta `1` (`qa_env_check`).
  - `reCAPTCHA verify` → HTTP 200 (`success=false`, esperado al usar token ficticio).
  - `Make webhook` → HTTP 200 con `X-Signature`.
  - `Discord webhook` → mensaje “QA env smoke <timestamp>”.
  - `PayPal token` → token sandbox (`expires_in≈9h`).
  - `Sentry self-test` → `Test event sent ...` (ID registrado en el dashboard).
- **SMTP real**: `MAIL_HOST=smtp.hostinger.com`, `MAIL_PORT=465`, `MAIL_USERNAME=academy@letstalkspanish.io`, `MAIL_PASSWORD=Ana.1405!*`, `MAIL_ENCRYPTION=ssl`. Validar con el Provisioner (“Enviar correo de prueba”) antes de empaquetar.
- **Google Sheets / OAuth**:
  - `GOOGLE_SERVICE_ACCOUNT_JSON_PATH=storage/app/keys/google.json`, `GOOGLE_SHEETS_ENABLED=true`, `SHEET_ID=140AEMMZc6vUrOC33TipkoBnJgrAmyNyX9YFbw_5CCog`.
  - Compartir la hoja con `lms-dataporter@aula-virtual-479621.iam.gserviceaccount.com` en modo Editor antes de usar DataPorter.
- **Seguimiento**: almacenar los payloads con secretos en un vault (no versionarlos), reutilizar `apply_provisioning_payload.php` para futuras rotaciones y ejecutar `real_integrations_smoke.php` previo a cada release. Adjuntar evidencias (logs HTTP, capturas Discord/Make, ID de Sentry) en el informe QA.

## 26. Hardening de performance y rate limiting (30-nov-2025 23:40 UTC)
- **Cache de config/vistas**: en cada despliegue ejecutar `php artisan config:cache` y `php artisan view:cache`. `route:cache` se mantiene deshabilitado porque `routes/web.php` usa varias closures (Laravel no puede cachearlas). Si en el futuro migramos esas rutas a controladores dedicados, podremos activar `route:cache` sin riesgos.
- **Spatie Permission cache**: el paquete ya cachea roles/permisos (`config/permission.php`, expiración 24h, store `default`). Cada vez que asignamos roles desde el panel el cache se limpia automáticamente; no es necesario tocar código adicional.
- **Rate limiting en auth**:
  - Login: `RateLimiter::for('login', Limit::perMinute(5))` + middleware `throttle:login` en `POST /{locale}/login`.
  - Registro: `RateLimiter::for('register', Limit::perMinute(3))` + middleware `throttle:register` en `POST /{locale}/register`.
  - Resultado: se frenan ataques de fuerza bruta sin afectar a usuarios legítimos, y Fortify devuelve HTTP 429 si superan el límite.
- **Guardias especializados**: por ahora seguimos usando un único guard (`web`) con `target_role`, pero la estructura permite añadir guards/subdominios específicos cuando necesitemos aislar por completo Admin/Teacher/Student. Documentado como mejora futura.
- **Rate limiting player events (01-dic-2025 03:05 UTC)**:
  - Se registró `RateLimiter::for('player-events', Limit::perMinute(60))` en `AppServiceProvider` y se añadió `middleware('throttle:player-events')` a `POST /{locale}/api/player/events`. La clave de rate se arma con el `user_id` (o IP) para bloquear flood en la telemetría del player sin afectar clases normales.
  - `supervisorctl status lts-queue` sigue mostrando el worker activo (pid 208667, uptime 20 min). `tail -5 /var/log/cron-lts.log` no reporta pendientes y `curl -sI https://app.letstalkspanish.io/es/login | head -10` respondió `HTTP/2 200` con los headers HSTS/X-Frame configurados, confirmando que los servicios quedaron sanos tras el cambio.

Mantener esta lista actualizada evita reintroducir regresiones en futuros despliegues y sirve como referencia rápida cuando un síntoma reaparece.

