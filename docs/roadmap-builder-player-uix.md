## Roadmap UIX 2030 — Builder (Paso 10) & Player (Paso 11)

Última actualización: 16-nov-2025

### Objetivos generales
- Subir el builder y el player al estándar **UIX 2030** descrito en el blueprint: claridad radical, microinteracciones suaves, animaciones de celebración y visualizaciones de progreso que incentiven la acción.
- Cerrar el loop estudio ↔ práctica, mostrando indicadores contextuales (packs, Discord, CTAs) en el builder y en el reproductor.

---

### A. Builder Drag & Drop (CourseBuilder)

1. **HUD de progreso y bloqueos**
   - Nuevos contadores dinámicos por capítulo: lecciones, bloqueos activos, horas estimadas.
   - Barra mini–heatmap basada en `estimated_minutes` para visualizar la carga.

2. **Panel de detalle enfocada (“Focus Panel”)**
   - Propiedades Livewire: `$selectedLesson`, `$selectedChapter`, métodos `selectLesson()` / `closeFocus()`.
   - Panel lateral fijo con pestañas: _Contenido_, _Configuración_, _Práctica_, _Gamificación_.
   - Quick actions (duplicar, convertir tipo, mover a otro capítulo) con confirmaciones micro-animadas.

3. **Microinteracciones**
   - Hook `builder:celebrate` con confetti al guardar lección / capítulo.
   - Estados `data-[state=saving]`, `data-[dragging]` para animaciones CSS (scale, shadow).
   - Toasts stackeables (ya existe base) con iconos contextuales según variante.

4. **Resumen de práctica y packs**
   - En cada lección mostrar chips “Discord Practice” / “Pack requerido”.
   - Botón rápido para abrir `PracticePackagesManager` o Planner.

5. **Accesibilidad**
   - Focus visible en drag handles.
   - Atajos de teclado (set mínimo): `N` = nuevo capítulo, `Ctrl+S` = guardar lección seleccionada.

6. **Tareas técnicas**
   - Extender `refreshState()` para calcular métricas por capítulo.
   - Nueva propiedad `public array $metrics`.
   - Componente Alpine para focus panel (transiciones 200 ms).
   - Hook JS para escuchar `builder:select` y aplicar focus states.

---

### B. Player (Paso 11)

1. **Barra de progreso enriquecida**
   - `VideoHeatmapSegment` → normalizar buckets (0–1) y renderizar gradiente (`heatmap` property).
   - Marcadores de capítulos y tareas (badges) sobre la barra.

2. **Panel de insights**
   - Mostrar `% visto`, `tiempo restante`, `streak` si aplica.
   - Botón “Volver a donde ibas” cuando se detecten saltos >15 s.

3. **CTAs contextuales**
   - Tarjeta “Siguiente acción” que varía según tipo de lección:
     - Video: sugerir práctica / pack.
     - Tarea: mostrar estado y link directo a feedback.
     - Quiz: micro celebraciones al aprobar.

4. **Animaciones y microfeedback**
   - `dispatchBrowserEvent('player:progress-tick', { percent })` para animar barra y heatmap.
   - `player:celebrate` al superar 90 % del video o al desbloquear la lección.
   - Motion-safe fallbacks (fade only) si `prefers-reduced-motion`.

5. **Tecnología**
   - Nuevo método `loadHeatmap()` (Player) que agrupa `VideoHeatmapSegment::whereLesson`.
   - Nuevas props `public array $heatmap = []; public float $progressPercent`.
   - Script JS actualizado para:
     - Sincronizar heatmap bar (CSS `--heat-X`).
     - Enviar `player:progress` eventos a Livewire (para CTA adaptativa).

6. **Acciones pendientes**
   - Crear componentes Blade parciales (`player.partials.timeline`, `player.partials.practice-cta`).
   - Tests Feature para validar heatmap data y focus panel toggles.

---

### C. Entregables
- Código Livewire/Blade/JS actualizado.
- Estilos (Tailwind) y variables CSS (`--timeline-glow`, `--heatmap-color`).
- Nuevos strings i18n para paneles y toasts.
- Tests (Feature) para:
  - Builder: selección de lección, guardado con focus panel.
  - Player: visualización heatmap cuando existen segmentos.
- Documentación breve (`docs/ui-playbook.md`) explicando los patrones.

---

### Secuencia recomendada
1. Implementar métricas + focus panel en builder (sin animaciones).
2. Integrar microinteracciones y atajos.
3. Player: cargar heatmap + progresos.
4. Player: CTAs contextuales y celebraciones.
5. Ajustar estilos, i18n, tests.

