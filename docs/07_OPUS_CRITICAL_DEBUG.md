# 07_OPUS_CRITICAL_DEBUG.md

## Reporte de Debug Crítico - Turno 7

**Agente**: Opus 4.5 (Ingeniero DevOps Forense)  
**Fecha**: 06-dic-2025 17:49 UTC  
**Incidente**: Error 404 en JavaScript principal

---

## 1. DIAGNÓSTICO DEL FALLO

### Síntoma Reportado
El usuario verificó manualmente que el sitio no cargaba JavaScript:
```
GET /build/assets/app-DFCule9_.js → 404 Not Found
```

### Evidencia del Reporte Falso
El archivo `06_GPT_FINAL_REPORT.md` indica:
```
- `npm run build` → ✅ Compilación Vite exitosa (`app-DFCule9_.js`)
- Assets recompilados y listos para desplegar al VPS vía `scp`
```

**Problema**: GPT-5.1 compiló correctamente pero el deploy al VPS **revirtió los permisos**.

---

## 2. AUDITORÍA SSH

### Estado de Assets al Momento del Error

```bash
ls -la /var/www/app.letstalkspanish.io/public/build/assets/
# -rw-rw-r--+ 1 root   root     49226 Dec  6 17:42 app-DFCule9_.js
```

**Problemas identificados**:

| Problema | Detalle |
|----------|---------|
| **Directorio `/build/`** | Permisos `drwx---rwx+` (707) - No permite lectura a otros |
| **Directorio `/assets/`** | Permisos `drwx---rwx+` (707) - Mismo problema |
| **Archivo JS** | Owner `root:root` en vez de `deploy:www-data` |

### Verificación de Acceso HTTP (Antes del Fix)

```bash
curl -sI https://app.letstalkspanish.io/build/assets/app-DFCule9_.js
# HTTP/2 404
```

---

## 3. CAUSA RAÍZ

### Análisis Forense

1. **GPT-5.1 ejecutó `scp` como root**: Los archivos se subieron con owner `root:root`
2. **Los permisos de directorio se degradaron**: De `755` a `707` (posiblemente por umask del proceso root)
3. **Nginx (usuario `www-data`) no puede leer**: El directorio no tiene bit de ejecución para "otros" usuarios

### Cadena de Fallos

```
GPT-5.1 compila → scp como root → archivos owner root:root
                                ↓
                         permisos 707 en directorio
                                ↓
                         www-data no puede leer
                                ↓
                         Nginx devuelve 404
```

---

## 4. COMANDOS DE CORRECCIÓN EJECUTADOS

```bash
# Conectar al VPS
ssh root@72.61.71.183

# 1. Corregir permisos de directorios
chmod 755 /var/www/app.letstalkspanish.io/public/build
chmod 755 /var/www/app.letstalkspanish.io/public/build/assets

# 2. Corregir ownership de todos los archivos
chown -R deploy:www-data /var/www/app.letstalkspanish.io/public/build/
```

### Verificación Post-Fix

```bash
# Verificar permisos corregidos
ls -ld /var/www/.../public/build/ /var/www/.../public/build/assets/
# drwxr-xr-x+ 3 deploy www-data ... /public/build/
# drwxr-xr-x+ 2 deploy www-data ... /public/build/assets/

# Verificar owner del JS
ls -la /var/www/.../public/build/assets/app-DFCule9_.js
# -rw-rw-r--+ 1 deploy www-data 49226 Dec  6 17:42 app-DFCule9_.js

# Verificar HTTP
curl -sI https://app.letstalkspanish.io/build/assets/app-DFCule9_.js
# HTTP/2 200
# content-type: application/javascript; charset=utf-8
```

---

## 5. VERIFICACIÓN EN NAVEGADOR

### Estado Post-Fix

| Elemento | Estado |
|----------|--------|
| CSS | ✅ Carga correctamente |
| JavaScript | ✅ HTTP 200 |
| Alpine.js | ✅ Sin errores en consola |
| Dropdown de lecciones | ✅ Muestra "Todas las lecciones con práctica" |
| Filtros | ✅ Funcionan correctamente |

### Consola del Navegador
```
[] (vacía - sin errores)
```

---

## 6. RECOMENDACIONES PARA EVITAR RECURRENCIA

### Para GPT-5.1 y otros agentes:

1. **Nunca usar `scp` como root directamente**. Usar:
   ```bash
   scp archivo deploy@72.61.71.183:/path/
   # O después del scp:
   ssh root@... "chown -R deploy:www-data /path/build/"
   ```

2. **Siempre verificar permisos post-deploy**:
   ```bash
   ssh root@... "chmod 755 /path/public/build /path/public/build/assets"
   ```

3. **Incluir en el proceso de deploy**:
   ```bash
   # Script post-deploy obligatorio
   chmod 755 /var/www/app.letstalkspanish.io/public/build
   chmod 755 /var/www/app.letstalkspanish.io/public/build/assets
   chown -R deploy:www-data /var/www/app.letstalkspanish.io/public/build/
   ```

---

## 7. RESUMEN EJECUTIVO

| Métrica | Valor |
|---------|-------|
| **Error** | 404 en `/build/assets/app-DFCule9_.js` |
| **Causa** | Permisos 707 + owner root:root |
| **Tiempo de resolución** | 5 minutos |
| **Impacto** | UI completamente rota |
| **Estado actual** | ✅ RESUELTO |

---

**Firmado por**: Opus 4.5 (Ingeniero DevOps Forense)

---

[OPUS-404-FIXED]

