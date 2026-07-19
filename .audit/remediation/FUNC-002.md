# FUNC-002 — Remediation Package
Descargas de calendario rotas: `/ical/*.ics` referenciados pero inexistentes (404).

## Resolution objective
Las cuatro opciones del diálogo "Añadir al calendario" funcionan en ambos eventos.

## Acceptance criteria
1. `curl -I https://caminodeldharma.org/ical/encuentro-nacional-2026.ics` → 200 con content-type de calendario (`text/calendar`).
2. Ídem para `pausa-profunda-cali.ics`.
3. Ambos archivos importan sin error en Apple Calendar y Google Calendar y sus campos coinciden con el JSON-LD Event de cada página.

## Evidence and root cause addressed
EVID-0026: dialog enlaza /ical/<slug>.ics (calendar.js:130-138; data-calendar-ics en eventos/index.html:268 y eventos/encuentro-nacional-2026/index.html:243); ambos 404 en producción; no existe `ical/` en el repo. Causa raíz VERIFICADA: archivos nunca creados.

## Scope and implementation locations
Crear `ical/encuentro-nacional-2026.ics` y `ical/pausa-profunda-cali.ics`. Posible `AddType text/calendar .ics` en `.htaccess` SOLO si el servidor no lo sirve ya con MIME correcto (verificar primero).

## Prerequisites and dependencies
Ninguno.

## Immediate containment, when required
No aplica.

## Minimal safe fix
Commitear los dos .ics estáticos generados desde el JSON-LD de cada página.

## Preferred durable fix
Lo mismo + paso de checklist documentado: toda nueva página de evento incluye su .ics.

## Ordered implementation steps
1. Crear `ical/encuentro-nacional-2026.ics` con el VEVENT del bloque siguiente. Resultado: archivo válido (validar con un linter ics o import de prueba).
2. Crear `ical/pausa-profunda-cali.ics` con DTSTART/DTEND tomados del JSON-LD de la página (2026-02-15T08:00:00-05:00 → usar TZID America/Bogota o UTC equivalentes; evento pasado, mantener exactitud histórica).
3. Deploy y verificación de status + content-type.
4. Si content-type no es de calendario: añadir `AddType text/calendar .ics` en la sección MIME de `.htaccess` (coordinar conflict group CG-HTACCESS) y redeploy.

## Proposed code, configuration, content, schema, or infrastructure changes
[PROPUESTO — NO EJECUTADO] `ical/encuentro-nacional-2026.ics`:
```text
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Camino del Dharma//Eventos//ES
BEGIN:VEVENT
UID:encuentro-nacional-2026@caminodeldharma.org
DTSTAMP:20260719T000000Z
DTSTART;VALUE=DATE:20260807
DTEND;VALUE=DATE:20260810
SUMMARY:7.º Encuentro Nacional Buddhista – 2026
LOCATION:Casa Retiro San Pablo\, Puerto Colombia
URL:https://caminodeldharma.org/eventos/encuentro-nacional-2026
END:VEVENT
END:VCALENDAR
```

## Local validation
Import de prueba en un cliente de calendario; cotejo campo a campo con JSON-LD.

## Automated and regression tests
El diálogo sigue abriendo; opciones Google/Outlook intactas (no se tocan).

## Deployment sequence
Commit archivos → deploy → curl de verificación.

## Production verification and monitoring
`curl -sS -o /dev/null -w '%{http_code} %{content_type}\n'` sobre ambas URLs; caída a cero de 404 en /ical/ si hay logs.

## Rollback triggers
.ics malformado que rompa imports.

## Rollback procedure
Eliminar los archivos (vuelve al 404 previo) o corregir contenido; git revert.

## Trade-offs and residual risks
El evento pasado aporta poco valor, pero su enlace sigue expuesto en la página: se corrige igual.

## Human decisions or approvals required
Ninguna.

## Owner and effort
Frontend — < 30 min.
