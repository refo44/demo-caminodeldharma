# Checklist de validación (post-implementación)

## Por tarea
Cada TASK-00XX incluye su "Independent validation checklist"; el validador usa TASK_VALIDATOR_TEMPLATE.md, contexto fresco, y escribe `implementation/validations/TASK-00XX.md`.

## Re-chequeo global tras WAVE-1
- [ ] `curl -sS -D - -o /dev/null https://caminodeldharma.org/ | grep -i strict-transport-security` → exactamente `max-age=31536000`
- [ ] Ídem en www, 404 y 301; HTTP sigue redirigiendo 301
- [ ] `curl -o /dev/null -w '%{http_code}' https://caminodeldharma.org/ical/encuentro-nacional-2026.ics` → 200 (y pausa-profunda-cali)
- [ ] `/contacto` sin `<form>` inentregable; CTAs operativos
- [ ] Smoke test de las 13 páginas (consola limpia)

## Re-chequeo global tras WAVE-4
- [ ] CSP enforced presente, sin violaciones navegando todo el sitio + diálogos + videos
- [ ] security.txt 200 con Expires futuro
- [ ] (si TASK-0006 ejecutada) perfil limpio sin `_ga*` antes del consentimiento; /privacidad enlazada en 14 páginas

## Re-chequeo global tras WAVE-2/5/6
- [ ] Ratios de imagen ≤2.5 (re-ejecutar script de raw/performance/image-audit.txt)
- [ ] Referencias css/js con `?v=` = VERSION; invalidación verificada
- [ ] `curl -s https://caminodeldharma.org/galeria | grep -c '<img'` ≥ 12

## Invariantes (no deben romperse nunca)
- [ ] 14/14 páginas siguen 200; 404 real en rutas inexistentes
- [ ] Canónicas y sitemap sin cambios no intencionados
- [ ] CLS se mantiene en 0; sin errores de consola
