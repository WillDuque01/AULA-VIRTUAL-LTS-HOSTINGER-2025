# Checklist de smoke tests — Hostinger

Desde noviembre 2025 el pipeline `deploy.yml` dispara automáticamente `smoke.yml` (manual, programado y encadenado vía `workflow_run`). Usa este checklist sólo como respaldo cuando se requiera verificación manual o para documentar incidencias detectadas por los workflows.

- `deploy.yml` envía un resumen al canal Slack configurado en `SLACK_WEBHOOK_URL`.
- `smoke.yml` notifica al mismo canal con el contexto (manual/programado/post-deploy) y enlace al run.

---

## 1. Preparación

| Paso | Responsable | Estado |
|------|-------------|--------|
| Verificar que `main` esté actualizado (`git pull origin main`). | DevOps | ☐ |
| Ejecutar `composer install --optimize-autoloader` y `npm run build`. | DevOps | ☐ |
| Limpiar cache previa (`php artisan cache:clear && php artisan config:clear`). | DevOps | ☐ |
| Subir artefactos por SFTP/SSH (Hostinger) o ejecutar workflow `deploy.yml`. | DevOps | ☐ |

---

## 2. Smoke funcional (máximo 15 minutos)

| Ítem | Comando / Ruta | Resultado |
|------|----------------|-----------|
| Migraciones aplicadas (`php artisan migrate --force`). | SSH | ☐ |
| Cache de rutas/config re-generada (`php artisan route:cache`, `config:cache`). | SSH | ☐ |
| **Login multirol**: Admin (`/es/admin/login`), Teacher (`/es/teacher/login`), Student (`/es/student/login`). | Navegador | ☐ |
| **Course Builder**: crear capítulo dummy, guardar lección con `Ctrl+S`, filtrar por `Pendiente`. | `/es/courses/{curso}/builder` | ☐ |
| **Planner Discord**: abrir `/es/professor/practices` y verificar que el calendario cargue eventos. | Browser | ☐ |
| **DataPorter**: exportar dataset `video_player_events` en CSV y verificar descarga. | `/es/admin/data-porter` | ☐ |
| **Player**: reproducir una lección, confirmar marcadores y bloqueo. | `/es/lessons/{lesson}/player` | ☐ |
| **Notificaciones**: enviar mensaje desde `Admin Messages` y confirmar entrega al destinatario. | `/es/admin/messages` | ☐ |

> Cualquier fallo debe registrarse en la tarjeta de despliegue y, si aplica, revertir o bloquear el release.

---

## 3. Validaciones de infraestructura

| Paso | Comando | Estado |
|------|---------|--------|
| Cron jobs activos (`crontab -l` debería incluir `artisan schedule:run`). | SSH | ☐ |
| Cola en ejecución (`php artisan queue:work --daemon` o supervisor). | SSH | ☐ |
| Storage simbólico (`php artisan storage:link`) existente. | SSH | ☐ |
| Verificar logs (`tail -f storage/logs/laravel.log`) por errores críticos. | SSH | ☐ |

---

## 4. Cierre

1. Adjuntar evidencia (capturas, outputs).
2. Marcar checklist como completado en la tarjeta.
3. Informar en el canal `#deployments` (o canal acordado) con:
   - Commit/tag desplegado.
   - Tiempo de ejecución.
   - Resultado smoke (OK o incidencias).

Cuando el pipeline `workflow_dispatch + smoke.yml` esté habilitado, esta lista servirá como base para automatizar los pasos.


