# ADR 0010: HSTS desactivado hasta auditoría

## Estado

Aceptada

## Fecha

2026-07-19

## Contexto

El sitio fuerza HTTPS mediante reglas `RewriteRule` en `.htaccess` (redirect 301 a `https://caminodeldharma.org`). **HTTP Strict Transport Security (HSTS)** instruye al navegador a usar solo HTTPS durante un periodo (`max-age`), incluso ante intentos de downgrade.

Activar HSTS prematuramente es **irreversible en la práctica** hasta que expire `max-age`: si el certificado, el dominio o la cadena HTTPS fallan, los usuarios no podrán acceder ni por HTTP. Hostinger, proxies y redirecciones de subdominios deben auditarse antes de fijar HSTS.

Actualmente la directiva está preparada pero **comentada** en `.htaccess`:

```apache
# Activar solo después de completar la auditoría HSTS:
# Header always set Strict-Transport-Security "max-age=31536000"
```

## Decisión

**HSTS permanecerá desactivado** hasta completar una auditoría explícita que confirme:

- Certificado TLS válido y renovación automática en `caminodeldharma.org`.
- Redirect canónico consistente (sin bucles HTTP/HTTPS).
- Ausencia de recursos mixtos (HTTP en páginas HTTPS).
- Política sobre subdominios (`includeSubDomains` solo si todos usan HTTPS válido).
- Plan de rollback documentado si se activa.

Cuando la auditoría cierre, se activará HSTS en `.htaccess` (o cabecera equivalente en panel Hostinger) y se registrará la fecha en `CHANGELOG.md`. Si se añade `preload`, requerirá ADR complementario por implicaciones de la lista preload de navegadores.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Activar HSTS inmediatamente con `max-age` corto | Mejor que preload agresivo, pero aún arriesgado sin auditoría de certificados y mixed content. |
| No usar HTTPS | Inaceptable para SEO, privacidad y confianza. |
| HSTS solo vía CDN externo | No aplicable al stack actual (Apache/LiteSpeed en Hostinger). |
| `max-age=31536000; includeSubDomains; preload` desde el día uno | Riesgo alto de lock-out si hay error de configuración. |

## Consecuencias

**Beneficios (al activar tras auditoría):**

- Protección contra downgrade SSL y cookies secuestradas en redes inseguras.
- Señal positiva en auditorías de seguridad.

**Riesgos (mientras esté desactivado):**

- Usuarios en redes comprometidas podrían ser víctimas de SSL stripping en la **primera** visita (mitigado parcialmente por redirect 301 en visitas posteriores sin HSTS).

**Trabajo futuro:**

- Checklist de auditoría: certificado, mixed content, Search Console HTTPS, cabeceras en [`securityheaders.com`](https://securityheaders.com) o equivalente.
- Descomentar directiva en `.htaccess` tras validación.
- Opcional: ADR futuro para preload.

## Referencias

- `.htaccess` (líneas HSTS comentadas; redirects HTTPS activos)
- `docs/17-orden-implementacion` (mantenimiento — revisión trimestral de cabeceras HTTP)
- `docs/15-assets-strategy` (recursos servidos por HTTPS)
