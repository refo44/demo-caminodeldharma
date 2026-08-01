# Playbook — Migración static → WordPress (aprendizajes generalizables)

Aplicación a Camino del Dharma de un playbook portable aportado por el propietario, destilado en otro
proyecto WordPress con la misma forma de migración (Revista de Filosofía LOGO ET SPES: código propio
versionado en Git, hosting compartido sin contenedores en producción, estático congelado como
referencia). Complementa —no sustituye— los ADR y `docs/17-orden-implementacion.md`.

**No se implementa nada de esto en esta sesión** — es análisis y decisión, para dejar listo el arranque
de Fase 3. Lo académico-específico del proyecto de origen (DOI/ORCID, portal de autores, sistema de
envíos) **no aplica** aquí y se omite.

**Origen:** Revista de Filosofía LOGO ET SPES (Fase 3, WordPress), 2026-07-31.

---

## Mapa: qué ya cubre este proyecto y qué es nuevo

| # | Aprendizaje | Estado en Camino del Dharma |
| - | ----------- | --------------------------- |
| 1 | El estático es el contrato | **Ya cubierto** — ADR 0001, 0002, 0009; ledger bidireccional ya existe en `migracion-static-wordpress.md` |
| 2 | Monorepo con separación dura | **Ya cubierto** (estructura de carpetas, ADR 0014) + **decisión nueva hoy** (plugin=dominio/theme=presentación, ADR 0024) |
| 3 | Unidades de trabajo con estado durable | **Nuevo — recomendado adoptar** (§3 abajo) |
| 4 | Jerarquía de fuentes de verdad | **Nuevo — recomendado formalizar** (§4 abajo), implícito hasta ahora |
| 5 | Alcance por fases con límites explícitos | **Ya cubierto** (criterios de aceptación por fase en `docs/17`); el grep de patrones prohibidos es **nuevo, recomendado** |
| 6 | Migración de contenido: generador + importador | **Nuevo — pendiente decidir** cuando se aborde la carga de eventos/galería/blog a WordPress |
| 7 | Matriz de cobertura static → WP | **Nuevo — recomendado crear** al iniciar Fase 3 |
| 8 | QA en 4 niveles con honestidad probatoria | **Parcialmente cubierto** (`Pass (local)` ya en ADR 0023/playbook Docker) — **recomendado generalizar** a los 4 niveles |
| 9 | Entorno local Docker | **Ya cubierto** — ADR 0023, `docs/docker-wordpress-playbook.md` |
| 10 | Despliegue acotado y manual | **Ya cubierto** — ADR 0015 (manual), ADR 0016 (CI/CD pospuesto); el detalle de `workflow_dispatch` aplica **cuando** se retome ADR 0016 |
| 11 | Política de plugins de terceros minimalista | **Decisión nueva hoy** — ADR 0025 (política) + ADR 0026 (Contact Form 7, primer caso) |
| 12 | Invariantes transversales | **Nuevo — recomendado adoptar como checklist de QA** (§12 abajo) |
| 13 | Checklist portable | **Nuevo — recomendado usar como checklist de arranque de Fase 3** (§13 abajo) |
| 14 | Anti-patrones | **Nuevo — recomendado como referencia** (§14 abajo) |

Las secciones "ya cubiertas" no se repiten en detalle aquí — sus ADR y documentos son la fuente de
verdad. Las secciones nuevas o parcialmente nuevas se desarrollan abajo.

---

## 3. Unidades de trabajo (WU) con estado durable

**Aprendizaje:** una migración larga no debe vivir solo en el historial de conversación; vive en
artefactos versionados que permiten reanudar el trabajo entre sesiones sin releer todo el contexto.

Camino del Dharma ya practica esto parcialmente (`TASK-NNNN.md`, `waves.md`, `.audit/decisions.md`),
pero esos artefactos nacieron de la auditoría (Fase 2.75), no de la implementación de Fase 3. Al iniciar
Fase 3, recomendado crear el equivalente:

| Artefacto propuesto | Propósito |
| -------------------- | --------- |
| `.audit/fase3-execution-state.md` | Dónde estamos, qué sigue, blockers, protocolo de reanudación |
| `.audit/fase3-validation-matrix.md` | Evidencia de QA con estados honestos (`Unverified` / `Pass (local)` / `Pass`) |
| `docs/migracion-static-wordpress.md` | Ya existe — sigue siendo el ledger de correcciones aprobadas |

**Protocolo de reanudación sugerido** (funciona igual con una persona o con un agente):
`git status` → leer `fase3-execution-state.md` → re-ejecutar la última QA registrada → continuar desde
"Next exact action".

**Cómo dividir el trabajo:** WU pequeñas con commits atómicos — p. ej. scaffold del plugin → modelo de
datos (CPT `sangha`/`event`) → theme → plantillas → migración de contenido → fixtures de prueba →
Contact Form 7 → despliegue a staging. Cada WU define: fuentes vinculantes (qué doc/ADR la rige),
criterios de aceptación, plan de QA, rollback.

---

## 4. Jerarquía de fuentes de verdad

Orden recomendado, para cuando docs y código diverjan (evita resolverlo en silencio):

1. **Contenido canónico** — `content-source/` (texto institucional aprobado por la comunidad)
2. **ADR** — decisiones de arquitectura, alcance, privacidad (`docs/adr/`)
3. **Documentación numerada** — `docs/01`–`docs/24`
4. **Implementación estática validada** — `static/` (referencia de paridad visual)
5. **Código actual en Git** — lo que realmente corre

**Regla:** si algo diverge, **registrar la discrepancia** (en `migracion-static-wordpress.md` o
`.audit/decisions.md`) y corregir en un commit separado — nunca resolver el conflicto en silencio
sobrescribiendo uno de los dos lados sin dejar rastro.

---

## 6. Migración de contenido: generador + importador

Pendiente de decidir cuando se aborde cargar a WordPress el contenido que hoy vive en el estático
(eventos de `/eventos`, imágenes de `/galeria`, la entrada de `/blog`, las sanghas confirmadas de
TASK-0020). Arquitectura recomendada, de dos pasos:

```text
content-source/ o static/  →  [generador local]  →  payload versionado (JSON)
                                                            ↓
                                                  [importador WP-CLI en camino-del-dharma-core]
                                                            ↓
                                                  WordPress (BD + Media Library)
```

Reglas que demostraron valor en el proyecto de origen y aplican igual aquí:

| Regla | Por qué importa aquí |
| ----- | --------------------- |
| Payload versionado en Git | El servidor de producción no necesita el repo completo para reconstruir el contenido |
| `validate → plan → import → verify` | Dry-run por defecto; escritura solo con `--apply` — evita pisar contenido real por accidente |
| Idempotencia | Re-ejecutar el importador = `skip unchanged`, no duplica eventos ni sanghas |
| Metadatos `_source_key`, `_source_hash` | Detecta si alguien editó a mano en WordPress algo que también vive en el payload versionado |
| `--force` solo en campos "owned" por la migración | No pisa ediciones editoriales hechas directamente en WordPress tras el corte |
| Guard de producción (`--confirm-production` + evidencia de backup) | Coherente con ADR 0005 (producción sin edición manual) |
| Texto canónico verbatim | El generador falla si el texto importado diverge de `content-source/` — protege contra el mismo tipo de error que causó el dato de fundación incorrecto (ver `.audit/decisions.md`, 2026-07-28) |

**Separar fixtures de datos reales:** si se usan datos de prueba para desarrollar el theme/plugin,
marcarlos (`_cdd_fixture = 1`) y usar comandos `seed/verify/teardown` idempotentes. Nunca mezclar datos
demo con contenido institucional real.

---

## 7. Matriz de cobertura static → WordPress

Recomendado crear, al iniciar Fase 3, una fila por cada una de las 13 URLs indexables (más 404):

| Static | Plantilla WP | Parte compartida | Fuente dinámica | Diferencias conocidas |
| ------ | ------------ | ----------------- | ---------------- | ---------------------- |
| `index.html` | `front-page.php` | … | … | … |
| `contacto/index.html` | `page-contacto.php` | … | Contact Form 7 (ADR 0026) | … |
| … | … | … | … | … |

**Por qué importa:** la trazabilidad 1:1 facilita la QA visual (comparar cada URL contra su
equivalente estático) y las revisiones futuras. Las páginas institucionales simples pueden compartir un
template part delgado (`docs/12-theme-file-structure.md` ya define la estructura); la lógica única
(formulario, listado de eventos, CPT `sangha`) queda en el wrapper específico de cada plantilla.

---

## 8. QA en cuatro niveles con honestidad probatoria

Generaliza la distinción `Pass (local)` vs `Pass` ya adoptada para Docker (ADR 0023) a las cuatro capas
completas:

| Nivel | Qué valida | Requiere runtime |
| ----- | ---------- | ------------------ |
| 1 — Estático | Sintaxis PHP, JSON/YAML si aplica, checksums CSS/JS, greps de patrones prohibidos | No — siempre disponible |
| 2 — Componente | CPTs, meta fields, comandos WP-CLI propios, idempotencia de migración/fixtures | Sí — Docker local (ADR 0023) basta |
| 3 — Integración | Activación de plugin/theme, permalinks, Contact Form 7 entregando, plugins de terceros aprobados | Sí — Docker local basta |
| 4 — Regresión UX | Paridad visual contra `static/`, navegación por teclado, ausencia de cookies (ADR 0019), red | Sí — Docker + staging real para lo que depende del hosting |

**Reglas de honestidad que ya aplican en este proyecto y se mantienen:**
- `Pass (local)` **no** sustituye a `Pass` para nada que dependa del hosting real de Hostinger:
  versión de PHP real, `.htaccess`/Apache real, HTTPS, cabeceras, entrega efectiva de correo (Contact
  Form 7 a caminodeldharma1@gmail.com).
- Nada se marca `Pass` sin evidencia ejecutada y registrada; lo no probado queda `Unverified` — mismo
  estándar que ya usa `.audit/` para los hallazgos de la auditoría.
- Pasar el nivel 1 no certifica una WU completa.

---

## 12. Invariantes transversales (checklist de QA continua)

Controles a verificar durante todo el desarrollo de Fase 3, no solo al final:

| Invariante | Cómo se verifica | Referencia en este proyecto |
| ---------- | ------------------ | ----------------------------- |
| CSS inmutable | Checksums SHA-256 del theme vs. `static/` | ADR 0009 |
| Sin datos demo en producción | Grep de marcadores de fixture (`_cdd_fixture`) antes de desplegar | §6 arriba |
| Sin cookies anónimas | `curl -I` + inspección de red sin `Set-Cookie` | ADR 0019 |
| Sin registro de dominio en el theme | Grep de `register_post_type`/`register_taxonomy` en `themes/camino-del-dharma/` (debe estar vacío) | ADR 0024 |
| Flush de rewrite solo en activación | Grep de `flush_rewrite_rules` — nunca en cada carga de página | Buena práctica WP estándar |
| Sin secretos en el repo | Grep de patrones de credenciales; secretos solo en `.env` (gitignored) | `docs/docker-wordpress-playbook.md` §3 |
| Guards por entorno funcionando | Probar que el plugin rechaza operaciones destructivas en `production` antes de corregir configuración | `docs/docker-wordpress-playbook.md` §3.1 |

---

## 13. Checklist portable — arranque de Fase 3

**Antes de escribir código WordPress:**
- [x] ADR: monorepo `static/` + `wordpress/` (ADR 0014)
- [x] ADR: plugin dueño del dominio vs. theme presentación (ADR 0024)
- [x] ADR: alcance de fase — criterios de aceptación ya en `docs/17-orden-implementacion.md`
- [ ] Harness: `fase3-execution-state.md` + `fase3-validation-matrix.md` (§3 arriba)
- [ ] Baseline de checksums CSS/JS del estático actual
- [ ] Matriz de cobertura pantalla a pantalla (§7 arriba)

**Durante la implementación:**
- [ ] WU con commits pequeños y QA por unidad (§3)
- [ ] Plantillas 1:1 con template parts compartidos (§7, `docs/12-theme-file-structure.md`)
- [ ] Migración de contenido en dos pasos: generador + WP-CLI (§6)
- [ ] Fixtures de desarrollo aisladas con teardown (§6)
- [ ] `docker-compose.yml` para QA local (ADR 0023, ya listo el playbook)
- [ ] Despliegue del theme/plugin solo manual y acotado (ADR 0015/0016)

**Antes del gate de lanzamiento (Fase 2.5 sobre el theme, `docs/17` § Transición):**
- [ ] Nivel 1 de QA completo en verde (§8)
- [ ] Niveles 2-3 en `Pass (local)` documentado
- [ ] Staging con los mismos pasos, registrado en la matriz de validación
- [ ] Paridad visual (nivel 4) verificada en hosting real
- [ ] Inventario de plugins de terceros (Contact Form 7, ADR 0026) configurado y verificado en staging

---

## 14. Anti-patrones a evitar

| Anti-patrón | Por qué falla |
| ------------ | -------------- |
| Migrar todo de golpe en un solo PR/sesión | Imposible de revisar, imposible de reanudar entre sesiones |
| Page builder o bloques no nativos sobre el CSS congelado | Rompe la paridad visual con `static/` (ADR 0009) — vetado por defecto en ADR 0025 |
| Ejecutar la migración de contenido dentro de la activación del plugin | Peligroso e impredecible; la migración debe ser un comando explícito, no un side-effect |
| Datos demo mezclados con contenido institucional real | Contamina producción; separar con marcador de fixture (§6) |
| Auto-deploy de WordPress sin QA de runtime | Contradice ADR 0015/0016 (despliegue manual mientras dure la transición) |
| Instalar PHP/MySQL global "para probar rápido" | No reproducible entre máquinas — por eso ADR 0023 elige Docker |
| Declarar una WU o un hallazgo "resuelto" sin evidencia ejecutada | Ya ocurrió en este proyecto (TASK-0002 y TASK-0006 se marcaron `COMPLETED` sin que el fix estuviera realmente en el código — ver revisión de esta misma sesión) — es la razón de ser de la distinción `Pass (local)` vs `Pass` |

---

## Síntesis

Tratar el estático como contrato visual congelado, separar dominio (plugin) de presentación (theme),
migrar contenido con un pipeline determinista e idempotente, validar en capas con evidencia honesta, y
desplegar solo lo mínimo de forma manual y acotada.

## Referencias

- ADR [0001](adr/0001-maqueta-estatica-como-base-definitiva.md), [0009](adr/0009-css-y-tokens-invariantes-en-migracion.md) — el estático como contrato
- ADR [0014](adr/0014-monorepo-static-wordpress.md), [0024](adr/0024-plugin-dominio-theme-presentacion.md) — monorepo y separación de dominio
- ADR [0015](adr/0015-despliegue-manual-temporal.md), [0016](adr/0016-automatizacion-ci-cd-pospuesta.md) — despliegue acotado
- ADR [0023](adr/0023-entorno-local-wordpress-docker.md) y `docker-wordpress-playbook.md` — entorno local
- ADR [0025](adr/0025-politica-plugins-terceros.md), [0026](adr/0026-contact-form-7.md) — plugins de terceros
- `docs/17-orden-implementacion.md` § Transición estático → WordPress, § Fase 3
- `docs/migracion-static-wordpress.md` — ledger operativo de diferencias static/WordPress
- `.audit/decisions.md` — trazabilidad completa de decisiones
