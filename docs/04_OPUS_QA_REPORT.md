# 04_OPUS_QA_REPORT.md

## Reporte de QA de Navegador - Turno 4

**Agente**: Opus 4.5 (Auditor QA de Navegador)  
**Fecha**: 06-dic-2025 17:10 UTC  
**Metodología**: Navegación real + Análisis de consola + Verificación SSH

---

## 1. RESUMEN EJECUTIVO

| Severidad | Cantidad | Estado |
|-----------|----------|--------|
| 🔴 CRÍTICO | 2 | Bloqueante |
| 🟡 ALTO | 1 | Afecta UX |
| 🟢 BAJO | 0 | - |

**Veredicto**: ⛔ **UI COMPLETAMENTE ROTA** - No se puede continuar con desarrollo frontend hasta resolver bugs críticos.

---

## 2. BUGS CRÍTICOS

### BUG-001: CSS No Se Carga (CRÍTICO)

**Síntoma**: La página muestra elementos sin estilos, estructura rota, navegación duplicada.

**Evidencia Consola**:
```
Refused to apply style from 'https://app.letstalkspanish.io/build/assets/app-CKk37mKG.css' 
because its MIME type ('text/html') is not a supported stylesheet MIME type
```

**Evidencia HTTP**:
```bash
curl -sI https://app.letstalkspanish.io/build/assets/app-CKk37mKG.css
# HTTP/2 404
```

**Causa Raíz**:
```bash
ls -la /var/www/app.letstalkspanish.io/public/build/
# drwx---rwx+ (permisos 707)
# El directorio no permite lectura a www-data (usuario de Nginx)

sudo -u www-data cat .../app-CKk37mKG.css
# Permission denied
```

**Archivos Afectados**:
- `/public/build/` (directorio)
- `/public/build/assets/` (directorio)
- `app-CKk37mKG.css` (CSS compilado)
- `app-Dk6Z6734.js` (JS compilado)

**Fix Requerido**:
```bash
# Corregir permisos de directorios
chmod 755 /var/www/app.letstalkspanish.io/public/build
chmod 755 /var/www/app.letstalkspanish.io/public/build/assets

# Corregir propiedad de archivos nuevos
chown deploy:www-data /var/www/app.letstalkspanish.io/public/build/assets/*
```

**Componente**: Infraestructura / Nginx  
**Responsable**: Opus 4.5 (Backend/DevOps)

---

### BUG-002: Alpine.js Bloqueado por CSP (CRÍTICO)

**Síntoma**: Ningún componente interactivo funciona (dropdowns, modales, filtros).

**Evidencia Consola**:
```
Alpine Expression Error: Refused to evaluate a string as JavaScript 
because 'unsafe-eval' is not an allowed source of script in the 
following Content Security Policy directive: "script-src 'self' 'unsafe-inline'"
```

**Expresiones Afectadas**:
- `{ open: false }` (dropdowns)
- `{ filtersOpen: window.innerWidth >= 1024 }` (filtros móviles)
- `selectedLabel()` (select agrupado)
- Todas las expresiones Alpine que usan `x-data`

**Causa Raíz**:
```
Content-Security-Policy: script-src 'self' 'unsafe-inline';
```
Alpine.js requiere `'unsafe-eval'` para evaluar expresiones dinámicas.

**Archivo Afectado**:
- `config/security.php` (línea 16)

**Fix Requerido**:
```php
// config/security.php línea 16
// Cambiar:
"script-src 'self' 'unsafe-inline';",
// Por:
"script-src 'self' 'unsafe-inline' 'unsafe-eval';",
```

**Componente**: Middleware de Seguridad  
**Responsable**: Opus 4.5 (Backend)

---

### BUG-003: Propiedad Incorrecta de Assets (ALTO)

**Síntoma**: Archivos CSS/JS compilados tienen propiedad `root:root` en lugar de `deploy:www-data`.

**Evidencia**:
```bash
ls -la /var/www/.../public/build/assets/
-rw-rw-r--+ 1 root   root     75411 Dec  6 17:02 app-CKk37mKG.css
-rw-rw-r--+ 1 root   root     93695 Dec  6 17:02 app-Dk6Z6734.js
```

**Causa Raíz**: Alguien ejecutó `npm run build` o `php artisan` como root en el servidor.

**Fix Requerido**:
```bash
chown -R deploy:www-data /var/www/app.letstalkspanish.io/public/build/
```

**Componente**: Proceso de Deploy  
**Responsable**: DevOps

---

## 3. CAPTURAS DE EVIDENCIA

| Archivo | Descripción |
|---------|-------------|
| `qa_student_practices_browser.png` | Browser de prácticas sin estilos |
| `qa_student_dashboard.png` | Dashboard sin estilos |

---

## 4. PÁGINAS VERIFICADAS

| Página | CSS | Alpine | Estado |
|--------|-----|--------|--------|
| `/es/login` | ❌ 404 | ❌ CSP | 🔴 Rota |
| `/es/student/dashboard` | ❌ 404 | ❌ CSP | 🔴 Rota |
| `/es/student/practices` | ❌ 404 | ❌ CSP | 🔴 Rota |

---

## 5. IMPACTO EN IMPLEMENTACIÓN DE GPT-5.1

El trabajo de GPT-5.1 (Turno 3) **NO PUEDE VERIFICARSE** debido a estos bugs:

| Feature Implementada | Verificable |
|---------------------|-------------|
| Componente `x-ui.select-grouped` | ❌ No (CSP bloquea Alpine) |
| Filtros agrupados en browser | ❌ No (CSS no carga) |
| Toasts globales | ❌ No (Alpine bloqueado) |
| `animatedCount` extraído | ❌ No (JS no carga) |

---

## 6. INSTRUCCIONES DE CORRECCIÓN

### Paso 1: Corregir Permisos (SSH)

```bash
# Conectar al VPS
ssh root@72.61.71.183

# Corregir permisos de directorios
chmod 755 /var/www/app.letstalkspanish.io/public/build
chmod 755 /var/www/app.letstalkspanish.io/public/build/assets

# Corregir propiedad
chown -R deploy:www-data /var/www/app.letstalkspanish.io/public/build/

# Verificar
curl -sI https://app.letstalkspanish.io/build/assets/app-CKk37mKG.css | head -3
# Debe devolver HTTP/2 200
```

### Paso 2: Corregir CSP para Alpine.js

```php
// config/security.php línea 16
// Cambiar:
"script-src 'self' 'unsafe-inline';",
// Por:
"script-src 'self' 'unsafe-inline' 'unsafe-eval';",
```

### Paso 3: Limpiar Caches

```bash
php artisan optimize:clear
php artisan config:cache
```

---

## 7. CONCLUSIÓN

El ciclo de implementación de GPT-5.1 está **BLOQUEADO** por bugs de infraestructura. Los fixes son simples pero deben aplicarse **ANTES** de cualquier prueba de UI.

**Prioridad de corrección**:
1. 🔴 BUG-001: Permisos de `/public/build/` (5 minutos)
2. 🔴 BUG-002: CSP para Alpine (5 minutos)
3. 🟡 BUG-003: Propiedad de archivos (ya incluido en paso 1)

---

**Firmado por**: Opus 4.5 (Auditor QA de Navegador)

---

[TURNO-OPUS-QA-FINALIZADO]

