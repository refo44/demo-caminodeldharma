# Análisis de hipótesis de URL y páginas por ciudad (2026-07-20)

> **CORRECCIÓN 2026-07-21 — GBP descartado.** Todo lo que este documento afirma sobre Google Business Profile como acción prioritaria queda **retirado**: la comunidad no tiene sede ni dirección física y no es elegible según las directrices de Google (las entidades exclusivamente en línea quedan excluidas; se exige dirección real verificable aunque se oculte al público). Las consultas locales dejan de contabilizarse como brecha alcanzable. El texto original se conserva sin modificar como registro del análisis del 19–20 de julio. Ver `decisions.md`.


Propuestas del propietario, evaluadas **antes de implementar**, con la instrucción explícita de crear
tareas solo si suponen una mejora real y omitirlas si no. Ninguna se ha implementado.

---

## Hipótesis 1 — Renombrar `/comunidad` → `/comunidad-budista` o `/comunidad-budista-colombia`

**Veredicto: NO IMPLEMENTAR.** No se crea tarea.

### Evidencia empírica en contra

**a) El propio sitio la refuta.** `caminodeldharma.org` es **#1 en Google CO** para "budismo chan
colombia" y "budismo tierra pura colombia" (EVID-0037). La URL que ocupa ese primer puesto es `/` —
**sin ninguna keyword en la ruta**. Las mejores posiciones del sitio provienen hoy de una URL
completamente neutra.

**b) Los competidores que rankean tampoco las usan.** Consulta "budismo medellin" (Google CO,
2026-07-20): las seis URLs orgánicas del top son **portadas de dominio**, ninguna con keyword en la
ruta:

```
montanadesilencio.org · budismocolombia.co · kadampa.org
espanol.buddhistdoor.net · budismocolombia.org · meditacionencolombia.org
```

**c) La keyword ya está donde sí pesa.** El `<title>` de la página es «Comunidad Budista en Colombia
| Camino del Dharma». El título es una señal de ranking muy superior a la cadena de la URL.

### Costo real del cambio

| Costo | Detalle |
|---|---|
| Pérdida de señal | Todo 301 disipa parte del valor acumulado |
| Indexación | Solo **4 de 13** URLs aparecen hoy en `site:`; renombrar reinicia lo poco consolidado |
| Proceso (ADR 0008) | Exige actualizar `11-arbol-urls-final`, `sitemap.xml`, redirects, **ADR nuevo** si es estructural, y solicitar reindexación. Puede implicar incremento **MAJOR** de versión |
| Migración pendiente | Cambiar URLs ahora y posiblemente otra vez en el corte a WordPress es rotación innecesaria |

**Conclusión:** beneficio marginal y no demostrado, contra un costo cierto y un ADR que declara la
estructura de URLs **definitiva**. La palanca real para esas consultas está en autoridad (SEO-EXT-001)
y presencia local, no en el slug.

---

## Hipótesis 2 — Páginas por ciudad (Bogotá, Medellín, Cali, Barranquilla)

**Veredicto: PROMETEDORA, pero condicionada.** Se crea **TASK-0020 (BLOCKED)**.

### Por qué sí tiene fundamento

La brecha está medida: el sitio **no aparece en página 1** de "budismo cali", "budismo medellin" ni
"budismo en colombia" (EVID-0037/0040). Y los competidores ganan ahí exactamente con este formato.

### La prueba de por qué funciona — y qué la hace funcionar

`meditacionencolombia.org/cali` es **#2 en Google CO para "budismo cali"**. Se inspeccionó su
contenido (2026-07-20):

| Elemento | Presente |
|---|---|
| Dirección física | ✅ dos sedes con calle y número |
| Horario concreto | ✅ «Martes de 7:00 a 8:30 p. m.» |
| Actividades específicas | ✅ ciclo con nombre propio + curso puntual con fecha |
| Precios | ✅ clase suelta, bono de 4, membresía |
| Contacto | ✅ WhatsApp |
| Fotos | ✅ dos |
| Texto único | ✅ ~1.200–1.400 palabras |

**El hallazgo clave: no rankea por tener «cali» en la URL, sino por ser una página real con
información que nadie más tiene.** La URL descriptiva acompaña; no es la causa. Esto es coherente con
la Hipótesis 1: para páginas **nuevas** el slug descriptivo no cuesta nada (no hay 301 ni señal que
perder), pero tampoco es lo que produce el resultado.

### Condición bloqueante

**¿Existen actividades reales y sostenidas en cada ciudad?** Lo verificado hasta ahora:

| Ciudad | Evidencia encontrada |
|---|---|
| Cali | Fundación de la comunidad (2012); taller «Pausa Profunda – Cali» (finalizado) |
| Barranquilla | Encuentro Nacional 2026 en Puerto Colombia (evento puntual, no sangha permanente) |
| Medellín | Mención en Facebook («En Medellín compartimos…») — sin confirmar en el sitio |
| Bogotá | **Sin evidencia** en el sitio |

**Riesgo si no hay actividad real:** páginas delgadas o duplicadas por ciudad son *doorway pages*,
explícitamente sancionadas por las directrices de Google. Sobre un dominio con **DA 2** que además
está intentando construir autoridad, una acción manual sería especialmente dañina. **Una página por
ciudad sin sangha real haría más daño que no tenerla.**

### Prioridad relativa: primero el perfil de empresa

En **todas** las consultas locales probadas, los tres primeros puestos los ocupa el *pack local* de
Google (fichas de Business Profile). Una página de ciudad compite por los puestos orgánicos **por
debajo** del pack; el GBP compite **dentro** de él. Por eso TASK-0020 va **después** de la decisión de
GBP (TASK-0014) y no antes.

---

## Hipótesis 2b — Estructura de URL de las páginas por ciudad

Planteadas por el propietario: `/cali` (raíz) o `/comunidad/cali` (anidada).

**Veredicto: ninguna de las dos. La estructura correcta ya está decidida en el propio proyecto:
`/sanghas/{ciudad}`, con listado en `/sanghas/`.**

### El proyecto ya lo diseñó

`docs/11-arbol-urls-final.md` §3.1 define, desde antes de esta auditoría:

```
| Listado | /sanghas/        |
| Single  | /sanghas/{slug}/ |
```

Y `docs/03-wordpress-content-model.md` §3.1 especifica el CPT con sus campos:
`sangha_name`, `sangha_city`, `sangha_contact_name`, `sangha_contact_whatsapp`,
`sangha_schedule`, `sangha_map_url`.

El origen está en `docs/01-plataforma-comunidad-plan.md`: fue una petición de la propia comunidad
—«que la página conecte con las sanghas, con un contacto de cada sangha»—, no una idea de SEO.

**Coincidencia notable:** esos campos son exactamente el contenido que hace rankear a
`meditacionencolombia.org/cali` (horario, contacto, ubicación). El modelo de contenido ya anticipaba
los ingredientes correctos.

### Por qué la estructura no cambia el SEO, pero sí la arquitectura

Empíricamente **ambas formas rankean**, lo que confirma que la estructura no es el factor:

| Competidor | Estructura | Resultado |
|---|---|---|
| `meditacionencolombia.org/cali` | plana (raíz) | #2 en "budismo cali" |
| `budismocolombia.co/centros/centro-cali` | anidada con listado | rankea en consultas locales |

El competidor de mayor alcance usa precisamente el patrón `/{seccion}/{slug}` — el mismo que
`/sanghas/{ciudad}`.

### Comparación de las tres opciones

| Opción | Convención del sitio | Breadcrumb | Página hub | Migración WP | Veredicto |
|---|---|---|---|---|---|
| `/cali` | ✗ rompe el árbol (todo lo demás es sección o anidado) | Inicio > Cali (pobre) | ✗ ninguna | sin CPT definido | descartada |
| `/comunidad/cali` | ~ convierte `/comunidad` en híbrido: es una página «quiénes somos», no un contenedor | Inicio > La comunidad > Cali | ✗ ninguna | sin CPT definido | aceptable, pero peor |
| **`/sanghas/cali`** | ✓ ya está en el árbol final | ✓ Inicio > Sanghas > Cali | ✓ `/sanghas/` | ✓ CPT ya especificado | **recomendada** |

### La ventaja que las otras dos no tienen: el listado

`/sanghas/` es en sí mismo un activo SEO —objetivo para consultas del tipo «sanghas budistas
Colombia», «dónde practicar budismo en Colombia»— y funciona como hub de enlazado interno hacia cada
ciudad. `/cali` suelto no aporta hub alguno, y las páginas quedarían huérfanas salvo enlaces manuales.

### Matiz sobre las keywords

«Sanghas» es término de practicante, no de búsqueda masiva. **No importa**: la ruta no es donde
compiten las keywords (§Hipótesis 1). El reparto correcto es **URL = arquitectura, título = keywords**:

```
URL:    /sanghas/cali
<title> Comunidad Budista en Cali — Camino del Dharma
```

Así se respeta el árbol de URLs y se conserva la intención de búsqueda donde sí pesa.

### Nota de alcance

`docs/03-wordpress-content-model.md` advierte: *«No implementar en fase inicial salvo necesidad real;
evita scope creep»*. Esa cautela sigue vigente y coincide con el bloqueo de TASK-0020: la necesidad
real es precisamente lo que hay que confirmar antes de crear nada.

---

## Hipótesis 2c — Contenido de la página de sangha y filtrado de eventos por ciudad

### Qué más puede contener una página de sangha

Los campos ya definidos en `docs/03` §3.1 (nombre, ciudad, contacto, WhatsApp, horario, mapa) son la
base. Lo que añade valor real —y lo que diferencia una página que rankea de una ficha vacía—:

| Bloque | Por qué |
|---|---|
| **Modalidad** (presencial / en línea / mixta) | Primera pregunta de quien busca; determina si el sitio le sirve |
| **Qué se practica ahí** (meditación, estudio, recitación) | Contenido único por ciudad; evita que las páginas se parezcan |
| **Quién acompaña o referencia** | Señal de confianza; el competidor que rankea no la tiene → oportunidad |
| **Cómo asistir la primera vez** | Baja la barrera de entrada. Es el bloque con mayor efecto en conversión, no solo en SEO |
| **Fotos reales de ese grupo o lugar** | Contenido propio no duplicable; también alimenta el perfil de empresa |
| **Próximos encuentros en esa ciudad** | La subsección de eventos que se propone — ver abajo |
| **Preguntas frecuentes de esa ciudad** | Captura consultas en lenguaje natural (coherente con ASO-001) |

Datos estructurados sugeridos: `Place` o subentidad de `Organization` con la localidad, más los
`Event` de esa ciudad. No `LocalBusiness` salvo que haya sede física con horario de atención.

### Taxonomía de ciudad para eventos: **sí, como dato**

Necesaria para asociar cada evento con su sangha y poder listarlos. Encaja en ambas versiones:

| Versión | Implementación |
|---|---|
| Estática (actual) | Atributo o clase en el marcado del evento; la sección «en esta ciudad» se mantiene a mano |
| WordPress | Taxonomía `event_city` sobre el CPT `event`, junto a la ya existente `event_type` |

### URLs públicas `/eventos/cali`: **no, y hay un motivo técnico concreto**

**1. Conflicto de rutas.** `docs/11` §3 ya define `/eventos/{slug}/` para el evento individual. Una
ruta `/eventos/cali` es indistinguible de un evento cuyo slug sea «cali»: **WordPress la resolvería
como single de evento, no como archivo de ciudad.** Requeriría una base distinta —`/eventos/ciudad/cali`—
lo que ya no es la propuesta original.

**2. Contenido delgado.** Hoy existen **dos** eventos en todo el sitio (uno vigente, uno finalizado).
Archivos por ciudad tendrían 0 o 1 elemento cada uno: páginas casi vacías, exactamente el patrón que
Google penaliza y el mismo riesgo que ya bloquea TASK-0020.

**3. Canibalización.** `/sanghas/cali` y `/eventos/cali` competirían por la misma consulta
(«budismo cali», «eventos budistas cali»). Con **DA 2**, repartir señal entre dos páginas débiles es
peor que concentrarla en una sólida.

**4. Precedente del propio proyecto.** `event_type` es una taxonomía real desde el diseño inicial y
**no tiene URL pública de archivo** en `docs/11`. Se usa como etiqueta y filtro, no como página. La
ciudad debería seguir el mismo criterio.

### Recomendación

**Los eventos de la ciudad se muestran dentro de `/sanghas/{ciudad}`**, no en una URL propia. Una sola
página fuerte por ciudad, que reúne identidad + práctica + contacto + próximos encuentros. Es además
lo que la propia propuesta apuntaba con «una subsección de eventos que aplique solo para esa ciudad».

**Revisar cuando haya volumen:** si una ciudad supera ~5 eventos y la sección se vuelve inmanejable
dentro de la página, entonces sí crear archivo público —pero como `/eventos/ciudad/{ciudad}` para
evitar el conflicto de rutas, y con ADR que lo justifique (ADR 0008).

### Costo de mantenimiento en la versión estática

Con dos eventos, mantener a mano la sección por ciudad es trivial. A medida que crezca, cada evento
nuevo obligará a tocar varias páginas. Es un argumento más para **no** multiplicar URLs ahora y dejar
el filtrado real para WordPress, donde la taxonomía lo resuelve sola.

---

## Resumen de decisiones

| Propuesta | Decisión | Motivo |
|---|---|---|
| Renombrar `/comunidad` | **Omitida** — sin tarea | Refutada por los datos del propio sitio y de los competidores; costo cierto contra beneficio no demostrado; contradice ADR 0008 |
| Páginas por ciudad | **TASK-0020 (BLOCKED)** | Fundamento sólido, pero exige confirmar actividad real por ciudad y contenido sustancial; se secuencia tras GBP |
| Estructura `/cali` o `/comunidad/cali` | **Ninguna** — se adopta `/sanghas/{ciudad}` | Ya definida en `11-arbol-urls-final` §3.1 y con CPT en `03-wordpress-content-model` §3.1; aporta página hub `/sanghas/` y sobrevive a la migración |
| Taxonomía de ciudad para eventos | **Sí, como dato** (`event_city`) | Necesaria para asociar eventos y sanghas; sirve en estático y en WordPress |
| URLs públicas `/eventos/cali` | **No por ahora** | Conflicto de rutas con `/eventos/{slug}`, contenido delgado (2 eventos en total) y canibalización con `/sanghas/{ciudad}`. Precedente: `event_type` tampoco tiene archivo público |
