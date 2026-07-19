# FUNC-001 — Remediation Package
Formulario de contacto no funcional (`action="#"`, sin backend ni handler JS).

## Resolution objective
Que ningún visitante pierda un mensaje en silencio: /contacto solo ofrece canales que entregan de verdad.

## Acceptance criteria
1. /contacto no contiene ningún `<form>` cuyo envío sea inentregable.
2. Los CTAs de WhatsApp (`https://wa.me/573206627608`) y correo (`mailto:caminodeldharma1@gmail.com`) son visibles, accesibles por teclado y funcionan.
3. (Fix duradero, si se aprueba) Un envío de prueba end-to-end llega a un buzón de prueba.

## Evidence and root cause addressed
EVID-0024 (ningún script adjunta handler de submit), EVID-0025 (markup `action="#" method="post"`, contacto/index.html:127-147). Causa raíz VERIFICADA: placeholder de maqueta desplegado a producción en host estático.

## Scope and implementation locations
Solo `contacto/index.html` líneas 127-147 (fix mínimo). El fix duradero añadiría un endpoint (PHP en Hostinger o servicio de formularios) — fuera de alcance hasta decisión de producto.

## Prerequisites and dependencies
Ninguno para el fix mínimo. El duradero depende de la decisión registrada en TASK-0003.

## Immediate containment, when required
No se requiere contención adicional: los canales WhatsApp/correo ya existen en la misma página (línea 149).

## Minimal safe fix
Sustituir el bloque `<form>` por una sección CTA que reutilice los enlaces existentes con el sistema de diseño actual (.btn/.btn-primary, iconos lucide inline).

## Preferred durable fix
Formulario real con procesador (PHP mail con honeypot+CSRF o servicio externo), estados de éxito/error accesibles (aria-live), y prueba end-to-end. Requiere decisión de producto (TASK-0003, BLOCKED).

## Ordered implementation steps
1. `contacto/index.html`: eliminar líneas 127-147 (el `<form>` completo). Resultado: sin formulario muerto.
2. En su lugar insertar bloque CTA (ver "Proposed changes"). Resultado: dos botones accesibles.
3. Ejecutar `npm run lint:css` si se tocan estilos. Resultado: lint limpio.
4. Verificación visual mobile (390px) y desktop (1440px). Resultado: sin ruptura de layout.

## Proposed code, configuration, content, schema, or infrastructure changes
[PROPUESTO — NO EJECUTADO]
```html
<div class="contact-cta section-gap">
  <a class="btn btn-primary" href="https://wa.me/573206627608" target="_blank" rel="noopener noreferrer">Escríbenos por WhatsApp <span class="visually-hidden">(abre en nueva pestaña)</span></a>
  <a class="btn" href="mailto:caminodeldharma1@gmail.com">Escríbenos un correo</a>
</div>
```

## Local validation
`grep -c '<form' contacto/index.html` → 0. Navegación por teclado hasta ambos CTAs.

## Automated and regression tests
No hay suite; prueba manual del journey de contacto en mobile/desktop. stylelint pasa.

## Deployment sequence
Deploy estático normal tras el cambio.

## Production verification and monitoring
`curl -s https://caminodeldharma.org/contacto | grep -c '<form'` → 0. Clic real en ambos CTAs. Opcional: evento GA4 en clics de contacto.

## Rollback triggers
Ruptura de layout o pérdida de los canales visibles.

## Rollback procedure
`git checkout` de contacto/index.html previo y redeploy (estado anterior conocido: formulario muerto pero página estable).

## Trade-offs and residual risks
Sin formulario en página, algunos usuarios preferirían no salir a WhatsApp/correo; se resuelve con el fix duradero si se aprueba.

## Human decisions or approvals required
Solo para el fix duradero (TASK-0003): ¿quiere la comunidad un formulario real y con qué procesador?

## Owner and effort
Frontend — 30 min a 2 h (mínimo).
