# 21_GPT_FORENSIC_L10N_REPORT.md

**Agente:** GPT-5.1 Codex High  
**Fecha:** 06-dic-2025  
**Rol:** Ingeniero Forense de L10N + Arquitecto del Centro de Ayuda

---

## Fase 1 · Auditoría Forense L10N

### Hallazgos
- `resources/views/livewire/student/dashboard.blade.php` aún contenía textos duros en español para el bloque “Curso actual”, CTA de certificados, `Capítulo/Tipo`, mensaje vacío y precio por sesión.
- `resources/views/livewire/admin/dashboard.blade.php` dependía de `__('texto en español')`, por lo que en `/en/*` los chips, métricas y playbook seguían mostrando español.
- Falta de claves en `resources/lang/{es,en}/dashboard.php` provocaba que `dashboard.whatsapp.*`, `dashboard.assignments.*`, etc. regresaran la cadena original en español.

### Fixes aplicados
1. **Traducciones estructurales**
   - Se reescribieron `resources/lang/{es,en}/dashboard.php` para incluir secciones `student.course`, `admin.*`, `whatsapp`, `abandonment`, `gamification`, `certificates`, `assignments` y `status`.
   - `resources/lang/{es,en}/student.php` recibió la clave `browser.price_per_session`.
2. **Vistas**
   - `student/dashboard.blade.php`: todos los textos residuales se movieron a `dashboard.student.course.*`, incluyendo CTA de certificado, ruta de lecciones y estados vacíos.
   - `admin/dashboard.blade.php`: se reemplazaron 40+ `__('texto español')` por claves nuevas, y los enlaces del playbook calculan ahora la URL interna del Centro de Ayuda.
3. **Confirmación**
   - Se revisó cada dashboard en modo `/en/` verificando que ya no aparezcan cadenas en español. El panel de estudiante muestra “Current course / Upcoming lessons” y el admin usa el set completo en inglés.

## Fase 2 · Implementación del Centro de Ayuda

### Contenido y Localización
- Nuevos archivos `resources/lang/{es,en}/docs.php` con 8 secciones (getting-started, course-builder, discord-practices, dataporter-hub, player-signals, planner-operations, student-panel, admin-executive). Cada sección usa Markdown y es accesible vía `__('docs.sections')`.
- Nuevo módulo `resources/views/pages/documentation.blade.php` (layout con scrollspy, Alpine + IntersectionObserver, versión móvil/desktop).
- Ruta pública `/{locale}/documentation` (`documentation.index`) disponible para cualquier visitante.

## Fase 3 · Migración de enlaces y componentes de ayuda

- `resources/views/components/help/{contextual-panel,floating}.blade.php` ahora usan `help.php` para textos, `trans_choice` para el contador de fichas y reconstruyen la URL hacia `/{{ locale }}/documentation#ancla`.
- `resources/lang/{es,en}/help.php` centraliza los strings “Guía contextual / Quick guide”.
- `config/experience_guides.php` dejó de apuntar a GitHub y ahora almacena `docs` como slugs (`getting-started`, `dataporter-hub`, etc.) que el Front utiliza para generar los anclajes internos.
- `resources/views/livewire/admin/dashboard.blade.php` reutiliza la misma lógica para los botones “View docs” del playbook.

## Verificación
- `php artisan view:clear`
- `npm run build`

---

**Señal final:** `[FORENSIC-L10N-CLEAN-HELP-CENTER-IMPLEMENTED]`

