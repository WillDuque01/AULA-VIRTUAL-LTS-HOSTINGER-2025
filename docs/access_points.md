# Accesos y rutas operativas (actualizado 01-dic-2025)

## Repositorio GitHub

- **Repositorio principal (`origin`)**
  - URL: `https://github.com/WillDuque01/AULA-VIRTUAL-LTS-HOSTINGER-2025.git`
  - Rama por defecto: `main`
  - Clonado rápido:  
    `git clone https://github.com/WillDuque01/AULA-VIRTUAL-LTS-HOSTINGER-2025.git lms`
  - Push/pull autenticados con el token/usuario de GitHub del propietario.
- **Repositorio de referencia (`source`)**
  - URL: `https://github.com/WillDuque01/lms-espanol.git`
  - Uso: respaldo histórico y comparación rápida (no se despliega desde aquí).
- **Hash verificado hoy**
  - `git rev-parse HEAD` y `git rev-parse origin/main` → `f8f85a438daeaa7db84deb73b196d159a6acdf18`
  - `git status -sb` limpio después de subir los cambios descritos.

## Servidor VPS / SSH

- **Servidor de producción**: `app.letstalkspanish.io`
- **IP pública**: `72.61.71.183`
- **Puerto**: `22`
- **Usuarios disponibles**:
  - `root` (se usa para tareas administrativas y despliegues manuales).
  - `deploy` (propietario de `/var/www/app.letstalkspanish.io`, usado por supervisor/cron).
- **Clave**: `C:\Users\Will Duque\.ssh\id_ed25519` (Win11, misma usada en los comandos anteriores).
- **Ejemplo de conexión actual**:
  ```
  ssh -i "C:\Users\Will Duque\.ssh\id_ed25519" root@72.61.71.183
  ```
- **Ruta del proyecto en producción**: `/var/www/app.letstalkspanish.io`
  - El árbol contiene el código completo, pero **no** se versiona `.git` por motivos de seguridad.
  - Para sincronizar archivos se usan copias directas (`scp`/`rsync`). Tras subir cambios en PHP/Blade conviene ejecutar:
    ```
    php artisan optimize:clear
    php artisan config:cache
    ```
  - Comandos de verificación ejecutados hoy:  
    `supervisorctl status lts-queue` · `tail -5 /var/log/cron-lts.log` · `curl -sI https://app.letstalkspanish.io/es/login | head -10`

## Comprobación de paridad (Local ↔ VPS)

- Se subieron las actualizaciones más recientes (`routes/web.php`, `app/Providers/AppServiceProvider.php`, `docs/hostinger_deployment_lessons.md`) vía `scp`.
- Hashes SHA256 coinciden en ambas ubicaciones:
  - `routes/web.php` → `13a448e4f52f1338b33c13eea51a0afb7be0371a9d4ec30e443ae2a076860d4a`
  - `app/Providers/AppServiceProvider.php` → `6e7bac168b6af566622399eba8605d0df464690b2a777bba74c676d50685ad5a`
  - `docs/hostinger_deployment_lessons.md` → `240d57fc52f1d3759bc0396f2d8126f94cdd22de0c702e7d5335b4a086e562d6`
- Conclusión: la instancia desplegada coincide con el repositorio local y con el último commit presente en GitHub (`origin/main`).

