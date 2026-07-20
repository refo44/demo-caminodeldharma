# Informe de Rendimiento SEO — Camino del Dharma

| | |
|---|---|
| **Tipo de documento** | Informe de Rendimiento SEO (entrega periódica de resultados) |
| **Dominio** | https://caminodeldharma.org |
| **Periodo cubierto** | 2026-07-17 → 2026-07-20 (**4 días**) |
| **Naturaleza de esta edición** | **Informe base (T0)** — no hay periodo anterior con el que comparar |
| **Cadencia propuesta** | Trimestral |
| **Próxima edición** | Entre 2026-08-17 y 2026-09-14 (4–8 semanas) |

---

## Advertencia sobre el alcance de esta edición

Un Informe de Rendimiento SEO, en su forma habitual, muestra **evolución del tráfico orgánico, conversiones, ventas y retorno de la inversión**. Esta edición **no puede mostrar tres de esas cuatro cosas**, y conviene decir por qué antes de mostrar cifra alguna:

| Elemento esperado | Estado | Motivo |
|---|---|---|
| Tráfico orgánico | ✅ Disponible, con reservas | Search Console activo desde el 2026-07-17. Solo **2 días** con datos |
| Evolución vs. periodo anterior | ❌ No disponible | **No existe periodo anterior**: el sitio se publicó el 2026-07-18 |
| Conversiones | ❌ No medibles | **No hay analítica instalada** — decisión deliberada, ADR 0019 |
| Ventas / ingresos | ❌ No aplica | El sitio **no es comercial**: no hay transacciones que medir |
| ROI | ❌ No calculable | Sin ingresos ni coste de campaña, la fórmula no tiene términos |

**Esto no es una carencia que haya que subsanar: en su mayor parte es una consecuencia coherente de decisiones tomadas a conciencia.** La §5 explica qué se medirá en su lugar, y por qué esa alternativa dice más sobre esta comunidad que un embudo de conversión.

Ninguna cifra de este documento se extrapola. Lo que no se pudo medir aparece como limitación, no como estimación.

---

## 1. Resumen de la edición

El sitio se publicó el **2026-07-18**. Cuatro días después, el balance es:

- **9 clics y 35 impresiones** desde Google. Volumen mínimo, esperable a esa edad.
- **Una sola consulta genera impresiones: el nombre de la comunidad.** Posición media 3,35.
- **Tres consultas en posición #1** en Google Colombia: la marca, «budismo chan colombia» y «budismo tierra pura colombia».
- **Cero presencia en página 1** para consultas amplias y locales.
- **14 de 20 tareas de la auditoría completadas** y desplegadas en producción (v1.0.14).

**La lectura correcta del periodo:** no es un informe de resultados, es la **línea de salida**. Alcanzar el #1 en dos consultas de nicho con dos días de vida es un punto de partida mejor de lo habitual.

---

## 2. Tráfico orgánico

Fuente: Google Search Console, export del propietario 2026-07-20 (EVID-0052). Filtro: búsqueda web, últimos 28 días — **con datos solo en 2 de esos días**.

| Métrica | Valor | Comparación |
|---|---:|---|
| Clics | **9** | sin base previa |
| Impresiones | **35** | sin base previa |
| CTR medio | 25,7 % | sin base previa |
| Posición media | 3,35 (consulta de marca) | sin base previa |

### Distribución

| Corte | Detalle |
|---|---|
| **País** | Colombia 8 clics / 32 impr. · España 1/2 · Vietnam 0/1 |
| **Dispositivo** | Móvil 6 clics / 7 impr. · Escritorio 3 / 28 |
| **Páginas** | `https://` 7 clics (pos. 3,0) · `https://www.` 1 (pos. 8,33) · `http://` 1 (pos. 1,0) |

**Lo único que estas cifras permiten afirmar con solidez:** el mercado objetivo responde (Colombia aporta 8 de 9 clics) y la localizabilidad es **exclusivamente por marca**.

**Lo que no permiten afirmar:** nada sobre tendencia, estacionalidad ni eficacia de acciones. Con 9 clics y dos días, un solo visitante mueve cualquier porcentaje varios puntos.

> **Dato de higiene técnica, no de tráfico:** Google contabiliza `https://`, `https://www.` y `http://` como páginas separadas. Las tres redirigen correctamente (301 verificado); la consolidación debería ocurrir sola. **Vigilar en la próxima edición.**

---

## 3. Visibilidad conseguida

| Consulta | Posición en Google CO |
|---|:---:|
| camino del dharma (marca) | **#1** |
| budismo chan colombia | **#1** |
| budismo tierra pura colombia | **#1** + PAA |
| budismo en colombia | fuera de página 1 |
| comunidad budista colombia | fuera de página 1 |
| budismo cali | fuera de página 1 |

Detalle completo en el [informe 04](04-posicionamiento-palabras-clave.md).

---

## 4. Trabajo ejecutado en el periodo

Origen: auditoría completa del 2026-07-19 y sus continuaciones. **Etapa 2 (implementación) cerrada el 2026-07-20** con deploy v1.0.14.

| Bloque | Estado |
|---|---|
| Limpieza del índice residual de WordPress (410 `/prueba`, 301 `category/*`, 301 `?page_id=*`) | ✅ Desplegado |
| Señales locales: fundación en Cali en 2012 en JSON-LD y texto visible | ✅ Desplegado |
| `knowsAbout` (Chan, Tierra Pura, meditación, atención plena) en JSON-LD | ✅ Desplegado |
| Titles temáticos en `/eventos` y `/blog` | ✅ Desplegado |
| Corrección de la ruta de descarga `.ics` | ✅ Desplegado |
| CTAs de WhatsApp/correo sustituyendo el formulario sin backend | ✅ Desplegado |
| Alta y verificación en Search Console | ✅ Hecho |
| Google Business Profile + 3 peticiones de enlace + URL en redes | ✅ Solicitado |
| Nota editorial «Sobre la palabra Buddhismo» en `/linaje` | ✅ Desplegado |

**14 de 20 tareas completadas. 6 bloqueadas** por decisiones que solo la comunidad puede tomar:

| Tarea | Decisión pendiente |
|---|---|
| TASK-0003 | ¿Formulario con backend real o solo WhatsApp/correo? |
| TASK-0016 | Plan editorial y temas de contenido |
| TASK-0020 | ¿En qué ciudades hay sangha real? (sin actividad confirmada serían *doorway pages*) |
| TASK-0004/0005 | HSTS — aplazado deliberadamente hasta después de WordPress (ADR 0020) |
| TASK-0012 | Inventario DNS (requiere acceso al panel de Hostinger) |

**TASK-0016 es la que más frena el resultado de la próxima edición:** sin plan editorial no hay contenido nuevo, y sin contenido nuevo el crecimiento depende únicamente de las peticiones de enlace.

---

## 5. Qué se medirá en lugar de conversiones y ROI

**No se usará Google Analytics.** Decisión definitiva del propietario, formalizada en **ADR 0019**. El motivo no es ideológico sino de utilidad: con CrUX sin datos, DA 2 y ausencia de la página 1 en consultas amplias, GA4 produciría un puñado de sesiones al mes — ruido estadístico, no información. **El cuello de botella medido no es qué hacen las visitas, sino que no llegan**, y esa pregunta la responde Search Console: gratis, sin cookies y sin banner.

Se suma que el propósito del sitio no es comercial. La señal de participación real —que alguien acuda a la meditación del lunes— **ya se observa directamente**, y quien se acerca pasa por un canal humano donde puede preguntarse cómo llegó. A esta escala eso aporta más que cualquier analítica agregada.

> **Verificado:** producción no sirve **ninguna cookie propia**. Es una posición poco común que conviene preservar: elimina la necesidad de banner, reduce la superficie legal y encaja con el registro editorial de la comunidad.

### Indicadores de esta comunidad

En lugar de conversiones y ROI, el rendimiento se evaluará con:

| Indicador | Fuente | Frecuencia |
|---|---|---|
| Clics e impresiones orgánicas | Search Console | Trimestral |
| Nº de consultas distintas que generan impresiones | Search Console | Trimestral |
| Proporción marca vs. no-marca | Search Console | Trimestral |
| URLs indexadas de las 13 del sitemap | Search Console | Trimestral |
| Posición en las keywords objetivo | SERP manual | Trimestral |
| Autoridad de dominio (DA) y brecha con la mediana del sector | SEO Review Tools | Trimestral |
| Dominios de referencia temáticos que enlazan | Verificación directa | Trimestral |
| **Asistentes a la meditación de los lunes** | Observación de la comunidad | Continua |
| **Contactos entrantes por WhatsApp/correo** | Observación de la comunidad | Continua |

Los dos últimos son los que de verdad importan y no salen de ninguna herramienta.

**Si algún día hiciera falta medir comportamiento**, la vía será analítica sin cookies (Plausible, Fathom o equivalente), nunca volver a GA4.

---

## 6. Objetivos para la próxima edición

| Objetivo | T0 | Meta |
|---|:---:|:---:|
| Consultas distintas con impresiones | 1 | ≥ 10 |
| Proporción de clics no-marca | 0 % | > 20 % |
| URLs con impresiones | 3 (variantes de la portada) | ≥ 6 páginas distintas |
| Autoridad de dominio (DA, misma herramienta) | 2 | 8 |
| Dominios temáticos que enlazan | 0 | ≥ 2 |
| Keywords en top 10 de Google CO | 3 | 5 |
| Variantes de host consolidadas en el índice | no | sí |

El objetivo de DA 8 no es arbitrario: iguala a `budismocolombia.org` y `centroyamantaka.org`, y es alcanzable porque el problema demostrado **no es volumen de enlaces sino calidad** — ese dominio tiene 214 enlaces frente a los 207 propios, y cuatro veces la autoridad.

---

## 7. Limitaciones de esta edición

1. **Ventana de 2 días.** Ninguna conclusión sobre eficacia de acciones SEO es sostenible hasta re-medir en 4–8 semanas.
2. **Sin periodo de comparación.** Toda la sección de evolución está vacía por construcción.
3. **Sin datos de conversión ni ingresos** (ADR 0019 + sitio no comercial). **No se estiman.**
4. **Sin datos de campo (CrUX).** El origen no alcanza el umbral de tráfico de Google. INP no medible.
5. **Métricas de autoridad de terceros**, no mediciones propias. Comparables solo contra sí mismas y con la misma herramienta.
6. **Los CTR de cortes pequeños no son interpretables** (móvil: 85,71 % sobre 7 impresiones).

---

## 8. Conclusión

**El sitio está técnicamente listo y comercialmente invisible, y las dos cosas son ciertas a la vez.** SEO interno 100, rendimiento 99/100, datos estructurados impecables — y un DR de 0,4 que impide que ese trabajo llegue a nadie.

La causa está identificada y es **inusualmente fácil de corregir**: tres fuentes temáticas autorizadas hablan de la comunidad y ninguna enlaza al dominio. No hace falta link building desde cero; hace falta pedirlo.

**El siguiente informe, en 4–8 semanas, es el primero que podrá medir si eso funcionó.** Medir antes solo produciría conclusiones falsas.

---

## Fuentes

`.audit/raw/gsc/` (EVID-0052) · `.audit/executive-summary.md` · `.audit/state.md` · `.audit/working/seo-external.md` §11 · `.audit/working/authority-backlinks.md` · `.audit/limitations.md` · ADR 0019 (sin analítica con cookies) · ADR 0020 (HSTS aplazado)
