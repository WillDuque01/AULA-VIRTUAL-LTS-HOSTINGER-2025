# 32_GPT_ROLLOUT_FIXES_REPORT.md

**Agente:** GPT-5.1 Codex High  
**Fecha:** 07-dic-2025  
**Rol:** Implementador Frontend · Turno 32 (UX/UI + Rollout)

---

## 1. Rediseño de Plantillas de Email (P0)

- **Layout base** `resources/views/emails/layouts/base.blade.php`
  - Integra colores dinámicos desde `BrandingSettings` y expone la paleta a todas las vistas (`$emailPalette`).
  - Nuevo layout claro: encabezado sólido con el color primario, cuerpo blanco con sombra y footer sobre fondo claro.
  - Botones, tipografías e imágenes adaptados a UIX 2030.
- **Componentes**
  - `emails/components/panel.blade.php`: panel claro con bordes suaves y padding consistente.
  - `emails/components/button.blade.php`: botón curvo con color de acento y sombra.
- **Plantillas actualizadas** (`course-unlocked`, `module-unlocked`, `message-notification`, `offer-announcement`, `payment-confirmation`, `subscription-status`)
  - Se eliminaron los colores oscuros legacy (`#0f172a`, `#38bdf8`, etc.) y se sustituyeron por la nueva paleta.
  - Los CTA y textos destacados usan `accent` y `muted` para mantener jerarquía visual.
  - Contenido adicional y footers ahora respetan el tono neutro y la tipografía base.

**Resultado:** todos los correos usan el mismo look & feel que el dashboard (UIX 2030) y toman automáticamente los colores definidos en el Branding Designer.

---

## 2. Fix QR en Verificación de Certificados (P1)

- **Controlador** `CertificateController@verify`
  - Se genera el QR como `data:image/png;base64` descargando una única vez el PNG y evitando peticiones del navegador a dominios externos.
  - Se agrega manejo de errores con `Http::timeout()` y logging de avisos.
- **Vista** `resources/views/certificates/verify.blade.php`
  - El `<img>` solo se muestra cuando hay QR disponible; en caso contrario se presenta un aviso con instrucciones para usar el enlace compartido.

**Impacto:** el QR vuelve a mostrarse en todos los navegadores y deja de depender de CSP del cliente.

---

## 3. Onboarding No Intrusivo (P2)

- Se eliminó el render global del banner desde `layouts/app.blade.php`.
- El componente `<livewire:profile.completion-banner />` se muestra únicamente dentro de `student/dashboard.blade.php`, justo debajo del panel contextual. De esta manera el dashboard guía al estudiante sin bloquear la UI.

---

## 4. Course Builder DnD Refinement (P3)

- Ajustes de estilo en `resources/views/livewire/builder/course-builder.blade.php`:
  - Capítulos y lecciones ahora usan `bg-white`, `border-slate-200`, sombras suaves y mayor espacio interno para alinearse con Page Builder.
  - Las tarjetas internas (metadatos de prácticas, chips, etc.) utilizan fondos claros (`bg-slate-50`) que facilitan el drag & drop y mejoran la legibilidad en móvil.

---

## 5. Verificaciones Ejecutadas

- `php artisan view:clear` (dos veces, tras los cambios de Blade y después del rediseño completo).
- Navegación local en `/certificates/verify/{code}` para validar que la imagen QR se renderiza como `data:` sin advertencias JS.

---

## Próximos pasos sugeridos para validación (Gemini 3 Pro)

1. Enviar el script `scripts/test_notifications.php` para revisar el nuevo diseño en un inbox real.
2. Abrir `/en/certificates/verify/{code}` y confirmar que el QR aparece sin bloqueos de navegador.
3. Revisar el Student Dashboard con un perfil incompleto y comprobar que solo se muestra el banner inline (sin modal).
4. Probar el Course Builder en desktop y móvil asegurando que el drag & drop mantiene los estilos claros.

**Señal de cierre:** `[ROLLOUT-FIXES-READY-FOR-VALIDATION]`


