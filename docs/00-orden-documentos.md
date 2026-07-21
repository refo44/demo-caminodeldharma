# Orden de los documentos (prefijo numérico)

Los documentos en `docs/` llevan prefijo de dos dígitos (`01-`, `02-`, …) para mantener un orden fijo. El orden sigue la **jerarquía de dependencias**: del "por qué" al "cómo", y de la estrategia a la implementación.

---

## Justificación del orden

| Rango | Criterio | Motivo |
|-------|----------|--------|
| **01** | Plan maestro | El plan de plataforma define el territorio; precede y orienta todo lo demás. |
| **02** | Identidad | Paleta, tipografía y manual de marca definen el criterio visual. |
| **03–05** | Qué existe | Modelo WordPress, mapa de pantallas y arquitectura de información. Definen *qué* contenido y *qué* pantallas existen. |
| **06** | Wireframes | Estructura de pantallas: jerarquía, bloques y flujo de lectura por vista. No diseño visual; apoya a mapa de pantallas y navegación. |
| **07–09** | Voz y copy | Guía de voz, diccionario de términos, UI copy sheet. El copy depende de identidad; la navegación los usa después. |
| **10** | Experiencia del visitante | User journey. Cómo se recorre el sitio y cómo la persona llega a la práctica. |
| **11–12** | Navegación y theme | Árbol de URLs y estructura de archivos del theme. Dependen de pantallas, copy y recorridos. |
| **13** | Estructura de archivos estáticos | Geografía del proyecto: docs, content-source, theme, assets. Dónde viven los archivos estáticos. |
| **14** | Arquitectura CSS | Capas (theme.json, main.css), tokens, naming, especificidad, accesibilidad en estilos. |
| **15–17** | Implementación técnica | Assets, inventario de contenido, orden de implementación. Convierten la arquitectura en código. |
| **adr/** | Decisiones arquitectónicas | ADR: registro inmutable de decisiones técnicas (despliegue, PWA, URLs, CSS, HSTS, etc.). Ver `adr/README.md`. |
| **18** | Criterios contemporáneos | Tendencias UX/UI aplicadas al sistema. Filtro estratégico para validación de diseño e implementación. |
| **19** | Accesibilidad | Estándares únicos de accesibilidad: estrategia, diseño, HTML semántico, ARIA, contenido editorial, checklist y testing. WCAG 2.1/2.2 AA. |
| **20** | Principios de layout | Ancho de lectura, ritmo vertical, uso del blanco, relación tipografía/imagen. Cierra el sistema visual antes de escribir HTML. |
| **21–23** | Voz y sistema editorial | Manual de copywriting; sistema editorial (tipos de contenido, presentación en pantalla, extensión del blog). |

---

## Regla de dependencias

Ningún documento debe depender de uno con número mayor. Las referencias cruzadas apuntan siempre a documentos con prefijo menor o igual.

---

## Lista ordenada (documentos para Camino del Dharma)

1. `01-plataforma-comunidad-plan`
2. `02-identidad-corporativa`
3. `03-wordpress-content-model`
4. `04-mapa-pantallas`
5. `05-arquitectura-informacion-navegacion`
6. `06-wireframes`
7. `07-guia-voz-microcopy-ux`
8. `08-voice-dictionary`
9. `09-ui-copy-sheet`
10. `10-user-journey`
11. `11-arbol-urls-final`
12. `12-theme-file-structure`
13. `13-static-file-structure`
14. `14-css-architecture`
15. `15-assets-strategy`
16. `16-content-source-inventario`
17. `17-orden-implementacion`
18. `18-tendencias-ux-ui-sistema-editorial`
19. `19-accesibilidad-estandares`
20. `20-layout-principles`
21. `21-manual-voz-copywriting-editorial`
23. `23-sistema-editorial`
24. `24-brief-editorial-blog-y-visibilidad` — brief autosuficiente para el equipo editorial (hallazgo de visibilidad, voz, formato y sugerencia de temas; puede compartirse sin `21` ni `23`)

**Informes SEO (entregables):** `informes-seo/README.md` — dos informes autosuficientes derivados de `.audit/` y redactados para entrega externa: `00-informe-auditoria-seo.md` (general y ejecutivo, para el liderazgo) y `02-auditoria-seo-tecnica.md` (estado de salud del sitio, para el equipo de publicación web). Se entregan junto a `24-brief-editorial-blog-y-visibilidad.md`, que cubre lo editorial. Se re-emiten con cadencia trimestral, por lo que **no llevan prefijo en la cadena lineal**: no son documentos de diseño del proyecto sino mediciones fechadas. Dependen de la auditoría, no de los docs numerados.

**ADR (decisiones arquitectónicas):** `adr/README.md` (0001–0017). **Migración:** `migracion-static-wordpress.md`. **Archivo (respaldos):** `archive/contacto-formulario-estatico/` — snapshot de la página `/contacto` y estilos del formulario para restauración post-WordPress o tras cambios del estático. No llevan prefijo numérico en `00` para no romper la regla de dependencias lineal; se referencian desde `17-orden-implementacion`.

**Nota sobre `21-manual-voz-*.docx`:** los `.docx` en `docs/` son exportaciones para compartir; el documento editable principal es `21-manual-voz-copywriting-editorial.md`.

---

## Fuentes de contenido

Todo el contenido proviene de:

- `content-source/Pagina web Camino del Dharma/Contenido_Web_Camino_del_Dharma.docx`
- `content-source/Pagina web Camino del Dharma/Lluvia de ideas para la página web de la comunidad.docx`
- `content-source/Pagina web Camino del Dharma/Identidad CAMINO DEL DHARMA- (1).pdf` (manual de marca)
- `content-source/Pagina web Camino del Dharma/FOTOS PAGINA WEB/` (imágenes y videos por pestaña)

---

## Trazabilidad: Lluvia de ideas → docs

Cada ítem de la Lluvia de ideas está cubierto en al menos un documento.

| Lluvia de ideas | Doc(s) |
|-----------------|--------|
| Estilo sencillo y sobrio | 01, 02, 07, 18 |
| Seguir paleta de colores actual | 02 |
| Agregar cronograma de eventos | 01, 03, 04 |
| Fotografías adicionales de los encuentros | 15, 16 |
| Espacio para subir videos de las conferencias, conectar canal de YouTube | 01, 03 (§6), 15, 16 |
| Visión de la comunidad más amplio y profundo, apoyado de videos del maestro | 01, 03 |
| Tener contenido adicional sobre el budismo | 01 (Capa 6), 07, 09 |
| Incluir testimonios | 01, 03 (CPT testimonial) |
| Indicaciones sobre meditación, video sobre indicaciones para meditar | 01, 03 (§6) |
| El buda responde: avatar que responde preguntas (interactivo) | 01 (Capa 6) |
| Incluir botón de donaciones | 01, 03, 05, 09 |
| Gestión de eventos por la página (inscripción, pagos, cronograma) | 01, 03 |
| Accesibilidad para personas con discapacidad visual (Sandra) | 01, 18, 19 |
| Apartado ¿Cómo hacer parte de la comunidad en términos de formación? | 01, 03, 04, 05 |
| Explicar espacios de formación, alcance y propósito de cada uno | 01, 03 |
| Animación con un camino: formación budista vs. solo meditación; llevar de la mano | 01, 10 |
| Que la página conecte con las sanghas, contacto de cada sangha | 01, 03 (CPT sangha) |
| Tener en cuenta organización de información de la página de Paramitas | 01, 16 |
| Mantener manual de marca (colores, fuentes, logos) | 01, 02 |
| Link para ir al WhatsApp de Camino del Dharma | 01, 03, 05, 09 |
