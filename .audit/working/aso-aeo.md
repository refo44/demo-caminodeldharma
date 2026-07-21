# Auditoría ASO / AEO — visibilidad y accionabilidad agéntica (continuación 2026-07-20)

> **CORRECCIÓN 2026-07-21 — GBP descartado.** Todo lo que este documento afirma sobre Google Business Profile como acción prioritaria queda **retirado**: la comunidad no tiene sede ni dirección física y no es elegible según las directrices de Google (las entidades exclusivamente en línea quedan excluidas; se exige dirección real verificable aunque se oculte al público). Las consultas locales dejan de contabilizarse como brecha alcanzable. El texto original se conserva sin modificar como registro del análisis del 19–20 de julio. Ver `decisions.md`.


**ASO** (Agentic Search Optimization) = si los motores y asistentes de IA *encuentran, entienden y citan*
el sitio. **AEO** (Agentic Engine Optimization) = si un agente puede *acceder, parsear y actuar* sobre él.

La auditoría original (`working/agentic.md`, `working/ai-search.md`) cubrió el lado **interno** de AEO:
descubrimiento, parsabilidad, acciones, inyección de prompts. Igual que ocurrió con SEO, faltaba la
dimensión **externa** (ASO): comportamiento real de motores agénticos y de consultas en lenguaje
natural. Esta continuación la añade y **corrige un hallazgo previo con causa raíz equivocada**.

## 1. Acceso de crawlers de IA (AEO — verificado 2026-07-20)

`curl` contra producción con 8 user-agents. **Resultado: 200 en todos, `content-length` idéntico
(22 483 b) — sin bloqueo, sin challenge WAF, sin cloaking.**

| Crawler | Propósito | Resultado |
|---|---|---|
| GPTBot | entrenamiento OpenAI | 200 |
| OAI-SearchBot | retrieval ChatGPT Search | 200 |
| ClaudeBot | Anthropic | 200 |
| PerplexityBot | Perplexity | 200 |
| Google-Extended | Gemini/Vertex | 200 |
| CCBot | Common Crawl | 200 |
| Googlebot / Bingbot | búsqueda clásica | 200 |

`robots.txt` no impone restricciones a crawlers de IA (apertura presumiblemente intencional, ya
registrada en `ai-search.md`). **PASS** — no hay barrera técnica de acceso.

## 2. Eficiencia de contexto (AEO — verificado 2026-07-20)

Texto extraído por un agente sin JS (script/style eliminados):

| Página | HTML | Texto | Ratio | ~tokens |
|---|---:|---:|---:|---:|
| home | 22 373 b | 4 187 c | 18,7 % | ~1 046 |
| practica | 22 427 b | 6 550 c | 29,2 % | ~1 637 |
| comunidad | 16 826 b | 5 848 c | 34,8 % | ~1 462 |
| eventos | 25 571 b | 5 290 c | 20,7 % | ~1 322 |
| galeria | 17 045 b | 1 704 c | 10,0 % | ~426 (**grid vacío sin JS — AEO-001**) |

**PASS** salvo galería: coste de contexto bajo, sin boilerplate excesivo. Confirma AEO-001 sin cambios.

## 3. Visibilidad en consultas de tipo pregunta (ASO — brecha principal)

Los agentes y asistentes reformulan en lenguaje natural. Verificado en Google CO (hl=es, gl=co):

| Consulta | Formato | ¿Aparece? |
|---|---|---|
| budismo chan colombia | keyword | **#1** ✅ |
| budismo tierra pura colombia | keyword | **#1** ✅ |
| **dónde practicar budismo chan en Colombia** | **pregunta** | **Ausente de página 1** ❌ |
| **meditación budista online en español gratis** | **intención** | **Ausente de página 1** ❌ |

**Hallazgo central (ASO-001):** el sitio gana la *keyword* pero desaparece en la *misma consulta
formulada como pregunta* — que es exactamente cómo recuperan los motores agénticos. En la versión
pregunta, el SERP lo ocupan packs locales (templos de Bogotá con Google Business Profile) y
competidores con páginas específicas de intención. En "meditación budista online" rankean quienes
tienen **página dedicada** al formato online (p. ej. Sravasti Abbey, "Meditación diaria en línea por
Zoom"); Camino del Dharma ofrece exactamente eso y no aparece.

Además, el AI Overview de Google para la consulta de marca "camino del dharma" (observado 2026-07-20)
responde sobre el *concepto* genérico y cita EcoEspiritualidad, Wisdom Library, Study Buddhism,
Wikipedia y The Buddhist Centre — **no cita caminodeldharma.org**, pese a que el sitio es el
resultado orgánico #1 de esa misma consulta. Ranking ≠ citación agéntica.

## 4. La meditación semanal no es una entidad citable (ASO-002)

La meditación online de los lunes es la **acción recurrente de mayor valor** del sitio (gratuita,
abierta a principiantes, virtual → sin límite geográfico) y su mayor diferenciador frente a
competidores presenciales. Estado actual:

| Requisito para que un agente la cite | Estado |
|---|---|
| URL propia y estable | ❌ solo párrafo en `/` y `/practica` |
| Datos estructurados (`Event` / `EventSeries` / `Schedule`) | ❌ ninguno — las únicas `Event` son los 2 eventos puntuales |
| Mención en `llms.txt` | ❌ ausente |
| Entrada propia en sitemap | ❌ |
| Enlace de unión directo | ❌ solo WhatsApp (Zoom se entrega tras contacto humano) |

Un agente al que le preguntan *"¿dónde puedo unirme a una meditación budista online en español?"* no
tiene ninguna entidad que citar, aunque la información existe en prosa. `llms.txt` es de buena
calidad (alcance, guía para agentes, precedencia canónica) pero **no menciona la meditación semanal**,
que es justamente lo que un agente necesita para dar una respuesta útil.

## 5. CORRECCIÓN de FUNC-002 — causa raíz equivocada en la auditoría original

La auditoría original registró: *"los archivos `/ical/*.ics` nunca se crearon; no existe `ical/`;
ambos eventos con 2 de 4 opciones rotas"* (severidad ALTA). **Las tres afirmaciones son inexactas.**

Verificación 2026-07-20:

1. El archivo **sí existe**: `eventos/ical/encuentro-nacional-2026.ics` → **200 en producción**
   (VCALENDAR válido, coherente con el JSON-LD del evento).
2. Las referencias son **relativas**, no absolutas: `ical/...` en `/eventos` y `../ical/...` en
   `/eventos/encuentro-nacional-2026`.
3. **Causa raíz real:** interacción entre esas rutas relativas y la **política canónica sin barra
   final**. Resolución verificada en el navegador real:

   | Página | Atributo | Resuelve a | Estado |
   |---|---|---|---|
   | `/eventos` | `ical/encuentro-nacional-2026.ics` | `/ical/encuentro-nacional-2026.ics` | **404** |
   | `/eventos/encuentro-nacional-2026` | `../ical/encuentro-nacional-2026.ics` | `/ical/encuentro-nacional-2026.ics` | **404** |

   En una URL sin barra final el último segmento se trata como archivo, no como directorio, así que
   la base de resolución es la raíz. El archivo correcto está en `/eventos/ical/`.
4. **Alcance menor al registrado:** solo **un** evento ofrece calendario. "Pausa Profunda – Cali" es
   un evento **finalizado** (`EventCompleted`, distintivo "Evento finalizado") y correctamente **no**
   ofrece opciones de calendario — no está roto.
5. Residuo adicional: el `.ics` se sirve como `text/plain`, no `text/calendar`.

**Consecuencia:** el síntoma observado (404) era correcto, pero el arreglo prescrito ("crear los dos
archivos .ics") habría **duplicado un archivo existente y no habría resuelto nada**. El arreglo
correcto es usar rutas absolutas (`/eventos/ical/...`) + tipo MIME. Severidad revisada **ALTA → MEDIA**
(una opción de descarga en un evento; las opciones Google/Outlook siguen funcionando).

## 6. Evaluación por tareas agénticas (estado 2026-07-20)

| ID | Tarea | Resultado | Punto de fallo |
|---|---|---|---|
| T1 | "¿Cuándo puedo meditar con la comunidad?" | **Parcial** | Dato solo en prosa; sin entidad ni enlace de unión; requiere WhatsApp humano |
| T2 | "Contactar a la comunidad" (formulario) | **FALLA silenciosa** | FUNC-001 sin cambios: `action="#"` sigue en producción |
| T2b | Contactar por WhatsApp / correo | **Correcto** | — |
| T3 | "Añadir el próximo evento al calendario" | **Parcial (2/4)** | `.ics` y Apple Calendar 404 por ruta relativa (§5); Google/Outlook OK |
| T4 | "¿Cómo dono?" | **Parcial** | Datos bancarios en texto; sin enlace de pago accionable |
| T5 | "¿Quién fundó la comunidad?" | **Correcto** | Zheng Gong, coherente en prosa + JSON-LD `Person` |

Seguridad agéntica: **sin cambios, PASS** — barrido de patrones de inyección (instrucciones ocultas,
override de sistema, texto dirigido a IA) en HTML, alt, metadatos y `llms.txt`: **cero coincidencias**.

## 7. Cambios implementados en esta continuación

Ninguno en código. Este bloque es **diagnóstico**: las correcciones van como tareas (TASK-0017,
TASK-0018) y como corrección de TASK-0001, para no mezclar cambios de contenido/estructura con el
despliegue pendiente de TASK-0013.

## 8. Acciones derivadas

- **TASK-0001 (CORREGIDA):** ya no "crear dos .ics". Ahora: rutas absolutas `/eventos/ical/...`,
  crear solo el `.ics` que falte si se añade un evento futuro, y `AddType text/calendar .ics`.
- **TASK-0017:** convertir la meditación semanal en entidad citable — URL propia, `Event`/`EventSeries`
  con `eventAttendanceMode: OnlineEventAttendanceMode` y `repeatFrequency` semanal, entrada en
  `llms.txt` y sitemap. Mayor palanca ASO por esfuerzo.
- **TASK-0018:** contenido orientado a preguntas (encabezados en forma de pregunta, bloques
  respondibles cortos, definiciones) para consultas en lenguaje natural — se coordina con TASK-0016.
- Sin acción: acceso de crawlers de IA, eficiencia de contexto, seguridad agéntica (todo PASS).
