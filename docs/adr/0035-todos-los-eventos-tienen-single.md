# ADR 0035: Todo evento tiene ficha pública; los pasados no se inscriben

## Estado

Aceptada

## Fecha

2026-08-28

## Contexto

En el estático live hay 10 eventos. Solo 3 tienen URL (`/eventos/{slug}`). Los otros 7 viven
solo como cards en `/eventos` (OWN-004). El inventario default era importar 10 CPT **sin**
inventar slugs.

El propietario decidió: **todos** los eventos, incluidos los pasados, deben tener página propia.
Los finalizados **no** muestran inscripción (Inscribirme / Preinscribirme).

Eso añade URLs públicas. ADR 0008 y el redirect ledger exigen registrarlas; no cambiarlas
en silencio.

## Decisión

1. Cada `event` tiene `single` público: `/eventos/{slug}` (sin barra final).
2. Los 3 slugs actuales se **KEEP**.
3. Los 7 que hoy no tienen ficha se publican en el corte (WordPress), con slugs de la tabla
   abajo (derivados de títulos/carteles; kebab-case). No son 301 desde URLs viejas: **no
   existían**. En el ledger: **PLANNED KEEP**.
4. CTA de inscripción **solo** si el evento está **vigente** y hay inscripción real.
   Finalizado: ficha + cartel + copy + JSON-LD `EventCompleted`; **sin** Inscribirme /
   Preinscribirme; **sin** `offers` de inscripción.
5. La meditación semanal sigue **sin** ser `event` (Page `/practica/meditacion-semanal-en-linea`).

| Evento (listado) | Slug previsto |
| ---------------- | ------------- |
| Círculos de Presencia Consciente | `circulos-de-presencia-consciente` (KEEP) |
| 7.º Encuentro Nacional 2026 | `encuentro-nacional-2026` (KEEP) |
| Meditación Presencial en Barranquilla | `meditacion-presencial-barranquilla` |
| Festival Calma en la Ciudad | `festival-calma-en-la-ciudad` |
| Pausa Profunda – Medellín | `pausa-profunda-medellin` |
| Ansiedad, agotamiento y crisis de atención… | `ansiedad-agotamiento-crisis-de-atencion` |
| Vesak 2026 – Colombia Cuida la Vida | `vesak-2026` |
| Pausa Profunda – Cali | `pausa-profunda-cali` (KEEP) |
| Buddhismo para tiempos de cansancio | `buddhismo-tiempos-cansancio` |
| 6.º Encuentro Nacional 2025 | `6-encuentro-nacional-2025` |

Cambiar un slug de esta tabla exige actualizar el ledger (KEEP o 301). No improvisar.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| 10 CPT, solo 3 singles | El propietario quiere ficha para el histórico. |
| Singles para pasados **con** Inscribirme | CTA muerto; inscripción de un evento ya ocurrido. |
| Inventar slugs al implementar sin tabla | Rompe ADR 0008 / ledger. |

## Consecuencias

**Beneficios:** archivo histórico enlazable; SEO de ficha; «Ver evento» en todas las cards.

**Riesgos:** 7 URLs nuevas en el sitemap de WordPress que el estático aún no tiene. Hasta el
corte no existen: no hace falta 301 desde el live. Tras el corte: 10 singles HTTP 200.

**Trabajo futuro:** importador crea los 10 singles; theme oculta inscripción si no vigente.
No implementar HTML estático en la sesión de este ADR (salvo que el propietario lo pida).

## Referencias

- OWN-004 · [`docs/redirect-ledger.md`](../redirect-ledger.md)
- ADR [0008](0008-urls-estables-desde-la-maqueta.md), [0034](0034-static-live-como-fuente-contenido-produccion.md)
- [`docs/03-wordpress-content-model.md`](../03-wordpress-content-model.md)
