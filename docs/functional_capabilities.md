# Mapa de funcionalidades por rol

Este documento detalla todas las áreas funcionales del LMS, organizadas por rol y enlazadas con los planes de prueba (`docs/test_roadmap.md`). Cada punto representa al menos un flujo que debe verificarse en QA; los totales sirven para estimar esfuerzos y asegurar que no queden módulos sin cubrir.

---

## 1. Accesos y sesiones independientes

| Rol / propósito | URL base (por locale) | Notas |
| --- | --- | --- |
| Admin full access | `/{locale}/admin/login` (`route('login.admin')`) | Redirige al dashboard global tras Fortify |
| Teacher Admin | `/{locale}/teacher-admin/login` (`route('login.teacher-admin')`) | Comparte UI con admin pero restringe módulos sensibles |
| Teacher (docente) | `/{locale}/teacher/login` (`route('login.teacher')`) | Acceso al panel docente, propuestas y mensajería |
| Student | `/{locale}/student/login` (`route('login.student')`) | Entra directo al dashboard gamificado |
| Registro público | `/{locale}/register` | Alta básica; la asignación de roles especiales sigue siendo manual |
| Login genérico | `/{locale}/login` | Utiliza `target_role` para decidir el panel predeterminado |

> **Recomendación**: para pruebas manuales usar navegadores / perfiles separados (o limpiar cookies entre sesiones) de modo que el estado de un rol no contamine a otro.

---

## 2. Totales de funcionalidades por rol

| Rol | Módulos / flujos controlados | Desglose |
| --- | --- | --- |
| Admin (y super admin) | **18** | Setup, branding, provisioner, data porter, payments, planner, cohorts, builder, pages, shop, notifications, queue/cron, etc. |
| Teacher Admin | **11** | Planner operativo, practice packs, cohort templates, mensajes, dashboards, duplicadores. |
| Teacher | **9** | Dashboard docente, propuestas de módulo, mensajes teacher, revisión de tareas asignadas, onboarding docente. |
| Student | **12** | Dashboard XP, player, assignments, practice browser, shop, checkout, mensajería estudiante, alertas WhatsApp. |
| Público / pre-login | **5** | Landing personalizada, registro, forgot password, selector de idioma, componente de paquetes públicos. |

Total flujos explícitos: **55**. Estos se referencian por ID en el roadmap de pruebas para asegurar cobertura total antes de empaquetar.

---

## 3. Árbol detallado por rol

### 3.1 Admin (18 flujos)
1. **Setup wizard / onboarding** – completar credenciales e integraciones.
2. **Branding Designer** – colores tipografía, logotipos, hero público.
3. **Provisioner** – definir SMTP, S3/R2, Google OAuth, Discord, WhatsApp, etc.
4. **DataPorter Hub + export firmado**.
5. **Payment Simulator** (PayPal/Stripe fake) y validación de colas.
6. **Assignments Manager** – aprobar/rechazar entregas, rubric, feedback.
7. **Practice Planner (modo administrador)** – alta rápida de sesiones, bloqueos.
8. **Practice Packages Manager** – creación/edición de paquetes pagados.
9. **Cohort Template Manager** – plantillas de academias, cupos, precios.
10. **Teacher Manager** – alta/baja de docentes, roles, invitaciones.
11. **Teacher Submissions Hub** – revisión de materiales subidos por maestros.
12. **Teacher Performance Report** – métricas y exportación CSV.
13. **Page Builder / Page Manager** – landings personalizadas, SEO.
14. **Product Catalog / Shop** – administración de artículos y visibilidad.
15. **Messages (admin)** – canales institucionales, filtros, notificaciones.
16. **Outbox / Integraciones** – seguimiento a eventos y webhooks.
17. **Grupos y Tiers** – segmentación y reglas de acceso.
18. **Panel de seguridad** – jobs, colas, supervisores, logs (desde dashboard).

### 3.2 Teacher Admin (11 flujos)
1. **Dashboard híbrido** (admin-lite) con métricas de cohorts asignadas.
2. **Planner profesor** – crear, mover, duplicar y cancelar prácticas.
3. **Practice Packs** – publicar/retirar paquetes, revisar desempeño.
4. **Cohort Templates** – usar plantilla base, ajustar slots/horarios.
5. **Mensajes admin** – responder tickets y avisos a estudiantes.
6. **Assignments (vista restringida)** – evaluar tareas asignadas a su cohort.
7. **Shop preview** – revisar oferta pública y aplicar códigos.
8. **Discord & WhatsApp alerts** – disparar pruebas desde la UI.
9. **DataPorter (lectura)** – descargar reportes autorizados.
10. **Provisioner (lectura)** – revisar estados de integraciones.
11. **Payments simulator (lectura)** – monitorear reintentos de pago.

### 3.3 Teacher (9 flujos)
1. **Teacher Dashboard** – guías contextuales, objetivos semanales.
2. **Propuesta de módulo / lección** – formulario con validaciones, adjuntos.
3. **Mensajería teacher** – enviar/recibir con estudiantes y admins.
4. **Assignments** – ver entregas de su curso, marcar como revisado.
5. **Practice calendar (solo lectura)** – ver agenda general.
6. **Onboarding docente** – completar perfil, bio, certificaciones.
7. **Notificaciones** – confirmaciones de clases, recordatorios.
8. **Perfil personal** – actualizar password, idioma, idioma de comunicación.
9. **Accesos rápidos** – enlaces a WhatsApp / Discord de soporte docente.

### 3.4 Student (12 flujos)
1. **Dashboard gamificado** – XP, tiempo de estudio, recordatorios.
2. **Player UIX 2030** – reproducción lessons, eventos telemetría.
3. **Assignments** – ver instrucciones, subir entrega, revisar feedback.
4. **Practice Browser** – reservar prácticas Discord (según cupo).
5. **Cart / Checkout** – packs, cupones, integración con Payment Simulator.
6. **Shop packs** – catálogo, filtros, recomendaciones.
7. **Mensajes student** – soporte, avisos, historial.
8. **WhatsApp redirect** – CTA directo a canal de soporte.
9. **Discord deep link** – unirse al servidor oficial.
10. **Perfil estudiante** – idioma, zona horaria, notificaciones.
11. **Historial de pagos** – ver logs y recibos (desde dashboard).
12. **Guías contextuales** – overlays que el admin puede actualizar.

### 3.5 Público / Pre-login (5 flujos)
1. **Landing / welcome** con hero personalizable desde Branding.
2. **Selector de idioma** (`/{locale}`) con redirecciones limpias.
3. **Registro** – alta básica (rol `student` por defecto, upgrade manual).
4. **Reset password** – flujo Fortify completo.
5. **Componentes públicos Livewire** – `practice-packages-showcase`, etc.

---

## 4. Flujos de registro y onboarding

1. **Alta automática de estudiantes**
   - Formulario en `/{locale}/register`.
   - Nueva cuenta entra como `student_free` hasta que admin la promueve.
2. **Invitación docente**
   - Admin crea usuario → asigna rol `teacher`/`teacher_admin`.
   - Docente completa su perfil en la primera sesión (onboarding).
3. **Bootstrap de admin**
   - Se respalda en el wizard + seeds (`seed_users.php`).

---

## 5. Uso dentro del roadmap de pruebas

- Cada flujo enumerado arriba tiene un ID implícito (sección / inciso).  
- `docs/test_roadmap.md` utiliza estos IDs para listar qué seeds, sesiones y evidencias se necesitan por sprint.  
- Cuando se detecta un fallo, se registra con referencia al inciso correspondiente (ej. “3.4.5 – Checkout”) y se corrige antes de avanzar al siguiente bloque.

Con este mapa se garantiza que el equipo tenga visibilidad completa de lo que se debe monitorear, validar y mantener antes de empaquetar para Hostinger u otras academias.


