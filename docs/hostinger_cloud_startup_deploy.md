# Guía de despliegue en Hostinger Cloud Startup

Esta guía resume, paso a paso, cómo montar el LMS en un plan **Cloud Startup** de Hostinger sin asumir conocimientos previos. Incluye la preparación del servidor, la carga del proyecto y la creación de todas las credenciales necesarias.

---

## 1. Preparar el entorno en Hostinger

1. **Contrata y accede al plan** Cloud Startup desde [hPanel](https://hpanel.hostinger.com/).
2. **Asocia un dominio**:
   - En hPanel → *Sitios web* → *Crear o agregar sitio*.
   - Si es un dominio externo, apunta los DNS a Hostinger y espera la propagación.
3. **Activa SSL** para el dominio (en *SSL* → *Activar* → opcionalmente marca “Forzar HTTPS”).

---

## 2. Estructura recomendada para Laravel

Hostinger publica automáticamente el contenido de `public_html/`. Para Laravel:

1. Sube el proyecto completo a una carpeta, por ejemplo `~/lms/`, fuera de `public_html`.
2. Dentro de `public_html/` deja sólo los archivos de `lms/public/` y ajusta `index.php` para que apunte a `../lms/bootstrap/app.php`.

---

## 3. Credenciales necesarias y cómo crearlas

### 3.1. Acceso SFTP / SSH

1. hPanel → *Acceso SSH*.
2. Copia los datos:
   - **host** (p. ej. `ssh.hostinger.com`).
   - **puerto** (suele ser `65002`, confirma en pantalla).
   - **usuario** (el del hosting o crea uno nuevo).
   - **contraseña** (puedes cambiarla en la misma sección).
3. Opcional: genera una clave SSH y súbela en *Claves SSH* si prefieres autenticación por llave.

### 3.2. Base de datos MySQL

1. hPanel → *Bases de datos* → *MySQL* → *Crear base de datos*.
2. Define:
   - Nombre de la base (`lms_db` por ejemplo).
   - Usuario (`lms_user`) y contraseña segura.
3. Anota estos datos + el **host** (normalmente `localhost`).

### 3.3. Credenciales para correos (opcional)

1. hPanel → *Email* → *Agregar cuenta*.
2. Tienes hasta 10 buzones gratuitos por sitio durante 1 año; configura usuario y clave.

### 3.4. Variables para GitHub Actions (si usas el deploy automático)

En tu repositorio de GitHub, ve a *Settings → Secrets and variables → Actions → New repository secret* y agrega:

| Secreto | Valor |
| ------- | ----- |
| `HOSTINGER_SFTP_SERVER` | Host de SFTP (ej. `ssh.hostinger.com`) |
| `HOSTINGER_SFTP_PORT` | Puerto de SFTP (ej. `65002`) |
| `HOSTINGER_SFTP_USERNAME` | Usuario SFTP |
| `HOSTINGER_SFTP_PASSWORD` | Contraseña SFTP |
| `HOSTINGER_PATH` | Ruta absoluta (ej. `/home/u12345678`) |
| `HOSTINGER_SSH_HOST/PORT/USER/PASSWORD` | Para el paso `ssh-action` si quieres ejecutar comandos remotos |
| `HOSTINGER_APP_URL` | URL pública (https://tudominio.com) para el smoke test |
| `SLACK_WEBHOOK_URL` | Opcional, para notificaciones |

---

## 4. Cargar el proyecto

### Método rápido (SFTP)

1. En tu PC, ejecuta `npm run build` para generar assets.
2. Comprime el proyecto en `lms.zip` (excluye `node_modules` y `vendor` si quieres reducir tamaño).
3. Conecta FileZilla vía SFTP usando las credenciales del punto 3.1.
4. Sube `lms.zip` a la raíz del hosting y descomprímelo desde el Administrador de archivos en `~/lms/`.

### Método automatizado (GitHub Actions)

1. Configura los secretos (punto 3.4).
2. Cada `git push main` activará `deploy.yml`, que sube archivos, corre `composer install` y ejecuta migraciones en Hostinger.

---

## 5. Instalar dependencias en el servidor

1. Conéctate por SSH:
   ```bash
   ssh -p 65002 usuario@ssh.hostinger.com
   ```
2. Dentro de la carpeta del proyecto:
   ```bash
   cd ~/lms
   composer install --no-dev --optimize-autoloader
   npm install && npm run build   # si no subiste los assets listos
   ```
3. Copia `.env.example` a `.env` y configura:
   - `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://tudominio.com`.
   - Credenciales de base de datos.
   - SMTP si usarás correo.
4. Genera la clave:
   ```bash
   php artisan key:generate
   ```

---

## 6. Migraciones y seeding

Ejecuta:

```bash
php artisan migrate --force
php artisan db:seed --force   # sólo si necesitas datos iniciales
```

Revisa `storage/logs/laravel.log` si aparece algún error.

---

## 7. Publicar la carpeta `public/`

1. Elimina cualquier archivo por defecto en `public_html/`.
2. Copia el contenido de `~/lms/public/` dentro de `public_html/`.
3. Edita `public_html/index.php` para referenciar la carpeta superior:
   ```php
   require __DIR__.'/../lms/vendor/autoload.php';
   $app = require_once __DIR__.'/../lms/bootstrap/app.php';
   ```

---

## 8. Caches, cron y tareas finales

1. Borra cualquier caché previa y ejecútalas de nuevo (en orden):
   ```bash
   rm -f bootstrap/cache/*.php
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan optimize
   ```
2. Configura un cron en hPanel → *Cron Jobs*:
   ```
   php /home/u12345678/lms/artisan schedule:run
   ```
   Ajusta la ruta con tu usuario real.
3. Revisa la web en tu navegador. Si algo falla, consulta `storage/logs/laravel.log`.

---

## 9. Resumen rápido de credenciales necesarias

| Uso | Dónde se crea | Datos requeridos |
| --- | ------------- | ---------------- |
| SFTP / SSH | hPanel → Acceso SSH | host, puerto, usuario, contraseña o clave |
| Base de datos MySQL | hPanel → Bases de datos | nombre DB, usuario, contraseña, host |
| Correo (opcional) | hPanel → Email | dirección y contraseña de cada buzón |
| Secrets para GitHub Actions | GitHub → Settings → Secrets | SFTP, SSH, rutas, URL pública y webhook Slack |

Con estos pasos documentados podrás repetir el despliegue o delegarlo a cualquier miembro del equipo sin perder detalle.


