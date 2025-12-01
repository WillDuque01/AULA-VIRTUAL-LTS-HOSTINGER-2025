# Roadmap de Pruebas Funcionales (E2E + Seeds)

Este documento organiza las validaciones exigidas para alcanzar el 100 % de cobertura funcional en el VPS (`app.letstalkspanish.io`) antes de empaquetar. Se enlaza con el roadmap general del proyecto (bloques 1‑5) **y** con el “Mapa de funcionalidades por rol” (`docs/functional_capabilities.md`), donde se listan los 55 flujos oficiales (Admin 18 + Teacher Admin 11 + Teacher 9 + Student 12 + Público 5).  

> **Objetivo**: cada hallazgo detectado durante las pruebas debe resolverse inmediatamente en el código fuente o en la configuración del servidor; sólo después de dejar el caso en verde se continúa con el siguiente escenario.

---

## 1. Relación con el Roadmap general

| Bloque maestro | Objetivo de negocio | Conjunto de pruebas vinculadas |
| --- | --- | --- |
| **1. UIX Course Builder** | Editor de cursos, wizard de módulos, telemetría del player | Secciones §4.1, §4.4, §4.5 |
| **2. Planner Discord & Packs** | Gestión de prácticas, duplicados, paquetes pagos | §4.2 (profesor/teacher_admin) + §3 (roles teacher_admin/teacher) |
| **3. Player UIX 2030** | Dashboard estudiante, player, celebraciones, assignments | §4.3 + §4.5 + §3 (rol student) |
| **4. Documentación / Playbooks** | Procedimientos + memoria | Este documento + `hostinger_deployment_lessons.md` §21 |
| **5. CI/CD extendido** | Smoke, empaquetado Hostinger, scripts | §5 + §6 (cierres y empaquetado) |

---

## 2. Preparación y semillas

1. **Usuarios y roles**
   ```bash
   php /tmp/seed_users.php
   ```
   - `academy@letstalkspanish.io / AcademyVPS2025!` → `admin` + `teacher_admin`
   - `teacher.admin@letstalkspanish.io / TeacherAdmin2025!` → `teacher_admin`
   - `teacher@letstalkspanish.io / TeacherQA2025!` → `teacher`
   - `student@letstalkspanish.io / StudentQA2025!` → `student_paid`

2. **Planner / prácticas QA**
   ```bash
   mysql -u lts_admin -p'AcademyDB2025!' -D lts_academy < /tmp/insert_practice.sql
   mysql -u lts_admin -p'AcademyDB2025!' -D lts_academy < /tmp/update_practice.sql
   ```
   - Crea la práctica `QA Planner Test` (id=1) posicionada en la semana activa del planner.

3. **Assignments**
   ```bash
   mysql -u lts_admin -p'AcademyDB2025!' -D lts_academy < /tmp/seed_assignment.sql
   ```
   - Genera `assignment_id=3` con entrega `assignment_submissions.id=3`.

4. **Player event payload**
   - Archivo `player_event.json` con evento `play` (`context_tag=qa-smoke`).

5. **Limpieza previa a cada ronda**
   ```bash
   php artisan optimize:clear
   php artisan queue:restart
   supervisorctl restart lts-queue
   ```

6. **Herramientas de apoyo**
   - `login_cookies.txt`, `csrf_token.txt`, `xsrf_token.txt` se regeneran por rol.
   - Capturas y logs en `browser-logs/` para adjuntar en reportes.
7. **Credenciales reales e integraciones**
   ```bash
   php scripts/apply_provisioning_payload.php admin.qa@letstalkspanish.io /tmp/real_env_payload.json
   php scripts/real_integrations_smoke.php
   ```
   - El primer comando escribe las claves finales en `.env` (usa tu propio JSON y elimínalo al terminar).
   - El segundo comando valida Pusher, Mixpanel, reCAPTCHA, Make, Discord, PayPal y Sentry.
   - Copia `Credenciales/google.json` a `storage/app/keys/google.json` (permisos `deploy:www-data`, `chmod 640`) antes de ejecutar DataPorter.
   - Ejecuta `php artisan sentry:test` y el botón “Enviar correo de prueba” del Provisioner para confirmar SMTP real.

---

## 3. Matriz de roles y flujos mínimos

| Rol | Módulos obligatorios (IDs `functional_capabilities.md`) | Artefactos/Seeds | Resultado esperado |
| --- | --- | --- | --- |
| **Admin (academy@…)** | 1.1‑1.18 | `seed_users.php`, `seed_assignment.sql`, `insert_practice.sql` | Acceso total sin errores 403/500, ability para aprobar tareas y disparar eventos |
| **Teacher Admin (teacher.admin@…)** | 2.1‑2.11 | `insert_practice.sql` | Duplicadores + arrastre funcionando; sin bloqueo de permisos |
| **Teacher (teacher@…)** | 3.1‑3.9 | `seed_users.php` | Formularios de propuesta/feedback guardan y pasan por colas |
| **Student (student@…)** | 4.1‑4.12 | `seed_assignment.sql`, `insert_practice.sql`, `player_event.json` | Player emite eventos (`api/player/events` 200), checkout y scheduler notifican |
| **Público / Landing** | 5.1‑5.5 | Branding/logo actualizados | Hero e información pública alineada a la academia, registro funcional |

> **Nota**: cada login debe ejecutarse en sesión limpia (logout o navegador incógnito) para evitar arrastre de cookies entre roles.

---

## 4. Checklists funcionales por área

### 4.1 Builder / Course UIX (IDs 1.8, 1.12, 3.2, 4.2, 4.12)
1. `https://app.letstalkspanish.io/es/courses/1/builder` (admin).
2. Crear módulo de prueba:
   - Completar formulario “Proponer módulo”.
   - Verificar que la propuesta aparezca en la tabla.
3. Revisar telemetría:
   - Reproducir lección 1 en el Player (rol student) y mandar `player_event.json`.
   - Confirmar `{"ok":true}` y que el evento aparezca en logs (`storage/logs/laravel.log`).
4. Scripts de respaldo (ejecutar antes de la ronda manual para poblar evidencia):
   - `php scripts/admin_builder_flow.php`
   - `php scripts/teacher_admin_builder_flow.php`

### 4.2 Planner y practice packs (IDs 1.7, 1.8, 2.2‑2.4, 4.4)
1. `DiscordPracticePlanner` (teacher_admin / teacher):
   - Ver la práctica `QA Planner Test`.
   - Usar botones `+1 día/+1 semana` y comprobar en base de datos (`SELECT start_at FROM discord_practices WHERE id=<id nuevo>`).
   - Usar drag & drop (inspeccionar `start_at` modificado).
2. `PracticePackagesManager`:
   - Crear pack dummy, marcarlo `published`.
   - Validar que aparezca en `/es/shop/packs` (rol student).

### 4.3 Dashboard y gamificación (student) (IDs 4.1‑4.12)
1. Dashboard general (`/es/dashboard` con `student@…`):
   - Widgets de XP, tiempo de estudio, recordatorios.
   - “Guía contextual” abre/cierra sin error.
2. Assignments:
   - Ver la entrega `QA Smoke…`, descargar adjunto, reenviar.
   - Enviar nueva entrega y revisar que admin la vea en `/admin/assignments`.
3. Shop/Checkout:
   - Añadir pack al carrito, avanzar hasta `/es/shop/checkout` (usar Payment Simulator en modo sandbox).
4. Autenticación social:
   - Usar el botón “Continuar con Google” en `/es/login` y completar el flujo OAuth (se requiere `academy` autorizado en la consola de Google).

### 4.4 Admin modules (IDs 1.1‑1.6, 1.9‑1.18)
1. Provisioner (`/es/provisioner`):
   - Editar un bloque (ej. SMTP) y guardar; revisar `.env`.
2. DataPorter (`/es/admin/data-porter`):
   - Solicitar export y validar firma (`/es/admin/data-porter/export` con link firmado).
3. Payments Simulator:
   - Ejecutar flujo “Capturar pago” → revisar `jobs` y `queue:work`.
4. Scripts de QA:
   - `php scripts/admin_product_catalog_flow.php`
   - `php scripts/admin_payments_flow.php`
   - `php scripts/admin_notifications_flow.php`

### 4.5 Mensajería, notifications, integraciones (IDs 1.11, 2.5, 3.3, 4.7‑4.9, 5.5)
1. `AdminMessageCenter` y `StudentMessageCenter` (admin/teacher, student):
   - Enviar mensaje de prueba y confirmar recepción en ambos lados.
2. WhatsApp redirect (`/es/whatsapp/redirect`):
   - Debe abrir `wa.me` con número configurado.
3. Discord webhook (Make):
   - Usar `curl` con `WEBHOOKS_MAKE_SECRET` para simular evento; log en `storage/logs/make-webhook.log`.

---

## 5. Gestión de hallazgos y correcciones

1. **Detección**
   - Registrar captura (web snapshot o screenshot) + log del servidor.
   - Anotar rol y módulo afectado.
2. **Corrección inmediata**
   - Ajustar código/Blade/JS o configuración del servidor.
   - Ejecutar nuevamente la semilla o restablecer el estado necesario.
3. **Validación**
   - Repetir el flujo completo, adjuntar evidencia.
4. **Documentación**
   - Añadir al `hostinger_deployment_lessons.md` (nuevo §21) o a esta guía según corresponda.

---

## 6. Cierre y empaquetado

1. Confirmar que todos los roles pasaron las pruebas sin pendientes.
2. Ejecutar:
   ```bash
   php scripts/build_hostinger_package.php
   Get-FileHash dist/hostinger_*.zip
   ```
3. Adjuntar:
   - Matriz de pruebas (checklist completo).
   - Hashes de los ZIP.
   - Registro de seeds y versiones (`git rev-parse HEAD`).

Una vez este roadmap esté en verde, se procede al empaquetado y a la entrega del bundle multi-academia.


