# Instalador CLI – Academia Hostinger

Este instalador es **independiente** del LMS. Se distribuye como script autónomo para preparar una academia en Hostinger Cloud Startup sin modificar el core del proyecto.

## Requisitos
- PHP 8.2+ con extensiones `zip`, `pdo_mysql`, `openssl`.
- Acceso al servidor (SSH) con permisos sobre `/home/<usuario>/`.
- Base de datos ya creada en hPanel (nombre, usuario, contraseña).
- Proyecto LMS empaquetado en `.zip` o accesible vía Git.

## Uso (CLI)
1. Subir este directorio `installer/` al servidor (idealmente fuera de `public_html/`).
2. Ejecutar:
   ```bash
   cd installer
   php install.php
   ```
3. Responder al formulario interactivo (ruta destino, URL, credenciales DB, SMTP opcional).
4. El instalador:
   - Descomprime/descarga el LMS.
   - Copia archivos públicos a `public_html`.
   - Genera `.env`, corre `composer install --no-dev`, migraciones y seeders.
   - Registra logs en `installer/logs/install.log`.
5. Al terminar, el instalador ofrece borrar sus archivos para evitar re-ejecuciones.

## Uso (Wizard web)
1. Copia el contenido de `installer/web/` dentro de `public_html/installer`.
2. (Opcional) Define un token en tu entorno: `export INSTALLER_TOKEN="tu_token_seguro"`.
3. Visita `https://tu-dominio.com/installer/`, introduce el token si se solicitó y completa el formulario.
4. Al pulsar “Desplegar academia”, el wizard ejecutará los mismos pasos que el CLI y mostrará el log en pantalla.
5. **Elimina la carpeta `installer/`** cuando verifiques que el sitio funciona para evitar accesos no autorizados.

## Notas
- El instalador **no configura cron jobs ni crea bases de datos** (Hostinger no provee API). Se proveen instrucciones para hacerlo manualmente.
- El script detecta si ya existe contenido y solicita confirmación antes de sobrescribir.
- Todo el código del LMS permanece intacto; este instalador sólo gestiona la copia y configuración inicial.


