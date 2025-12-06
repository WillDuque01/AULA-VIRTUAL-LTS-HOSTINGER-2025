# 26_OPUS_L10N_SYNC_FIX_REPORT.md

## Sincronización L10N y Corrección de Textos Faltantes
**Agente**: Opus 4.5  
**Fecha**: 07-dic-2025  
**Rol**: Ingeniero L10N y DevOps

---

# PROBLEMA IDENTIFICADO

El usuario reportó múltiples textos en español que aparecían en las rutas `/en/*`:

## Textos Reportados en Español

### Dashboard (`/en/dashboard`)
- "Tu perfil está completado al 0%"
- "Contacto" / "Teléfono o WhatsApp..."
- "Ubicación" / "País, región y ciudad..."
- "Prácticas en vivo" / "Reserva tu sesión en Discord"
- "Pedir más fechas"
- "Lección" / "Todas las lecciones con práctica"
- "Limpiar filtros"
- "No hay sesiones programadas..."
- "Prácticas premium" / "Haz que cada clase cuente"
- "Cupos garantizados en Discord"
- "Actualizar lista" / "Ver carrito"
- "Comprar ahora" / "Agregar al carrito"

### Player (`/en/lessons/1/player`)
- "Progreso del curso"
- "Ir a Saludos y presentaciones..."
- "Haz clic en los hitos para centrar..."

---

# CAUSA RAÍZ

Los archivos de idioma y vistas **no estaban sincronizados con el servidor de producción**. Aunque las traducciones existían localmente, el servidor seguía usando versiones anteriores.

---

# ACCIONES EJECUTADAS

## 1. Sincronización de Archivos de Idioma

```bash
scp -r resources/lang/* root@72.61.71.183:/var/www/app.letstalkspanish.io/resources/lang/
```

**Archivos sincronizados**: 28 archivos (13 ES + 13 EN + 2 JSON)

## 2. Sincronización de Vistas Blade

```bash
# Vistas de perfil
scp resources/views/livewire/profile/completion-banner.blade.php root@.../resources/views/livewire/profile/

# Vistas de estudiante
scp resources/views/livewire/student/*.blade.php root@.../resources/views/livewire/student/

# Vistas del player
scp resources/views/livewire/player/modes/*.blade.php root@.../resources/views/livewire/player/modes/
```

## 3. Corrección de Clave de Traducción

El texto "Tu perfil está completado al :percent%" tenía una clave incorrecta en los archivos JSON:

**Antes**:
```json
"Tu perfil está completado al": "Your profile is"
```

**Después**:
```json
"Tu perfil está completado al :percent%": "Your profile is :percent% complete"
```

## 4. Limpieza de Caché

```bash
ssh root@72.61.71.183 "cd /var/www/app.letstalkspanish.io && \
    php artisan view:clear && \
    php artisan cache:clear && \
    php artisan config:cache"
```

---

# TRADUCCIONES VERIFICADAS

## Archivo `resources/lang/en/student.php`

| Clave | Traducción EN |
|-------|---------------|
| `browser.title` | "Live practices" |
| `browser.subtitle` | "Book your Discord session" |
| `browser.description` | "Pick the ideal practice for your course and confirm a spot in seconds." |
| `browser.request_dates` | "Request more dates" |
| `browser.reset_filters` | "Reset filters" |
| `browser.empty_state` | "No sessions scheduled yet. Request a new one so your teacher can see it." |
| `packs.badge` | "Premium practices" |
| `packs.title` | "Make every class count" |
| `packs.description` | "Short, focused sessions with actionable feedback. Book in 30 seconds." |
| `packs.guarantee` | "Guaranteed Discord slots" |
| `packs.refresh` | "Refresh list" |
| `packs.view_cart` | "View cart" |
| `packs.buy_now` | "Buy now" |
| `packs.add_to_cart` | "Add to cart" |

---

# ERRORES 404 EXPLICADOS

Los errores 404/Forbidden reportados durante las pruebas fueron causados por:

| Ruta | Comportamiento | Causa |
|------|---------------|-------|
| `/en/shop/cart` | HTTP 403 Forbidden | Requiere rol `student_*` |
| `/en/shop/packs` | HTTP 403 Forbidden | Requiere rol `student_*` |
| `/en/student/dashboard` | HTTP 403 Forbidden | Requiere rol `student_*` |

**Esto es comportamiento correcto**: Las rutas de estudiante no deben ser accesibles para roles de Admin.

---

# LIMITACIÓN TÉCNICA PERSISTENTE

## Guías Contextuales (`config/experience_guides.php`)

Los textos del panel contextual flotante **siguen en español** porque:

1. Los archivos de configuración de Laravel se cargan **ANTES** del traductor
2. No es posible usar `__()` o `trans()` en archivos de config
3. Requiere refactorización arquitectónica (crear un servicio dedicado)

### Textos Afectados

- "Panel estudiante"
- "Gamificación + recordatorios en un solo lugar."
- "Los cuatro contadores superiores resumen progreso, tiempo y XP."
- "Cuando veas un pack recomendado, abre el browser de prácticas para reservar."
- "Los recordatorios de tareas incluyen un deeplink a WhatsApp para soporte inmediato."

### Solución Propuesta (Futura)

1. Crear `App\Services\ExperienceGuideService`
2. Mover textos a `resources/lang/{locale}/guides.php`
3. Modificar `contextual-panel.blade.php` para usar el servicio

---

# COMMIT

```bash
git add resources/lang/en.json resources/lang/es.json docs/26_OPUS_L10N_SYNC_FIX_REPORT.md
git commit -m "[OPUS] Turno 26: Fix clave traducción perfil + sincronización completa L10N"
git push origin main
```

---

**[L10N-SYNC-DEPLOYED]**

