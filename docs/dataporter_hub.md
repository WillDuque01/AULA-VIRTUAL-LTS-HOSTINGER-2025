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
4. **Prácticas Discord** (`discord_practices`): sesiones planificadas con estado, capacidad, cohorte y paquete asociado. Teacher Admin debe indicar `course_id` o `lesson_id`.
5. **Pedidos de packs** (`practice_package_orders`): órdenes con estado, sesiones restantes, pago y docente creador. Teacher Admin debe filtrar por curso o lección.
6. **Propuestas docentes** (`teacher_submissions`): historial completo de módulos/lecciones/packs enviados. Admin y Teacher Admin pueden exportarlo; estos últimos deben indicar `course_id`.
7. **Asignaciones curso-docente** (`course_teacher_assignments`): estado actual del pivote `course_teacher` con trazabilidad del asignador. Requiere `course_id` para Teacher Admin.

### Flujo de exportación
1. Seleccionar dataset y formato (CSV/JSON).
2. Aplicar filtros (Teacher Admin debe indicar `course_id` o `lesson_id`).
3. DataPorter genera URL firmada (`/admin/data-porter/export?signature=...`) y abre la descarga.
4. CSV contempla encabezados legibles y convierte `payload/metadata` a JSON inline; JSON stream mantiene arrays nativos.
5. **Estado de sincronización** (panel ampliado):
   - Muestra `events_pending` (`video_player_events` sin `synced_at`) y la última sync en la zona horaria de la app.
   - Indica si GA4/Mixpanel están habilitados (ícono verde/ámbar) y cuántos drivers se ejecutaron.
   - Botón `Ejecutar telemetry:sync` lanza la sincronización desde el Hub (requiere permiso `manage-settings`).
   - Historial de las últimas 5 ejecuciones (`telemetry_sync_logs`) con estado, eventos procesados, duración y usuario que detonó la acción.

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
- **Consumo de sesiones**: `RecordPracticeSessionConsumedSnapshot` toma `PracticePackageSessionConsumed` y registra `practice_pack_consumption` con sesiones restantes, orden y paquete.
- **Solicitudes escaladas**: `RecordPracticeRequestEscalationSnapshot` captura `DiscordPracticeRequestEscalated` y crea snapshots `practice_request_escalated` en `teacher_activity_snapshots` con backlog pendiente y destinatarios.
- **Asistencia efectiva**: el comando `practices:sync-attendance` recorre las prácticas ya impartidas y emite `practice_attendance` por cada reserva confirmada.
- **Cancelaciones tardías**: el mismo comando registra `practice_cancellation` cuando la reserva se cancela dentro de la ventana configurada (`DISCORD_PRACTICES_LATE_CANCEL_MINUTES`), marcando el flag `late`.
- Todos usan `TelemetryRecorder`, por lo que las entradas aparecen al instante en DataPorter sin carga manual.

## 8. Monitoreo y alertas
- **Backlog de telemetría**: `telemetry:monitor-backlog` compara `video_player_events` sin sincronizar contra `TELEMETRY_ALERT_THRESHOLD` y envía `TelemetryBacklogAlertNotification` (mail + base) respetando `TELEMETRY_ALERT_COOLDOWN_MINUTES`.
- **Panel DataPorter**: el card de “Eventos pendientes” se ilumina cuando se supera el umbral y muestra el mensaje de acción inmediata.
- **Historial**: `telemetry_sync_logs` guarda estado/duración/driver/usuario por cada `telemetry:sync`, accesible desde el Hub.
- **Asistencia**: `practices:sync-attendance --limit=25` se ejecuta cada 30 minutos (scheduler) y deja constancia en snapshots para analytics.

## 9. Métricas externas
- Cada dataset permite exportar CSV/JSON. GA4 y Mixpanel siguen su driver respectivo (`telemetry:sync`).
- Los snapshots (`student_activity_snapshots` y `teacher_activity_snapshots`) pueden importarse vía CSV/JSON usando `TelemetryRecorder`.

