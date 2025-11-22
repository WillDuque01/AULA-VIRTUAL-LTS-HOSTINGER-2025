# Concepto: Instalador automático para Academias en Hostinger

## Objetivo
Permitir que cualquier persona despliegue una nueva instancia de la academia (LMS) en un plan **Cloud Startup** de Hostinger simplemente subiendo un paquete `.zip` y pulsando un botón “Desplegar Academia” en el navegador, emulando la experiencia de instalación de WordPress.

## Flujo propuesto
1. **Paquete ZIP**:
   - Contiene el código del LMS (o un script que lo descarga desde GitHub).
   - Incluye un mini instalador (`installer.php` + assets) que se ubica temporalmente en `public_html/`.

2. **Ejecución web**:
   - Al ingresar a `https://nuevo-dominio.com/`, el usuario ve un formulario con los datos mínimos:
     - Dominio / URL final.
     - Credenciales de base de datos (host, nombre, usuario, contraseña).
     - Credenciales SMTP (opcional).
     - Usuario administrador inicial.
   - Botón “Desplegar Academia” desencadena las acciones.

3. **Acciones automatizadas** (ejecutadas por el instalador):
   - Validar requisitos (versión PHP, extensiones, permisos).
   - Subir/extraer el proyecto en `~/academia-XXXX`.
   - Copiar los archivos públicos hacia `public_html/` y apuntar `index.php`.
   - Generar y rellenar `.env`.
   - Ejecutar `composer install --no-dev`, `php artisan migrate --force`, `php artisan db:seed`.
   - Configurar caches (`config:cache`, `route:cache`, etc.).
   - Programar `cron` para `artisan schedule:run` (si Hostinger expone API; de lo contrario se guía al usuario).
   - Registrar un log (`storage/logs/installer.log`) y mostrar progreso en pantalla.
   - Autodestruir el instalador y renombrar la carpeta inicial como respaldo.

4. **Post-instalación**:
   - Mostrar resumen con credenciales creadas y recordatorio de borrar el ZIP inicial si sigue presente.
   - Opcional: permitir “clonar” la academia actual generando otro ZIP con la configuración ya preestablecida.

## Riesgos y dependencias
- El instalador necesita ejecutar comandos del sistema (`shell_exec`, `proc_open`). Debe confirmarse que el plan Cloud Startup lo permita.
- Los procesos largos (Composer, migraciones) pueden exceder los límites de tiempo de PHP; se requiere evaluar `max_execution_time` y, de ser necesario, dividir tareas o usar colas.
- Seguridad: el instalador debe autenticarse (por ejemplo, pidiendo un “token” inicial) y autodestruirse al finalizar para evitar que terceros lo ejecuten de nuevo.
- Gestión de múltiples academias: si se quieren alojar varias bajo un mismo plan, se necesitan scripts adicionales para crear subdominios y aislar instancias (posiblemente vía API o procedimientos manuales documentados).

## Próximos pasos (investigación)
1. **Blueprint oficial de Hostinger**:
   - Revisar documentación de Cloud Startup sobre límites de `shell_exec`, extensiones disponibles, límites de memoria/tiempo y política de procesos persistentes.
   - Confirmar si existe API para crear cron jobs y gestionar subdominios desde scripts.
2. **Prototipo**:
   - Crear `installer.php` con un `wizard` paso a paso.
   - Implementar un “modo simulación” que corra en local para validar la secuencia.
3. **Hardening**:
   - Añadir autenticación básica o un token único para acceder al instalador.
   - Firmar el paquete ZIP y verificar integridad antes de extraerlo.

## Hallazgos actuales sobre Hostinger Cloud Startup

| Tema | Estado |
| ---- | ------ |
| SSH / SFTP | Disponible desde hPanel, sin root. Provee shell jail con `git`, `composer`, `npm`. Autenticación por contraseña o clave. |
| Comandos del sistema | `shell_exec`/`proc_open` permitidos en Cloud; Hostinger puede cerrar procesos si exceden CPU/RAM. |
| Límites PHP | `max_execution_time` ~300 s en web; desde CLI no aplica, pero hay políticas anti abuso. |
| Cron jobs | Solo configurables desde hPanel; intervalo mínimo 1 minuto. Sin API pública. |
| Directorios / permisos | Control total dentro de `/home/<usuario>/`. Se puede crear carpeta superior a `public_html`. Hasta 2 millones de inodos. |
| Composer / Node | Composer accesible mediante `php composer.phar`; Node disponible para hasta 5 apps front-end. |
| MySQL | Host `localhost`, gestión de usuarios/bases únicamente en hPanel. |
| API hPanel | No existe API oficial para crear sitios/subdominios/cron; cualquier automatización debe guiar al usuario. |

## Roadmap propuesto (Prototipo v0.1)

1. **Fase de descubrimiento (1 semana)**
   - Validar in situ los hallazgos: ejecutar `shell_exec`, `composer`, limites de tiempo y uso de recursos en una cuenta real.
   - Definir parámetros que el instalador debe solicitar (DB, SMTP, admin).
   - Diseñar estructura de carpetas (`/installer`, `/lms`, `public_html`).

2. **Fase de prototipo CLI (2 semanas)**
   - Implementar script PHP/CLI que realice toda la secuencia (descarga, `.env`, `composer`, migraciones, caches).
   - Añadir logging detallado y manejo de errores.
   - Ejecutar en entorno de staging de Hostinger para medir tiempos reales.

3. **Fase de interfaz web (1 semana)**
   - Convertir el script en un `installer.php` con wizard (HTML + Alpine/Livewire ligero).
   - Añadir validaciones de formulario y paso intermedio de confirmación.
   - Implementar autodestrucción y protección por token.

4. **Fase de hardening y empaquetado (1 semana)**
   - Firmar el ZIP resultante, documentar procedimiento de subida.
   - Incluir chequeos de integridad, recordatorios de borrar instalador.
   - Redactar guía para crear cron job y base de datos manualmente (pasos que el script no puede automatizar).

5. **Fase piloto (1 semana)**
   - Desplegar 2–3 academias reales usando únicamente el instalador.
   - Recoger métricas (tiempo total, puntos de fallo) y retroalimentación.

Esta documentación servirá como base para la investigación profunda de Hostinger y el desarrollo del instalador automático.


