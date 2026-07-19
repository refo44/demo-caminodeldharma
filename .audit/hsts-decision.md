DECISION: ACTIVATE HSTS NOW — DESPLIEGUE ESCALONADO

# Decisión HSTS — caminodeldharma.org

**Actualización 2026-07-19:** la auditoría original aprobaba `max-age=31536000` de inmediato. Tras la transición planificada a WordPress (ADR 0018), la decisión vigente es **activar HSTS ya** con **`max-age` corto en Fase 1** y subir a **un año en Fase 2** cuando WordPress esté estable en producción.

## Resumen ejecutivo

| Fase | Cuándo | Cabecera (solo host) | Decisión |
| ---- | ------ | -------------------- | -------- |
| **Fase 1 — transición** | Ahora (TASK-0004) | `Strict-Transport-Security "max-age=604800"` (7 días) | **ACTIVATE** — confianza ALTA |
| **Fase 2 — producción estable** | Tras corte WordPress + ≥30 días sin incidencias TLS/redirect | `Strict-Transport-Security "max-age=31536000"` | **APPROVED** — implementar en mantenimiento post-corte |
| `includeSubDomains` | — | — | **REJECTED** (TASK-0012) |
| `preload` | — | — | **REJECTED** |

Documento normativo del proyecto: **`docs/adr/0018-hsts-despliegue-escalonado.md`**.

---

## Fase 1 — cabecera a activar ahora

```apache
Header always set Strict-Transport-Security "max-age=604800"
```

**Línea en** `DOCS/demo-caminodeldharma/.htaccess` **(~103):** sustituir la línea comentada por la cabecera anterior (no basta descomentar la de un año).

La activación la ejecuta un agente implementador externo mediante **TASK-0004**, con verificación independiente en **TASK-0005**.

### Por qué 7 días (604800) y no un año de inmediato

Durante la transición estático → WordPress puede haber cambios en hosting, `.htaccess`, certificados o redirects. Un `max-age` largo acorta la ventana de recuperación ante un fallo TLS: los visitantes recurrentes quedan anclados hasta que expire la política o reciban `max-age=0` en una visita posterior.

**7 días** equilibra protección HSTS real durante la transición con una salida de emergencia razonable. Para un sitio de comunidad como este, **un año desde el día uno seguía siendo técnicamente seguro** según la auditoría; el escalonado es una opción **conservadora legítima** cuando hay incertidumbre operativa, no un requisito técnico del audit.

### Variante ultra-conservadora (opcional)

Si se desea margen máximo durante la transición:

```apache
Header always set Strict-Transport-Security "max-age=300"
```

(5 minutos) o `max-age=86400` (1 día). Menor protección efectiva en semanas de cambio; reversión casi inmediata (300 s) o en 24 h (86400).

---

## Fase 2 — objetivo durable (post-WordPress)

```apache
Header always set Strict-Transport-Security "max-age=31536000"
```

**Cuándo:** después del corte a WordPress en producción, smoke test completo y al menos **30 días** sin incidencias atribuibles a TLS, redirects o certificado.

**Criterios:** mismo checklist que TASK-0005; valor esperado en curl = exactamente `max-age=31536000`. Registrar en `CHANGELOG.md`.

---

## Alternativa: año completo desde el día uno

La auditoría original (**ACTIVATE_HSTS_NOW** con `max-age=31536000`) permanece **válida** si la comunidad prefiere máxima protección y acepta la reversión más lenta (`max-age=0` + hasta un año en clientes que no revisiten). **No es la decisión preferida del proyecto** mientras dure la transición (ADR 0018).

---

## Evidencia favorable verificada

(Sin cambio respecto a la auditoría del 2026-07-19 — aplica a cualquier `max-age` solo-host.)

| # | Verificación | Resultado | Evidencia |
|---|---|---|---|
| 1 | Entradas HTTP → HTTPS | 301 directo en apex; www en cadena segura de 2 saltos (http→https mismo host→apex) | EVID-0004, EVID-0029 |
| 2 | Sin bucles ni degradaciones; ruta y query se preservan | Verificado con `?tema=zen&x=1` | EVID-0029 |
| 3 | Certificado TLS | Let's Encrypt, SAN cubre apex y www, válido 2026-07-14 → 2026-10-12, TLS 1.3 | EVID-0005 |
| 4 | Páginas, assets, redirecciones y errores sobre HTTPS | 13/13 páginas 200; 404 real; 301 de barra final; assets correctos | EVID-0006, EVID-0009, EVID-0027 |
| 5 | Contenido mixto | Cero referencias `http://` en recursos cargados por las páginas | EVID-0011 |
| 6 | Dependencias externas | GA4, YouTube, Vimeo, wa.me — todas HTTPS; fuentes autoalojadas | EVID-0012 |
| 7 | www/no-www | Intencional (canónico sin www en .htaccess), cubierto por certificado | EVID-0004, EVID-0005, EVID-0008 |
| 8 | HSTS no emitido actualmente en ninguna clase de respuesta | Confirmado en 200/301/404 | EVID-0029 |
| 9 | Sin dependencia operativa de HTTP | Sitio estático; rewrites fuerzan HTTPS; CSP `upgrade-insecure-requests` como red adicional | EVID-0008 |
| 10 | Configuración fuente = producción | 14/14 páginas idénticas byte a byte al commit auditado; cada regla de .htaccess observada en producción | EVID-0008, EVID-0010 |

## Bloqueadores

Ninguno para Fase 1 (solo host, cualquier `max-age` ≥ 0 razonable).

## Riesgo residual

1. **Renovación de certificado no verificable** en CT logs (crt.sh 502 — EVID-0030). Mitigación: monitorización de caducidad (vence 2026-10-12).
2. **Fijación proporcional al max-age activo:** Fase 1 = hasta 7 días; Fase 2 = hasta 1 año. Mitigación: reversión con `max-age=0` sobre HTTPS válido.
3. **DNS parcial** (EVID-0003): irrelevante para variante solo-host.

## Decisiones por directiva

| Variante | Decisión | Notas |
|---|---|---|
| `max-age=604800` (solo host) | **ACTIVATE NOW — Fase 1** (preferida) | ADR 0018; TASK-0004 |
| `max-age=300` o `86400` (solo host) | **ACTIVATE NOW** (alternativa ultra-conservadora) | Sustituir valor en TASK-0004 |
| `max-age=31536000` (solo host) | **APPROVED — Fase 2** (o inmediato si se renuncia al escalonado) | Tras WordPress estable, o day-one bajo riesgo aceptado |
| `max-age=31536000; includeSubDomains` | **REJECTED** | TASK-0012 |
| `max-age=31536000; includeSubDomains; preload` | **REJECTED** | — |

## Acción recomendada exacta (Fase 1)

1. **TASK-0004:** en `.htaccess` ~103, activar `Header always set Strict-Transport-Security "max-age=604800"` y desplegar.
2. **TASK-0005:** validar que apex, www, 404 y 301 devuelven exactamente `max-age=604800`; smoke test HTTPS completo.

## Comandos de validación (post-despliegue Fase 1)

```bash
curl -sS -D - -o /dev/null https://caminodeldharma.org/ | grep -i strict-transport-security
curl -sS -D - -o /dev/null https://www.caminodeldharma.org/ | grep -i strict-transport-security
curl -sS -D - -o /dev/null https://caminodeldharma.org/pagina-inexistente | grep -i strict-transport-security
curl -sS -D - -o /dev/null https://caminodeldharma.org/eventos/ | grep -i strict-transport-security
curl -sS -D - -o /dev/null http://caminodeldharma.org/   # 301 intacto
```

Valor esperado Fase 1: exactamente `max-age=604800`, sin `includeSubDomains`, sin `preload`.

Valor esperado Fase 2: exactamente `max-age=31536000`.

## Restricciones de reversión

- Revertir sirviendo `Strict-Transport-Security "max-age=0"` sobre HTTPS válido; no retirar simplemente la cabecera.
- Tras Fase 2, clientes que no revisiten pueden conservar política de 1 año; en Fase 1, el techo es 7 días (o el max-age elegido).
- La reversión requiere TLS operativo; monitorizar renovación del certificado.
