# Player Signals & QA Checklist

Este playbook resume cómo instrumentamos el Player UIX 2030 y qué debe validar QA/UI para asegurar que las señales (prácticas, packs, recursos, banners) se registren correctamente.

## 1. Eventos disponibles
Los eventos se envían a `POST /{locale}/api/player/events` y quedan en `video_player_events`:

| Event             | Descripción | Metadata clave |
|-------------------|-------------|----------------|
| `progress_tick`   | Pulso cada 5 s (se origina en `/api/video/progress`). | `playback_seconds`, `watched_seconds`, `provider`. |
| `play` / `pause`  | Cambio de estado en YouTube/Vimeo/Cloudflare. | `playback_seconds`. |
| `seek`            | Usuario usa banner “Retoma” o scrub (UI/CTA). | `metadata.source` = `ui`, `return_hint`, `scrub`. |
| `banner_view` / `banner_click` | Banner “Retomar desde…”. | `metadata.banner`. |
| `cta_view` / `cta_click` | Cualquier CTA contextual (práctica, pack, recurso). | `metadata.type`, `practice_id` / `pack_id`, `origin` (`highlight`, `secondary`, `practice_card`, etc.). |
| `resource_click`  | Enlaces genéricos del contenido estático. | `metadata.type`. |

Los eventos se exponen en DataPorter (`video_player_events`) y pueden filtrarse por `event`, `lesson_id` y ventana temporal.

## 2. `window.playerSignals`
En la vista del player existe un bus ligero:

```js
window.playerSignals.emit('cta_view', { metadata: { type: 'practice', practice_id: 123 } });
window.playerSignals.emitOnce('cta_practice_view_123', 'cta_view', { ... }); // evita duplicados.
```

Los componentes Alpine usan `emitOnce` al montar (impresión) y `emit` al hacer clic. Si el reproductor aún no expuso su `emitImpl`, los eventos quedan en cola y se despachan cuando llega el hand-shake (`registerGlobalEmitter`).

## 3. Checklist de QA
1. **Práctica vinculada**  
   - Abrir player con `practiceCta`. Confirmar en Network tab la petición `POST /api/player/events` con `cta_view` + `practice_id`.  
   - Click en “Reservar en Discord” → registrar `cta_click` con `action: reserve`.
2. **Pack recomendado**  
   - Player sin práctica, pero con pack. Debe emitir `cta_view` (`type: pack`) al cargar la tarjeta verde.  
   - Botón principal envía `cta_click` con `owned: false/true`.
3. **Banner “Retoma desde…”**  
   - Aparece cuando corresponde y dispara `banner_view`.  
   - Al pulsar “Volver ahora” se envía `banner_click` + `seek` (`source: return_hint`).
4. **CTA highlight**  
   - Vista de highlight (práctica/pack/recurso) genera `cta_view`.  
   - Acciones disparan `cta_click` con `origin: highlight`.
5. **Recursos secundarios**  
   - Las tarjetas “Abrir recurso” (cuando no hay highlight) y los enlaces de lecciones estáticas generan `cta_view`/`cta_click`.
6. **Controles del reproductor**  
   - Play/Pause/Seek manual en YouTube, Vimeo y Cloudflare crean eventos `play/pause/seek` con `provider` correcto y `playback_seconds` razonable.

> Recomendación: usar DataPorter (dataset `video_player_events`) para exportar en CSV/JSON tras las pruebas y adjuntar al reporte de QA.

