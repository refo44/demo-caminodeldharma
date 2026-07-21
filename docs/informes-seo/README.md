# Informes SEO — Camino del Dharma

Cuatro informes derivados de la auditoría de `.audit/`, separados por **audiencia y pregunta que responden**. No se solapan: cada uno existe porque hay una pregunta distinta que contestar.

| # | Documento | Responde a | Audiencia | Cadencia |
|---|---|---|---|---|
| 00 | [Informe de Auditoría SEO](00-informe-auditoria-seo.md) | ¿Qué se encontró y qué hay que decidir? | **Liderazgo — informe general, es el que se comparte** | Por hito |
| 01 | [Informe de Rendimiento SEO](01-informe-rendimiento-seo.md) | ¿Qué resultados dio el periodo? | Liderazgo de la comunidad | Trimestral |
| 02 | [Auditoría SEO Técnica](02-auditoria-seo-tecnica.md) | ¿Está sano el sitio? | Quien implementa | Por versión / tras cambios grandes |
| 03 | [Análisis de Visibilidad Orgánica](03-analisis-visibilidad-organica.md) | ¿Dónde estamos frente a los demás? | Liderazgo + editorial | Trimestral |
| 04 | [Posicionamiento de Palabras Clave](04-posicionamiento-palabras-clave.md) | ¿Qué posiciones tenemos? | Editorial + SEO | Mensual o trimestral |

## Qué compartir con la comunidad

**Se comparten dos documentos, y son complementarios por diseño:**

| Documento | Cubre | Audiencia |
|---|---|---|
| [00 — Informe de Auditoría SEO](00-informe-auditoria-seo.md) | Lo que está **fuera del blog**: hallazgos, enlaces, presencia local y cuatro decisiones pendientes | Liderazgo |
| [24 — Brief editorial](../24-brief-editorial-blog-y-visibilidad.md) | Lo que va **dentro del blog**: temas, voz, formato | Equipo editorial |

No se solapan: el 24 se limita explícitamente a entradas de blog; el 00 cubre lo institucional que el 24 deja fuera por alcance (peticiones de enlace, artículos por ciudad, formulario, meditación semanal).

> **Corrección registrada 2026-07-20:** la primera versión situaba **Google Business Profile** como acción prioritaria. Se retiró tras confirmar que la comunidad **no tiene sede ni dirección física** y, por tanto, no es elegible según las reglas de Google. Fue una deducción a partir del SERP sin verificar el requisito — el mismo error de extrapolación que la auditoría ya había cometido y corregido con el supuesto backlink de EcoEspiritualidad. Detalle en el informe 00 §4.

**El 03 se entrega solo si piden el detalle con cifras.** El 01 es prematuro hasta la re-medición de septiembre —reportar «9 clics» sin el contexto de que el sitio tenía dos días invita a la conversación equivocada—. El 02 es interno: su única conclusión relevante para la comunidad (*el sitio está técnicamente impecable; el problema no es la web*) ya está en el 00 §6.

---

## Estado de esta primera emisión (2026-07-20)

**Los cuatro son mediciones base (T0).** El sitio estático se publicó el **2026-07-18**, así que en el momento de la auditoría tenía **un día de vida** y en el de estos informes, dos.

Eso condiciona qué puede afirmar cada documento:

- **02 (técnico)** es plenamente concluyente: la salud del sitio no depende de su antigüedad.
- **03 y 04** describen un punto de partida real, pero **la ausencia de las consultas amplias es esperable a esa edad** — es una brecha a trabajar, no un defecto.
- **01 (rendimiento)** es el más limitado: sin periodo anterior, sin analítica (ADR 0019) y sin transacciones, no puede reportar evolución, conversiones ni ROI. Lo explica en su primera sección y propone qué medir en su lugar.

**Nada se extrapola.** Lo que no se pudo medir figura como limitación explícita en cada informe.

---

## Hallazgo transversal

**El sitio está técnicamente impecable y comercialmente invisible.** SEO interno 100, rendimiento 99/100 móvil, datos estructurados 100 — y **DR 0,4 / DA 2** en un dominio de 7 años y medio.

La causa está verificada una por una: **tres fuentes temáticas autorizadas hablan de la comunidad y ninguna enlaza al dominio** (Buddhistdoor enlaza solo a Facebook; EcoEspiritualidad es el #2 de marca sin ningún enlace saliente; el directorio budismo.com lista 11 centros y no a esta comunidad).

Es una causa inusualmente fácil de corregir: no hace falta link building desde cero, **hace falta pedirlo**.

---

## Próxima emisión

**Entre 2026-08-17 y 2026-09-14** (4–8 semanas después de T0).

Antes de esa fecha, cualquier conclusión sobre si las acciones SEO funcionaron sería falsa: se estaría midiendo la eficacia de algo que aún no ha tenido tiempo de actuar.

Al re-emitir:

1. Copiar el informe a `NN-nombre-AAAA-MM-DD.md` para conservar el histórico.
2. Actualizar el vigente **por secciones**, sin reescribirlo entero.
3. Rellenar la columna **Δ** del informe 04 comparando contra T0.
4. Seguir el **protocolo de medición** del informe 04 §6 — sin él, la comparación entre periodos no significa nada.

**La regla que hace comparables las mediciones:** misma herramienta, mismo método, mismo mercado, misma lista de keywords. Comparar DA de Moz con DR de Ahrefs no dice nada.

---

## Relación con el resto de la documentación

- **Origen de los datos:** `.audit/` — informes, ledgers de evidencia (`evidence-ledger.jsonl`), hallazgos (`findings.jsonl`) y datos crudos (`raw/`). Estos cuatro documentos **no generan datos nuevos**: los sintetizan por audiencia.
- **Decisiones que los condicionan:** [ADR 0019](../adr/0019-sin-analitica-con-cookies.md) (sin analítica con cookies) y [ADR 0020](../adr/0020-hsts-aplazado-hasta-wordpress.md) (HSTS aplazado).
- **Documento hermano:** [`24-brief-editorial-blog-y-visibilidad.md`](../24-brief-editorial-blog-y-visibilidad.md) — traduce estos hallazgos a instrucciones para el equipo editorial.
- **Migración pendiente:** el sitio actual es temporal (WordPress, ADR 0002/0018). Ninguna recomendación compromete al sitio a plazos largos.
