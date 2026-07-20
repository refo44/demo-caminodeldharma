# Análisis de hipótesis de URL y páginas por ciudad (2026-07-20)

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

### Coste real del cambio

| Coste | Detalle |
|---|---|
| Pérdida de señal | Todo 301 disipa parte del valor acumulado |
| Indexación | Solo **4 de 13** URLs aparecen hoy en `site:`; renombrar reinicia lo poco consolidado |
| Proceso (ADR 0008) | Exige actualizar `11-arbol-urls-final`, `sitemap.xml`, redirects, **ADR nuevo** si es estructural, y solicitar reindexación. Puede implicar incremento **MAJOR** de versión |
| Migración pendiente | Cambiar URLs ahora y posiblemente otra vez en el corte a WordPress es rotación innecesaria |

**Conclusión:** beneficio marginal y no demostrado, contra un coste cierto y un ADR que declara la
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

## Resumen de decisiones

| Propuesta | Decisión | Motivo |
|---|---|---|
| Renombrar `/comunidad` | **Omitida** — sin tarea | Refutada por los datos del propio sitio y de los competidores; coste cierto contra beneficio no demostrado; contradice ADR 0008 |
| Páginas por ciudad | **TASK-0020 (BLOCKED)** | Fundamento sólido, pero exige confirmar actividad real por ciudad y contenido sustancial; se secuencia tras GBP |
| Estructura `/cali` o `/comunidad/cali` | **Ninguna** — se adopta `/sanghas/{ciudad}` | Ya definida en `11-arbol-urls-final` §3.1 y con CPT en `03-wordpress-content-model` §3.1; aporta página hub `/sanghas/` y sobrevive a la migración |
