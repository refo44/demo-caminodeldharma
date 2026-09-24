# ADR 0046: El despliegue lo inicia solo un tag de versión aprobado

## Estado

Aceptada el 2026-09-23. Alcance: contrato de release y despliegue de **staging
WordPress anterior al corte**. No autoriza producción.

Sustituye el disparador de [ADR 0006](0006-github-actions-para-despliegue.md) (push a
`main` o `workflow_dispatch` hacia producción) y el aplazamiento de
[ADR 0016](0016-automatizacion-ci-cd-pospuesta.md) solo para el staging que este ADR
define. El deploy de código WordPress a staging deja de ser el ZIP manual de
[ADR 0015](0015-despliegue-manual-temporal.md); el estático de producción y la
prohibición de instalar WordPress en ese `public_html` siguen en 0015.

D-A y D-C están cerradas. D-B queda diferida a propósito: es la promoción a producción
después del corte y no bloquea esta aceptación ni el contrato de staging.

La arquitectura está aceptada. La implementación no está hecha: no hay rulesets, no
hay entorno `staging`, no hay workflow de deploy y no existe el tag `theme-v0.5.3`.
Staging sigue en el theme `0.5.2` hasta esa release, después de configurar los controles.

## Fecha

2026-09-23

## Contexto

[ADR 0006](0006-github-actions-para-despliegue.md) fijó GitHub Actions como dirección de
CI/CD y describió un `deploy.yml` disparado por push a `main` y/o `workflow_dispatch`,
con destino de producción vía SSH y rsync. [ADR 0016](0016-automatizacion-ci-cd-pospuesta.md)
aplazó esa implementación. Activarla exige un ADR nuevo, o marcar 0016 como **Sustituida**.

[ADR 0015](0015-despliegue-manual-temporal.md) mantiene que un merge no es un despliegue.
[ADR 0038](0038-pruebas-tdd-phpunit-sonar.md) añadió el CI de calidad
(`.github/workflows/test.yml`, jobs `php` y `css`) y rechazó activar `deploy.yml`.
[ADR 0043](0043-trunk-based-conventional-branch-commits.md) fija el tronco en `main` por
pull request y repite que proteger la rama no enciende CD.

El propietario fijó, el 2026-09-23, el contrato de disparo que 0006 no contiene: un
merge o un push a `main` no despliega; el despliegue nace solo de un tag de versión
aprobado; ese tag representa una versión [SemVer 2.0.0](https://semver.org/); el commit
etiquetado pertenece a `main`; los checks `php` y `css` de ese commit exacto están en
verde; si no, el deploy falla cerrado. El primer destino es staging. El `public_html`
estático de producción queda fuera hasta el corte.

SemVer fija el significado de la versión. No fija qué componente versiona el tag.
Hoy conviven líneas independientes:

| Identidad | Valor vigente | Dónde vive | Qué ha significado hasta ahora |
| --------- | ------------- | ---------- | ------------------------------ |
| Repositorio / estático | `1.0.35` | `VERSION`, `CHANGELOG.md` | Releases publicadas del estático. Tags anotados `v1.0.14` … `v1.0.35`. ZIP `camino-del-dharma-vX.Y.Z.zip` |
| Theme | `0.5.3` | `style.css` | Paquete del theme. `[Unreleased]` dice que no cambia el artefacto estático. Staging sigue en `0.5.2` |
| Plugin | `0.7.5` | cabecera y `CDD_CORE_VERSION` | Código de dominio |
| Payload | `1.0.35` | `migration/payload.json` `source.version` | Extracción de contenido. No viaja en un deploy de código |

No hay tags `theme-v0.5.3` ni `v0.5.3`. El único esquema ya usado como release es
`vX.Y.Z` alineado con `VERSION`. También existe el tag no semántico
`fase3-pre-reorg-v1.0.35`: un tag cualquiera no es un disparador. [ADR 0008](0008-urls-estables-desde-la-maqueta.md)
ya dice que un cambio de URL puede implicar MAJOR en `VERSION`. Esa frase es SemVer
aplicada a la línea del estático. No elige la unidad de release de WordPress.

El theme `0.5.3` está en `main` (`d1e234cc4024fa8af2fef65a96a267dee2fb9b1d`). `0.5.3`
es un valor SemVer válido. El tag de esa línea, una vez D-A cerrada, es `theme-v0.5.3`,
no `v0.5.3` ni `v1.0.36`. Aceptar este ADR no crea `theme-v0.5.3`. Crear el tag es
la decisión de release, después de los controles de GitHub y del workflow, no una
consecuencia del merge ni de esta aceptación.

## Decisión

El contrato aceptado de **staging anterior al corte** es solo este camino. Aceptar la
arquitectura no significa que el workflow ya exista:

```text
approved component tag
        |
        v
resolve exact SHA
        |
        v
verify SHA belongs to main history
        |
        v
checkout exact SHA
        |
        v
validate tag namespace + SemVer + component version
        |
        v
rerun mandatory php + css gates
        |
        v
fail closed if any gate fails
        |
        v
build/select ONLY the tagged component
        |
        v
record tag + SHA + artifact checksum
        |
        v
staging preflight
        |
        v
deploy ONLY to staging
        |
        v
post-deploy verification
```

Producción no entra en ese camino. Este ADR no autoriza un workflow de producción.
El `public_html` estático de producción permanece intacto. El workflow de staging no
lleva credenciales de producción. Un tag `v*` no dispara el deploy de WordPress a
staging: `v*` es la línea del estático, y protegerlo no lo convierte en disparador.
`theme-v*` despliega solo el theme. `plugin-v*` despliega solo el plugin. El payload
no tiene tag de deploy.

### Disparo

1. Un merge o un push a `main` no despliega. Esos eventos ejecutan solo el CI de calidad.
2. Crear un tag no es consecuencia automática de un merge. Ningún workflow crea tags,
   releases de GitHub ni despliegues al integrar en `main`.
3. Un despliegue lo inicia únicamente un tag de versión nuevo y aprobado:

   > A merge to `main` MUST NOT trigger a deployment. A deployment MUST be initiated only by an explicitly approved semantic-version Git tag.

4. Solo es elegible un tag cuyo commit está contenido en `main`:

   > Only version tags whose commit is contained in `main` are eligible for deployment.

5. El tag no basta. El commit etiquetado tiene que tener en verde los checks requeridos
   `php` y `css` de [ADR 0038](0038-pruebas-tdd-phpunit-sonar.md) y
   `.github/workflows/test.yml`. Esos jobs son, hoy, la definición de «linters y tests
   unitarios obligatorios»: `php -l`, `composer audit --locked`, PHPUnit `tests/Unit` y
   PHPCS dentro de `php`; Stylelint dentro de `css`. `composer test:wp` no forma parte
   de ese gate. Si falta esa evidencia para ese SHA exacto, el deploy no corre. Falla
   cerrado.
6. Actions construye los artefactos desde ese commit exacto.
7. El primer target es staging. Producción sigue fuera hasta definir y promover el corte.
8. Ningún workflow escribe en
   `/home/u548735796/domains/caminodeldharma.org/public_html`.
9. Un deploy de código no ejecuta migración, seed, convert, demo purge ni
   aprovisionamiento de formularios, y no usa `--confirm-production`.
10. El `.htaccess` propio del entorno no se sustituye. En staging se conserva la copia
    fusionada que no redirige a `caminodeldharma.org`.
11. Cada despliegue queda asociado a tag + SHA del commit + checksum del artefacto.

### SemVer 2.0.0

Los tags desplegables representan una versión [Semantic Versioning 2.0.0](https://semver.org/).
La especificación es normativa. No basta con que el texto tenga forma `X.Y.Z`.

- `MAJOR.MINOR.PATCH` significa lo que dice SemVer 2.0.0.
- PATCH: corrección compatible hacia atrás.
- MINOR: funcionalidad compatible hacia atrás.
- MAJOR: cambio incompatible de la API pública de lo que esa versión identifica.
- Una versión ya publicada no se modifica. Cualquier cambio posterior exige una versión nueva.
- Un identificador de pre-release solo se usa si el proyecto decide explícitamente publicar
  una pre-release. Este ADR no adopta pre-releases.
- La metadata de build puede usarse cuando corresponda. No altera la precedencia.

No se inventa otra lectura de MAJOR, MINOR o PATCH.

SemVer se aplica en la frontera de release, no en cada merge. Varios pull requests pueden
acumularse en `main` desde el tag anterior. El incremento se elige al preparar la release,
según el conjunto de cambios de esa release respecto de la anterior. Un PR solo de
documentación, un refactor, un cambio de tooling o un commit preparatorio no exige por sí
solo un bump, un tag, una release ni un despliegue.

```text
feature branch
    ↓
PR
    ↓
CI green
    ↓
main
    ↓
zero or more additional merges
    ↓
explicit release decision
    ↓
choose next SemVer according to accumulated release changes
    ↓
approved version tag
    ↓
tag quality gate
    ↓
artifact
    ↓
staging
```

### Valor SemVer y nombre del tag

Son cosas distintas.

| | Ejemplo | Quién lo decide |
| - | ------- | --------------- |
| Valor SemVer | `0.5.3`, `0.7.5`, `1.0.35` | SemVer 2.0.0, ya fijado |
| Nombre del tag en Git | `v1.0.35` para el estático; `theme-v0.5.3` y `plugin-v0.7.5` para WordPress | D-A, cerrada abajo |

Un prefijo de componente no viola SemVer: el valor versionado puede seguir siendo
`1.2.3`. Este ADR no afirma que el nombre del tag deba ser solo `X.Y.Z`. Lo que tiene
que ser SemVer es la versión de release que el tag desplegable representa. Los tags ya
publicados `v1.0.14` … `v1.0.35` son la convención histórica de la línea `VERSION`, con
prefijo `v`. Esa convención no se reinterpreta aquí como namespace de theme o de plugin.

### Inmutabilidad de una versión publicada

Un tag de versión que ya inició una release aceptada es inmutable. No se mueve ni se
reutiliza para apuntar a otro contenido. El contenido distinto exige un SemVer nuevo.
Un workflow futuro falla cerrado antes de desplegar bytes distintos bajo una identidad
de versión ya publicada. El mecanismo de GitHub está en D-C, ya cerrada.

### D-A. Unidad de release y namespace del tag

Cerrada por el propietario el 2026-09-23. Theme y plugin se versionan y se despliegan
por separado. El valor SemVer es el de cada línea. El prefijo del tag dice qué artefacto
se construye. `vX.Y.Z` sigue siendo solo la línea `VERSION` del estático.

| Línea | Valor vigente | Tag desplegable | Artefacto |
| ----- | ------------- | --------------- | --------- |
| Estático | `1.0.35` | `v1.0.35` | ZIP de `static/`, como hasta ahora |
| Theme | `0.5.3` | `theme-v0.5.3` | Solo `wordpress/wp-content/themes/camino-del-dharma/` |
| Plugin | `0.7.5` | `plugin-v0.7.5` | Solo `wordpress/wp-content/plugins/camino-del-dharma-core/` |

Un tag `theme-v*` no reconstruye ni redespliega el plugin. Un tag `plugin-v*` no
redespliega el theme. `v0.5.3` no es un tag de este proyecto. `v1.0.36` no despliega
WordPress. El payload no tiene tag de deploy.

El tag que correspondería al theme ya integrado en `main` es `theme-v0.5.3`. No se crea
en este ADR: crearlo es la decisión de release, después de aceptar el ADR. El valor
`0.5.3` ya está en `style.css`; el tag no lo inventa ni lo cambia.

### D-C. Tag aprobado y rulesets de release

Cerrada por el propietario el 2026-09-23. La arquitectura no supone que el repositorio
tenga siempre un solo desarrollador. Hoy hay una persona, y esa persona ocupa a la vez
el trabajo de desarrollo y el de release. Eso es configuración operativa actual, no una
restricción permanente. Mañana puede haber más Developers y uno o varios Release
Maintainers. Añadir un Developer no le concede autoridad de release.

Ser colaborador no implica poder publicar. Poder contribuir código, abrir una rama,
abrir o revisar un pull request, o integrar en `main` por el tronco aceptado no otorga
autoridad para crear un tag de release.

```text
Developer
    |
    +-- feature branches
    +-- PRs
    +-- normal development
    |
    +-- NO implicit release authority

Release Maintainer
    |
    +-- explicitly authorized release role
    +-- may create an approved release tag
    +-- does NOT gain permission to rewrite an existing released tag
```

Un tag está «approved» cuando lo ha creado un actor autorizado para ejercer el rol de
**Release Maintainer**, bajo el ruleset de creación de releases. La pertenencia al
repositorio, la capacidad de crear pull requests, hacer merge o desarrollar código no
concede por sí sola autoridad para crear tags de release.

Crear ese tag es la decisión explícita de release. No lo generan un pull request, un
merge, un push a `main`, un CI en verde ni un bump de versión por sí solos. El tag
aprobado sigue sujeto al resto de invariantes de este ADR: namespace de D-A, SemVer
2.0.0, versión del componente igual a la del tag, commit contenido en `main`, gate
`php` y `css` de ese SHA exacto, identidad no reutilizada con otro contenido, y destino
permitido para ese namespace.

El cumplimiento en GitHub son dos rulesets de tags, no uno. El bypass de un ruleset
cubre el ruleset entero. Un solo ruleset que restringiera creación, actualización y
borrado, y diera bypass al Release Maintainer, también le permitiría reescribir el tag.
Eso no cumple la inmutabilidad.

**Ruleset A — creación.** Patrones `theme-v*`, `plugin-v*` y `v*`. Restrict creations
activado. Bypass: solo los actores autorizados como Release Maintainer. No entran los
Developers por el hecho de contribuir, ni todos los colaboradores, ni GitHub Actions,
ni otra automatización, ni un bypass genérico del rol de admin salvo que ese actor esté
elegido de forma explícita como autoridad de release. La lista es configuración
operativa y puede crecer. La arquitectura no exige que sea exactamente una persona.

**Ruleset B — inmutabilidad.** Los mismos tres patrones. Restrict updates, restrict
deletions y block force pushes, todos activados. Bypass vacío. Ni Developer, ni Release
Maintainer, ni Actions, ni un admin ordinario forman parte del mecanismo normal. El
Release Maintainer puede crear una identidad nueva. No puede reescribir una ya publicada.
`tag → SHA` queda fijo. El contenido distinto exige un SemVer nuevo y, por tanto, un tag
nuevo.

Ruleset A responde quién puede crear una identidad de release: los Release Maintainers
autorizados. Ruleset B responde si esa identidad puede reescribirse: no, en la operación
normal del repositorio.

Límite residual, dicho sin disimulo: un administrador con privilegio suficiente puede
acabar cambiando o desactivando los rulesets. Eso es un acto administrativo auditado,
fuera del mecanismo normal de release. GitHub no impide criptográficamente que el
dueño del repositorio cambie su gobernanza.

GitHub Actions no es actor de bypass en ninguno de los dos rulesets. El workflow de
despliegue, cuando exista, no crea, no mueve y no borra tags, y no toma la decisión de
release: reacciona a un tag ya creado. Su permiso de GitHub para el despliegue normal a
staging es `contents: read`. No necesita `contents: write`. Otro permiso exigiría un ADR
aceptado que lo justifique.

Los rulesets protegen `theme-v*`, `plugin-v*` y `v*`. No usan `*` ni `*v*`: el tag
existente `fase3-pre-reorg-v1.0.35` no es una release, y no debe volverse una porque su
nombre contenga «v». Un patrón `theme-v*` no prueba por sí solo que el valor sea SemVer
ni que coincida con `style.css` o con la cabecera del plugin. El workflow futuro valida
la sintaxis completa y la versión del componente, y falla cerrado.

Los rulesets protegen también `v*`. El primer workflow de staging, antes del corte,
escucha solo `theme-v*` y `plugin-v*`. No escucha `v*`. Esa serie sigue siendo la del
estático, y este ADR no autoriza un despliegue automático del estático a producción.
Proteger `v*` contra creación o mutación no autorizada no enciende un deploy desde `v*`.

El entorno de GitHub llamado `staging`, cuando se configure, aísla los secretos SSH de
staging, limita los namespaces que pueden desplegar allí a `theme-v*` y `plugin-v*`
(sin `v*` antes del corte) y evita que el CI de un pull request vea esos secretos.
También deja sitio, más adelante, para reglas de protección del despliegue. Hoy no forma
parte de D-C una segunda aprobación humana en ese entorno. La aprobación de la release
es la creación del tag protegido por un Release Maintainer. Con un solo maintainer, exigir
esa segunda aprobación o impediría el self-review o sería un segundo clic de la misma
persona. No se exige.

Si más adelante se quiere separar funciones —una persona crea el tag y otra aprueba la
promoción a producción— eso puede entrar por required reviewers del entorno, o por un
ADR posterior. No es requisito del diseño de staging. La aprobación de la release (crear
el tag protegido) y la aprobación de una promoción a producción son cosas distintas.
Este ADR no exige un objeto GitHub Release para autorizar el despliegue. Publicar una
release no sustituye a los rulesets ni es el mecanismo de inmutabilidad. Puede adoptarse
después para notas o activos, sin redefinir el tag protegido como identidad de la release.
Los tags firmados no son requisito de este ADR. El mecanismo elegido es creación
autorizada, inmutabilidad protegida, validación del SHA exacto, gate de calidad y fallo
cerrado.

### D-B. Promoción a producción, diferida

D-B permanece **intencionalmente sin decidir**. Pertenece al diseño futuro de la
promoción a producción, después del corte. No es un prerrequisito del staging ni de
aceptar este ADR.

No se elige que producción reciba el artefacto exacto probado en staging. No se elige
que producción reconstruya desde el tag. No se afirma «build once, promote». Los ADR
aceptados describen un rsync desde el tag o el commit (ADR 0007, ADR 0013). Eso no es
una decisión nueva del propietario sobre D-B.

D-B no bloquea esta aceptación, ni configurar los rulesets de tags, ni el entorno
`staging`, ni el workflow de staging disparado por tag, ni validar el SHA, ni volver a
ejecutar `php` y `css`, ni desplegar solo el componente etiquetado a staging, ni crear
`theme-v0.5.3` como decisión de release después de esos controles. Crear el tag no
forma parte de esta aceptación.

### Relación con ADR anteriores

- Sustituye el disparador de [ADR 0006](0006-github-actions-para-despliegue.md): el
  deploy de staging lo inicia el tag aprobado, con SemVer, commit en `main` y `php`/`css`
  en verde. No activa el `deploy.yml` de 0006 hacia producción. GitHub Actions como
  dirección, y SSH/rsync de [ADR 0007](0007-rsync-como-mecanismo-de-sincronizacion.md),
  siguen compatibles.
- Sustituye el aplazamiento de [ADR 0016](0016-automatizacion-ci-cd-pospuesta.md) solo
  para el staging definido aquí. La automatización de producción sigue fuera.
- [ADR 0007](0007-rsync-como-mecanismo-de-sincronizacion.md) y [ADR 0013](0013-fuentes-de-verdad-duales-y-alcance-despliegue.md)
  no se reabren. D-A fija el directorio: `theme-v*` solo el theme, `plugin-v*` solo el plugin.
- [ADR 0015](0015-despliegue-manual-temporal.md) sigue gobernando el ZIP estático de
  producción y la prohibición de convertir ese `public_html` en WordPress. El código
  WordPress de staging se despliega según este ADR, cuando el workflow exista. Hasta
  entonces no se crea `theme-v0.5.3` y no se sube el theme `0.5.3` a mano como si el
  pipeline ya estuviera. D-B queda diferida y no bloquea ese staging.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------------------------ |
| `deploy.yml` de ADR 0006 | Despliega por push a `main` hacia producción |
| Tratar `0.5.3` como tag `v0.5.3` porque es SemVer | SemVer no elige la unidad de release. La serie publicada es `v1.0.x` |
| Tratar `v1.0.36` como el próximo deploy de WordPress | Esa serie es el estático |
| Una release WordPress conjunta, o `v0.5.3`, o `v1.0.36` como deploy del theme | D-A eligió tags por componente. Esas tres lecturas quedan descartadas |
| Exigir un bump SemVer en cada merge | Contradice el tronco: el incremento se decide al preparar la release |
| Mover un tag ya publicado para corregir el artefacto | Contradice SemVer 2.0.0 y la inmutabilidad fijada arriba |
| Elegir ahora el artefacto exacto o la reconstrucción desde el tag para producción | D-B queda diferida. No bloquea el staging |
| Un solo ruleset con bypass del Release Maintainer para crear y también para mover | El bypass cubre el ruleset entero y rompe la inmutabilidad |
| «Solo el propietario puede crear tags» como regla permanente | El rol es Release Maintainer. Hoy puede haber una persona. Mañana pueden ser varias, sin concedérselo a todo Developer |
| Exigir un GitHub Release o un tag firmado para autorizar el deploy | No son el mecanismo de aprobación ni el de inmutabilidad |
| Exigir ya una segunda aprobación humana en el entorno `staging` | La aprobación de la release es crear el tag. La promoción a producción queda para más adelante |

## Consecuencias

**Ya no hace falta volver a decidir:** tronco con ramas cortas; merge y push a `main` no
despliegan; el tag no se crea solo; SemVer 2.0.0 es la semántica de la versión desplegable;
el SHA pertenece a `main`; `php` y `css` de ese SHA están en verde o el deploy no corre;
falla cerrado; staging es el único destino antes del corte; una versión publicada no se
reescribe.

**D-A está cerrada.** El tag del theme es `theme-v<SemVer>`. El del plugin es
`plugin-v<SemVer>`. El del estático es `v<SemVer>`. El payload no se despliega por tag.
**D-C está cerrada:** el tag aprobado lo crea un Release Maintainer bajo el ruleset de
creación; el de inmutabilidad no tiene bypass; Actions no crea tags; el deploy normal
usa `contents: read`. **D-B queda diferida** al diseño futuro de la promoción a
producción y no bloquea el alcance de staging de este ADR.

**Arquitectura aceptada, implementación pendiente.** El orden es: esta aceptación, luego
los rulesets de tags, el entorno `staging` y sus secretos, el workflow que escucha
`theme-v*` y `plugin-v*`, y solo después la decisión de release que crea `theme-v0.5.3`.
Hoy no hay `deploy.yml`. El CI de pull requests no cambia. Staging permanece en el
theme `0.5.2`.

**Esta aceptación no autoriza:** despliegue a producción, credenciales de producción en
el workflow de staging, escribir el `public_html` de producción, el corte, que `v*`
dispare WordPress en staging, tags automáticos, deploy al merge o al push a `main`,
migración, seed, convert, demo purge, aprovisionamiento de CF7, `--confirm-production`,
sustituir el `.htaccess` del entorno, ni promover la base de datos o `uploads/` de
staging.

## Referencias

- [Semantic Versioning 2.0.0](https://semver.org/)
- ADR 0004, 0006, 0007, 0008, 0013, 0015, 0016, 0038, 0043
- `VERSION` (`1.0.35`), tags anotados `v1.0.14` … `v1.0.35`, `CHANGELOG.md`
- `.github/workflows/test.yml` (jobs `php` y `css`; sin `tags:`, sin deploy)
- Theme `0.5.3` en `main`, commit `d1e234cc4024fa8af2fef65a96a267dee2fb9b1d`
- [#26](https://github.com/refo44/demo-caminodeldharma/issues/26)
