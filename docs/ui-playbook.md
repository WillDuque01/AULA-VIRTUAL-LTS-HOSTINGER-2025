# UI Playbook — LMS UIX 2030

Este playbook documenta los patrones visuales e interactivos que ya están implementados en el Course Builder, dashboards y módulos de prácticas. Sirve para mantener consistencia cuando añadamos nuevas pantallas o evoluciones UIX 2030.

---

## 1. Principios clave

| Principio | Descripción | Ejemplo actual |
|-----------|-------------|----------------|
| **Claridad progresiva** | Mostrar solo la información necesaria para editar/actuar. Profundizar con paneles laterales o chips expandibles. | Focus panel del builder con pestañas (`Contenido`, `Config`, `Práctica`, `Gamificación`). |
| **Estados explícitos** | Cada recurso (capítulo, lección, pack) expone su estado (`pending`, `published`, `rejected`) mediante chips con contraste suficiente. | Chips verdes/ámbar/rosas en cards de capítulo/lección y dashboard docente. |
| **Microinteracciones accesibles** | Animaciones < 240 ms, siempre respetando `prefers-reduced-motion`. Tooltips y atajos deben poder replicarse vía clic. | Botones del builder tienen `hover`, `focus`, `aria-label` y atajos documentados. |
| **Contexto accionable** | Cada dato crítico acompaña accesos directos (planner, packs, DataPorter). | Chips de prácticas en lecciones muestran CTA “Abrir planner” y “Gestionar packs”. |

---

## 2. Tokens visuales

| Token | Valor | Uso |
|-------|-------|-----|
| **Radio base** | `1rem` (`rounded-2xl`) | Tarjetas principales, chips de status. |
| **Color éxito** | `#059669` (`text-emerald-700`, `bg-emerald-50`) | Estados `published`, packs activos, métricas positivas. |
| **Color warning** | `#d97706` (`text-amber-700`, `bg-amber-50`) | `pending`, recordatorios o cancelaciones tardías. |
| **Color error** | `#be123c` (`text-rose-700`, `bg-rose-50`) | `rejected`, alertas de backlog (DataPorter). |
| **Tipografía** | `Inter` / `Onest` (según entorno) con tracking positivo en headings | Headlines: `tracking-[0.3em]` en mayúsculas, cuerpo `text-sm`-`text-base`. |

> **Nota:** Todos los colores cumplen contraste AA sobre fondo blanco. Cuando se use `bg-amber-50/80`, el texto se mantiene en `text-amber-700`.

---

## 3. Patrones del Course Builder

### 3.1. Panel de métricas
- Tarjetas 3-col con gradientes suaves (`from-blue-50 to-white`).
- Contadores animados con Alpine (`animatedCount`), iniciados en `x-init="start()"`.
- Mensajes descriptivos en `text-xs` bajo cada contador.

### 3.2. Focus panel
- Tabs: `content`, `config`, `practice`, `gamification`.
- Estado actual se guarda en `$focusTab` (Livewire) y se refleja en `aria-selected`.
- Acciones rápidas:
  - `N`: nuevo capítulo.
  - `Ctrl/⌘ + S`: guardar lección enfocada.
  - `Shift + ?`: abrir panel de atajos.
  - `[`, `]`: cambiar pestaña previa/siguiente.
- Tooltip accesible: siempre acompañar `title` + `aria-label`.

### 3.3. Filtro por estado
- Filtro global antes del listado.
- Botones redondos (`rounded-full`) marcados con border primario cuando están activos.
- Lógica: muestra capítulos cuyo estado coincide **o** que contengan lecciones con ese estado.
- Etiquetas de estado:
  - `pending` → `border-amber-200 bg-amber-50 text-amber-700`.
  - `published` → `border-emerald-200 bg-emerald-50 text-emerald-700`.
  - `rejected` → `border-rose-200 bg-rose-50 text-rose-700`.

### 3.4. Chips de integraciones
- Prácticas Discord: icono `🎙️`, total y próxima fecha (`translatedFormat('d M H:i')`).
- Packs: icono `💼`, sesiones y precio.
- CTA complementario: enlace al planner / manager con ícono `↗`.

---

## 4. Dashboards (Admin, Teacher, DataPorter)

| Componente | Reglas |
|------------|--------|
| **Cards resumidas** | `border-slate-100`, `shadow-sm`, headings `tracking-[0.25em]`. |
| **Tablas resumen** | Para métricas Docentes: filas con `hover:bg-slate-50/60`, columnas centradas para totales, badges para tasas. |
| **Alertas** | Cuando se supera un threshold (ej. backlog telemetría), cambiar color del card completo y añadir texto de acción. |
| **Historiales** | Listas verticales, cada ítem con fecha (`diffForHumans()` o `format('d M H:i')`) y resúmenes en `text-[11px]`. |

---

## 5. Hotkeys & accesibilidad

- Siempre verificar que los atajos tengan alternativa visible (botón o menú).
- Ignorar combinaciones cuando el foco está en inputs o elementos `contenteditable`.
- Documentar atajos en panel dedicado (Shift+?).
- Enviar eventos Livewire (`$wire.call(...)`) desde Alpine para mantener lógica en el servidor.

---

## 6. Checklist UI al crear nuevas vistas

1. **Tipografía y espaciado** respetan el grid (padding `px-6 py-4` en cards principales).
2. **Estados**: cada recurso tiene chip y color consistente (`pending/published/rejected`).
3. **CTAs secundarios**: estilo pill con borde (`border-slate-200`), icono y flecha `→` o `↗`.
4. **Atajos**: si la acción se repite (guardar, crear, filtrar) evaluar atajo de teclado con documentación visible.
5. **Responsividad**: `flex-col md:flex-row`, `grid gap-3 md:grid-cols-2`, etc.
6. **Integraciones**: vincular guías/links relevantes (planner, DataPorter, help).

Cumplir estos puntos garantiza que nuevos módulos mantengan la UIX 2030 establecida en builder, dashboards y experiencias asociadas.


