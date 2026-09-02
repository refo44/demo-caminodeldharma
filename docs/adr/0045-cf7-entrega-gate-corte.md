# ADR 0045: Entrega de correo CF7 es gate del corte

## Estado

Aceptada

## Fecha

2026-09-01

## Contexto

ADR [0041](0041-cf7-corte-sin-asesoria-legal.md) hace elegible Contact Form 7 en el corte sin
asesoría legal y pedía verificar entrega en staging. El punto 5 de 0041 permitía cortar con CF7
**apagado** (WhatsApp/correo) si el correo de staging fallaba.

El propietario (OWN-033 / D-WU-10, 2026-09-01) **no** acepta ese fallback como camino por
defecto: el corte **espera** prueba de entrega. Quien implementa no tiene acceso al buzón
`caminodeldharma1@gmail.com`; la prueba técnica puede ir a un correo del implementador y el
cliente confirma el buzón de la comunidad.

La elegibilidad legal de CF7 (0041 puntos 1–4, 6) **no** cambia.

## Decisión

1. **El corte con CF7 activo exige entrega demostrada** a
   `caminodeldharma1@gmail.com` (confirmación del cliente: un mensaje sintético recibido).
   `Pass (local)` no cuenta.
2. **Prueba técnica en staging** (Hostinger): el implementador puede enviar a
   `refo44@gmail.com`. Eso prueba MTA/CF7/Hostinger (**Pass staging, desarrollador**). No
   sustituye el punto 1.
3. Tras la prueba técnica, el destinatario de producción es otra vez
   `caminodeldharma1@gmail.com`. El formulario público **nunca** queda apuntando al Gmail
   personal.
4. Si staging no entrega, **no** se corta con CF7 encendido. Se espera, se reabre OWN-033, o se
   acuerda por escrito un corte con CF7 apagado (ya no es el default de 0041 §5).
5. Este ADR **sustituye solo el punto 5** de ADR 0041. El resto de 0041 sigue vigente.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Cortar con CF7 off + WhatsApp si el mail falla (0041 §5) | El propietario eligió esperar prueba de entrega |
| Cortar con CF7 on sin prueba | El visitante puede enviar a un agujero negro |
| Usar `refo44@gmail.com` como buzón de producción | No es el correo de la comunidad |

## Consecuencias

Checklists y matriz: el gate de release de correo es **cliente + buzón comunitario**, no solo
el Gmail del implementador. ADR 0041 se marca con esta sustitución parcial.

## Referencias

- OWN-018 · OWN-033
- ADR [0041](0041-cf7-corte-sin-asesoria-legal.md), [0026](0026-contact-form-7.md)
- `docs/cutover-checklist-wordpress.md`
