# Canal de Colaboración · Academia Virtual

[ACCESO Y CONFIGURACIÓN]
- **Repositorio local**: `D:\AULA VIRTUAL LTS\LTS Aula Virtual Hostinger Cursor\lms`. Usar `git status -sb` para validar cambios antes de cualquier commit.
- **Remotos Git**:
  - `origin` → `https://github.com/WillDuque01/AULA-VIRTUAL-LTS-HOSTINGER-2025.git` (rama `main`, fuente de verdad).
  - `source` → `https://github.com/WillDuque01/lms-espanol.git` (histórico, solo referencia).
- **SSH al VPS**: `ssh -i "C:\Users\Will Duque\.ssh\id_ed25519" root@72.61.71.183`. El código vive en `/var/www/app.letstalkspanish.io` (sin carpeta `.git`; cualquier actualización va vía `scp/rsync`). Tras subir código, ejecutar: `php artisan optimize:clear && php artisan config:cache`.
- **Stack operativo**: PHP 8.2, Laravel 10, Livewire 3, MySQL/MariaDB (hostinger), cola supervisada (`supervisorctl status lts-queue`), cron activo (`/var/log/cron-lts.log`). Frontend con Vite/Tailwind, assets compilados con `npm run build`.
- **Scripts clave** (ejecutar desde `/var/www/app.letstalkspanish.io`):
  - `php scripts/real_integrations_smoke.php` → verifica Pusher, Mixpanel, reCAPTCHA, Make, Discord, PayPal, Sentry.
  - `php scripts/backend_role_smoke.php` → login + dashboards Admin / Teacher Admin / Teacher / Student.
  - `php scripts/register_smoke.php` y `php scripts/student_flow.php` → QA centrado en estudiantes.
  - `php artisan sentry:test` y `php artisan queue:failed` para diagnósticos rápidos.
- **Documentación relacionada**: revisar `docs/access_points.md` (accesos detallados) y `docs/hostinger_deployment_lessons.md` (bitácora de despliegues). Cualquier hallazgo nuevo debe anotarse también en `docs/test_roadmap.md`.

[HISTORIAL DE GPT-5.1]
- **Módulos completos**: Setup wizard (responsive), Branding/Public landing (con `BrandingSettings`), Auth Fortify (login/register + Google OAuth + rate limiting), Page Builder y Page Blocks, Course Builder (Teacher Admin), Planner/Discord, Practice Packs + Checkout simulado, Student Dashboard + Player (telemetría estricta), Notifications/Mensajería, Integración con Provisioner y credenciales reales (Pusher, Mixpanel, Google, SMTP, Sentry).
- **Stack aplicado**: Laravel 10 + Livewire 3, Tailwind/Vite, Spatie Permission, Socialite (Google), Sentry SDK, Pusher/Mixpanel SDKs propios, PayPal sandbox, Discord webhooks, Make webhook, queue con Supervisor + Redis fallback (base MariaDB).
- **Pruebas ya ejecutadas**:
  - Automatizadas: `scripts/real_integrations_smoke.php`, `backend_role_smoke.php`, `register_smoke.php`, `student_flow.php`, `teacher_admin_builder_flow.php`, `admin_product_catalog_flow.php`, `admin_payments_flow.php`, `admin_notifications_flow.php`, `admin_certificates_notifications_flow.php`.
  - Manuales en VPS: login/registro multi-rol, planner, assignments, player strict mode, checkout packs, mensajes internos, celebraciones/certificados. Logs validados (`journalctl -u php8.2-fpm`, `supervisorctl status`, `curl -sI /es/login`).
- **Pendientes conocidos**: QA profundo del rol Student (botones/CTA/video), documentación de integraciones desactivadas (WhatsApp), refinamiento UI/UX (tipografía/chips/animaciones) y plan detallado de subdominios/guards.

[TURNO-COMPLETADO: PREPARACIÓN-OK]

