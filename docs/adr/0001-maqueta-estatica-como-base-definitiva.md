# ADR 0001: Maqueta estática como base definitiva

## Estado

Aceptada

## Fecha

2026-07-19

## Contexto

El sitio de Camino del Dharma debe transmitir calma, claridad y coherencia. No es un producto comercial ni una web informativa agresiva, sino un espacio de acogida. El equipo necesitaba una base visual y estructural sólida antes de introducir WordPress como motor de contenido.

En muchos proyectos, la maqueta estática se trata como un prototipo desechable que se rediseña al migrar a CMS. Eso duplica trabajo, introduce regresiones visuales y diluye la validación editorial ya hecha contra `content-source/` y los documentos de diseño (`04`, `06`, `09`).

## Decisión

La **maqueta estática HTML/CSS/JS** en la raíz del repositorio es la **primera versión real del sitio**, no un prototipo temporal.

- Define layout, bloques, copy, tokens, URLs y componentes definitivos.
- Debe construirse con la estructura de rutas finales (`/comunidad/`, `/practica/`, etc.).
- Se **congela** antes de iniciar Fase 3 (WordPress); cambios posteriores solo por error, accesibilidad o inconsistencia con `content-source/`.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Prototipo en Figma solo | No valida HTML semántico, performance, accesibilidad ni URLs reales. |
| Maqueta desechable y rediseño en WordPress | Alto riesgo de deriva visual y retrabajo; contradice la orientación editorial del proyecto. |
| WordPress desde el inicio | Anticipa complejidad de hosting, theme y contenido antes de cerrar diseño y copy. |

## Consecuencias

**Beneficios:**

- Validación temprana de UX, accesibilidad, SEO y performance en producción estática.
- Migración WordPress acotada a «cambiar motor, no rediseñar» (ver ADR 0002).
- Menor riesgo de debates de diseño durante la fase CMS.

**Riesgos y limitaciones:**

- Eventos y blog requieren simulación estática hasta WordPress o automatización editorial.
- La disciplina de congelamiento debe respetarse para que la decisión siga siendo válida.

**Trabajo futuro:**

- Completar criterios de aceptación de Fase 2 y Fase 2.5 en `17-orden-implementacion`.
- Mantener paridad visual estricta al crear plantillas PHP.

## Referencias

- `docs/17-orden-implementacion` §2, §2.6
- `docs/13-static-file-structure`
- `docs/06-wireframes`, `docs/04-mapa-pantallas`
- ADR 0002, ADR 0008, ADR 0009
