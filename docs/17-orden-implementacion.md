# Camino del Dharma — Orden de implementación

**Secuencia acordada para llevar el sitio a la web.** **No saltar etapas.**  
**Versión 1.2**

**Depende de:** `02-identidad-corporativa`, `03-wordpress-content-model`, `04-mapa-pantallas`, `05-arquitectura-informacion-navegacion`, `06-wireframes`, `09-ui-copy-sheet`, `11-arbol-urls-final`, `12-theme-file-structure`, `13-static-file-structure`, `14-css-architecture`, `15-assets-strategy`, `16-content-source-inventario`, `18-tendencias-ux-ui-sistema-editorial`

---

## Fase 1: Documentación y diseño

1. **Completar identidad:** Extraer paleta y tipografía del PDF `Identidad CAMINO DEL DHARMA- (1).pdf` → actualizar `02-identidad-corporativa.md`
2. **Wireframes:** Estructura de bloques por pantalla según `06-wireframes` (y `04-mapa-pantallas`); en papel, Figma o HTML
3. **Validar documentación:** Revisar que todos los docs estén alineados
4. **Consultar tendencias UX/UI:** `18-tendencias-ux-ui-sistema-editorial` como filtro para decisiones de diseño

---

## Fase 2: Maqueta estática

1. **Maqueta responsiva** con:
   - HTML5 semántico
   - CSS3 (tokens de identidad, roles semánticos)
   - JS mínimo con `defer` (navegación, formularios, accesibilidad)
2. Contenido según: `04-mapa-pantallas`, `05-arquitectura-informacion-navegacion`, `09-ui-copy-sheet`, `02-identidad-corporativa`
3. Assets desde `content-source/` copiados a `public/assets/` (regla en `15-assets-strategy`, inventario en `16-content-source-inventario`)
4. **Validar contra checklist** de `18-tendencias-ux-ui-sistema-editorial` (§8) antes de dar por cerrada la fase
5. **Validar responsive:** Comportamiento en móvil, tablet y desktop antes de pasar a WordPress

### 2.1 Estructura HTML final (base para WordPress)

La maqueta estática debe construirse con la misma estructura de rutas finales del sitio.  
La fase WordPress será una **adaptación directa del HTML a plantillas PHP**, sin rediseñar ni cambiar la estructura visual.

Estructura recomendada:

- `/index.html`
- `/comunidad/index.html`
- `/linaje/index.html`
- `/practica/index.html`
- `/eventos/index.html`
- `/galeria/index.html`
- `/contacto/index.html`
- `/404.html`
- `/assets/`

**Regla:**

- `/ruta/` → `ruta/index.html`
- URLs limpias desde el inicio
- No inventar rutas temporales

### 2.2 Correspondencia futura con WordPress

La maqueta define el layout definitivo. En Fase 3 solo se envolverá el HTML con WordPress:

| HTML estático             | WordPress                                |
| ------------------------- | ---------------------------------------- |
| `/index.html`             | `front-page.php`                         |
| `/comunidad/index.html`   | `page-comunidad.php`                     |
| `/linaje/index.html`      | `page-linaje.php`                        |
| `/practica/index.html`    | `page-practica.php`                      |
| `/eventos/index.html`     | `archive-event.php` o `page-eventos.php` |
| `/galeria/index.html`     | `page-galeria.php`                       |
| `/contacto/index.html`    | `page-contacto.php`                      |
| `/404.html`               | `404.php`                                |

### 2.3 Reglas para la maqueta

La maqueta debe comportarse como el sitio real:

- Usar clases definitivas (no temporales)
- No usar estilos inline
- Un solo CSS principal (`main.css`)
- HTML semántico desde el inicio
- Estructura de bloques igual a `06-wireframes`
- Microcopy final desde `09-ui-copy-sheet`

### 2.4 Simulación de estados dinámicos

Antes de WordPress, se validan flujos con contenido estático:

- **Eventos:**
  - Versión con evento
  - Versión sin evento (mensaje amable)
- **Single evento (opcional):** `/eventos/retiro/index.html`

Esto permite validar navegación real sin backend.

### 2.5 Invariantes de diseño

Durante la migración a WordPress no se modifica:

- Estructura de bloques
- Jerarquía visual
- Copy editorial
- Tokens de identidad
- Arquitectura CSS

WordPress solo aporta: motor de contenido, administración, eventos dinámicos.

### Por qué este agregado es importante

Con esto el documento deja explícito algo clave:

- La maqueta **no es un prototipo**; es la primera versión real del sitio.
- La fase WordPress pasa a ser solo: **cambiar motor, no rediseñar.**

Eso reduce riesgo técnico y de diseño.

### 2.6 Congelamiento de maqueta

Antes de iniciar Fase 3:

- La maqueta se considera estructura definitiva.
- Cambios posteriores solo si: hay error, hay problema de accesibilidad, hay inconsistencia con `content-source/`.

Esto evita rediseños eternos.

---

## Fase 3: WordPress

1. **Convertir** la maqueta en theme de WordPress según `12-theme-file-structure` (plantillas, parts, URL → plantilla)
2. Ajustar a `03-wordpress-content-model` y `11-arbol-urls-final`; assets dentro del theme según `15-assets-strategy`
3. Implementar CPT `event` si se requieren eventos dinámicos
4. **Subir** al servidor: staging (opcional) y producción; configurar contenido y hosting

---

## Prioridad de páginas

1. **Inicio** — Hero, meditación semanal, caminos de participación
2. **Contacto** — Formulario + WhatsApp
3. **La comunidad** — Quiénes somos, fundador
4. **Práctica** — Meditación, talleres, retiros
5. **El linaje** — Tradición, Chan, Tierra Pura
6. **Galería** — Página dedicada con grid de imágenes (43 fotos); en Inicio solo fila de 4 imágenes + enlace «Ver galería completa»
7. **Eventos** — Condicional; implementar cuando haya eventos vigentes

---

## Regla

No escribir código de theme WordPress ni subir a servidor final hasta que la maqueta estática esté validada.

---

## Checklist pre-lanzamiento

- [ ] Identidad (paleta, tipografía) definida
- [ ] Todas las páginas maquetadas
- [ ] Formulario de contacto funcional
- [ ] Botón WhatsApp operativo
- [ ] Enlaces externos (Pausa Profunda, redes) verificados
- [ ] Datos bancarios correctos en footer
- [ ] Accesibilidad: estándares 19 aplicados (contraste, alt, teclado, focus, formularios)

---

## Cierre

Este documento define el **orden oficial de implementación**: documentación y diseño → maqueta estática validada → theme WordPress. No escribir código de theme ni desplegar hasta que la maqueta esté validada. Prioridad de páginas y checklist pre-lanzamiento están alineados con mapa de pantallas (04), arquitectura (05) y contenido (03).

---

**Versión:** 1.3
