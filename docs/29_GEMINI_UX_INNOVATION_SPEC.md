# 🎨 INSTRUCCIÓN PARA AGENTE GEMINI 3 PRO (TURNO 29)

**MODELO:** Gemini 3 Pro  
**ROL:** Arquitecto de Experiencia de Usuario (UX), Diseñador de Interfaces e Innovador de Diseño  
**FECHA:** 2025-12-07

---

## 🎯 MISIÓN: AUDITORÍA EXHAUSTIVA DE UI/UX Y ESPECIFICACIONES DE DISEÑO

Tu objetivo es realizar un análisis profundo y exhaustivo de **cada página, componente, flujo y elemento visual** del LMS, identificando inconsistencias, oportunidades de mejora y propuestas de innovación que eleven la experiencia de usuario a estándares premium.

---

## 📋 BLOQUE A: INVENTARIO EXHAUSTIVO DE PÁGINAS Y COMPONENTES

### Instrucción
Debes navegar y documentar **TODAS** las páginas del sistema, organizadas por rol. Para cada página, analiza:

| Aspecto | Qué Documentar |
|---------|----------------|
| **URL** | Ruta exacta (ej. `/en/admin/dashboard`) |
| **Componentes** | Lista de componentes Livewire/Alpine presentes |
| **Jerarquía Visual** | Headers, secciones, cards, botones, modales |
| **Consistencia** | ¿Sigue el Design System? (colores, tipografía, espaciado) |
| **Responsividad** | Comportamiento en Desktop/Tablet/Mobile |
| **Accesibilidad** | Contraste, navegación por teclado, etiquetas ARIA |

### Páginas por Rol a Auditar

#### 🔐 GUEST (Público)
```
/es/login
/en/login
/es/register  
/en/register
/es/welcome (landing)
/en/welcome
```

#### 👤 STUDENT
```
/{locale}/dashboard
/{locale}/student/practices
/{locale}/student/dashboard
/{locale}/shop/packs
/{locale}/shop/cart
/{locale}/shop/checkout
/{locale}/lessons/{id}/player
/{locale}/profile
```

#### 👨‍🏫 TEACHER / TEACHER ADMIN
```
/{locale}/professor/dashboard
/{locale}/professor/courses
/{locale}/professor/assignments
/{locale}/professor/practice-planner
/{locale}/professor/practice-packages
/{locale}/professor/gradebook
```

#### ⚙️ ADMIN
```
/{locale}/admin/dashboard
/{locale}/admin/users
/{locale}/admin/branding
/{locale}/admin/integrations
/{locale}/admin/pages (Page Manager)
/{locale}/admin/pages/{id}/builder (Page Builder)
/{locale}/courses/{id}/builder (Course Builder)
/{locale}/admin/messages
/{locale}/admin/payments
/{locale}/admin/dataporter
```

---

## 📋 BLOQUE B: AUDITORÍA DE COMPONENTES CRÍTICOS

### B.1 Sistema de Navegación
- **Header principal**: Logo, menú, selector de idioma, perfil dropdown
- **Sidebar** (si aplica): Estructura, iconografía, estados activos
- **Breadcrumbs**: Presencia y consistencia
- **Mobile navigation**: Hamburger menu, comportamiento de apertura/cierre

### B.2 Sistema de Cards
- **Course cards**: Imagen, título, progreso, CTA
- **Practice cards**: Fecha, horario, plataforma, cupos
- **Package cards**: Precio, sesiones, features
- **Consistency check**: ¿Todas las cards siguen el mismo patrón?

### B.3 Formularios
- **Inputs**: Estilo, estados (focus, error, disabled)
- **Selects/Dropdowns**: Consistencia visual
- **Botones**: Primarios, secundarios, terciarios, estados
- **Validación**: Mensajes de error, posicionamiento

### B.4 Modales y Overlays
- **Onboarding modal**: El modal de "Complete your profile" es intrusivo
- **Confirmation modals**: Estilo consistente
- **Toasts/Notifications**: Posicionamiento, duración, estilos por tipo

### B.5 Sistema de Mensajería (CRÍTICO)
Analizar el componente de mensajes (`/admin/messages`):
- Layout de conversaciones
- Burbuja de mensajes (enviados vs recibidos)
- Input de mensaje y botón de envío
- Scroll automático al nuevo mensaje
- Estados: cargando, vacío, error
- Responsive en móvil

### B.6 Page Builder
- Panel de kits disponibles
- Canvas de edición
- Controles de bloque (mover, duplicar, eliminar)
- Preview responsive
- Selector de tema
- Edición inline

### B.7 Course Builder  
- Drag & Drop de capítulos y lecciones
- Panel de propiedades de lección
- Estados visuales (publicado, borrador)
- Feedback de guardado

### B.8 Player de Lecciones
- Video player controls
- Sidebar de navegación del curso
- Progress bar
- Recursos adjuntos
- Quiz integrado
- Certificados

---

## 📋 BLOQUE C: ANÁLISIS DE FLUJOS DE USUARIO

Para cada flujo, documenta:
1. **Pasos del usuario**: Desde entrada hasta completar la acción
2. **Puntos de fricción**: Donde el usuario puede confundirse
3. **Oportunidades de mejora**: Simplificaciones posibles

### Flujos Críticos a Analizar

| Flujo | Descripción |
|-------|-------------|
| **Onboarding** | Registro → Verificación → Completar perfil → Dashboard |
| **Compra de pack** | Catálogo → Carrito → Checkout → Confirmación |
| **Reserva de práctica** | Browser → Seleccionar fecha → Confirmar cupo |
| **Progreso de curso** | Dashboard → Seleccionar lección → Completar → Siguiente |
| **Creación de curso** | Admin → Course Builder → Capítulos → Lecciones → Publicar |
| **Creación de página** | Admin → Page Manager → Page Builder → Bloques → Publicar |

---

## 📋 BLOQUE D: PROPUESTAS DE INNOVACIÓN UX

### D.1 Onboarding Simplificado
- ¿El modal actual es la mejor opción?
- Propuesta: Onboarding progresivo vs modal bloqueante
- Gamificación del completado de perfil

### D.2 Dashboard Inteligente
- Widgets personalizados por rol
- Acciones rápidas contextuales
- Métricas relevantes vs ruido visual

### D.3 Panel de Ayuda Contextual
- El panel flotante actual (contextual guides) necesita revisión
- Propuestas: Tooltips inline, centro de ayuda integrado, chatbot

### D.4 Feedback Visual
- Microinteracciones en acciones importantes
- Estados de carga más informativos
- Celebraciones de logros (confetti, badges)

### D.5 Personalización
- Temas claro/oscuro
- Preferencias de densidad de UI
- Atajos de teclado

---

## 📋 BLOQUE E: ESPECIFICACIONES TÉCNICAS DE DISEÑO

Para cada mejora propuesta, incluir:

```
## [NOMBRE DEL COMPONENTE]

### Estado Actual
- Problema identificado
- Screenshot/referencia

### Propuesta de Mejora
- Descripción de cambios

### Especificaciones CSS/Tailwind
```css
/* Clases Tailwind recomendadas */
.component {
    @apply rounded-2xl shadow-lg ...;
}
```

### Colores
- Primary: #hex
- Secondary: #hex
- Background: #hex

### Tipografía
- Font family: ...
- Sizes: h1, h2, body, small

### Espaciado
- Padding: ...
- Margin: ...
- Gap: ...

### Prioridad
- [ ] Alta / [ ] Media / [ ] Baja

### Archivos a Modificar
- `resources/views/...`
- `resources/css/...`
```

---

## 📝 FORMATO DE REPORTE DE SALIDA

### Archivo Principal
`29_GEMINI_UX_AUDIT_COMPLETE.md`

### Estructura del Reporte

```markdown
# AUDITORÍA COMPLETA DE UI/UX - LMS LetsTalkSpanish

## 1. RESUMEN EJECUTIVO
- Total de páginas auditadas
- Problemas críticos encontrados
- Quick wins identificados
- Estimación de esfuerzo

## 2. INVENTARIO DE PÁGINAS
[Tabla completa por rol]

## 3. HALLAZGOS POR COMPONENTE
[Análisis detallado de cada componente]

## 4. ANÁLISIS DE FLUJOS
[Diagramas y puntos de fricción]

## 5. PROPUESTAS DE INNOVACIÓN
[Ideas priorizadas con especificaciones]

## 6. INSTRUCCIONES PARA GPT-5.1
[Bloque de código con especificaciones exactas para implementar]

## 7. PRIORIZACIÓN FINAL
| Prioridad | Componente | Esfuerzo | Impacto |
|-----------|------------|----------|---------|
| P0 | ... | ... | Alto |
| P1 | ... | ... | Medio |
| P2 | ... | ... | Bajo |
```

---

## 🔧 INSTRUCCIONES PARA GPT-5.1 (GENERADAS POR GEMINI)

Al final del reporte, Gemini debe generar un bloque de instrucciones **listas para copiar y pegar** para que GPT-5.1 implemente los cambios:

```markdown
## 🤖 INSTRUCCIÓN PARA GPT-5.1 (TURNO 30)

**MODELO:** GPT-5.1 Codex High  
**ROL:** Implementador Frontend Senior

### MISIÓN: IMPLEMENTAR MEJORAS DE UI/UX

Basado en el análisis de Gemini 3 Pro, implementa los siguientes cambios:

#### TAREA 1: [Nombre]
- Archivo: `resources/views/...`
- Cambio: [Descripción exacta]
- CSS/Tailwind: `[clases]`

#### TAREA 2: [Nombre]
...

### VERIFICACIÓN
- [ ] Probar en Desktop (>1280px)
- [ ] Probar en Mobile (<768px)
- [ ] Verificar consistencia de colores
- [ ] Validar accesibilidad básica

### SEÑAL DE CIERRE
[GPT-UX-IMPLEMENTED]
```

---

## ⏱️ ENTREGABLES ESPERADOS

| Entregable | Descripción |
|------------|-------------|
| `29_GEMINI_UX_AUDIT_COMPLETE.md` | Reporte completo de auditoría |
| Screenshots | Capturas de problemas identificados |
| Wireframes (opcional) | Propuestas visuales de mejoras |
| Instrucciones GPT-5.1 | Bloque ejecutable para implementación |

---

## 🚦 SEÑALES DE COMUNICACIÓN

| Señal | Significado |
|-------|-------------|
| `[GEMINI-AUDIT-IN-PROGRESS]` | Auditoría en curso |
| `[GEMINI-AUDIT-COMPLETE]` | Auditoría finalizada |
| `[READY-FOR-GPT-IMPLEMENTATION]` | Listo para que GPT-5.1 implemente |

---

## 📌 NOTAS IMPORTANTES

1. **No implementes cambios**: Solo documenta y especifica
2. **Sé específico**: Incluye clases Tailwind exactas, no solo descripciones
3. **Prioriza impacto**: Quick wins primero, cambios estructurales después
4. **Considera L10N**: Asegura que propuestas funcionen en ES y EN
5. **Respeta el Design System existente**: No reinventes la rueda

---

**INICIO DE AUDITORÍA:** `[GEMINI-AUDIT-IN-PROGRESS]`

---

*Documento generado por Opus 4.5 - Turno 29*

