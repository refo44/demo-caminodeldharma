# ADR 0022: La ciudad es taxonomía, no URL — sin archivos de eventos por ciudad

## Estado

Aceptada

## Fecha

2026-07-22

## Contexto

Al diseñar las páginas por ciudad (TASK-0020) se planteó si los eventos de cada ciudad debían tener
dirección propia, del tipo `/eventos/cali`.

El análisis del 2026-07-20 (`.audit/working/url-hypotheses.md` §2c) descartó esa ruta con cuatro
motivos —conflicto con `/eventos/{slug}`, contenido delgado, canibalización con `/sanghas/{ciudad}`
y el precedente de `event_type`, que es taxonomía sin archivo público— pero dejó una **condición de
revisión**: si una ciudad superaba unos 5 eventos, se crearía el archivo como `/eventos/ciudad/{ciudad}`.

Al revisar esa condición aparecieron tres problemas.

**1. Contradice una decisión previa del propio proyecto.** `03-wordpress-content-model` §3 ya define
cómo escala el listado de eventos: *"con el ritmo actual (~8–12 eventos/año) el listado único con
agrupación por año es suficiente hasta unos 25–30 eventos. A partir de ahí, archivos por año
(`/eventos/2025/`)"*. Es decir, el proyecto **ya tenía respuesta** al crecimiento del listado, y es
por fecha. La condición de revisión introducía una segunda respuesta, por ciudad, sin cruzarla con la
primera. El volumen crece por fecha, no por ciudad.

**2. El umbral estaba mal planteado.** Fijaba un criterio de **cantidad** (~5 eventos) para una
decisión que es de **contenido**. Una lista filtrada por ciudad no aporta nada que no exista ya en
`/eventos` y en `/sanghas/{ciudad}`, tenga cinco elementos o veinte.

**3. Es navegación facetada.** `/eventos/ciudad/cali` es una URL de filtro. La documentación de Google
recomienda **impedir su rastreo**, porque genera páginas casi duplicadas sin beneficio. El argumento de
canibalización tampoco mejora con el volumen: quien busca «eventos budistas en cali» queda servido por
`/sanghas/cali`; más eventos no separan la intención de búsqueda, solo reparten la señal entre dos
páginas.

Dato de contexto: el análisis original razonaba sobre **2 eventos**. Tras la incorporación del archivo
de encuentros (21/07/2026) son **5**, repartidos en Bogotá (2), Medellín (2) y Barranquilla (1).
Ninguna ciudad pasa de dos, y con una cadencia de 2–3 encuentros al año por ciudad, llegar a cinco en
una sola tomaría unos dos años.

## Decisión

**No se crean URLs de filtro por ciudad para eventos. Ni ahora ni al superar ningún umbral.**

Queda derogada la condición de revisión que preveía `/eventos/ciudad/{ciudad}`.

| Necesidad | Cómo se resuelve |
|---|---|
| Asociar cada evento con su ciudad | Taxonomía `event_city` sobre el CPT `event` (atributo en el marcado, en la maqueta estática). **Sin archivo público**, igual que `event_type` |
| Mostrar los encuentros de una ciudad | Sección dentro de `/sanghas/{ciudad}` |
| El listado general crece demasiado | Archivos por año, según `03-wordpress-content-model` §3 |

**El criterio que sustituye al umbral numérico:** una dirección propia se justifica cuando tiene
contenido que no existe en ninguna otra parte del sitio **y** responde a una búsqueda distinta. Una
lista filtrada no cumple ninguna de las dos. `/sanghas/{ciudad}` sí: identidad, qué se practica ahí,
quién acompaña, cómo asistir la primera vez y los encuentros.

## Alternativas consideradas

**`/eventos/cali`.** Descartada ya en el análisis original: `11-arbol-urls-final` §3 define
`/eventos/{slug}` para el evento individual, de modo que WordPress resolvería `/eventos/cali` como
ficha de un evento cuyo slug fuera «cali».

**`/eventos/ciudad/{ciudad}`** como base alternativa. Evitaba el conflicto de rutas, pero no los otros
tres motivos. Es la que este ADR deroga.

**Filtro en la propia página sin generar URL.** Viable si algún día se quiere filtrar visualmente el
listado general. No crea direcciones nuevas y por tanto no plantea ninguno de los problemas anteriores.
No se implementa ahora: con cinco eventos no hay nada que filtrar.

## Consecuencias

**A favor**

- Una sola página fuerte por ciudad en vez de dos débiles, sobre un dominio de autoridad 2.
- Sin páginas de filtro que rastrear, indexar ni mantener.
- Elimina la contradicción entre la condición de revisión y `03-wordpress-content-model` §3.
- Coherente con el precedente del proyecto: `event_type` es taxonomía real y no tiene archivo público.

**Contrapartidas aceptadas**

- No habrá una dirección que se pueda compartir con «todos los encuentros de Bogotá». Se comparte
  `/sanghas/bogota`, que además da contexto.
- Si en el futuro alguna ciudad concentrara mucha actividad, habría que reabrir la cuestión — pero con
  el criterio de contenido de arriba, no con un conteo.

## Referencias

- `.audit/working/url-hypotheses.md` §2c (análisis original, 2026-07-20) y `.audit/decisions.md`
- **TASK-0020** (páginas por ciudad, BLOCKED) y **TASK-0022** (historial de encuentros por ciudad) en `.audit/`
- `03-wordpress-content-model` §3 (escalado del listado por año) y §4 (taxonomías)
- `11-arbol-urls-final` §3 y §3.1 (rutas de eventos y sanghas)
- Google — [Managing crawling of faceted navigation URLs](https://developers.google.com/search/docs/crawling-indexing/crawling-managing-faceted-navigation)
- ADR [0008](0008-urls-estables-desde-la-maqueta.md) (política de URLs canónicas)
