# ADR 0026: Contact Form 7 para el formulario de contacto

## Estado

Aceptada

## Fecha

2026-07-31 (decisión original); formalizada como ADR el 2026-07-31 bajo la política de ADR 0025

## Contexto

FUNC-001 (auditoría de producción, 2026-07-19): el formulario de `/contacto` en el estático nunca
entregó mensajes (`<form action="#">`, sin backend, sin manejador JS). TASK-0002 mitigó el riesgo
añadiendo CTAs de WhatsApp/correo junto al formulario; TASK-0003 quedó pendiente para el fix duradero.

Se decidió resolver TASK-0003 en WordPress, no en el estático — el estático se mantiene sin cambios por
decisión expresa del propietario de minimizar modificaciones a la producción actual mientras se
construye WordPress en paralelo. El `<form action="#">` permanece en `contacto/index.html` hasta el
corte a WordPress, como degradación aceptada conscientemente (ver `.audit/decisions.md`, 2026-07-31).

Al evaluar cómo implementarlo en WordPress, se compararon tres plugins de formularios.

## Decisión

Se usa **Contact Form 7** para el formulario de contacto en el theme WordPress, con envío al buzón
confirmado **caminodeldharma1@gmail.com**.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| --- | --- |
| **WPForms (Lite)** | Interfaz más simple tipo arrastrar-soltar con antispam básico incluido, pero la versión gratuita es más limitada en campos e integraciones que Contact Form 7 |
| **Fluent Forms (Free)** | Guarda las entradas dentro de WordPress sin plugin adicional (a diferencia de CF7+Flamingo), pero menos extendido/documentado que CF7 para este caso de uso |
| **Endpoint propio** (PHP/servicio externo, contemplado originalmente en TASK-0003) | Descartada: mantener código propio para algo que un plugin maduro y auditable ya resuelve no aporta valor, y contradice la preferencia de ADR 0024/0025 por evitar lógica de negocio duplicada fuera del plugin first-party |

## Consecuencias

- Antispam vía el propio ecosistema de CF7 (honeypot/Akismet — mecanismo exacto a definir al implementar
  TASK-0003).
- El envío de prueba en Docker local (ADR 0023) no certifica la entrega real desde el hosting —
  `Pass (local)` ≠ `Pass` (`docs/docker-wordpress-playbook.md` §4): la entrega efectiva a
  caminodeldharma1@gmail.com se valida en staging real antes de publicar.
- Contact Form 7 es el primer plugin de terceros aprobado bajo la política de ADR 0025.

## Referencias

- ADR [0025](0025-politica-plugins-terceros.md) — política que formaliza esta aprobación
- ADR [0024](0024-plugin-dominio-theme-presentacion.md) — por qué no se reintrodujo un endpoint propio
- `.audit/implementation/tasks/TASK-0003.md`
- `.audit/decisions.md` — 2026-07-31 (decisión original y confirmación de buzón)
- `docs/docker-wordpress-playbook.md` §4 — distinción `Pass (local)` vs `Pass`
