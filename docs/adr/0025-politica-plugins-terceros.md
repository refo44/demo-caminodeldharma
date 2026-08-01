# ADR 0025: Plugins de terceros solo con aprobación por ADR

## Estado

Aceptada

## Fecha

2026-07-31

## Contexto

Hasta ahora, la elección de un plugin de terceros (Contact Form 7 para TASK-0003, decidido el
2026-07-31) se registró en `.audit/decisions.md` y en la ficha de tarea correspondiente, sin pasar por
un ADR propio. Un playbook de aprendizajes de otro proyecto WordPress (Revista de Filosofía LOGO ET
SPES, migración static → WordPress) recomienda un orden de preferencia explícito y vetos por defecto,
para evitar acumular dependencias de terceros no auditadas: page builders que romperían el CSS
congelado (ADR 0009), suites SEO todo-en-uno que competirían con el SEO técnico ya en 100/100 (auditoría
2026-07-19), o ACF desplazando el modelo de contenido ya definido en `docs/03-wordpress-content-model.md`.

## Decisión

1. **Orden de preferencia** para cualquier necesidad de funcionalidad en WordPress:
   1. APIs nativas de WordPress (bloques de Gutenberg, hooks del core).
   2. Código first-party en `camino-del-dharma-core` (ADR 0024).
   3. Plugin de terceros — **solo con ADR propio** que documente las alternativas descartadas.
2. **Vetados por defecto** (requieren ADR explícito para excepcionarlos; no se instalan por comodidad):
   ACF u otros constructores de campos, page builders (Elementor, Divi, etc.), suites SEO todo-en-uno
   (Yoast, RankMath — el SEO técnico ya se gestiona a mano y está en 100/100), plugins de optimización
   "todo en uno", y cualquier analítica no aprobada (ADR 0019 ya descarta GA4 y toda analítica con
   cookies).
3. **Contact Form 7 queda promovido retroactivamente a decisión de ADR** bajo este criterio — ver
   ADR [0026](0026-contact-form-7.md), que documenta la elección con sus alternativas ya comparadas
   (WPForms, Fluent Forms).
4. Cada plugin de terceros aprobado lleva un inventario operativo: versión, configuración obligatoria,
   impacto de privacidad, procedimiento de verificación tras actualizaciones y procedimiento de
   retirada.

## Alternativas consideradas

| Alternativa | Decisión |
| --- | --- |
| Caso por caso, sin política escrita (criterio usado hasta hoy) | Descartada: sin veto por defecto, es fácil acumular plugins (page builder, ACF, suite SEO) que erosionan decisiones ya tomadas — CSS congelado, modelo de contenido, SEO técnico |
| Prohibir todo plugin de terceros | Descartada: poco realista — un formulario de contacto funcional (TASK-0003) requiere procesar envíos, y reinventar eso en first-party no aporta valor frente a un plugin maduro y auditable |

## Consecuencias

**A favor**

- Cada plugin de terceros queda documentado con su razón de ser y sus alternativas, igual que el resto
  de decisiones del proyecto.
- Los vetos por defecto protegen invariantes ya decididas (CSS, modelo de contenido, SEO) de erosionarse
  plugin a plugin, sin necesidad de discutirlo cada vez.

**Contrapartidas aceptadas**

- Más fricción de proceso para adoptar un plugin nuevo (requiere ADR, no solo una línea en
  `decisions.md`) — aceptada a cambio de trazabilidad.

## Referencias

- ADR [0024](0024-plugin-dominio-theme-presentacion.md) — el código first-party siempre tiene preferencia sobre terceros
- ADR [0026](0026-contact-form-7.md) — Contact Form 7, primer plugin aprobado bajo esta política
- ADR [0009](0009-css-y-tokens-invariantes-en-migracion.md) — CSS congelado que un page builder rompería
- ADR [0019](0019-sin-analitica-con-cookies.md) — analítica ya descartada
- `.audit/implementation/tasks/TASK-0003.md`
- Playbook de aprendizajes, Revista de Filosofía LOGO ET SPES, 2026-07-31 (§11)
