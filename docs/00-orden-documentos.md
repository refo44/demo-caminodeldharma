# Orden de los documentos (prefijo numérico)

Los documentos en `docs/` llevan prefijo de dos dígitos (`01-`, `02-`, …) para mantener un orden fijo. El orden sigue la **jerarquía de dependencias**: del "por qué" al "cómo", y de la estrategia a la implementación.

---

## Justificación del orden

| Rango | Criterio | Motivo |
|-------|----------|--------|
| **01** | Plan maestro | El plan de plataforma define el territorio; precede y orienta todo lo demás. |
| **02** | Identidad | Paleta, tipografía y manual de marca definen el criterio visual. |
| **03–05** | Qué existe | Modelo WordPress, mapa de pantallas y arquitectura de información. Definen *qué* contenido y *qué* pantallas existen. |
| **06–08** | Voz y copy | Guía de voz, diccionario de términos, UI copy sheet. El copy depende de identidad; la navegación los usa después. |
| **09** | Experiencia del visitante | User journey. Cómo se recorre el sitio y cómo la persona llega a la práctica. |
| **10–11** | Navegación y URLs | Arquitectura de información/navegación y árbol de URLs. Dependen de pantallas, copy y recorridos. |
| **12–14** | Implementación técnica | Theme, assets, inventario de contenido, orden de implementación. Convierten la arquitectura en código. |
| **15** | Criterios contemporáneos | Tendencias UX/UI aplicadas al sistema. Filtro estratégico para validación de diseño e implementación. |
| **16** | Accesibilidad | Estándares únicos de accesibilidad: estrategia, diseño, HTML semántico, ARIA, contenido editorial, checklist y testing. WCAG 2.1/2.2 AA. |

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
6. `06-guia-voz-microcopy-ux`
7. `07-voice-dictionary`
8. `08-ui-copy-sheet`
9. `09-user-journey`
10. `10-arbol-urls-final`
11. `11-theme-file-structure`
12. `12-assets-strategy`
13. `13-content-source-inventario`
14. `14-orden-implementacion`
15. `15-tendencias-ux-ui-sistema-editorial`
16. `16-accesibilidad-estandares`

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
| Estilo sencillo y sobrio | 01, 02, 06, 15 |
| Seguir paleta de colores actual | 02 |
| Agregar cronograma de eventos | 01, 03, 04 |
| Fotografías adicionales de los encuentros | 12, 13 |
| Espacio para subir videos de las conferencias, conectar canal de YouTube | 01, 03 (§6), 12, 13 |
| Visión de la comunidad más amplio y profundo, apoyado de videos del maestro | 01, 03 |
| Tener contenido adicional sobre el budismo | 01 (Capa 6), 06, 08 |
| Incluir testimonios | 01, 03 (CPT testimonial) |
| Indicaciones sobre meditación, video sobre indicaciones para meditar | 01, 03 (§6) |
| El buda responde: avatar que responde preguntas (interactivo) | 01 (Capa 6) |
| Incluir botón de donaciones | 01, 03, 05, 08 |
| Gestión de eventos por la página (inscripción, pagos, cronograma) | 01, 03 |
| Accesibilidad para personas con discapacidad visual (Sandra) | 01, 15, 16 |
| Apartado ¿Cómo hacer parte de la comunidad en términos de formación? | 01, 03, 04, 05 |
| Explicar espacios de formación, alcance y propósito de cada uno | 01, 03 |
| Animación con un camino: formación budista vs. solo meditación; llevar de la mano | 01, 09 |
| Que la página conecte con las sanghas, contacto de cada sangha | 01, 03 (CPT sangha) |
| Tener en cuenta organización de información de la página de Paramitas | 01, 13 |
| Mantener manual de marca (colores, fuentes, logos) | 01, 02 |
| Link para ir al WhatsApp de Camino del Dharma | 01, 03, 05, 08 |
