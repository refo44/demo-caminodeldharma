# ADR 0023: Entorno de desarrollo local de WordPress con Docker

## Estado

Aceptada

## Fecha

2026-07-31

## Contexto

Fase 3 (WordPress) construye el theme en paralelo al estático, en un staging separado del hosting de
producción actual (Hostinger). Antes de escribir código de theme, el propietario pidió resolver cómo
se va a **desarrollar y probar** WordPress localmente — no solo dónde se aloja el staging final.

Dos necesidades concretas:

1. Poder levantar y derribar una instancia de WordPress local, repetible, sin depender de un servidor
   remoto para iterar.
2. Poder hacer auditorías visuales de UI/UX comparando, lado a lado, el theme en desarrollo local
   contra la versión estática que hoy está en producción (`https://caminodeldharma.org`) — la
   referencia congelada según `docs/17-orden-implementacion.md` §2.6 ("maqueta como referencia
   definitiva, no prototipo").

El calendario de auditorías (`.audit/audit-schedule.md`, Hito 2) ya identificó el riesgo central de
esta migración: que el entorno de pruebas difiera de producción de forma silenciosa (versión de PHP,
de MySQL, reglas de `.htaccess` que WordPress reescribe, etc.) y esa diferencia se descubra recién en
el corte. Fijar el entorno local con Docker, replicando las versiones reales de Hostinger, reduce ese
riesgo desde la fase de desarrollo, no solo en el staging final.

## Decisión

1. El desarrollo local de WordPress se hace con **Docker** (`docker-compose`), no con instalación
   directa, MAMP/XAMPP ni un servicio gestionado como Local by Flywheel.
2. Las imágenes replican, en la medida de lo posible, la **versión real de PHP y MySQL/MariaDB del
   hosting de Hostinger** — pendiente de confirmar en el hPanel antes de escribir el
   `docker-compose.yml` definitivo. No se fija una versión "reciente genérica" por comodidad; el
   objetivo es paridad con producción, no la imagen más nueva disponible.
3. El entorno local sirve para dos cosas: (a) desarrollo iterativo del theme sin depender de un
   servidor remoto, y (b) comparación visual UI/UX contra la producción estática, abriendo ambas
   versiones lado a lado.
4. **Dockerizar y levantar el entorno es una tarea independiente de implementar el theme WordPress en
   sí.** Se ejecuta en su propia sesión, no mezclada con la sesión donde se escribe el código del
   theme — decisión expresa del propietario para mantener acotado el alcance de cada sesión de trabajo.
5. La configuración de Docker vive en la raíz del repo (`docker-compose.yml`), como tooling de
   desarrollo — mismo criterio que ya aplica a `scripts/` en la estructura de ADR 0014. No se versiona
   en Git: core de WordPress, `wp-config.php`, credenciales, volúmenes de base de datos ni `uploads/` —
   mismo criterio ya establecido en `docs/17-orden-implementacion.md` § "No versionar en Git".
6. **Arquitectura de 3 servicios** (`db` con healthcheck, `wordpress`, `wpcli`), con bind-mount
   limitado al theme propio (`camino-del-dharma`, y al plugin propio si llega a crearse) — el core y la
   base de datos quedan en volúmenes Docker, nunca en Git. Detalle completo, gotchas y checklist en
   [`docs/docker-wordpress-playbook.md`](../docker-wordpress-playbook.md), adaptado de un playbook
   aportado por el propietario desde otro proyecto WordPress con la misma forma (Revista de Filosofía
   LOGO ET SPES, 2026-07-31).
7. La QA sobre este entorno se registra con el estado `Pass (local)`, distinto de `Pass`: no sustituye
   la validación en staging real (Fase 2.5 sobre el theme, `docs/17-orden-implementacion.md` §
   Transición) para nada que dependa del hosting real de Hostinger.

## Alternativas consideradas

| Alternativa | Decisión |
| --- | --- |
| Instalación directa (PHP + MySQL locales en la máquina) | Descartada: depende de dependencias globales de la máquina, difícil de reproducir igual en otra máquina o de derribar limpio |
| MAMP/XAMPP | Descartada: control de versión de PHP/MySQL menos preciso y menos portable que una imagen Docker fijada por tag |
| Local by Flywheel u otra herramienta dedicada a WordPress | Descartada: añade una capa propietaria de gestión sobre el entorno; Docker plano da control total sobre las versiones exactas a replicar |
| Imágenes Docker recientes ("latest") sin verificar Hostinger | Descartada por decisión expresa: el objetivo es paridad con producción, no comodidad de imagen genérica |

## Consecuencias

**A favor**

- Entorno reproducible entre sesiones y, si hace falta, entre máquinas.
- Paridad de versiones con Hostinger reduce el riesgo ya señalado en el Hito 2 del calendario de
  auditorías (`.audit/audit-schedule.md`).
- Comparación visual UI/UX contra producción posible desde el primer momento del desarrollo del
  theme, no solo al final en staging.

**Contrapartidas aceptadas**

- Requiere confirmar manualmente la versión de PHP y MySQL/MariaDB de Hostinger antes de fijar las
  imágenes en el `docker-compose.yml` — tarea pendiente; no bloquea esta decisión, pero sí bloquea su
  implementación.
- Un entorno Docker local no reproduce configuraciones propias del hosting compartido de Hostinger
  (límites de recursos, módulos específicos del servidor web). Docker **no sustituye** la validación
  en staging real descrita en `docs/17-orden-implementacion.md` § Transición estático → WordPress,
  antes del corte.

## Referencias

- `docs/17-orden-implementacion.md` § Transición estático → WordPress, § Fase 3
- `.audit/audit-schedule.md` — Hito 2 (verificación post-corte; riesgo de discrepancia staging/producción)
- ADR [0014](0014-monorepo-static-wordpress.md) — estructura del monorepo
- `docs/13-static-file-structure.md` — qué se versiona y qué no
- `.audit/decisions.md` — decisión de contacto vía Contact Form 7 (2026-07-31), misma sesión
- [`docs/docker-wordpress-playbook.md`](../docker-wordpress-playbook.md) — arquitectura concreta,
  gotchas y checklist de implementación (playbook portable de otro proyecto, adaptado aquí)
