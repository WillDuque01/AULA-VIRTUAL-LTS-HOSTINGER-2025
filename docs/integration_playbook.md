# Integration Playbook — Credenciales y Validaciones

Este documento concentra la investigación actualizada de cada servicio externo requerido por el LMS, junto con pruebas rápidas y notas de diagnóstico. Úsalo en conjunto con el asistente (`/setup`) y el nuevo panel de “Playbook de integraciones” disponible para Admin y Teacher Admin.

---

## 1. Video & Streaming

| Servicio | Qué aporta | Tokens clave | Validación rápida | Recursos |
|----------|------------|--------------|-------------------|----------|
| **YouTube nocookie** | Fallback gratuito para todas las lecciones de video. | `YOUTUBE_ORIGIN` con el dominio HTTPS final. | Abre una lección y confirma en DevTools que el iframe cargue desde `https://www.youtube-nocookie.com`. Si aparece “Playback ID” el dominio no está autorizado. | [Docs](https://developers.google.com/youtube/v3/docs) |
| **Vimeo** | Modo “privado” con control de dominios y estadísticas. | `VIMEO_TOKEN` (scopes `video_files` + `private`). | `curl -H "Authorization: bearer <TOKEN>" https://api.vimeo.com/me` debe devolver 200. | [Guía Vimeo](https://developer.vimeo.com/api/guides/start) |
| **Cloudflare Stream** | Streaming HLS con token rotatorio y analytics. | `CLOUDFLARE_ACCOUNT_ID`, `CLOUDFLARE_STREAM_TOKEN`. | `curl -H "Authorization: Bearer <TOKEN>" https://api.cloudflare.com/client/v4/accounts/<ACCOUNT_ID>/stream/live_inputs`. | [Docs Stream](https://developers.cloudflare.com/stream) |

**Tips comunes**
- Verifica que el dominio público esté en las listas de “Allowed Origins” (YouTube, Vimeo y CF).
- Para evitar “mixed content” fuerza siempre HTTPS en embeds.

---

## 2. Storage & Realtime

| Servicio | Tokens | Validación | Troubleshooting |
|----------|--------|------------|-----------------|
| **S3 / R2 / Wasabi** | `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`, `AWS_ENDPOINT`. | `aws s3 ls s3://<bucket> --endpoint-url=<endpoint>` | Error `SignatureDoesNotMatch` = región o modo path style incorrecto. |
| **Pusher / Ably** | `PUSHER_APP_ID`, `PUSHER_APP_KEY`, `PUSHER_APP_SECRET`, `PUSHER_APP_CLUSTER`. | Abre el builder y revisa que el websocket `pusher` conecte vía WSS. | Si ves 401, cluster incorrecto o APP_KEY distinto al front. |
| **Modos locales** | `FORCE_FREE_STORAGE`, `FORCE_FREE_REALTIME`, `FORCE_YOUTUBE_ONLY`. | — | Usa estos flags solo en local; en producción deben estar desactivados. |

---

## 3. Correo y Notificaciones

| Servicio | Tokens | Validación | Recursos |
|----------|--------|------------|----------|
| **SMTP Hostinger/otro** | `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_*`. | Desde Admin › Integraciones usa el botón “Enviar correo de prueba” y verifica la bandeja del admin. | [Hostinger SMTP](https://support.hostinger.com/en/articles/1583247-how-to-set-up-email-on-laravel) |
| **WhatsApp deeplink** | `WHATSAPP_DEEPLINK` | Haz clic en cualquier CTA de WhatsApp dentro del dashboard. | [FAQ Wa.me](https://faq.whatsapp.com/591746941422197) |

> Para la API oficial de Meta añade también `WHATSAPP_TOKEN` y `WHATSAPP_PHONE_ID` en `config/services.php`.

---

## 4. Marketing & Telemetría

| Servicio | Tokens | Validación | Observaciones |
|----------|--------|------------|---------------|
| **Google Analytics 4** | `GA4_MEASUREMENT_ID`, `GA4_API_SECRET`, `GA4_ENABLED=true`. | `php artisan telemetry:sync --limit=1` y revisa GA4 › DebugView. | Asegura que la zona horaria del data stream coincida con la del LMS. |
| **Mixpanel** | `MIXPANEL_PROJECT_TOKEN`, `MIXPANEL_API_SECRET`, `MIXPANEL_ENABLED=true`. | `php artisan telemetry:sync --driver=mixpanel`. | Ajusta el endpoint a `https://api-eu.mixpanel.com` si tu proyecto es EU. |
| **reCAPTCHA v3** | `RECAPTCHA_SITE_KEY`, `RECAPTCHA_SECRET_KEY`. | `curl "https://www.google.com/recaptcha/api/siteverify" -d "secret=<SECRET>&response=test"`. | Ajusta el threshold (config/security.php) si recibes falsos positivos. |
| **Sentry** | `SENTRY_LARAVEL_DSN`. | `php artisan sentry:test`. | Configura `environment` en `.env` para diferenciar staging/production. |

---

## 5. Automatización & Bots

| Servicio | Tokens | Validación | Notas |
|----------|--------|------------|-------|
| **Google OAuth** | `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`. | Ejecuta “Continuar con Google” en `/login`. | Verifica el dominio en el panel de consentimiento para evitar la pantalla amarilla. |
| **Google Sheets** | `GOOGLE_SERVICE_ACCOUNT_JSON_PATH`, `SHEET_ID`, `GOOGLE_SHEETS_ENABLED`. | Desde Admin › DataPorter ejecuta un export/import hacia Google Sheets y revisa que aparezca en tu documento. | Comparte la hoja con el correo del service account en modo Editor. |
| **Make.com** | `MAKE_WEBHOOK_URL`, `WEBHOOKS_MAKE_SECRET`. | `curl -X POST "<URL>" -H "X-Signature: <hash>" -d '{"ping":true}'`. | Usa un secret generado con `php artisan key:generate --show` y configúralo también en el escenario. |
| **Discord webhook** | `DISCORD_WEBHOOK_URL`, `DISCORD_WEBHOOK_USERNAME`, `DISCORD_WEBHOOK_THREAD_ID`. | `curl -H "Content-Type: application/json" -d '{"content":"Ping"}' <WEBHOOK>`. | Si usas Threads activa el modo desarrollador para copiar los IDs. |
| **WhatsApp deeplink** | `WHATSAPP_DEEPLINK`. | Clic en CTA (player/dashboard) → debe abrir la app. | Codifica el texto (`%20` para espacios). |

---

## 6. Observabilidad

- **Sentry** es el único servicio obligatorio en esta categoría. Tras definir el DSN ejecuta `php artisan sentry:test` y valida que el issue se registre.
- Complementa con los logs propios del LMS (`storage/logs/laravel.log`) y con la tabla `integration_events` para detectar fallos en las colas.

---

### Buenas prácticas generales
1. **Versiona solo ejemplos**: ninguna credencial real debe llegar al repo. Usa `.env.example` para documentar nuevas claves.
2. **Automatiza smoke tests**: el workflow `deploy.yml` ya invoca `/es` y `/es/dashboard`. Añade aquí cualquier endpoint crítico de integraciones externas.
3. **Checklist antes de cada release**:
   - Revisa el nuevo panel “Playbook de integraciones” (Admin) y confirma que todos los bloques estén en verde.
   - Ejecuta `php artisan telemetry:sync --limit=10` para vaciar colas y confirmar comunicación con GA4/Mixpanel.
   - Desde Admin › DataPorter revisa que no haya eventos “pending” antes del deploy.

> Mantén este documento sincronizado con `config/integration_guides.php` para que el asistente y los dashboards muestren exactamente la misma información.

