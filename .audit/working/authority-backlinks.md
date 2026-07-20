# Autoridad de dominio y perfil de enlaces (continuación 2026-07-20)

Cuantifica lo que SEO-EXT-001 afirmaba cualitativamente ("autoridad casi nula"). Métricas de
autoridad (DA/PA/DR/UR/Spam Score) + verificación reproducible de dominios de referencia.

## 1. Métricas obtenidas

| Métrica | Valor | Escala | Fuente | Fecha |
|---|---:|---|---|---|
| **DR** (Domain Rating, Ahrefs) | **0,4** | 0–100 | Dapachecker DR/UR (ejecución manual del propietario) | 2026-07-20 |
| **UR** (URL Rating, Ahrefs) | **4** | 0–100 | Dapachecker DR/UR (manual) | 2026-07-20 |
| **DA** (Domain Authority, Moz) | **6** | 0–100 | Dapachecker DA/PA (manual) | 2026-07-20 |
| **PA** (Page Authority, Moz) | **18** | 0–100 | Dapachecker DA/PA (manual) | 2026-07-20 |
| **Spam Score** (Moz) | **7 %** | 0–100 % | Dapachecker DA/PA (manual) | 2026-07-20 |
| **Antigüedad del dominio** | **7 años 5 meses** | — | Dapachecker DA/PA (manual) | 2026-07-20 |
| DA (estimación Semrush) | 2 | 0–100 | SEO Review Tools (agente) | 2026-07-20 |
| PA (estimación Semrush) | 8 | 0–100 | SEO Review Tools (agente) | 2026-07-20 |
| Enlaces externos → dominio | 207 | conteo | SEO Review Tools (agente) | 2026-07-20 |
| Enlaces externos → portada | 163 | conteo | SEO Review Tools (agente) | 2026-07-20 |
| **Compartidos en redes (portada y dominio)** | **0 / 0** | conteo (FB + Pinterest) | SEO Review Tools (export manual del propietario) | 2026-07-20 |

Los cuatro valores de SEO Review Tools fueron **confirmados de forma independiente** por ejecución
manual del propietario (CSV en `raw/seo-external/seoreviewtools-export-2026-07-20.csv`, EVID-0045):
DA 2, PA 8, 207 y 163 — idénticos a la medición del agente. La confianza pasa de fuente única a
contrastada.

**No obtenidas:** Trust Flow / Citation Flow / ratio TF-CF (Majestic requiere registro; los
comprobadores gratuitos consultados no los exponen) y Authority Score de Semrush nativo. Registrado
como limitación, **no** extrapolado.

## 2. Interpretación

**a) La cifra decisiva: DR 0,4 con un dominio de 7 años y 5 meses.** No es un sitio nuevo que
necesite tiempo para madurar; es un dominio **establecido que nunca acumuló enlaces**. Descarta el
argumento de "hay que esperar": siete años y medio de historial sin adquisición de autoridad.

**b) No hay problema de enlaces tóxicos — hay ausencia de enlaces.** Spam Score 7 % es **sano**
(riesgo bajo). La estrategia correcta es **adquisición**, no desautorización (`disavow`). Esto responde
directamente a la disyuntiva habitual de una auditoría de enlaces: aquí no toca limpiar, toca construir.

**c) Discrepancia entre proveedores — tratar con cautela.** DA 6 (Moz) vs DA 2 (estimación Semrush)
es normal: escalas y grafos de enlaces distintos. Ambos coinciden en el diagnóstico: autoridad
mínima. El dato "207 enlaces externos al dominio" **no es coherente** con DR 0,4: probablemente
cuenta enlaces duplicados, de baja calidad, sociales o de directorios automáticos. **No usar ese
conteo como evidencia de perfil de enlaces**; el número que importa son los *dominios de referencia*
únicos y temáticamente relevantes (§3).

**d) Compartidos sociales: métrica inservible, no señal del sitio.** ~~El 0/0 indicaba que ni la
propia audiencia amplifica el contenido.~~ **CORREGIDO 2026-07-20 con el baseline (EVID-0046):** los
**seis** dominios medidos devuelven exactamente 0, incluidos los líderes del sector. Un cero
universal indica que la herramienta **no está recolectando** el dato (Facebook restringió el acceso
público a los conteos de compartidos), no que estos sitios no se compartan. **Descartada como
evidencia.** Precedente: un cero idéntico en toda la muestra es señal de fallo de medición, no de
hallazgo — solo el baseline de competidores permitió detectarlo.

**e) PA 18 / UR 4 frente a DA 6 / DR 0,4:** la portada concentra lo poco que hay; las páginas internas
carecen de fuerza propia, coherente con que solo 4 de 13 URLs aparezcan en `site:` (SEO-EXT-002).

## 3. Dominios de referencia — verificación directa (lo más fiable de este bloque)

Los conteos de las herramientas son cajas negras. Esto sí es reproducible:

| Fuente | Relación con la comunidad | ¿Enlaza al dominio? | Verificado |
|---|---|---|---|
| **Buddhistdoor en Español** (artículo de referencia sobre budismo en Colombia) | La describe en profundidad como LA referencia Chan del país | **NO** — enlaza solo a Facebook | 2026-07-19 (EVID-0035) |
| **EcoEspiritualidad** (ficha de comunidad; **#2 en Google para la marca**) | Ficha completa: propósito, liderazgo, congresos internacionales del abad | **NO** — ningún enlace externo | 2026-07-20 (EVID-0044) |
| **budismo.com** (directorio de centros de Colombia) | — | **NO LISTADA** (11 centros listados, ella ausente) | 2026-07-19 (EVID-0036) |
| Facebook (6,8 K seguidores) / Instagram | Perfiles propios | Enlaces sociales (`nofollow`, sin transferencia de autoridad) | 2026-07-19/20 |

**Patrón consistente en tres fuentes temáticas autorizadas: citan a la comunidad, pero no enlazan al
dominio.** Eso explica DR 0,4 pese a 7,5 años y pese a ser una entidad reconocida — y es una causa
**inusualmente fácil de corregir**: no requiere link building desde cero, sino pedir a quienes ya
escriben sobre la comunidad que añadan el enlace, y darse de alta donde ya deberían estar.

## 4. CORRECCIÓN de una conclusión propia (continuación 2026-07-19)

En `seo-external.md` §8 registré ecoespiritualidad.org como *"cita/backlink existente"*. **Era una
inferencia, no una verificación**: deduje el enlace de que la ficha apareciera en el SERP. Comprobada
la página (EVID-0044), **no contiene ningún enlace saliente al dominio**. Corregido allí y aquí.
Es exactamente el error que la regla de "no extrapolar" busca evitar; queda como precedente.

## 5. Baseline de competidores — OBTENIDO 2026-07-20

Ejecución manual del propietario, **misma herramienta (SEO Review Tools) y misma fecha** para los
seis dominios: requisito de comparabilidad cumplido. CSV en `raw/seo-external/` (EVID-0046).

| Dominio | DA | PA | Enlaces → dominio | Enlaces → portada |
|---|---:|---:|---:|---:|
| sotozencolombia.org | **20** | 42 | 585 | 387 |
| budismocolombia.co | **18** | 2 | 368 | 263 |
| meditacionencolombia.org | **17** | 51 | 1 327 | 779 |
| budismocolombia.org | 8 | 1 | 214 | 155 |
| centroyamantaka.org | 8 | 16 | 496 | 411 |
| **caminodeldharma.org** | **2** | 8 | 207 | 163 |

DA de competidores: mínimo 8 · **mediana 17** · máximo 20.

### Lectura estratégica — escenario favorable

**a) El nicho entero es de autoridad baja.** Ningún competidor supera DA 20; la mediana es 17. No hay
ningún actor dominante con DA 40+ contra el que competir. Este es explícitamente el escenario
"alcanzable", no el de "replantear la estrategia": las brechas son de **6 puntos** al peldaño más
cercano (DA 8), 15 a la mediana y 18 al líder.

**b) El hallazgo más accionable: no faltan enlaces, faltan enlaces BUENOS.**
`budismocolombia.org` tiene **214** enlaces externos al dominio — prácticamente los mismos **207**
que caminodeldharma.org — y sin embargo **DA 8 frente a DA 2**: cuatro veces la autoridad con el
mismo volumen. La diferencia no es cantidad sino **calidad y diversidad de dominios de referencia**.
Confirma §2c (el conteo de 207 no es evidencia de perfil de enlaces) y valida la estrategia de
TASK-0014: tres enlaces temáticos de calidad valen más que cientos de enlaces indistintos.

**c) Los dos que ganan los SERPs son los que suman DA y PA altos.**
`meditacionencolombia.org` (DA 17 / PA 51 / 1 327 enlaces) y `sotozencolombia.org` (DA 20 / PA 42)
son exactamente los que dominaron las consultas tipo pregunta y las locales que probé. Coherencia
completa entre autoridad medida y visibilidad observada.

**d) Inversiones DA/PA anómalas — no sobreinterpretar.** `budismocolombia.org` (DA 8 / PA 1) y
`budismocolombia.co` (DA 18 / PA 2) presentan portadas con PA muy inferior a su DA, lo que suele
indicar que la herramienta midió una redirección o una forma de URL distinta. Se registran como
observados, sin construir conclusiones sobre ellos.

### Meta de autoridad propuesta

**Primer hito realista: DA 8** (igualar a `budismocolombia.org` y `centroyamantaka.org`) — 6 puntos,
alcanzable con las tres peticiones de enlace de TASK-0014 más el alta en directorio, porque el
problema demostrado es de calidad y no de volumen. **Segundo hito: DA 15–17** (mediana del sector),
que ya requiere el plan editorial sostenido (TASK-0016) y presencia local (GBP). Re-medir
trimestralmente **con esta misma herramienta**.

## 6. Fiabilidad de las herramientas — advertencia metodológica

Los **comprobadores de "PageRank"** (`dnschecker.org/pagerank.php`, `smallseotools`, `prchecker.info`)
**no se usaron como evidencia**. Google retiró el PageRank público en 2016 y no publica ningún valor
desde entonces; lo que muestran esas páginas son puntuaciones propias no auditables o valores
históricos caducados, presentados como si fueran de Google. Incluir esos números como métricas de
auditoría sería fabricar evidencia. `prchecker.info` además exige CAPTCHA.

Las métricas de §1 sí proceden de grafos de enlaces reales (Moz, Ahrefs, Semrush vía intermediarios)
y se reportan **con proveedor, fecha y escala**, como estimaciones de terceros — nunca como
mediciones propias.

## 7. Impacto en hallazgos existentes

- **SEO-EXT-001:** su causa "autoridad casi nula" queda **cuantificada** (DR 0,4; DA 2–6) y con causa
  raíz precisa: citación editorial sin enlace. Sin cambio de severidad.
- **ASO-001:** refuerzo relevante — la propia documentación de SEO Review Tools cita investigación
  (Kevin Indig) según la cual la autoridad de dominio **correlaciona con las menciones en plataformas
  de IA** (ChatGPT, AI Mode, Perplexity). Coherente con lo observado: el AI Overview de marca no
  cita al sitio. La autoridad no es solo un asunto de SEO clásico: condiciona la citación agéntica.
- **TASK-0014:** pasa de genérica a específica y accionable (§3): tres peticiones concretas, cada
  una a una fuente que ya escribe sobre la comunidad.
