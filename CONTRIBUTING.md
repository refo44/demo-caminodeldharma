# Contributing — Camino del Dharma

Gracias por contribuir al sitio web de la Comunidad Buddhista Camino del Dharma. Este repositorio combina maqueta estática, documentación técnica y (futuro) theme WordPress.

## Antes de empezar

1. Lee `docs/17-orden-implementacion.md` para conocer la fase activa del proyecto.
2. Revisa los [ADR vigentes](docs/adr/README.md) (`docs/adr/`). Las decisiones **Aceptada** son obligatorias.
3. El copy editorial proviene de `content-source/` — no parafrasear.

## Flujo Git

1. Crear rama desde `main`: `feature/descripcion-corta` o `fix/descripcion-corta`.
2. Implementar cambios en el repositorio (nunca editar producción directamente — ADR 0005).
3. Ejecutar validaciones locales (ver abajo).
4. Abrir Pull Request hacia `main` con descripción clara del cambio y referencia a docs/ADR si aplica.
5. Tras merge, **despliegue manual** según README y ADR 0015 (CI/CD pospuesto — ADR 0016).

**Producción solo desde `main`**, asociada a `VERSION` y `CHANGELOG.md`.

## Transición estático → WordPress

Durante Fase 3, registrar cambios que afecten solo una implementación en [`docs/migracion-static-wordpress.md`](docs/migracion-static-wordpress.md). Cambios de diseño, CSS, navegación o a11y en producción deben portarse también a `wordpress/`.

## Validaciones locales

```bash
npm install
npm run lint:css
```

Stylelint debe finalizar **sin errores** antes de commit, PR o despliegue.

Para cambios de CSS/HTML significativos, revisar también:

- `docs/19-accesibilidad-estandares` (§10–11)
- Checklist de `docs/18-tendencias-ux-ui-sistema-editorial` (§8)

## Política de cambios

Si el cambio afecta **estructura**, **navegación**, **identidad visual** o **arquitectura**:

1. Actualizar el documento correspondiente en `docs/` (o crear ADR en `docs/adr/`).
2. Implementar en código.
3. Validar según criterios de la fase en `17-orden-implementacion`.

## Decisiones arquitectónicas (ADR)

Nueva decisión estructural → archivo numerado en `docs/adr/` siguiendo la plantilla del [README de ADR](docs/adr/README.md).

Los ADR aceptados son **inmutables**. Para cambiar una decisión, crear un ADR nuevo y marcar el anterior como **Sustituida**.

## Despliegue

**Manual únicamente** (ADR 0015). Automatización pospuesta (ADR 0016).

### Fase 2 (actual): raíz del repo → `public_html`

Ver `README.md`: sitemap, `VERSION`, `CHANGELOG.md`, `npm run lint:css`, ZIP acotado al sitio estático.

**No subir** `docs/`, `wordpress/`, `scripts/` ni el repo completo.

### Fase 3: `static/` → `public_html`

ZIP generado solo desde `static/` tras reorganización del repo.

### WordPress

Theme desplegado manualmente a **staging separado** hasta el corte final. Post-corte: sync acotado al theme (ADR 0013); automatización futura (ADR 0006, diferida por ADR 0016).

## Commits

- Mensajes en español o inglés, claros y en imperativo cuando sea posible.
- Un commit por unidad lógica de cambio.
- No incluir `node_modules/`, ZIPs de despliegue ni secretos.

## Licencia

Código (HTML, CSS, JS, scripts): MIT — ver `LICENSE`.  
Contenido y recursos de marca: © Comunidad Buddhista Camino del Dharma.

## Contacto

- Correo: caminodeldharma1@gmail.com
- WhatsApp: +57 320 662 7608
