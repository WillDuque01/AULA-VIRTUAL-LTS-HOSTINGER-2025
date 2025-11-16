# DataPorter Hub – Telemetría y exportes

Este documento describe la nueva capa de telemetría local y el módulo **DataPorter Hub**, disponible en `/{locale}/admin/data-porter`.

## 1. Objetivo
- Persistir eventos del reproductor (`video_player_events`) y snapshots agregados de estudiantes/profesores.
- Permitir descargas **CSV/JSON** filtradas por curso, lección, categoría o proveedor.
- Ofrecer importación controlada (CSV/JSON) para migrar snapshots históricos o traer datos de GA4/Mixpanel.
- Exponer un flujo compatible para Admins y Teacher Admins (estos últimos siempre deben filtrar por curso o lección).
- Mostrar estado de sincronización (último `telemetry:sync`, drivers activos, eventos pendientes) para diagnósticos rápidos.

## 2. Tablas nuevas
| Tabla | Descripción | Claves |
|-------|-------------|-------|
| `video_player_events` | Evento crudo del player (play, progreso, seek, CTA). | `user_id`, `lesson_id`, `course_id`, `event`, `provider`, `playback_seconds`, `metadata`, `recorded_at`, `synced_at`. |
| `student_activity_snapshots` | Snapshot agregado por estudiante (progreso, packs, reservas). | `user_id`, `course_id`, `lesson_id`, `practice_package_id`, `category`, `scope`, `value`, `payload`, `captured_at`. |
| `teacher_activity_snapshots` | Snapshot agregado por teacher admin (planner, packs, anuncios). | `teacher_id`, `course_id`, `lesson_id`, `category`, `scope`, `value`, `payload`, `captured_at`. |

## 3. TelemetryRecorder
Ubicación: `App\Support\Analytics\TelemetryRecorder`.

- `recordPlayerTick(VideoProgress $progress, array $data = [])`: guarda cada tick en `video_player_events`.
- `recordStudentSnapshot(int $userId, string $category, array $attributes = [])`: crea snapshot estudiante.
- `recordTeacherSnapshot(int $teacherId, string $category, array $attributes = [])`: crea snapshot teacher.

`VideoProgressController` ahora llama al recorder con cada POST `/api/video/progress`, alimentando la tabla de telemetría.

## 4. DataPorter Hub (Livewire)
Ruta: `/{locale}/admin/data-porter`  
Componente: `App\Livewire\Admin\DataPorterHub`.

### Dataset actuales
1. **Eventos del reproductor** (`video_player_events`): filtros por fecha, curso, lección, evento y proveedor.
2. **Snapshots de estudiantes** (`student_activity_snapshots`): filtros por fecha, curso, lección, pack y categoría. Importable.
3. **Snapshots de Teacher Admin** (`teacher_activity_snapshots`): filtros por fecha/curso/lección/categoría. Solo Admin (teacher_admin no puede exportar este dataset). Importable.
4. **(Próximos)** Dataset de prácticas/pedidos: utilizará snapshots `practice_reservation` y `practice_pack_purchase` como base para CSV/JSON específicos.

### Flujo de exportación
1. Seleccionar dataset y formato (CSV/JSON).
2. Aplicar filtros (Teacher Admin debe indicar `course_id` o `lesson_id`).
3. DataPorter genera URL firmada (`/admin/data-porter/export?signature=...`) y abre la descarga.
4. CSV contempla encabezados legibles y convierte `payload/metadata` a JSON inline; JSON stream mantiene arrays nativos.
5. **Estado de sincronización** (nuevo panel):
   - Muestra `events_pending` (`video_player_events` sin `synced_at`).
   - Indica si GA4/Mixpanel están habilitados (ícono verde/ámbar).
   - Botón `Ejecutar telemetry:sync` lanza el comando vía UI (requiere permiso `manage-settings`), registrando la última ejecución.

### Flujo de importación
1. Seleccionar dataset importable (por ahora snapshots de estudiantes/teachers).
2. Subir archivo `.csv` o `.json` con encabezados:  
   - Estudiantes: `user_id,course_id,lesson_id,practice_package_id,category,scope,value,payload,captured_at`
   - Teachers: `teacher_id,course_id,lesson_id,practice_package_id,category,scope,value,payload,captured_at`
3. DataPorter valida filas (IDs obligatorios) y delega en `TelemetryRecorder` la inserción.

## 5. Seguridad y roles
- Todas las descargas usan rutas firmadas (`URL::temporarySignedRoute`) con validez de 5 minutos.
- Teacher Admin comparte el hub con Admin, pero debe aplicar filtros de alcance (curso/lección) antes de exportar.
- Importaciones solo habilitadas para usuarios con `manage-settings`.

## 6. Drivers GA4 / Mixpanel
- **Config** (`config/telemetry.php`):  
  - GA4 → `GA4_ENABLED=true`, `GA4_MEASUREMENT_ID=G-XXXX`, `GA4_API_SECRET=...`.  
  - Mixpanel → `MIXPANEL_ENABLED=true`, `MIXPANEL_PROJECT_TOKEN=...`, `MIXPANEL_API_SECRET=opcional`.  
- **Servicio**: `App\Support\Telemetry\TelemetrySyncService` toma los drivers activos y marca los eventos (`synced_at`) tras el envío.
- **Command**: `php artisan telemetry:sync --limit=500` (registrado en `Schedule` cada hora) empuja lotes a GA4 (Measurement Protocol) y Mixpanel (`/track`). Si ningún driver está habilitado, el comando informa y no toca los registros.
- **Payload**: se envían `event`, `lesson_id`, `course_id`, `playback_seconds`, `context_tag`, `metadata` (CTA view/click, banner, etc.). En GA4 se usa `client_id=user_id.event_id`, mientras que en Mixpanel el `distinct_id` es `user_id` o `guest-{event}`.
- **Dashboard de estado** (Hub):
  - Última sincronización (timestamp y duración).
  - Logs compactos de los últimos 5 comandos (éxito/error).
  - Acción manual “Reintentar último lote” (reutiliza `TelemetrySyncService` con `limit` configurable).

## 7. Snapshots automáticos
- **Reservas Discord**: el listener `RecordPracticeReservationSnapshot` captura cada `DiscordPracticeReserved` y genera un snapshot `practice_reservation` con curso/lección y metadata (cohorte, inicio, estado de reserva).
- **Compras de packs**: `RecordPracticePackPurchaseSnapshot` escucha `PracticePackagePurchased` y guarda `practice_pack_purchase` incluyendo pack, sesiones, precio y orden (`order_id`, `paid_at`).
- Ambos usan `TelemetryRecorder`, por lo que las entradas aparecen al instante en DataPorter sin carga manual.

## 8. Próximos pasos
- Extender snapshots para consumos (sesiones gastadas) y solicitudes escaladas.
- Agregar dataset para prácticas Discord y pedidos de packs (fuente `practice_packages` / `DiscordPractice`).

