# BITÁCORA DE EJECUCIÓN - STATUS BOARD

**Proyecto**: Academia Virtual LTS  
**Fase**: Estabilización de Infraestructura  
**Inicio**: 06-dic-2025

---

## ESTADO: Turno 1 (Opus) Completado.

[LINK] Ver Reporte de Infraestructura en 01_OPUS_INFRA_PLAN.md

---

## ESTADO: Turno 2 (Gemini) Completado.

[LINK] Ver Especificación de Diseño en 02_GEMINI_DESIGN_SPEC.md

> ESTADO: ESPECIFICACIÓN LISTA. TURNO DE GPT-5.1.

---

## ESTADO: Ciclo de Refactorización Completado.

- **Implementación**: Se creó el componente `resources/views/components/ui/select-grouped.blade.php`, se agruparon las lecciones en `DiscordPracticeBrowser`, se añadieron toasts globales y se extrajo `animatedCount` a `resources/js/animations.js`.  
- **UI**: El browser de prácticas ahora usa filtros agrupados y `wire:loading` en todas las acciones, siguiendo la especificación de Gemini.
- **Pruebas**: `php artisan test --filter=Student\\DiscordPracticeBrowserTest` → ✅ (5 pruebas / 12 assertions). Se observó el warning `git: 'VIRTUAL' is not a git command` durante la ejecución, sin impacto en el resultado.
- **Documentación**: Detalles completos en `docs/03_GPT_EXECUTION_REPORT.md`.

---

## ESTADO: Turno 4 (Opus QA) Completado.

[LINK] Ver Reporte de QA en 04_OPUS_QA_REPORT.md

### ⛔ BUGS CRÍTICOS DETECTADOS

| Bug | Severidad | Causa |
|-----|-----------|-------|
| CSS no carga (HTTP 404) | 🔴 CRÍTICO | Permisos de `/public/build/` (707 en vez de 755) |
| Alpine.js bloqueado | 🔴 CRÍTICO | CSP no incluye `'unsafe-eval'` |

### Fixes Requeridos ANTES de continuar

```bash
# 1. Corregir permisos
chmod 755 /var/www/app.letstalkspanish.io/public/build
chmod 755 /var/www/app.letstalkspanish.io/public/build/assets
chown -R deploy:www-data /var/www/app.letstalkspanish.io/public/build/

# 2. Corregir CSP en config/security.php (línea 16)
# script-src 'self' 'unsafe-inline' → 'self' 'unsafe-inline' 'unsafe-eval'
```

> ~~ESTADO: UI BLOQUEADA. REQUIERE FIXES DE INFRAESTRUCTURA.~~

---

## ✅ FIXES APLICADOS (06-dic-2025 17:17 UTC)

| Fix | Comando/Cambio | Resultado |
|-----|----------------|-----------|
| Permisos `/public/build/` | `chmod 755` + `chown deploy:www-data` | ✅ CSS HTTP 200 |
| CSP `unsafe-eval` | `config/security.php` línea 16 | ✅ Alpine funciona |

**Estado Visual**: UI renderiza correctamente con estilos y filtros.

**Errores Pendientes (Frontend)**: 
- "Detected multiple instances of Alpine running" 
- "Cannot read properties of undefined (reading 'entangle')"

Estos son errores de código frontend, no de infraestructura.

---

[TURNO-OPUS-QA-FINALIZADO] → [FIXES-APLICADOS]
