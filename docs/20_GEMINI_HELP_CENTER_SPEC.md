# 20_GEMINI_HELP_CENTER_SPEC.md

## Especificación Técnica: Centralización del Centro de Ayuda

**Agente**: Gemini 3 Pro (Arquitecto de Contenido)  
**Fecha**: 06-dic-2025  
**Objetivo**: Eliminar enlaces externos a GitHub y crear un sistema de documentación interno localizable.

---

## 1. ARQUITECTURA DE LA PÁGINA (UX/CONTENIDO)

### 1.1 Ruta Localizable
*   **Patrón**: `/{locale}/documentation`
*   **Nombre de Ruta**: `documentation.index`
*   **Middleware**: `web` (Público o Autenticado, según preferencia; sugerido: Público para SEO/Pre-venta).

### 1.2 Estructura de la Vista (Single Page Scrollspy)
El diseño se basa en un layout de dos columnas responsivo:
1.  **Sidebar (Izquierda - Sticky):** Índice de navegación rápida generado dinámicamente desde las claves de traducción. En móvil, se colapsa en un acordeón o drawer.
2.  **Contenido (Derecha):** Renderizado de bloques de texto enriquecido (Markdown o HTML seguro) con anclas automáticas.

### 1.3 Localización (L10N)
Todo el contenido residirá en archivos de idioma estándar de Laravel (`resources/lang/{es,en}/docs.php`). Esto permite:
*   Gestión centralizada del texto.
*   Cero hardcoding en las vistas Blade.
*   Fácil expansión a otros idiomas.

---

## 2. ESPECIFICACIÓN TÉCNICA (PARA GPT-5.1)

### 2.1 Archivos de Idioma (`resources/lang/{es,en}/docs.php`)
Estructura jerárquica para generar menú y contenido automáticamente.

```php
<?php

return [
    'title' => 'Centro de Ayuda y Documentación',
    'sections' => [
        'getting-started' => [
            'title' => 'Primeros Pasos',
            'content' => 'Bienvenido a la plataforma...',
        ],
        'course-builder' => [
            'title' => 'Constructor de Cursos',
            'content' => 'Aprende a crear módulos y lecciones...',
        ],
        'discord-practices' => [
            'title' => 'Prácticas con Discord',
            'content' => 'Guía para agendar sesiones en vivo...',
        ],
        // ... más secciones
    ],
];
```

### 2.2 Ruta (`routes/web.php`)
Añadir dentro del grupo `{locale}`:

```php
Route::get('/documentation', function () {
    return view('pages.documentation');
})->name('documentation.index');
```

### 2.3 Vista Blade (`resources/views/pages/documentation.blade.php`)
Usar Alpine.js para la interactividad del índice (scrollspy).

```html
<x-app-layout>
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8" x-data="{ activeSection: '' }">
        <div class="lg:grid lg:grid-cols-4 lg:gap-8">
            <!-- Sidebar Sticky -->
            <aside class="hidden lg:block lg:col-span-1">
                <nav class="sticky top-24 space-y-1">
                    @foreach(__('docs.sections') as $key => $section)
                        <a href="#{{ $key }}"
                           class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                           :class="activeSection === '{{ $key }}' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                           @click.prevent="document.getElementById('{{ $key }}').scrollIntoView({ behavior: 'smooth' }); activeSection = '{{ $key }}'">
                            {{ $section['title'] }}
                        </a>
                    @endforeach
                </nav>
            </aside>

            <!-- Contenido Principal -->
            <main class="mt-8 lg:mt-0 lg:col-span-3 space-y-12">
                <div class="prose prose-indigo max-w-none">
                    <h1>{{ __('docs.title') }}</h1>
                    
                    @foreach(__('docs.sections') as $key => $section)
                        <section id="{{ $key }}" class="scroll-mt-24" x-intersect.margin.-20%="activeSection = '{{ $key }}'">
                            <h2>{{ $section['title'] }}</h2>
                            <div class="text-slate-600">
                                {!! \Illuminate\Support\Str::markdown($section['content']) !!}
                            </div>
                        </section>
                        @if(!$loop->last) <hr class="border-slate-100 my-8"> @endif
                    @endforeach
                </div>
            </main>
        </div>
    </div>
</x-app-layout>
```
*Nota: Se requiere el plugin `@tailwindcss/typography` para la clase `prose`. Si no está, usar estilos manuales.*

---

## 3. INTEGRACIÓN Y DELEGACIÓN

### 3.1 Instrucción para Agente Implementador (Turno 21 - GPT-5.1)

> **TAREA PRIORITARIA:** Reemplazo de Enlaces de Documentación.
>
> 1.  **Busca** en todo el proyecto (`grep -r "View documentation" resources/views`) cualquier enlace que apunte a GitHub o documentación externa.
> 2.  **Reemplaza** esos enlaces con la nueva ruta interna, apuntando al ancla específica si es posible.
>     *   *Antes:* `<a href="https://github.com/..." target="_blank">View documentation ↗</a>`
>     *   *Ahora:* `<a href="{{ route('documentation.index', ['locale' => app()->getLocale()]) }}#course-builder" target="_blank">{{ __('Ver documentación') }} ↗</a>`
> 3.  **Implementa** la vista `documentation.blade.php` y los archivos de idioma `docs.php` según la especificación técnica de arriba.

---

[HELP-CENTER-SPEC-READY]

