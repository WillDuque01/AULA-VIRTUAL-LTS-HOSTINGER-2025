# 10_GEMINI_UX_AUDIT_ROADMAP.md

## Roadmap de Certificación Frontend — Auditoría de Experiencia de Usuario (UX)

**Agente**: Gemini 3 Pro (Arquitecto de Experiencia de Usuario)
**Fecha**: 06-dic-2025
**Base**: Perfiles definidos en `09_OPUS_BACKEND_AUDIT_ROADMAP.md`

---

## 1. RESUMEN EJECUTIVO

Este roadmap complementa la auditoría de backend de Opus, enfocándose exclusivamente en la **capa de presentación y experiencia del usuario final**. El objetivo es certificar que la interfaz no solo "funcione" (backend), sino que sea **usable, consistente y responsiva** para cada perfil de usuario.

| Área | Foco | Método | Prioridad |
|------|------|--------|-----------|
| **Flujos Críticos** | Bloqueos de navegación por rol | E2E Manual/Auto | 🔴 CRÍTICA |
| **Identidad Visual** | Coherencia UIX 2030 (Glassmorphism) | Inspección Visual | 🟡 ALTA |
| **Feedback** | Respuesta del sistema (Toasts, Spinners) | Interacción | 🟡 ALTA |
| **Accesibilidad** | Contraste, Teclado, Móvil | Lighthouse/Manual | 🟢 MEDIA |

---

## 2. AUDITORÍA DE FLUJOS CRÍTICOS (UX)

Pruebas específicas para validar la experiencia según el estado del usuario.

### 2.1 Perfil: Student Pending (`student.pending@`)
*Objetivo: Verificar que el usuario no se pierda en un limbo de pago incompleto.*

*   **Test UX-01: Intercepción de Navegación**
    *   *Acción:* Intentar acceder a `/lessons/{id}/player` o `/student/dashboard`.
    *   *Esperado:* ¿El sistema redirige amigablemente al checkout o muestra un banner persistente de "Pago Pendiente"?
    *   *Fallo:* Acceso permitido al contenido o error 403 genérico sin contexto.
*   **Test UX-02: Recuperación de Carrito**
    *   *Acción:* Ir a `/shop/checkout`.
    *   *Esperado:* El carrito debe persistir los ítems previos. El botón de pago debe estar habilitado y visible.

### 2.2 Perfil: Student Paid (`student.paid@`)
*Objetivo: Validar la experiencia de consumo de contenido sin fricción.*

*   **Test UX-03: Player Inmersivo**
    *   *Acción:* Acceder a una lección de video.
    *   *Esperado:* El video carga. El sidebar (timeline) es colapsable en móvil. El progreso se marca visualmente en tiempo real (barra de progreso o check).
*   **Test UX-04: Navegación de Prácticas**
    *   *Acción:* Acceder al Browser de Prácticas.
    *   *Esperado:* Las tarjetas de sesión muestran claramente "Reservar" (si hay cupo) o "Lista de Espera" (si no). Feedback inmediato (Toast) al reservar.

### 2.3 Perfil: Student Waitlist (`student.waitlist@`)
*Objetivo: Manejo de frustración por falta de cupo.*

*   **Test UX-05: Feedback de Agotado**
    *   *Acción:* Intentar reservar en una cohorte llena.
    *   *Esperado:* El botón debe estar deshabilitado visualmente o mostrar un modal de "Unirse a lista de espera". **NO** debe permitir clic y fallar después.
    *   *Toast:* Mensaje claro: "Lo sentimos, los cupos se agotaron hace X minutos".

### 2.4 Perfil: Admin Principal (`academy@`)
*Objetivo: Consistencia del Dashboard UIX 2030.*

*   **Test UX-06: Dashboard KPIs**
    *   *Acción:* Carga inicial del Dashboard.
    *   *Esperado:* Todos los contadores (Ingresos, Usuarios, Retención) deben cargar con la animación `animatedCount`. No deben verse "0" estáticos antes de cargar (usar esqueletos o spinners).
*   **Test UX-07: Gestión de Usuarios**
    *   *Acción:* Tabla de usuarios.
    *   *Esperado:* Acciones (Editar, Banear) accesibles. Paginación funcionando sin recargar toda la página (Livewire SPA feel).

---

## 3. AUDITORÍA VISUAL Y DE ACCESIBILIDAD

### 3.1 Consistencia Visual (UIX 2030)
*   **Glassmorphism:** Verificar que las tarjetas en Dashboard y Player usen `bg-white/85` y `backdrop-blur` consistentemente.
*   **Tipografía:** Confirmar uso de `Inter` para UI y `Onest` para headings en todas las vistas nuevas.
*   **Espaciado:** Verificar márgenes consistentes en móvil (padding lateral seguro) vs escritorio.

### 3.2 Responsividad
*   **Menú Móvil:** El Drawer de navegación debe abrir/cerrar suavemente y tener un backdrop oscuro que cierre al clic.
*   **Tablas:** Las tablas de Admin deben colapsar a tarjetas ("Stacked view") o permitir scroll horizontal en móviles sin romper el layout.

### 3.3 Accesibilidad (A11y)
*   **Contraste:** Verificar textos `text-slate-400` sobre blanco. Si es ilegible, ajustar a `text-slate-500`.
*   **Teclado:** ¿Se puede navegar el Player (Play/Pause, Siguiente Lección) usando solo Tab y Enter?
*   **Etiquetas:** Botones de iconos (ej. "Cerrar", "Menú") deben tener `aria-label`.

---

## 4. AUDITORÍA DE INTERACTIVIDAD Y FEEDBACK

*   **Toasts (Notificaciones):**
    *   Disparar acciones de éxito y error.
    *   Verificar animación de entrada (slide-in) y salida (fade-out).
    *   Asegurar que no cubran elementos críticos de navegación en móvil.
*   **Estados de Carga (`wire:loading`):**
    *   Cada botón que dispara una acción al servidor (Reservar, Guardar) debe mostrar un spinner o cambiar a estado "Procesando..." inmediatamente. **El usuario nunca debe dudar si hizo clic.**

---

## 5. REQUERIMIENTOS PARA GPT-5.1 (AUTOMATIZACIÓN E2E)

GPT-5.1 deberá crear/actualizar los scripts de prueba de navegador (Dusk o similar) para cubrir estos escenarios:

1.  **`tests/Browser/StudentFlowTest.php`**:
    *   Login como `student.pending@` -> Verificar redirección/aviso.
    *   Login como `student.paid@` -> Navegar a Player -> Verificar carga de video.
2.  **`tests/Browser/CheckoutFlowTest.php`**:
    *   Simular compra de Pack -> Verificar actualización de UI en Header (créditos disponibles).
3.  **`tests/Browser/AdminDashboardTest.php`**:
    *   Verificar presencia de gráficos y contadores animados.
    *   Verificar responsividad del menú lateral en viewport móvil (375px).

---

## 6. CRITERIOS DE APROBACIÓN UX

| Criterio | Estándar |
| :--- | :--- |
| **Bloqueos Críticos** | 0 Flujos rotos (pantallas blancas, botones muertos). |
| **Consistencia Visual** | 95% de las vistas usan tokens de diseño UIX 2030. |
| **Responsividad** | Usable en dispositivos de 360px de ancho. |
| **Feedback** | < 100ms de latencia visual al hacer clic (estado loading). |

---

[TURNO-GEMINI-AUDIT-FINALIZADO]

