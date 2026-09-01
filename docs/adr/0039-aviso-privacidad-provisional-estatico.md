# ADR 0039: Publicar aviso de privacidad provisional en el estático

## Estado

Aceptada. El gate de Contact Form 7 y la espera de revisión legal (puntos 4–5 de «Decisión»)
fueron sustituidos por
[ADR 0041](0041-cf7-corte-sin-asesoria-legal.md). La publicación de `/privacidad` y el carácter
provisional del aviso siguen vigentes.

## Fecha

2026-08-29

## Contexto

ADR [0028](0028-privacidad-aplazada-conscientemente.md) aplazó `/privacidad` hasta asesoría legal:
publicar un texto genérico sin esa revisión podía ser peor que no publicar nada, y Contact Form 7
(ADR 0026) quedaba gated a que existiera la página.

El propietario pidió publicar ya la página en el sitio estático, tomando como **referencia de
estructura** el aviso de Revista Logos (`page-privacidad.html`), no como copy. El tratamiento real
de este proyecto es distinto: no hay analítica propia, el formulario de `/contacto` no envía, los
canales operativos son WhatsApp y correo, hay embeds de YouTube/Vimeo, y actividades vigentes usan
formularios de Google, Classroom y Zoom.

La Ley 1581/2012 sigue aplicando al tratamiento de datos (contacto, preinscripciones, registros del
servidor) aunque no haya cookies propias (ADR 0019). La pregunta de si aplica el RGPD permanece
jurídica: este ADR no la responde.

## Decisión

1. Se publica `/privacidad` en el sitio estático (`privacidad/index.html`), enlazada de forma
   discreta en el pie de **todas** las páginas (nunca en el menú), y se añade al `sitemap.xml`.
2. El texto describe **hechos técnicos verificables** de este sitio. No se copia el aviso de Logos
   (analítica propia, GitHub Pages, envíos editoriales, licencias CC BY). No se afirma que el RGPD
   aplique o no.
3. El documento se marca **provisional** hasta validación por asesoría legal. Eso no impide
   publicarlo: informa con honestidad y deja el vacío jurídico acotado, no oculto.
4. **Contact Form 7 sigue gated.** Publicar esta página no habilita el envío en producción. Antes
   de que el formulario se procese en un servidor hay que actualizar este aviso (el texto actual
   dice que el formulario no envía) y contar con la revisión legal. El disparador de ADR 0028 se
   conserva con ese matiz.
   *(Histórico 2026-08-29. Sustituido el 2026-08-31 por ADR 0041: CF7 entra al corte; la
   revisión legal no es prerrequisito; en WordPress se actualizan solo los párrafos del
   formulario.)*
5. En WordPress (Fase 3) la página es una Page `privacidad` con `templates/page.html`; el enlace
   vive en `parts/footer.html`. No hace falta plantilla propia. El copy se importa del HTML live,
   no se reescribe.

Este ADR **sustituye** el aplazamiento de publicación de ADR 0028. Conserva el gate de Contact
Form 7 y la necesidad de asesoría legal para dar por cerrado PRIV-001b.
*(El gate y esa espera jurídica los sustituye ADR 0041; PRIV-001b queda como trabajo posterior,
no como lanzamiento.)*

## Alternativas consideradas

| Alternativa | Decisión |
| --- | --- |
| Seguir sin página hasta asesoría legal (ADR 0028) | Descartada por el propietario: el sitio ya trata datos por WhatsApp, correo y formularios de Google; el silencio informativo ya no se sostiene |
| Copiar el aviso de Revista Logos con nombres cambiados | Descartada: afirmaría analítica propia, un segundo dominio y un proceso editorial que este sitio no tiene |
| Redactar como texto legal definitivo | Descartada: la conclusión jurídica sigue correspondiendo a asesoría; el sello «provisional» es parte del compromiso, no un adorno |
| Publicar la página y activar Contact Form 7 | Descartada: el aviso actual niega el envío al servidor; CF7 cambiaría el tratamiento |

## Consecuencias

- `/privacidad` pasa a ser URL pública KEEP (ADR 0008, sin barra final).
- PRIV-001b deja de ser «página ausente»; permanece abierto en lo jurídico hasta la revisión legal.
  *(ADR 0041: esa revisión ya no es gate de lanzamiento; PRIV-001b es trabajo posterior.)*
- El pie de todas las páginas HTML incluye el enlace. Criterio PRIV-001 de la auditoría SEO.
- La matriz de migración, el inventario, el ledger y el árbol de URLs se actualizan: el copy ya no
  está «por definir».
- Antes de CF7 en producción: actualizar el punto del formulario y la fecha del aviso.

## Referencias

- ADR [0028](0028-privacidad-aplazada-conscientemente.md) — aplazamiento que este ADR sustituye
- ADR [0019](0019-sin-analitica-con-cookies.md) — sin cookies de analítica
- ADR [0026](0026-contact-form-7.md) — plugin; elegibilidad de producción en ADR 0041
- ADR [0041](0041-cf7-corte-sin-asesoria-legal.md) — sustituye el gate de CF7 y la espera legal
- `docs/informes-seo/02-auditoria-seo-tecnica.md` §9
- `privacidad/index.html`
