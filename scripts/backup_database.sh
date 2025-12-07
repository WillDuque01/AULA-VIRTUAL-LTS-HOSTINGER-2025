#!/bin/bash
# ============================================================================
# Script de Backup de Base de Datos - Academia Virtual LTS
# Autor: Opus 4.5 (Turno 28)
# Fecha: 07-dic-2025
# ============================================================================

# Configuración
APP_DIR="/var/www/app.letstalkspanish.io"
BACKUP_DIR="$APP_DIR/storage/backups"
RETENTION_DAYS=7

# Cargar credenciales desde .env
source "$APP_DIR/.env" 2>/dev/null || {
    echo "ERROR: No se pudo cargar .env"
    exit 1
}

# Variables de fecha
DATE=$(date +%Y-%m-%d_%H-%M-%S)
BACKUP_FILE="$BACKUP_DIR/lts_academy_$DATE.sql.gz"

# Crear directorio si no existe
mkdir -p "$BACKUP_DIR"

# Ejecutar backup
echo "[$(date)] Iniciando backup de base de datos..."
mysqldump -h "$DB_HOST" -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" \
    --single-transaction \
    --routines \
    --triggers \
    --add-drop-table \
    2>/dev/null | gzip > "$BACKUP_FILE"

if [ $? -eq 0 ]; then
    SIZE=$(ls -lh "$BACKUP_FILE" | awk '{print $5}')
    echo "[$(date)] ✅ Backup completado: $BACKUP_FILE ($SIZE)"
    
    # Limpiar backups antiguos
    find "$BACKUP_DIR" -name "lts_academy_*.sql.gz" -mtime +$RETENTION_DAYS -delete
    DELETED=$(find "$BACKUP_DIR" -name "lts_academy_*.sql.gz" -mtime +$RETENTION_DAYS 2>/dev/null | wc -l)
    echo "[$(date)] 🗑️ Limpieza: $DELETED backups antiguos eliminados"
else
    echo "[$(date)] ❌ ERROR: Falló el backup"
    exit 1
fi

# Listar backups actuales
echo ""
echo "📁 Backups disponibles:"
ls -lh "$BACKUP_DIR"/*.sql.gz 2>/dev/null || echo "  (ninguno)"

