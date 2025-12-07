# QA · Credenciales por Rol

Estas cuentas se crean automáticamente mediante `AuditorProfilesSeeder` y comparten la misma contraseña de pruebas. Úsalas exclusivamente en entornos de staging/local.

> **Contraseña universal**: `AuditorQA2025!`

| Rol principal | Email | Notas de uso |
| --- | --- | --- |
| Admin + Teacher Admin | `academy@letstalkspanish.io` | Acceso completo a branding, Page Builder, integraciones, provisioning y dashboards administrativos. |
| Teacher Admin | `teacher.admin.qa@letstalkspanish.io` | Permisos docentes avanzados (Course Builder, Planner Discord, packs). |
| Teacher | `teacher.qa@letstalkspanish.io` | Enfocado en módulos asignados, bandeja de propuestas y dashboard docente. |
| Student Paid | `student.paid@letstalkspanish.io` | Estudiante con pack activo; usar para probar player, prácticas reservadas y pagos confirmados. |
| Student Pending | `student.pending@letstalkspanish.io` | Pack en estado `pending`; ideal para flujos de retry de pago y notificaciones. |
| Student Waitlist | `student.waitlist@letstalkspanish.io` | Asociado a cohorte llena (`qa-full-cohort`); usar para validar comportamientos de lista de espera. |

## Consejos de prueba

- Después de sembrar: `php artisan migrate:fresh --seed`.
- Para Google OAuth o accesos por idioma, anteponer `/es` o `/en` a las rutas (`/en/login`).
- Si cambias la contraseña de alguna cuenta durante QA, ejecuta nuevamente `php artisan db:seed --class=AuditorProfilesSeeder` para restaurar el valor por defecto.

[CREDENTIALS-DOC-GENERATED]


