# 09_OPUS_BACKEND_AUDIT_ROADMAP.md

## Roadmap de Certificación Backend — Auditoría Exhaustiva

**Agente**: Opus 4.5 (Arquitecto de Pruebas Backend)  
**Fecha**: 06-dic-2025 18:10 UTC  
**Alcance**: Todos los componentes no visuales del sistema  
**Referencia**: 55 flujos documentados en `functional_capabilities.md`

---

## 1. RESUMEN EJECUTIVO

Este roadmap define el plan de certificación para validar que **toda la lógica de negocio y servicios externos** funcionan correctamente bajo todos los escenarios de Rol/Permiso.

| Área | Componentes | Método de Prueba | Prioridad |
|------|-------------|------------------|-----------|
| Autenticación y Roles | Fortify, Spatie, Gates | SSH + DB + API | 🔴 CRÍTICA |
| Servicios Externos | Discord, Pagos, Make, SMTP | SSH + Webhooks + Logs | 🔴 CRÍTICA |
| Lógica de Contenido | Cursos, Lecciones, Progress | DB + API | 🟡 ALTA |
| Mensajería y Notificaciones | Email, WhatsApp, Push | SSH + Logs | 🟡 ALTA |
| Telemetría y Analytics | GA4, Mixpanel, Sentry | SSH + Queues | 🟢 MEDIA |

---

## 2. ÁREA 1: AUTENTICACIÓN Y ROLES

### 2.1 Matriz de Roles y Permisos

| Rol | Permisos Clave | URLs Críticas | Seeds Requeridas |
|-----|----------------|---------------|------------------|
| **Admin** | `manage-settings`, todos los recursos | `/admin/*`, `/provisioner`, `/data-porter` | `seed_users.php` |
| **Teacher Admin** | Planner, Packs, Cohorts (lectura datos) | `/professor/*`, `/admin/products` (limitado) | `seed_users.php` |
| **Teacher** | Dashboard docente, propuestas | `/teacher/*`, mensajería | `seed_users.php` |
| **Student** | Player, Checkout, Prácticas | `/student/*`, `/shop/*`, `/lessons/*/player` | `seed_users.php` |
| **Público** | Landing, Registro | `/`, `/register`, `/catalogo` | N/A |

### 2.2 Pruebas de Permisos por Rol

```bash
# MÉTODO: SSH + curl con cookies de sesión

# 1. Login cada rol y guardar cookies
curl -c cookies_admin.txt -b cookies_admin.txt \
  -X POST https://app.letstalkspanish.io/es/login \
  -d "email=academy@letstalkspanish.io&password=AcademyVPS2025!&_token=<CSRF>"

# 2. Probar acceso a URLs restringidas
curl -b cookies_student.txt https://app.letstalkspanish.io/es/admin/dashboard
# Esperado: 403 Forbidden o redirect a /student/dashboard

# 3. Verificar Gates en base de datos
SELECT u.email, r.name as role, p.name as permission
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
LEFT JOIN role_has_permissions rhp ON r.id = rhp.role_id
LEFT JOIN permissions p ON rhp.permission_id = p.id
WHERE u.email LIKE '%letstalkspanish.io';
```

### 2.3 Casos de Prueba Específicos

| ID | Caso | Rol | Acción | Resultado Esperado |
|----|------|-----|--------|-------------------|
| AUTH-01 | Login Admin | Admin | POST `/es/admin/login` | Redirect a `/admin/dashboard` |
| AUTH-02 | Login Student | Student | POST `/es/student/login` | Redirect a `/student/dashboard` |
| AUTH-03 | Acceso cruzado | Student → Admin URL | GET `/es/admin/dashboard` | 403 o redirect |
| AUTH-04 | Gate `manage-settings` | Admin | GET `/es/provisioner` | 200 OK |
| AUTH-05 | Gate `manage-settings` | Teacher | GET `/es/provisioner` | 403 Forbidden |
| AUTH-06 | OAuth Google | Público | GET `/es/login/google` | Redirect a Google |
| AUTH-07 | Registro público | Público | POST `/es/register` | Crear usuario rol `student` |
| AUTH-08 | Password reset | Público | POST `/es/forgot-password` | Email enviado |

### 2.4 Integridad de Semillas (DB)

```sql
-- Verificar usuarios de prueba existen
SELECT id, name, email, created_at FROM users 
WHERE email IN (
  'academy@letstalkspanish.io',
  'teacher.admin@letstalkspanish.io',
  'teacher@letstalkspanish.io',
  'student@letstalkspanish.io'
);

-- Verificar roles asignados
SELECT u.email, GROUP_CONCAT(r.name) as roles
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
GROUP BY u.id;

-- Verificar permisos del rol admin
SELECT p.name FROM permissions p
JOIN role_has_permissions rhp ON p.id = rhp.permission_id
JOIN roles r ON rhp.role_id = r.id
WHERE r.name = 'admin';
```

---

## 3. ÁREA 2: SERVICIOS EXTERNOS (CRÍTICO)

### 3.1 Discord Integration

| Componente | Token/Config | Método de Prueba |
|------------|--------------|------------------|
| Webhook | `DISCORD_WEBHOOK_URL` | curl POST con payload |
| Prácticas | `discord_channel_url` en BD | Verificar URLs válidas |
| Eventos | `DiscordPracticeScheduled` | Logs + Outbox |

**Pruebas:**

```bash
# 1. Verificar webhook configurado
grep DISCORD_WEBHOOK_URL /var/www/app.letstalkspanish.io/.env

# 2. Enviar ping de prueba
curl -H "Content-Type: application/json" \
  -d '{"content":"[QA] Ping desde auditoría backend"}' \
  "$DISCORD_WEBHOOK_URL"

# 3. Verificar eventos en outbox
SELECT * FROM integration_events 
WHERE event_type LIKE 'discord%' 
ORDER BY created_at DESC LIMIT 5;

# 4. Verificar prácticas con Discord URL
SELECT id, title, discord_channel_url, start_at 
FROM discord_practices 
WHERE discord_channel_url IS NOT NULL 
LIMIT 5;
```

### 3.2 Integración de Pagos

| Componente | Servicio | Método de Prueba |
|------------|----------|------------------|
| Checkout | `PracticeCheckout` | Flujo completo con Payment Simulator |
| Orders | `PracticePackageOrderService` | DB + Logs |
| Cohorts | `CohortEnrollmentService` | DB + Exception handling |
| Webhooks | PayPal/Stripe (simulado) | POST a endpoints |

**Pruebas:**

```bash
# 1. Verificar productos en catálogo
SELECT p.id, p.title, p.type, p.price_amount, p.status, p.inventory
FROM products p WHERE p.status = 'published';

# 2. Verificar órdenes de pago
SELECT o.id, o.user_id, o.status, o.total_amount, o.created_at
FROM practice_package_orders o
ORDER BY created_at DESC LIMIT 10;

# 3. Verificar inscripciones a cohortes
SELECT cr.id, cr.user_id, cr.cohort_template_id, cr.status, cr.paid_at
FROM cohort_registrations cr
ORDER BY created_at DESC LIMIT 10;

# 4. Probar flujo de checkout (vía curl)
# a) Añadir producto al carrito (sesión)
# b) POST a /es/shop/checkout
# c) Verificar orden creada en DB

# 5. Probar excepción CohortSoldOut
# a) Llenar cohorte hasta capacity
# b) Intentar nueva inscripción
# c) Verificar mensaje "Ya no hay cupos disponibles"
```

### 3.3 Make.com Webhooks

```bash
# 1. Verificar configuración
grep MAKE_WEBHOOK_URL /var/www/app.letstalkspanish.io/.env
grep WEBHOOKS_MAKE_SECRET /var/www/app.letstalkspanish.io/.env

# 2. Simular webhook entrante
curl -X POST "$MAKE_WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -H "X-Signature: <HMAC_SHA256>" \
  -d '{"event":"test","timestamp":"2025-12-06T18:00:00Z"}'

# 3. Verificar logs
tail -20 /var/www/app.letstalkspanish.io/storage/logs/make-webhook.log
```

### 3.4 SMTP / Email

```bash
# 1. Verificar configuración
grep -E "MAIL_HOST|MAIL_PORT|MAIL_USERNAME" /var/www/app.letstalkspanish.io/.env

# 2. Enviar email de prueba (desde Provisioner o artisan)
php artisan tinker --execute="Mail::raw('Test QA', fn(\$m) => \$m->to('test@example.com')->subject('QA Test'));"

# 3. Verificar logs de email
grep -i "mail\|smtp" /var/www/app.letstalkspanish.io/storage/logs/laravel.log | tail -20
```

### 3.5 Casos de Prueba Servicios Externos

| ID | Servicio | Caso | Método | Resultado Esperado |
|----|----------|------|--------|-------------------|
| EXT-01 | Discord | Webhook ping | curl POST | Mensaje en canal |
| EXT-02 | Discord | Práctica programada | Crear práctica | Evento en outbox |
| EXT-03 | Pagos | Checkout pack | POST checkout | Orden status=paid |
| EXT-04 | Pagos | Cohorte llena | Intentar compra | CohortSoldOutException |
| EXT-05 | Pagos | Webhook retry | Simular fallo | Retry en 3 intentos |
| EXT-06 | Make | Webhook entrante | POST con firma | Log en make-webhook.log |
| EXT-07 | SMTP | Email transaccional | Artisan mail | Entrega confirmada |
| EXT-08 | WhatsApp | Deeplink | Click CTA | Abre wa.me correctamente |

---

## 4. ÁREA 3: LÓGICA DE CONTENIDO

### 4.1 Cursos y Lecciones

```sql
-- Verificar estructura de contenido
SELECT c.id, c.title, c.status, COUNT(ch.id) as chapters, 
       SUM((SELECT COUNT(*) FROM lessons WHERE chapter_id = ch.id)) as lessons
FROM courses c
LEFT JOIN chapters ch ON ch.course_id = c.id
GROUP BY c.id;

-- Verificar lecciones con video
SELECT l.id, l.title, l.video_provider, l.video_id, l.duration_seconds
FROM lessons l
WHERE l.video_id IS NOT NULL;
```

### 4.2 Progress Tracking

```bash
# 1. Verificar eventos de player
SELECT vpe.user_id, vpe.lesson_id, vpe.event, vpe.playback_seconds, vpe.created_at
FROM video_player_events vpe
ORDER BY created_at DESC LIMIT 20;

# 2. Verificar progreso de video
SELECT vp.user_id, vp.lesson_id, vp.watched_seconds, vp.completed, vp.last_second
FROM video_progress vp
ORDER BY updated_at DESC LIMIT 10;

# 3. Probar endpoint de eventos
curl -X POST https://app.letstalkspanish.io/es/api/player/events \
  -H "Content-Type: application/json" \
  -H "Cookie: <SESSION_COOKIE>" \
  -d '{"lesson_id":1,"event":"play","playback_seconds":30,"provider":"youtube"}'
# Esperado: {"ok":true}
```

### 4.3 Assignments

```sql
-- Verificar assignments
SELECT a.id, a.title, a.course_id, a.due_at, 
       (SELECT COUNT(*) FROM assignment_submissions WHERE assignment_id = a.id) as submissions
FROM assignments a;

-- Verificar entregas
SELECT s.id, s.user_id, s.assignment_id, s.status, s.score, s.submitted_at
FROM assignment_submissions s
ORDER BY submitted_at DESC LIMIT 10;
```

### 4.4 Casos de Prueba Contenido

| ID | Componente | Caso | Rol | Resultado Esperado |
|----|------------|------|-----|-------------------|
| CONT-01 | Cursos | Crear curso | Admin | Curso en BD |
| CONT-02 | Lecciones | Crear lección | Admin | Lección con video |
| CONT-03 | Player | Evento play | Student | video_player_events |
| CONT-04 | Progress | Marcar completado | Student | video_progress.completed=1 |
| CONT-05 | Assignments | Crear tarea | Admin | Assignment en BD |
| CONT-06 | Submissions | Entregar | Student | Submission status=submitted |
| CONT-07 | Feedback | Calificar | Admin | Submission score + feedback |

---

## 5. ÁREA 4: MENSAJERÍA Y NOTIFICACIONES

### 5.1 Sistema de Mensajes Internos

```sql
-- Verificar mensajes
SELECT m.id, m.sender_id, m.recipient_id, m.subject, m.read_at, m.created_at
FROM messages m
ORDER BY created_at DESC LIMIT 10;

-- Verificar canales de admin
SELECT mc.id, mc.name, mc.type, mc.created_at
FROM message_channels mc;
```

### 5.2 Notificaciones Push/Email

```bash
# 1. Verificar notificaciones en BD
SELECT n.id, n.type, n.notifiable_type, n.notifiable_id, n.read_at
FROM notifications n
ORDER BY created_at DESC LIMIT 10;

# 2. Verificar jobs de notificación
SELECT j.id, j.queue, j.payload, j.created_at
FROM jobs j
WHERE j.queue = 'notifications'
ORDER BY created_at DESC LIMIT 5;
```

### 5.3 WhatsApp Deeplinks

```bash
# 1. Verificar configuración
grep WHATSAPP_DEEPLINK /var/www/app.letstalkspanish.io/.env

# 2. Verificar logs de CTA
SELECT * FROM student_activity_snapshots
WHERE category = 'whatsapp_cta'
ORDER BY created_at DESC LIMIT 5;
```

### 5.4 Casos de Prueba Mensajería

| ID | Componente | Caso | Rol | Resultado Esperado |
|----|------------|------|-----|-------------------|
| MSG-01 | Mensajes | Enviar mensaje | Admin → Student | Mensaje en BD |
| MSG-02 | Mensajes | Leer mensaje | Student | read_at actualizado |
| MSG-03 | Email | Confirmación pago | Sistema | Email enviado |
| MSG-04 | Email | Recordatorio tarea | Sistema | Email programado |
| MSG-05 | WhatsApp | CTA Player | Student | Deeplink funciona |
| MSG-06 | Push | Notificación | Sistema | Notificación en BD |

---

## 6. ÁREA 5: TELEMETRÍA Y ANALYTICS

### 6.1 Telemetry Recorder (Async)

```bash
# 1. Verificar job de telemetría
SELECT * FROM jobs WHERE queue = 'telemetry' ORDER BY created_at DESC LIMIT 5;

# 2. Verificar supervisor
supervisorctl status lts-queue

# 3. Verificar eventos procesados
SELECT COUNT(*) as total, DATE(created_at) as fecha
FROM video_player_events
GROUP BY DATE(created_at)
ORDER BY fecha DESC LIMIT 7;
```

### 6.2 Servicios de Analytics

```bash
# 1. Verificar configuración GA4/Mixpanel
grep -E "GA4_|MIXPANEL_" /var/www/app.letstalkspanish.io/.env

# 2. Sincronizar telemetría manualmente
php artisan telemetry:sync --limit=5

# 3. Verificar Sentry
php artisan sentry:test
```

---

## 7. SCRIPTS DE AUDITORÍA AUTOMATIZADA

### 7.1 Script Principal: `backend_audit_suite.php`

```php
<?php
// scripts/backend_audit_suite.php
// [AGENTE: OPUS 4.5] - Suite de auditoría backend

$tests = [
    'AUTH' => [
        'Usuarios de prueba existen' => fn() => User::whereIn('email', [
            'academy@letstalkspanish.io',
            'student@letstalkspanish.io'
        ])->count() >= 2,
        
        'Roles asignados correctamente' => fn() => User::where('email', 'academy@letstalkspanish.io')
            ->first()?->hasRole('admin'),
    ],
    
    'EXTERNAL' => [
        'Discord webhook configurado' => fn() => !empty(env('DISCORD_WEBHOOK_URL')),
        'SMTP configurado' => fn() => !empty(env('MAIL_HOST')),
        'Sentry DSN presente' => fn() => !empty(env('SENTRY_LARAVEL_DSN')),
    ],
    
    'CONTENT' => [
        'Cursos existen' => fn() => Course::count() > 0,
        'Lecciones con video' => fn() => Lesson::whereNotNull('video_id')->count() > 0,
    ],
    
    'INFRA' => [
        'Colas funcionando' => fn() => Job::where('queue', 'telemetry')->count() === 0, // Sin backlog
        'Crontab activo' => fn() => !empty(shell_exec('crontab -u deploy -l')),
    ],
];

// Ejecutar y reportar...
```

---

## 8. DATOS DE PRUEBA PARA GEMINI

### 8.1 Perfiles de Usuario Específicos

| Perfil | Email | Rol | Subrol/Estado | Escenario de Prueba |
|--------|-------|-----|---------------|---------------------|
| Admin Principal | `academy@letstalkspanish.io` | admin + teacher_admin | Full access | Panel completo |
| Teacher Admin QA | `teacher.admin.qa@letstalkspanish.io` | teacher_admin | Limitado | Planner + Packs |
| Teacher QA | `teacher.qa@letstalkspanish.io` | teacher | Solo docente | Dashboard + Propuestas |
| Student Paid | `student.paid@letstalkspanish.io` | student | `student_paid` | Player + Checkout |
| Student Free | `student.free@letstalkspanish.io` | student | `student_free` | Acceso limitado |
| Student con Pago Pendiente | `student.pending@letstalkspanish.io` | student | `student_free` + orden pending | Checkout retry |
| Student en Cohorte Llena | `student.waitlist@letstalkspanish.io` | student | - | CohortSoldOut |

### 8.2 Semillas SQL Requeridas

```sql
-- seed_qa_profiles.sql
-- Crear usuario con pago pendiente
INSERT INTO users (name, email, password, created_at, updated_at) VALUES
('Student Pending QA', 'student.pending@letstalkspanish.io', '$2y$12$...<hash>', NOW(), NOW());

-- Asignar rol student
INSERT INTO model_has_roles (role_id, model_type, model_id) VALUES
((SELECT id FROM roles WHERE name = 'student'), 'App\\Models\\User', LAST_INSERT_ID());

-- Crear orden pendiente
INSERT INTO practice_package_orders (user_id, practice_package_id, total_amount, status, created_at) VALUES
(LAST_INSERT_ID(), 1, 29.99, 'pending', NOW());

-- Crear cohorte llena para pruebas
UPDATE cohort_templates SET capacity = 1, enrolled_count = 1 
WHERE id = (SELECT id FROM cohort_templates LIMIT 1);
```

### 8.3 Escenarios de Prueba Frontend (Para Gemini)

| Escenario | Usuario | Acción Frontend | Comportamiento Backend Esperado |
|-----------|---------|-----------------|--------------------------------|
| Login exitoso | student.paid@ | Formulario login | Session + redirect |
| Checkout completo | student.paid@ | Añadir + pagar | Orden + inscripción |
| Checkout fallido | student.pending@ | Reintentar pago | Retry logic + notificación |
| Cohorte agotada | student.waitlist@ | Intentar comprar | Toast "No hay cupos" |
| Práctica reservada | student.paid@ | Click "Reservar" | Reserva en BD + Discord event |
| Player tracking | student.paid@ | Reproducir video | video_player_events |
| Mensaje enviado | admin@ | Enviar a student | Message + notification |

---

## 9. CRONOGRAMA DE EJECUCIÓN

| Día | Fase | Áreas | Responsable |
|-----|------|-------|-------------|
| D+0 | Preparación | Seeds + Configuración | Opus 4.5 |
| D+1 | Auth & Roles | AUTH-01 a AUTH-08 | Opus 4.5 |
| D+2 | Servicios Externos | EXT-01 a EXT-08 | Opus 4.5 |
| D+3 | Contenido | CONT-01 a CONT-07 | Opus 4.5 |
| D+4 | Mensajería | MSG-01 a MSG-06 | Opus 4.5 |
| D+5 | Telemetría | Verificación final | Opus 4.5 |
| D+6 | Reporte Final | Consolidación | Opus 4.5 |

---

## 10. CRITERIOS DE CERTIFICACIÓN

### 10.1 Requisitos para Certificación ✅

| Área | Criterio | Umbral |
|------|----------|--------|
| Auth | Todos los casos AUTH-* pasando | 100% |
| Servicios | Conexiones externas verificadas | 100% |
| Contenido | CRUD completo funcional | 100% |
| Mensajería | Emails enviados correctamente | 100% |
| Logs | Sin errores 500 en 24h | 0 errores |

### 10.2 Estados de Certificación

| Estado | Significado | Acción |
|--------|-------------|--------|
| 🟢 CERTIFICADO | Todas las pruebas pasaron | Listo para producción |
| 🟡 CONDICIONAL | >90% pasó, fallos menores | Documentar excepciones |
| 🔴 NO CERTIFICADO | Fallos críticos | Nuevo ciclo de fixes |

---

## 11. CONCLUSIÓN

Este roadmap proporciona un plan exhaustivo para certificar la estabilidad del backend del LMS. Cubre:

1. **55 flujos funcionales** mapeados por rol
2. **8 servicios externos** críticos
3. **7 perfiles de usuario** para pruebas
4. **Cronograma de 6 días** para ejecución completa

La ejecución de este roadmap garantizará que Gemini pueda diseñar pruebas de frontend con confianza en que el backend responderá correctamente.

---

**Firmado por**: Opus 4.5 (Arquitecto de Pruebas Backend)

---

[TURNO-OPUS-AUDIT-FINALIZADO]

