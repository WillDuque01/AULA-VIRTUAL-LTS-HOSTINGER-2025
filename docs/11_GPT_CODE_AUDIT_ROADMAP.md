# 11_GPT_CODE_AUDIT_ROADMAP.md

## 1. Objetivo y Alcance
Consolidar un plan de certificación de código que cubra los flujos críticos identificados por **Opus** (backend) y **Gemini** (frontend) para garantizar que pagos, integraciones Discord, roles/permisos y la navegación UX (Login → Builder → Logout) estén protegidos por pruebas automatizadas, revisiones de seguridad y controles de deuda técnica. // [AGENTE: GPT-5.1 CODEX]

---

## 2. Matriz de Áreas Críticas
| Área | Componentes/Archivos | Riesgo | Tipo de prueba |
| --- | --- | --- | --- |
| Pagos & Cohortes | `PracticeCheckout`, `PracticePackageOrderService`, `CohortEnrollmentService`, rutas `/shop/*` | Cobros erróneos, cohortes llenas | PHPUnit + Browser |
| Discord & Planner | Eventos `DiscordPractice*`, Livewire `DiscordPracticeBrowser`, Jobs `RecordPracticeEvent` | Falta de reserva real-time | PHPUnit + Logs |
| Autenticación & Gates | `FortifyServiceProvider`, `DashboardRedirector`, middleware `EnsureRole`, rutas `/admin/*` | Escalada de privilegios | PHPUnit + Artisan |
| UX Crítico (Login → Builder → Logout) | Views `login/register`, Livewire `builder/*`, navigation layout | Bloqueos de flujo, botones muertos | Dusk / Playwright |

---

## 3. Cobertura de Pruebas Unitarias / Funcionales
### 3.1 Pagos y Cohortes
- **Nuevo**: `tests/Feature/Payments/CheckoutFlowTest.php`
  - `test_student_can_pay_practice_pack` (mock gateway, assert `practice_package_orders` created)
  - `test_pending_order_blocks_content` (student pending -> expect redirect to checkout)
- **Nuevo**: `tests/Feature/Cohorts/CohortCapacityTest.php`
  - Simular cohorte llena (`capacity == enrolled_count`) y verificar que `CohortSoldOutException` lanza toast y no crea registro.
- **Mock**: usar `Http::fake()` para webhooks PayPal/Stripe; assert handshake y firma.

### 3.2 Discord / Planner
- **Actualizar** `tests/Feature/Student/DiscordPracticeBrowserTest.php`
  - Agregar `test_reservation_dispatches_discord_event` con `Event::fake(DiscordPracticeReserved::class)`.
- **Nuevo**: `tests/Feature/Integrations/DiscordWebhookTest.php`
  - Simular payload + `Notification::fake()` para asegurar que el webhook se firma con `DISCORD_WEBHOOK_URL`.

### 3.3 Autenticación, Roles y Permisos
- **Nuevo**: `tests/Feature/Auth/RoleGateTest.php`
  - `test_student_cannot_access_admin_dashboard` (403/redirect)
  - `test_admin_can_access_provisioner` (200 + Gate `manage-settings`)
- **Extender** `tests/Feature/Auth/GoogleLoginTest.php`
  - Validar `DashboardRedirector::resolve()` para cada rol (`admin`, `teacher_admin`, `student_paid`).

### 3.4 Lógica UX crítica
- **Nuevo**: `tests/Feature/Builder/NavigationFlowTest.php`
  - Autenticar Admin → GET `/courses/{id}/builder` → POST cambio → Logout. Assert flashes, redirect y eventos `CourseUpdated`.
- **Cobertura del Player**:
  - `tests/Feature/Player/ProgressEventTest.php`: POST `/api/player/events` con throttle y verificar `video_player_events`.

---

## 4. Pruebas de Navegador (E2E/Dusk)
| Test Dusk | Escenarios | Datos |
| --- | --- | --- |
| `StudentFlowTest` | Login `student.pending@` → redirección a checkout; login `student.paid@` → Player carga video → Logout | Seeds `student_pending`, `student_paid` |
| `CheckoutFlowTest` | Añadir pack → completar pago simulado → ver toasts y actualización de créditos en header | Mock gateway |
| `AdminDashboardTest` | `academy@` abre dashboard → contadores animados (ver `data-testid="animated-count"`), drawer móvil responsive | Resize viewport 375px |
| `PracticesBrowserTest` | Toggle filtros, reservar, cancelar, lista de espera; validar toasts y `wire:loading` en botones | `student.paid@` |

> Para cada test: capturar screenshot, validar ausencia de errores en consola (API `assertConsoleLogDoesNotContain('Alpine')` si se usa Playwright).

---

## 5. Análisis de Seguridad (Code Review)
### 5.1 Mass Assignment & Validaciones
- Revisar controladores `Auth\RegisteredUserController`, `PracticeCheckoutController`, Livewire `PracticeCartPage`. Asegurar `$fillable` o `$guarded` correctos y validar request via FormRequest/`rules`.
- Verificar `DiscordPracticeBrowser` y `builder` Livewire: todos los métodos públicos que aceptan IDs deben usar `authorize()` o `Gate` y `->findOrFail()` con `user_id`.

### 5.2 Rutas y Middleware
- Asegurar que `/api/player/events` y `/shop/*` estén bajo `auth` + CSRF (ver `routes/web.php`). Añadir `EnsureRole` middleware donde falte.
- Revisar `app/Support/Redirects/DashboardRedirector.php` para proteger contra `targetRole` inválido (whitelist).

### 5.3 Webhooks & Firmas
- Confirmar que endpoints Make/PayPal/Stripe calculen HMAC y comparen timing-safe (`hash_equals`). Agregar pruebas unitarias para `WebhookSignatureValidator`.

---

## 6. Deuda Técnica a Corregir Antes de Auditoría
1. **Duplicación JS**: `registerCelebrations` y listeners `notify` se duplican en componentes inline — migrar todos los scripts inline de Blade (`player.blade.php`, `navigation.blade.php`) a módulos Vite.
2. **Blade Partials Fragmentados**: Views de Page Builder `page/blocks/*.blade.php` repiten tokens; crear componentes `x-page.block-*`.
3. **Livewire sin Tests**: `PracticeCartPage`, `Admin\DataPorterHub` carecen de pruebas; añadir factories/seeds y cubrir `mount()` + acciones.
4. **Logs / Observability**: `RecordPlayerEventJob` no tiene `try/catch` con `report()` — añadir para evitar jobs “ghost”.

---

## 7. Plan de Ejecución (Código)
| Día | Acción | Responsable |
| --- | --- | --- |
| D+0 | Crear seeds QA faltantes (`student.pending`, cohortes llenas) | Backend |
| D+1 | Implementar PHPUnit (Payments, Discord, Auth) | GPT-5.1 |
| D+2 | Implementar Dusk tests (StudentFlow, Checkout, Admin) | GPT-5.1 + Gemini |
| D+3 | Revisar seguridad (mass assignment, HMAC) y aplicar fixes | GPT-5.1 |
| D+4 | Refactor JS/Blade duplicado | GPT-5.1 |
| D+5 | Ejecutar pipeline completo (PHPUnit + Dusk + npm run build) y documentar resultados | GPT-5.1 |

---

**Resultado esperado:** Al finalizar este roadmap, todos los flujos críticos tendrán pruebas automatizadas, la capa de seguridad habrá sido auditada y las deudas técnicas bloqueantes estarán identificadas para remediación inmediata.

[TURNO-GPT-AUDIT-FINALIZADO]

