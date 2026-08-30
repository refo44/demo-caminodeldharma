# ADR 0040: Retirar `content-source`; producción publicada gobierna el contenido

## Estado

Aceptada

## Fecha

2026-08-29

## Contexto

`content-source/` fue el insumo original de copy, identidad y media. El sitio estático evolucionó,
fue validado y pasó a producción con contenido, estructura, estilos y comportamiento que ya no
coinciden necesariamente con esos archivos. OWN-007 estableció que el HTML publicado gana; conservar
el directorio como supuesto canon seguía generando instrucciones contradictorias.

La carpeta estaba ignorada por Git, no era reproducible entre clones y mantenía una dependencia
accidental del generador DOCX. El propietario decidió eliminarla permanentemente sin copia de
respaldo. Los recursos operativos necesarios ya viven en `assets/`, HTML y documentación versionada.

## Decisión

1. Eliminar permanentemente `content-source/` del proyecto. No recrearlo ni usarlo como fuente,
   respaldo, migración, validación o aceptación.
2. Antes del corte, `https://caminodeldharma.org` es la fuente de verdad para todo lo visible por
   visitantes: copy, contenido, estructura, media, estilos y comportamiento.
3. El `VERSION` y commit más recientes del repo son el insumo determinista de extracción. Deben
   compararse con producción; cualquier delta se resuelve explícitamente en el ledger.
4. La migración static → WordPress debe ser imperceptible salvo funcionalidades y contenido aprobados.
5. Tras el corte, WordPress gobierna el contenido editorial y Git el theme/plugin propios.
6. Las menciones anteriores a `content-source/` se conservan solo cuando documentan estado histórico;
   no tienen fuerza operativa.

Esta decisión sustituye la regla editorial de ADR 0004 y las partes de ADR 0032/0033 que asignaban
copy institucional a `content-source/`. No altera sus decisiones sobre Git, los cinco entregables,
idempotencia, fixtures ni protección de ediciones en wp-admin.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Mantenerlo como referencia | Continuaba creando dos fuentes editoriales y no era reproducible en Git. |
| Archivarlo fuera del repo | El propietario eligió eliminación permanente sin copia. |
| Restaurar producción desde esos archivos | Sobrescribiría el contenido que realmente ven los visitantes. |

## Consecuencias

- El extractor usa HTML/JSON y media del sitio estático, no documentos legacy.
- QA compara WordPress contra producción publicada y registra diferencias repo/Hostinger.
- Assets y tooling no pueden depender de rutas bajo `content-source/`.
- La identidad ya extraída continúa en `docs/02-identidad-corporativa.md`, CSS y assets versionados.
- El inventario histórico del directorio queda marcado como retirado.

## Referencias

- OWN-006, OWN-007 y OWN-017
- ADR [0001](0001-maqueta-estatica-como-base-definitiva.md),
  [0002](0002-wordpress-como-adaptacion-sin-rediseno.md),
  [0004](0004-git-como-fuente-unica-de-verdad.md),
  [0032](0032-contrato-migracion-static-wordpress.md),
  [0033](0033-importador-contenido-vs-fixtures.md),
  [0034](0034-static-live-como-fuente-contenido-produccion.md)
