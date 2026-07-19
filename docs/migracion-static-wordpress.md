# Migración static/ → WordPress

Registro operativo de diferencias entre la implementación estática (`static/`) y el theme WordPress (`wordpress/`) **durante la Fase 3**.

**No sustituye** a los ADR ni a `17-orden-implementacion`. Complementa el seguimiento día a día de la transición.

---

## Cuándo actualizar este documento

Registrar cada cambio que afecte una sola implementación o que esté en curso de portarse al theme.

| Tipo de cambio | Static | WordPress |
| ---------------- | ------ | --------- |
| Contenido temporal (p. ej. evento que caduca antes del corte) | Sí | No aplica |
| Corrección editorial permanente | Sí | Sí (cuando se porte) |
| Diseño, CSS, JS, navegación, a11y, SEO, componentes | Sí | **Obligatorio** portar |

---

## Registro de cambios

| Fecha | Cambio | Static | WordPress | Estado |
| ----- | ------ | ------ | --------- | ------ |
| — | *(añadir filas al iniciar Fase 3)* | — | — | — |

**Estados sugeridos:** `Pendiente`, `En migración`, `Completo`, `No aplica`, `Cerrado`.

---

## Clasificación de cambios

### Solo static (permitido)

- Eventos u ofertas temporales vigentes solo hasta la fecha de lanzamiento de WordPress.
- Hotfixes urgentes de producción mientras el theme aún no refleja el fix (debe registrarse aquí y planificarse porte).

### Ambas implementaciones (obligatorio)

- Estructura de bloques, navegación, URLs.
- CSS, JavaScript, tokens, identidad visual.
- Accesibilidad, SEO técnico, comportamiento de componentes.
- Copy permanente alineado con `content-source/`.

---

## Antes del corte final

1. Revisar que no queden filas en `En migración` o `Pendiente` (salvo `No aplica`).
2. Migración final de contenido editorial a WordPress.
3. Validación en staging (Fase 2.5 sobre theme).
4. Backup estático + backup WordPress (BD + uploads).
5. Corte a producción según checklist en `17-orden-implementacion` § Transición.

---

## Referencias

- `docs/17-orden-implementacion` § Transición estático → WordPress
- ADR 0012, ADR 0014, ADR 0015, ADR 0016
- `docs/12-theme-file-structure`

---

**Versión:** 1.0
