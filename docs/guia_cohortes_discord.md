# Guía operativa de cohortes (Discord Practices)

Esta guía describe cómo planificar, lanzar y dar seguimiento a las cohortes que utilizan el Planner de prácticas Discord + Practice Packs. Se apoya en las funciones existentes (`DiscordPracticePlanner`, `PracticePackagesManager`, snapshots DataPorter).

---

## 1. Preparativos

1. **Definir objetivo** (nivel, temática, sesiones por semana).
2. **Crear plantilla** en `DiscordPracticePlanner`:
   - Selecciona la lección base.
   - Configura slots (`weekday`, `time`) y duración.
   - Marca `requires_package` si la cohorte será cerrada.
   - Guarda como plantilla con nombre reconocible (`B2-Mañanas-INTENSIVO`).
3. **Pack asociado**:
   - Desde `PracticePackagesManager`, crea pack privado.
   - Define sesiones, precio y visibilidad.
   - Vincula el pack a la lección/cohort-label para CTA automático.

---

## 2. Lanzamiento de cohortes

1. En `DiscordPracticePlanner`, usa **"Aplicar plantilla"** o **"Programar serie"**:
   - Selecciona semanas (1-12) y fecha de inicio.
   - Verifica que ningún slot caiga en el pasado (el sistema ajusta a `now()+1h` si es necesario).
2. El planner envía el evento `DiscordPracticeScheduled`, disparando:
   - Notificación interna al docente.
   - Registro en outbox/integraciones (si se habilita).
3. **Checklist pre-lanzamiento**:

   | Paso | Responsable | Estado |
   |------|-------------|--------|
   | Validar cupos y `capacity` | Teacher Admin | ☐ |
   | Confirmar `discord_channel_url` | Docente | ☐ |
   | Publicar anuncio en comunidad | CM | ☐ |

---

## 3. Seguimiento operativo

| Indicador | Dónde se consulta | Notas |
|-----------|-------------------|-------|
| Reservas y ocupación | `Student > Prácticas` (chips) y snapshots `practice_reservation` | El builder también muestra totales/próxima fecha por lección. |
| Packs vendidos | `PracticePackagesManager` + dataset `practice_package_orders` | Teacher Admin puede exportar filtrando por curso/lección. |
| Solicitudes escaladas | Notificación + snapshot `practice_request_escalated` | Cuando la demanda supera `request_threshold`. |

---

## 4. Asistencia y cancelaciones

El comando `practices:sync-attendance` corre cada 30 min:

- Genera `practice_attendance` para reservas confirmadas + sesiones cumplidas.
- Registra `practice_cancellation` si la reserva se marca como cancelada dentro de la ventana `DISCORD_PRACTICES_LATE_CANCEL_MINUTES` (default 180).
- Marca la práctica como `completed` y almacena `attendance_synced_at` para evitar reprocesos.

**Recommended remediation**:
- Si el backlog de asistencia crece, ejecutar manualmente `php artisan practices:sync-attendance --limit=50`.

---

## 5. Retroalimentación y reporting

1. **Dashboards**:
   - Admin ve métricas en `Teacher Performance Report` (aprobación, tiempos, backlog).
   - Teacher Admin revisa el estado en `TeacherSubmissionsHub` y DataPorter.
2. **Snapshots DataPorter**:
   - `discord_practices`: exportar agenda por cohorte.
   - `student_activity_snapshots`: filtrar por `category = practice_attendance` o `practice_cancellation`.
3. **Cierre de cohorte**:
   - Actualizar documentación interna con aprendizajes.
   - Llenar la checklist de smoke (Hostinger) si hubo deploy con cambios relevantes.

---

## 6. Plantillas recomendadas

| Nombre | Slots | Notas |
|--------|-------|-------|
| `A2-Mañanas-Standard` | Lunes/Jueves 08:00 GMT-5, 60 min | Usa pack de 4 sesiones (`is_global = true`). |
| `B2-Night-INTENSIVO` | Lunes/Miércoles/Viernes 19:00 GMT-5, 45 min | Requiere pack privado; se recomienda serie de 6 semanas. |
| `Club Speaking Global` | Sábados 10:00 GMT-5, 90 min | Abierto, sin pack, `type = global`. |

> Ajusta `duration_minutes` y `capacity` según recursos docentes disponibles. Mantén `discord_channel_url` apuntando al hilo correspondiente para evitar fricción.


