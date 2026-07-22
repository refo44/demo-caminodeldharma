# informes-seo/ — nota interna

**Este archivo no se entrega.** Es la nota de trabajo de la carpeta.

## Los tres entregables

| # | Documento | Cubre | Destinatario |
|---|---|---|---|
| 00 | [Informe de Auditoría SEO](00-informe-auditoria-seo.md) | General y ejecutivo: visibilidad, autoridad, comparación con otras comunidades, plan de acción y decisiones pendientes | Liderazgo de la comunidad |
| 02 | [Auditoría SEO Técnica](02-auditoria-seo-tecnica.md) | Estado de salud del sitio: indexación, velocidad, datos estructurados, seguridad, protocolo de medición | Equipo de publicación web |
| 24 | [Brief editorial](../24-brief-editorial-blog-y-visibilidad.md) | Qué escribir en el blog y en qué orden (la voz la define `21`, que se entrega con él) | Equipo editorial |

**No se solapan y no compiten.** El 00 responde *qué se encontró y qué hay que decidir*; el 02, *cómo está construido el sitio*; el 24, *qué escribir*. Cada uno es autosuficiente: ninguno depende de `.audit/`, de este README ni de los otros dos para leerse, y los tres se remiten entre sí donde corresponde.

La numeración conserva un hueco en el 01 (informe de rendimiento, ver abajo). No es un error de secuencia.

## Origen de los datos

`.audit/` — informes de auditoría, ledger de evidencia, hallazgos y datos crudos. Los entregables **no generan datos nuevos**: los redactan para audiencia externa, sin códigos de evidencia ni identificadores de tarea.

## Informe de rendimiento — pendiente hasta septiembre

No se emitió. Un informe de rendimiento reporta evolución de tráfico, conversiones y retorno de inversión, y hoy **ninguna de las tres es reportable**: no hay periodo anterior (el sitio se publicó el 2026-07-18), no hay analítica (decisión formalizada) y no hay transacciones (sitio no comercial).

Rellenarlo con estimaciones habría sido fabricar evidencia. El cuadro de indicadores que lo sustituye —incluidos asistentes a la meditación y contactos entrantes— está en el **§8** del informe 00.

**Emitir tras la re-medición**, si para entonces hay datos que lo justifiquen.

## Corrección de fondo registrada

**Google Business Profile quedó descartado** tras confirmar que la comunidad no tiene sede ni dirección física y, por tanto, no es elegible según las directrices de Google (las entidades exclusivamente en línea quedan excluidas, y Google exige una dirección real verificable aunque se oculte al público).

Una versión preliminar lo situaba como acción prioritaria: fue una deducción a partir del SERP sin verificar el requisito. Documentado en el §7 del informe 00, con la consecuencia estratégica — las búsquedas locales dejan de contar como brecha y el foco pasa a autoridad y práctica en línea.

## Actualización del 21 de julio

Los dos informes se revisaron tras el despliegue de **v1.0.15 y v1.0.16**:

- **Meditación semanal con página propia** (`/practica/meditacion-semanal-en-linea`): sale del plan de acción y pasa a «hecho». Sitemap 13 → **14 URLs**.
- **Cinco encuentros presenciales** en Barranquilla, Bogotá y Medellín incorporados con datos estructurados completos. Es la **primera señal geográfica real** del sitio, y la vía legítima tras descartar Google Business Profile.
- **Listado de eventos** agrupado por año con encabezados reales y carga diferida de imágenes.
- **FUNC-003**: enlaces de navegación que resolvían a la raíz por usar ruta relativa bajo la política canónica sin barra final. Afectaba a `/practica/videos` **desde su publicación**. Segunda aparición de la misma causa que FUNC-002 → regla elevada a **ADR 0008** y añadida al checklist del corte a WordPress.

Lo detectó el propietario al hacer clic en el enlace, no la auditoría.

### Segunda revisión del 21 de julio (v1.0.18–v1.0.19)

- **Nueva §16 del informe 02** con el trabajo de rendimiento: una sola hoja de estilos bloqueante, CSS minificado, tipografía subsetada (52,1 → 3,4 KB) e imágenes adaptadas. Cierra 3 de las 4 oportunidades del cuadro de §6.
- **Corrección de estados en §8 y §12.** Dos hallazgos que constaban resueltos —**PERF-002** (versionado de CSS/JS) y **A11Y** (galería sin `noscript`)— **no lo estaban**. El cierre del despliegue del 20 de julio se hizo por confirmación global, sin verificar tarea por tarea; afectó a cuatro tareas. Detalle en `.audit/implementation/results/DEPLOY-v1.0.14.md`.
- **Política de privacidad, precisada en ambos informes.** Que se descartara la analítica resolvió lo *de las cookies*, no el **tratamiento de datos personales**: la Ley 1581/2012 lo cubre en general, y tras retirarse el formulario el contacto por WhatsApp y correo sigue recogiendo datos. Nuevo apartado en §9 del informe 02, entrada en §5 del informe 00, hallazgo **PRIV-001b** y fila propia en el plan de §12. **La conclusión jurídica queda explícitamente fuera del alcance de la auditoría.**
- **RGPD incorporado**, a raíz de los visitantes desde España que registra Search Console (1 clic / 2 impresiones de 9 / 35). Se separan las dos vías del art. 3.2: la de *observar el comportamiento* **no aplica y es verificable** (cero cookies, sin analítica, sin perfilado — la decisión de ADR 0019 protege también aquí); la de *ofrecer servicios* queda planteada con sus elementos a favor y en contra, sin resolverla. La meditación por Zoom, sin restricción geográfica, es el elemento a valorar.
- **Las cifras de Lighthouse de §6 no se recalcularon**: la cuota diaria de la API de PageSpeed estaba agotada. Lo de §16 son mediciones directas de recursos en producción. **Queda pendiente relanzar PageSpeed** tras desplegar el cambio de `/galeria`.

Estas dos revisiones siguen la regla de la sección siguiente: actualizar por secciones y conservar fechada la medición original, en lugar de reescribir cifras del 20 de julio.

---

## Próxima emisión

**Entre el 17 de agosto y el 14 de septiembre de 2026.** Antes no: se estaría midiendo la eficacia de acciones que aún no han tenido tiempo de actuar.

1. Copiar a `NN-nombre-AAAA-MM.md` para conservar el histórico.
2. Actualizar por secciones, sin reescribir los documentos enteros.
3. Seguir el protocolo del **§11** del informe 02 — misma herramienta, mismo método, misma lista de palabras clave.
4. Añadir columna de evolución a la tabla de posiciones del **§2** del informe 00, y comparar contra las metas del **§8**.
5. Evaluar si ya procede emitir el informe de rendimiento.

## Decisiones que condicionan los informes

- [ADR 0019](../adr/0019-sin-analitica-con-cookies.md) — sin analítica con cookies.
- [ADR 0020](../adr/0020-hsts-aplazado-hasta-wordpress.md) — HSTS aplazado hasta después de WordPress.
- ADR 0002 / 0018 — el sitio estático es temporal, previo a la migración a WordPress.
