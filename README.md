<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/1%20CMYK/1%20Full%20Color/laravel-logomark-cmyk-red.svg" width="120" alt="Laravel Logo">
</p>

<h1 align="center">Aula Virtual LTS - Hostinger 2025</h1>

LMS bilingue (ES/EN) con builder drag & drop, player inteligente, notificaciones multicanal y panel de provisionamiento de integraciones listo para Hostinger Cloud Startup.

---

## Stack principal

- Laravel 10 + PHP 8.2 + MySQL 8
- Livewire 3 + Alpine.js + Tailwind CSS 3
- Spatie Laravel Settings & Permissions
- Integraciones: Google OAuth/Sheets, Pusher, S3/R2, Vimeo, Cloudflare Stream, MailerLite, GA4, reCAPTCHA v3, Sentry, WhatsApp, Discord, Make
- CI con GitHub Actions (`.github/workflows/ci.yml`)

## Puesta en marcha local

```bash
cp .env.example .env
composer install
npm install
php artisan key:generate
php artisan migrate --seed
npm run dev   # o npm run build
php artisan serve
```

Credenciales seed:
- Admin: `admin@example.com / password`
- Profesor: `teacher@example.com / password`
- Alumno: `student@example.com / password`

## Provisionador de integraciones
- Ruta: `/es/provisioner`
- Permiso requerido: `manage-settings`
- Valida y sanitiza credenciales antes de escribir en `.env`
- Aplica cambios al vuelo (`config:clear`, `config:cache`, `IntegrationConfigurator::apply()`)
- Ejecutar `php artisan credentials:check` para ver estados y variables faltantes

## Funcionalidades ya implementadas

- **Builder drag & drop** (Livewire) para capítulos/lecciones + recursos (video, PDF, iframe, texto, quiz) con prerequisitos, badges, estimaciones de tiempo, CTA y microinteracciones UIX 2030.
- **Player inteligente** con reanudación exacta, bloqueo best-effort (YouTube) y modo estricto listo para Vimeo/Cloudflare.
- **Player inteligente** con reanudación exacta, agenda de liberación, prerequisitos y conmutador de modo estricto (Vimeo/Cloudflare) + CTA configurable.
- **Centro de mensajes** (admin/estudiante): bandeja Livewire, envío segmentado y plantillas de correo unificadas.
- **Simulador de pagos** (`/admin/payments/simulator`): asigna tiers, registra `payment_events`, dispara emails y despacha webhooks.
- **Telemetría de video**: API de progreso alimenta segmentos `video_heatmap_segments`, expuestos como heatmap de abandono en el dashboard de profesores.
- **Eventos automatizados**: desbloqueos de curso/módulo, ofertas y cambios de tier notifican por correo e ingresan al outbox (`integration_events`) para Make/Discord/Sheets/MailerLite. Panel `/admin/integrations/outbox` permite filtrar y reintentar.
- **Outbox avanzado**: filtros por destino/estado, vista de payload/errores y acción para ignorar eventos problemáticos sin borrar historial.
- **CLI + scheduler de integraciones**: `php artisan integration:retry failed --target=make` reencola eventos pendientes/fallidos; además, el scheduler (`daily@02:00`) lanza el comando automáticamente para vaciar el outbox.
- **Dashboards**:
  - Admin: usuarios, MRR 30d, horas vistas, estado de integraciones, horas por curso y “top momentos de abandono”.
  - Profesor: actividad de estudiantes (7d), completitud promedio y heatmap granular por lección.
  - Estudiante: progreso personal, minutos vistos, XP acumulado, racha gamificada y próximas lecciones.
- **Gamificación + celebraciones**: `LessonCompletionService` detecta finalización (>90%), otorga XP/streak, persiste `gamification_events` y emite `LessonCompleted`; el player lanza confetti (`canvas-confetti`) y toasts con los puntos obtenidos.
- **Branding Designer** (`/admin/branding`): panel Livewire para ajustar colores, tipografías, logos y modo oscuro, guardando en `BrandingSettings`.
- **i18n + SEO**: rutas duplicadas `/es` / `/en` con middleware `localized`, switcher en el layout, hreflang/canonical automáticos y `sitemap.xml` multiidioma.
- **Integraciones externas**: outbox `integration_events` + job `DispatchIntegrationEventJob` con reintentos/HMAC; webhooks Make (`/api/webhooks/make`), despachos a Discord, Google Sheets (service account) y MailerLite cuando hay credenciales.
- **Mensajería y notificaciones**: migraciones, eventos/listeners, campañas por email/push y centros de mensajes para Admin/Alumno.
- **Tiers / Suscripciones / Pagos simulados**: asociaciones curso-tier, simulador de pagos y listeners que actualizan el acceso.
- **Panel de seguridad**: headers CSP/HSTS configurables, middleware `SecurityHeaders`.
- **CI/CD**: workflows de build/test y deploy Hostinger (SFTP/SSH puerto 65002) con comandos post-deploy.

## Seguridad
- Middleware `SecurityHeaders` aplicado a todas las rutas web
- CSP, HSTS, frame/referrer/permissions policy configurables en `config/security.php`
- Variables `.env` relevantes:
  ```
  SECURITY_HEADERS_ENABLED=true
  SECURITY_CSP_ENABLED=true
  SECURITY_HSTS_ENABLED=true
  ```

## Tests y seeds

```bash
php artisan test
php artisan migrate:fresh --seed
```

84 pruebas (241 assertions) cubren autenticación, perfiles, builder/player, gamificación, provisionamiento, outbox de integraciones, webhooks y comandos personalizados.

## CI / Build

Workflow: `.github/workflows/ci.yml`

- Composer + npm install y `npm run build`
- Migraciones y `php artisan test`
- Artefactos listos para deploy (`public/build`, caches)

## Checklist despliegue Hostinger
1. Clonar repo o sincronizar archivos
2. `composer install --no-dev` y `npm ci && npm run build`
3. Configurar `.env` con credenciales reales
4. `php artisan key:generate`, `php artisan migrate --force`
5. `php artisan config:cache`, `php artisan route:cache`
6. Configurar cronjobs y colas en Hostinger
7. Smoke test en `/es` y `/en` (Builder, Player, Provisioner, Notificaciones)

## Documentacion pendiente
- Guia operativa con credenciales reales + pipeline CI/CD
- Checklist de smoke test post deploy

## Contribuir

1. Crear rama `feature/...`
2. Ejecutar `php artisan test`
3. Abrir PR con descripcion y checklist de QA

## Licencia

Software propietario para Aula Virtual LTS (2025). Contacto: `academy@letstalkspanish.io`.
