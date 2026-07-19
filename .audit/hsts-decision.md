DECISION: ACTIVATE HSTS NOW

# Decisión HSTS — caminodeldharma.org

**Cabecera evaluada (candidata exacta):**

```apache
Header always set Strict-Transport-Security "max-age=31536000"
```

**Línea de configuración exacta a activar** (descomentar en `DOCS/demo-caminodeldharma/.htaccess`, línea 103):

```apache
Header always set Strict-Transport-Security "max-age=31536000"
```

Decisión sobre la variante básica (solo host): **ACTIVATE_HSTS_NOW** — confianza **ALTA**.
La activación la ejecuta un agente implementador externo mediante **TASK-0004**, con verificación de producción independiente en **TASK-0005**. El auditor no activa la cabecera.

## Evidencia favorable verificada

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

Ninguno. No existen prerrequisitos técnicos pendientes para la variante básica solo-host.

## Riesgo residual

1. **Historial de renovación del certificado no verificable** en CT logs (crt.sh devolvió 502 — EVID-0030). La fiabilidad de renovación es INFERIDA: certificado gestionado por Hostinger, emitido hace 5 días. Mitigación: TASK-0005 exige confirmar monitorización de caducidad.
2. **Fijación de 1 año**: una caída de TLS futura sería un fallo duro para visitantes recurrentes. Mitigación: reversión con `max-age=0` sobre HTTPS válido (nunca retirando la cabecera sin más).
3. **DNS parcial**: AAAA/NS/MX no enumerables desde el entorno (EVID-0003). Sin indicios de otros hostnames en ninguna configuración observada; irrelevante para la variante solo-host.

## Decisiones por directiva

| Variante | Decisión | Evidence IDs | Riesgo residual | Requisitos pendientes |
|---|---|---|---|---|
| `max-age=31536000` | **ACTIVATE_HSTS_NOW** (confianza ALTA) | EVID-0004, EVID-0005, EVID-0006, EVID-0008, EVID-0009, EVID-0011, EVID-0012, EVID-0027, EVID-0029 | Renovación de certificado inferida; fijación 1 año | Ninguno |
| `max-age=31536000; includeSubDomains` | **REJECTED** | EVID-0003 | Inventario de subdominios no enumerado; el éxito del host no prueba la seguridad de subdominios | Inventario DNS autoritativo; verificación HTTPS de cada subdominio; aprobación separada (TASK-0012) |
| `max-age=31536000; includeSubDomains; preload` | **REJECTED** | — | Persistencia cuasi irreversible en listas de navegadores | includeSubDomains aprobado y estable; criterios vigentes de hstspreload.org verificados; reconocimiento organizativo explícito |

`includeSubDomains` y `preload` son decisiones independientes posteriores y **nunca** se heredan de la aprobación básica.

## Acción recomendada exacta

1. TASK-0004 (agente implementador): descomentar la línea 103 de `.htaccess` — ningún otro cambio — y desplegar.
2. TASK-0005 (validador independiente): ejecutar los comandos de validación y el smoke test completo.

## Comandos de validación (post-despliegue)

```bash
curl -sS -D - -o /dev/null https://caminodeldharma.org/ | grep -i strict-transport-security
curl -sS -D - -o /dev/null https://www.caminodeldharma.org/ | grep -i strict-transport-security
curl -sS -D - -o /dev/null https://caminodeldharma.org/pagina-inexistente | grep -i strict-transport-security
curl -sS -D - -o /dev/null https://caminodeldharma.org/eventos/ | grep -i strict-transport-security
curl -sS -D - -o /dev/null http://caminodeldharma.org/   # 301 intacto
```

Valor esperado: exactamente `max-age=31536000`, sin `includeSubDomains`, sin `preload`.

## Restricciones de reversión

- Revertir sirviendo `Strict-Transport-Security "max-age=0"` sobre HTTPS válido; no retirar simplemente la cabecera.
- La reversión requiere TLS operativo; la renovación del certificado (vence 2026-10-12) debe estar monitorizada.
- Navegadores que no revisiten tras el `max-age=0` conservan la política hasta 1 año.
