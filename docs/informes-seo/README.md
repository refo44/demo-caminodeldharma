# Informes SEO — Camino del Dharma

Dos informes derivados de la auditoría de `.audit/`, separados por **audiencia y pregunta que responden**.

| # | Documento | Responde a | Audiencia | Cadencia |
|---|---|---|---|---|
| 00 | [Informe de Auditoría SEO](00-informe-auditoria-seo.md) | ¿Qué se encontró y qué hay que decidir? | **Liderazgo — es el que se comparte** | Por hito |
| 03 | [Análisis de Visibilidad Orgánica](03-analisis-visibilidad-organica.md) | ¿Dónde estamos, frente a quién, y cómo se vuelve a medir? | Coordinación + quien pida detalle | Trimestral |

> **Se conserva la numeración 00 y 03** de la primera emisión, para que las referencias externas y el historial de git sigan resolviendo. No es un error de secuencia.

---

## Qué compartir con la comunidad

**Se comparten dos documentos, y son complementarios por diseño:**

| Documento | Cubre | Audiencia |
|---|---|---|
| [00 — Informe de Auditoría SEO](00-informe-auditoria-seo.md) | Lo que está **fuera del blog**: hallazgos, enlaces, decisiones pendientes | Liderazgo |
| [24 — Brief editorial](../24-brief-editorial-blog-y-visibilidad.md) | Lo que va **dentro del blog**: temas, voz, formato | Equipo editorial |

No se solapan: el 24 se limita explícitamente a entradas de blog; el 00 cubre lo institucional que el 24 deja fuera por alcance (peticiones de enlace, artículos por ciudad, formulario, meditación semanal).

**El 03 se entrega solo si piden el detalle con cifras.**

---

## Dos correcciones registradas

**1. Google Business Profile — recomendación retirada (2026-07-20).** La primera emisión lo situaba como acción prioritaria. Se retiró tras confirmar que la comunidad **no tiene sede ni dirección física** y no es elegible según las reglas de Google: las entidades exclusivamente en línea quedan excluidas, y aunque una organización sin sede visible puede ocultar la dirección al público, Google exige una dirección real verificable en el back end. Fue una deducción a partir del SERP sin comprobar el requisito — el mismo error de extrapolación que la auditoría ya había cometido con el supuesto backlink de EcoEspiritualidad. Detalle en el informe [00 §4](00-informe-auditoria-seo.md).

**Efecto estratégico:** las búsquedas locales dejan de contar como brecha, y el esfuerzo se reorienta a la autoridad y a la **práctica en línea** — donde la comunidad es el único caso virtual entre los seis dominios comparados.

**2. Tres informes eliminados (2026-07-20).** La primera emisión tenía cinco. Se redujo a dos:

| Eliminado | Motivo |
|---|---|
| `01-informe-rendimiento-seo.md` | Sin periodo anterior, sin analítica (ADR 0019) y sin transacciones, no reportaba nada. Se re-emitirá desde cero en septiembre, cuando haya datos. Su cuadro de indicadores se conservó en el 03 §11 |
| `02-auditoria-seo-tecnica.md` | Duplicaba `.audit/report.md` y `.audit/working/`, que son la fuente de verdad. Su única conclusión relevante para la comunidad (*el sitio está impecable; el problema no es la web*) está en el 00 §7 |
| `04-posicionamiento-palabras-clave.md` | Anexo natural del 03. Su tabla T0 y el protocolo de re-medición se integraron en el 03 §11 |

Recuperables desde git (commit `19cb801`) si en algún momento hacen falta.

---

## Estado de esta emisión (2026-07-20)

**Ambos son mediciones base (T0).** El sitio estático se publicó el **2026-07-18**: en el momento de la auditoría tenía **un día de vida**.

Eso condiciona qué puede afirmarse. La ausencia de las consultas amplias **es esperable a esa edad** — es una brecha a trabajar, no un defecto. Lo que sí es un hallazgo de fondo, independiente de la edad, es el déficit de autoridad heredado de la etapa WordPress.

**Nada se extrapola.** Lo que no se pudo medir figura como limitación explícita.

---

## Hallazgo transversal

**El sitio está técnicamente impecable y comercialmente invisible.** SEO interno 100, rendimiento 99/100 móvil, datos estructurados 100 — y **DR 0,4 / DA 2** en un dominio de 7 años y medio.

La causa está verificada una por una: **tres fuentes temáticas autorizadas hablan de la comunidad y ninguna enlaza al dominio** (Buddhistdoor enlaza solo a Facebook; EcoEspiritualidad es el #2 de marca sin ningún enlace saliente; el directorio budismo.com lista 11 centros y no a esta comunidad).

Es una causa inusualmente fácil de corregir: no hace falta link building desde cero, **hace falta pedirlo**. Y no requiere sede, dirección ni presupuesto — a diferencia de todo lo relacionado con búsqueda local.

---

## Próxima emisión

**Entre 2026-08-17 y 2026-09-14** (4–8 semanas después de T0).

Antes de esa fecha, cualquier conclusión sobre si las acciones funcionaron sería falsa: se estaría midiendo la eficacia de algo que aún no ha tenido tiempo de actuar.

Al re-emitir:

1. Copiar el informe a `NN-nombre-AAAA-MM-DD.md` para conservar el histórico.
2. Actualizar el vigente **por secciones**, sin reescribirlo entero.
3. Seguir el **protocolo de medición** del informe [03 §11](03-analisis-visibilidad-organica.md) — sin él, la comparación entre periodos no significa nada.
4. Evaluar si ya procede re-emitir un informe de rendimiento con datos reales.

**La regla que hace comparables las mediciones:** misma herramienta, mismo método, mismo mercado, misma lista de keywords. Comparar DA de Moz con DR de Ahrefs no dice nada.

---

## Relación con el resto de la documentación

- **Origen de los datos:** `.audit/` — informes, ledger de evidencia (`evidence-ledger.jsonl`), hallazgos (`findings.jsonl`) y datos crudos (`raw/`). Estos documentos **no generan datos nuevos**: los sintetizan por audiencia.
- **Decisiones que los condicionan:** [ADR 0019](../adr/0019-sin-analitica-con-cookies.md) (sin analítica con cookies) y [ADR 0020](../adr/0020-hsts-aplazado-hasta-wordpress.md) (HSTS aplazado).
- **Documento hermano:** [`24-brief-editorial-blog-y-visibilidad.md`](../24-brief-editorial-blog-y-visibilidad.md) — traduce estos hallazgos a instrucciones para el equipo editorial.
- **Migración pendiente:** el sitio actual es temporal (WordPress, ADR 0002/0018). Ninguna recomendación compromete al sitio a plazos largos.
