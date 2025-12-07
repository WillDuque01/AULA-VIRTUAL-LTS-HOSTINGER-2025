<?php

return [
    'title' => 'Centro de Ayuda y Documentación',
    'view_link' => 'Ver documentación',
    'sections' => [
        'getting-started' => [
            'title' => 'Primeros pasos',
            'content' => <<<'MD'
1. **Activa los idiomas** desde *Branding → Locales* y confirma que `es` / `en` tengan rutas `/locale`.
2. **Define el branding** cargando logotipo, colores y fuentes para que la landing y los dashboards sean coherentes.
3. **Asigna roles base**: al menos un `admin`, un `teacher_admin` y un `student_paid` con cohortes de prueba (usa los seeders `AuditorProfilesSeeder` si estás en local).
4. **Publica una landing** en el Page Builder y márcala como *home* para que reemplace al placeholder temporal.
5. **Limpia caches** (`php artisan optimize:clear`) y sincroniza assets con `npm run build` antes de desplegar.
MD,
        ],
        'course-builder' => [
            'title' => 'Constructor de cursos',
            'content' => <<<'MD'
* Arrastra capítulos y lecciones usando el nuevo módulo Alpine `courseBuilderDnD`.
* Usa los chips de foco para bloquear avance, programar liberaciones o asignar prácticas de Discord.
* Cada lección puede declarar **CTA**, badges, duración y prerequisites sin salir del panel lateral.
* Al terminar, ejecuta `Guardar orden` (evento `builder-reorder`) para persistir los cambios en la base de datos.
MD,
        ],
        'discord-practices' => [
            'title' => 'Prácticas con Discord',
            'content' => <<<'MD'
* El planner docente permite **duplicar slots**, moverlos entre cohortes y sincronizar recordatorios.
* Los estudiantes ven un grid responsivo con estados (`Disponible`, `Lista de espera`, `Pack requerido`).
* Integra tus packs desde `Market → Practice Packages`; cada compra emite eventos para Discord y WhatsApp.
* Recomendación: mantén al menos **dos semanas** de prácticas publicadas para evitar huecos en la agenda.
MD,
        ],
        'dataporter-hub' => [
            'title' => 'DataPorter & Automations',
            'content' => <<<'MD'
* DataPorter expone un hub para exportar métricas de cursos, prácticas y certificados.
* Los *tokens* (`DATAPORTER_API_KEY`, `DATAPORTER_ENDPOINT`) deben existir en `.env` y en el VPS.
* Configura los jobs recurrentes en Supervisor (`dataporter:sync`) para mantener los dashboards al día.
MD,
        ],
        'player-signals' => [
            'title' => 'Telemetría del Player',
            'content' => <<<'MD'
* El endpoint `/api/player/events` valida el rate limit `player-events` y despacha `RecordPlayerEventJob`.
* Usa `TelemetryQueueTest` como referencia para simular eventos (`play`, `pause`, `complete`).
* Activa `TelemetryRecorder::$useQueue = true` en producción para no bloquear el hilo de Livewire.
MD,
        ],
        'planner-operations' => [
            'title' => 'Operación del Planner',
            'content' => <<<'MD'
1. Publica un `PracticePackage` (estado `published`) y asígnalo a una cohorte.
2. Desde el planner admin puedes etiquetar slots como **Discord / Zoom / Meet** según la necesidad.
3. Cada práctica genera *webhooks* hacia Discord → canal privado del profesor con la lista de estudiantes confirmados.
MD,
        ],
        'student-panel' => [
            'title' => 'Panel del estudiante',
            'content' => <<<'MD'
* El resumen superior muestra progreso, tiempo de estudio, XP y racha traducidos según el locale.
* El módulo de tareas usa `whatsapp.assignment.*` para generar enlaces de ayuda personalizados.
* El área **Mi curso** ahora soporta CTA de certificado y lista de próximas lecciones con scroll accesible.
MD,
        ],
        'admin-executive' => [
            'title' => 'Checklist ejecutivo',
            'content' => <<<'MD'
* Revisa diariamente el panel admin: métricas, backlog docente, WhatsApp y certificados.
* El **Playbook de Integraciones** agrupa tokens críticos y ahora enlaza al Centro de Ayuda interno.
* Si detectas fallos, usa los scripts `scripts/backend_role_smoke.php` o `scripts/real_integrations_smoke.php`.
MD,
        ],
    ],
];

