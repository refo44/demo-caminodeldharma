# TASK-0006 (parte de embeds) — Resultado de implementación

- **Estado de la tarea:** sigue `READY` — esta es **solo la mitad de embeds**; la política de privacidad continúa pendiente
- **Fecha:** 2026-07-20
- **Hallazgo:** PRIV-001 (rebajado a BAJA por ADR 0019)
- **Permisos:** escritura sí · despliegue **no** · commit **no**

## Cambios

| Archivo | iframes YouTube | iframes Vimeo | `embedUrl` JSON-LD |
|---|---:|---:|---:|
| `index.html` | 2 | — | — |
| `practica/index.html` | 2 | 1 | — |
| `practica/videos/index.html` | 4 | 1 | 5 |
| **Total** | **8** | **2** | **5** |

- YouTube: `www.youtube.com/embed/…` → `www.youtube-nocookie.com/embed/…`
- Vimeo: `player.vimeo.com/video/…` → `…?dnt=1` (Do Not Track)

**Los 5 `embedUrl` del JSON-LD se actualizaron también**, no por descuido: `VideoObject.embedUrl`
debe describir el reproductor realmente incrustado. Dejarlos apuntando al dominio anterior habría
creado una incoherencia entre los datos estructurados y el marcado.

## Verificaciones ejecutadas

| Verificación | Resultado |
|---|---|
| `robots.txt` de youtube-nocookie.com no bloquea `/embed/` | **PASS** — solo bloquea /login, /results, /videos, etc. |
| Player nocookie accesible | **PASS** — 200 |
| Vimeo con `dnt=1` no se rompe | **PASS** — ver nota |
| JSON-LD sigue parseando en las 3 páginas | **PASS** |
| Sin embeds pendientes de migrar | **PASS** — 0 restantes |
| `npm run lint:css` | **PASS** |

### Nota sobre el 401 de Vimeo

Consultar `player.vimeo.com/video/1164143915` con curl devuelve **401 con y sin `dnt=1`**. Con un
`Referer` de dominio autorizado devuelve **200 en ambos casos**. Es la restricción de dominio propia
de Vimeo, **no un efecto del parámetro**: no hay regresión. Consecuencia preexistente, ajena a este
cambio: un rastreador que pida ese `embedUrl` sin `Referer` recibirá 401.

## Alcance de lo que resuelve

Elimina la última vía de cookies **del propio sitio**. Matiz honesto sobre YouTube: el modo
«privacidad mejorada» no almacena información **mientras no se reproduzca** el vídeo; al pulsar play,
YouTube puede fijar cookies igualmente. Para eliminación total haría falta una *facade* (miniatura que
carga el iframe solo al hacer clic), no contemplada aquí.

## Pendiente para cerrar TASK-0006

**Política de privacidad** — requiere texto aprobado por la comunidad. Sigue siendo recomendable pese
a ADR 0019: la Ley 1581/2012 cubre el tratamiento de datos personales en general, no solo cookies.

## Rollback

`git checkout -- index.html practica/index.html practica/videos/index.html`. Recordar que revertir en
Git no cambia producción: el despliegue es por ZIP manual (ADR 0015).
