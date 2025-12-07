# 📦 Guía de Instalación - Academia Virtual LTS

## Pre-requisitos del Servidor

| Componente | Versión Mínima |
|------------|----------------|
| PHP | 8.2+ |
| MySQL/MariaDB | 8.0+ / 10.6+ |
| Node.js | 18+ |
| Composer | 2.0+ |
| Nginx/Apache | 1.18+ / 2.4+ |
| Supervisor | 4.0+ |

### Extensiones PHP Requeridas
```
php-fpm php-mysql php-xml php-mbstring php-curl php-zip php-gd php-bcmath php-redis
```

---

## Paso 1: Clonar Repositorio

```bash
cd /var/www
git clone https://github.com/WillDuque01/AULA-VIRTUAL-LTS-HOSTINGER-2025.git academy
cd academy/lms
```

---

## Paso 2: Instalar Dependencias

```bash
# Backend
composer install --no-dev --optimize-autoloader

# Frontend
npm ci
npm run build
```

---

## Paso 3: Configurar Entorno

```bash
# Copiar y editar .env
cp .env.example .env
nano .env
```

### Variables Críticas (.env)

```env
APP_NAME="Mi Academia"
APP_URL=https://mi-academia.com
APP_KEY=  # Se generará automáticamente

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mi_academia
DB_USERNAME=mi_usuario
DB_PASSWORD=mi_password_seguro

# Integraciones Opcionales
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
DISCORD_WEBHOOK_URL=
MAKE_WEBHOOK_URL=
```

---

## Paso 4: Generar Key y Migrar

```bash
php artisan key:generate
php artisan migrate --seed --force
```

---

## Paso 5: Configurar Permisos

```bash
chown -R www-data:www-data /var/www/academy
chmod -R 755 /var/www/academy
chmod -R 775 /var/www/academy/lms/storage
chmod -R 775 /var/www/academy/lms/bootstrap/cache
```

---

## Paso 6: Configurar Nginx

```nginx
server {
    listen 80;
    server_name mi-academia.com;
    root /var/www/academy/lms/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## Paso 7: Configurar Supervisor (Colas)

Crear `/etc/supervisor/conf.d/academy-worker.conf`:

```ini
[program:academy-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/academy/lms/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/academy/lms/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
supervisorctl reread
supervisorctl update
supervisorctl start academy-worker:*
```

---

## Paso 8: Configurar Cron

```bash
crontab -e
```

Agregar:
```
* * * * * cd /var/www/academy/lms && php artisan schedule:run >> /dev/null 2>&1
0 3 * * * /var/www/academy/lms/scripts/backup_database.sh >> /var/log/academy-backup.log 2>&1
```

---

## Paso 9: Caché de Producción

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache
```

---

## Paso 10: Configurar SSL (Let's Encrypt)

```bash
apt install certbot python3-certbot-nginx -y
certbot --nginx -d mi-academia.com
```

---

## Verificación Final

```bash
# Verificar servicios
systemctl status nginx php8.2-fpm mysql supervisor

# Verificar colas
supervisorctl status

# Verificar cron
crontab -l

# Prueba de acceso
curl -sI https://mi-academia.com/es/login | head -5
```

---

## Usuarios de Prueba (Seeders)

| Email | Rol | Password |
|-------|-----|----------|
| academy@letstalkspanish.io | Admin | AuditorQA2025! |
| teacher.admin.qa@letstalkspanish.io | Teacher Admin | AuditorQA2025! |
| teacher.qa@letstalkspanish.io | Teacher | AuditorQA2025! |
| student.paid@letstalkspanish.io | Student | AuditorQA2025! |

---

## Comandos Útiles

```bash
# Resetear datos de prueba
php artisan academy:reset-demo --force

# Backup manual
/var/www/academy/lms/scripts/backup_database.sh

# Limpiar cachés
php artisan optimize:clear

# Ver logs de colas
tail -f /var/www/academy/lms/storage/logs/worker.log
```

---

## Troubleshooting

### Error 500 al cargar
```bash
tail -50 /var/log/nginx/error.log
tail -50 /var/www/academy/lms/storage/logs/laravel.log
```

### Permisos incorrectos
```bash
chown -R www-data:www-data /var/www/academy
find /var/www/academy -type d -exec chmod 755 {} \;
find /var/www/academy -type f -exec chmod 644 {} \;
chmod -R 775 /var/www/academy/lms/storage bootstrap/cache
```

### Assets no cargan (404)
```bash
npm run build
php artisan view:clear
```

---

**Documentación generada por Opus 4.5 - Turno 28**

