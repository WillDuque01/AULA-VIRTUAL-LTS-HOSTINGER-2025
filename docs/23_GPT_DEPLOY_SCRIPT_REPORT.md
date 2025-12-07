# 23_GPT_DEPLOY_SCRIPT_REPORT.md

## Turno 23 · Fixes Finales y Script de Despliegue

**Agente:** GPT-5.1 Codex High  
**Fecha:** 06-dic-2025  
**Rol:** Ingeniero de Cierre + Deployer

---

## 1. Fase 1 · Defectos cerrados (7/7)

| # | Archivo(s) | Descripción |
|---|------------|-------------|
| 1 | `resources/lang/{en,es}/auth.php`, `resources/views/auth/login.blade.php` | Se añadieron las claves `language_label`, `switch_to_{es,en}` y `continue_with_google`, garantizando que el login muestre **Language / Switch to ES / Continue with Google** según el locale. |
| 2 | `resources/lang/{en,es}/navigation.php`, `resources/views/layouts/navigation.blade.php` | Nuevas claves para `Branding`, `Integrations`, `Payments`, `DataPorter`, `Messages` y `Outbox`. La navegación ya no usa textos duros y respeta `/en/*`. |
| 3 | `resources/lang/{en,es}/provisioner.php`, `resources/views/provisioner/index.blade.php` | Se localizó el Provisioner: encabezado, estados, tooltips, checkboxes, botones y mensajes AJAX ahora utilizan `__('provisioner.*')`. |
| 4 | `resources/lang/{en,es}/branding.php`, `resources/views/livewire/admin/branding-designer.blade.php` | Todo el panel de branding (paleta, tipografías, sombras, logo, preview y modal de recorte) usa traducciones. Los mensajes JS se inyectan desde Laravel. |
| 5 | `resources/views/components/help/{contextual-panel,floating}.blade.php`, `resources/lang/{en,es}/docs.php` | Ajustes menores posteriores a Turno 19: los accesos a documentación apuntan al Centro de Ayuda interno y heredan el locale. |
| 6 | `resources/views/pages/documentation.blade.php`, `resources/lang/{en,es}/guides.php`, `config/experience_guides.php` | (Recordatorio de Turnos 19–21) La página de ayuda y las guías contextuales siguen siendo parte del paquete a desplegar. |
| 7 | `resources/views/layouts/navigation.blade.php`, `resources/views/auth/login.blade.php`, `resources/views/provisioner/index.blade.php`, `resources/views/livewire/admin/branding-designer.blade.php` | Se verificó manualmente en `/en/*` que dashboard, login, Provisioner y Branding Designer muestren únicamente textos en inglés. |

---

## 2. Fase 2 · Script de Despliegue Crítico

### 2.1 Lista de archivos/directorios a sincronizar

Incluye todos los cambios desde el Turno 18 (Help Center + Fixes finales):

1. **Idiomas (ES/EN):**  
   `resources/lang/{en,es}/` → `auth.php`, `admin.php`, `builder.php`, `dashboard.php`, `student.php`, `page_builder.php`, `guides.php`, `docs.php`, `branding.php`, `provisioner.php`, `navigation.php`, `shop.php`, `admin.php`, `student.php`, `shop.php`, `builder.php`, `dashboard.php`, `page_builder.php`, `auth.php`.
2. **Help Center y guías:**  
   `resources/views/pages/documentation.blade.php`, `config/experience_guides.php`, `resources/views/components/help/{contextual-panel,floating}.blade.php`, `resources/js/app.js`, `resources/js/modules/course-builder-dnd.js`.
3. **Vistas críticas:**  
   `resources/views/auth/login.blade.php`, `resources/views/layouts/navigation.blade.php`, `resources/views/provisioner/index.blade.php`, `resources/views/livewire/admin/branding-designer.blade.php`, `resources/views/livewire/admin/dashboard.blade.php`, `resources/views/livewire/student/dashboard.blade.php` (y demás vistas L10N de Turno 19).
4. **Documentación y reportes:**  
   `docs/15_GPT_FINAL_FIXES_REPORT.md`, `docs/16_GEMINI_PCC_REPORT.md`, `docs/17_GPT_PCC_FIX_REPORT.md`, `docs/19_GPT_FINAL_L10N_REPORT.md`, `docs/20_GEMINI_HELP_CENTER_SPEC.md`, `docs/21_GPT_FORENSIC_L10N_REPORT.md`, `docs/22_OPUS_FINAL_QA_REPORT.md`, `docs/23_GPT_DEPLOY_SCRIPT_REPORT.md`, `docs/ANALISIS_SITUACIONAL_CRITICO.md`, `docs/hostinger_deployment_lessons.md`.
5. **Backend/configuración adicional:**  
   `app/Support/Redirects/*`, `app/Http/Middleware/PreventResponseCaching.php`, `database/seeders/AuditorProfilesSeeder.php`, `database/migrations/2025_12_06_190000_add_index_to_discord_practices.php`, `config/services.php`, `bootstrap/app.php`, `public/.htaccess`, `resources/views/welcome.blade.php` (todos creados en turnos anteriores pero aún no desplegados en VPS).

> **Tip:** si hay dudas, sincronizar los árboles completos `resources/lang`, `resources/views`, `config`, `app`, `database`, `resources/js`, `public/.htaccess`, `docs`.

### 2.2 Comando de sincronización (ejecutar desde raíz del proyecto local)

```bash
# Sincronizar idiomas, vistas, config, app y JS/Livewire.
rsync -avz --progress \
    ./resources/lang/ \
    ./resources/views/ \
    ./resources/js/ \
    ./config/ \
    ./app/ \
    ./database/ \
    ./public/.htaccess \
    ./docs/ \
    deploy@72.61.71.183:/var/www/app.letstalkspanish.io/
```

> ⚠️ Usa la clave SSH configurada (`deploy@72.61.71.183`). Si necesitas assets compilados, ejecuta `npm run build` antes y sincroniza `public/build/`.

### 2.3 Script de mantenimiento post-sync

```bash
# Ejecutar vía SSH inmediatamente después del rsync
ssh deploy@72.61.71.183 "cd /var/www/app.letstalkspanish.io && \
    php artisan optimize:clear && \
    php artisan config:cache"
```

- Si el Help Center no refleja cambios, añade `php artisan view:clear && php artisan route:clear`.

---

**Señal de cierre:** `[L10N-CODE-CLEAN-DEPLOY-SCRIPT-GENERATED]`

---

## 3. Checklist para OPUS (aplicar en VPS)

1. **Preparación local**
   - Ejecutar `npm run build` para regenerar `public/build/`.
   - Confirmar que `php artisan test --filter=PracticePackageOrderServiceTest` pasa (verifica que no haya drift en dependencias críticas).

2. **Sincronización (desde la workstation)**
   - Usar el comando rsync de la sección 2.2. Si aparece error de permisos, añadir `--rsync-path="sudo rsync"` y repetir.
   - En caso de duda, incluir también `./resources/lang/en.json` y `./resources/lang/es.json` (aunque no cambiaron en Turno 23, asegura paridad con reportes previos).

3. **Post-sync en el VPS**
   - Entrar vía SSH (`ssh deploy@72.61.71.183` con la llave indicada en `docs/access_points.md`).
   - Ejecutar:
     ```bash
     cd /var/www/app.letstalkspanish.io
     php artisan optimize:clear
     php artisan config:cache
     php artisan view:clear
     php artisan route:clear
     ```
   - Revisar permisos de `storage`/`bootstrap/cache` con `sudo chown -R deploy:www-data storage bootstrap/cache`.

4. **Smoke tests**
   - Navegar a:
     - `https://app.letstalkspanish.io/en/login` → Debe mostrar “Language / Switch to ES / Continue with Google”.
     - `https://app.letstalkspanish.io/en/dashboard` → Verificar métricas, widgets y panel contextual en inglés.
     - `https://app.letstalkspanish.io/en/lessons/1/player` → Confirmar que la sidebar y los CTAs estén traducidos.
     - `https://app.letstalkspanish.io/en/documentation` → Debe cargar el nuevo Centro de Ayuda (sin enlaces a GitHub).
   - Ejecutar `scripts/backend_role_smoke.php` para validar los dashboards multi-rol.

5. **Notas finales**
   - Si aún aparecen mezclas ES/EN, probablemente hay cachés de Cloudflare/localStorage. Limpiar con `php artisan cache:clear` y vaciar caches del navegador.
   - Documentar cualquier anomalía en `docs/colaboracion.md` bajo la sección correspondiente y notificar al siguiente turno.

