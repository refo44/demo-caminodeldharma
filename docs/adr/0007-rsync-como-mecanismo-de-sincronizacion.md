# ADR 0007: rsync como mecanismo de sincronización

## Estado

Aceptada

## Fecha

2026-07-19

## Contexto

El despliegue manual por ZIP extrae archivos en `public_html` pero **no elimina** automáticamente archivos obsoletos renombrados o retirados (p. ej. `site.webmanifest`, rutas antiguas, assets renombrados). Eso deja basura en producción y puede servir contenido desactualizado.

Con Git como fuente única (ADR 0004) y prohibición de edición manual (ADR 0005), el despliegue debe reflejar **exactamente** el árbol de archivos del commit desplegado.

## Decisión

Los despliegues automatizados usarán **SSH + `rsync`** hacia Hostinger, con sincronización unidireccional **repo → servidor**.

Parámetros orientativos:

```bash
rsync -avz --delete \
  --exclude '.git/' \
  --exclude 'node_modules/' \
  --exclude 'docs/' \
  --exclude 'content-source/' \
  --exclude 'scripts/' \
  --exclude '.github/' \
  ./ user@host:~/domains/caminodeldharma.org/public_html/
```

Principios:

- **`--delete`:** elimina en destino lo que ya no existe en origen (dentro del scope del sync).
- **Exclusiones explícitas:** no desplegar material de desarrollo (`docs/`, `scripts/`, `node_modules/`).
- **Lista de inclusiones** alineada con el ZIP de producción en README (HTML, `assets/`, `.htaccess`, sitemap, favicons, etc.).
- Rollback: redeploy del tag/commit anterior (ADR 0006).

El flujo ZIP manual puede usarse hasta que el workflow esté activo; al migrar, el operador debe eliminar manualmente archivos huérfanos equivalentes a lo que haría `--delete`.

**Alcance:** este ADR aplica al **sitio estático** desplegado en `public_html` (Fase 2–4 pre-corte). Tras WordPress, el rsync con `--delete` se limita al **directorio del theme** (ADR 0013); no sincronizar uploads ni core WP.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| FTP sin delete | Deja archivos obsoletos; estado impredecible. |
| ZIP extract overwrite | No borra huérfanos; proceso manual propenso a errores. |
| git pull en servidor | Viola ADR 0005 (servidor no es entorno de edición); expone `.git` si no se configura con cuidado. |
| Rclone / scp recursivo sin rsync | Menos eficiente; sin diff incremental estándar. |

## Consecuencias

**Beneficios:**

- Producción es espejo del artefacto versionado.
- Transferencias incrementales más rápidas que ZIP completo.
- Compatible con GitHub Actions vía SSH key.

**Riesgos:**

- `--delete` mal configurado puede borrar contenido legítimo fuera del scope (p. ej. uploads WordPress futuros); en fase WordPress habrá que ajustar exclusiones (`wp-content/uploads/`).
- Requiere acceso SSH habilitado en Hostinger.

**Trabajo futuro:**

- Implementar en `deploy.yml` con lista de exclusiones validada.
- Script local `scripts/deploy.sh` opcional como wrapper del mismo comando.
- Revisar exclusiones al migrar a theme WordPress.

## Referencias

- `README.md` (contenido del ZIP de producción)
- `.github/workflows/deploy.yml`
- `docs/17-orden-implementacion` Fase 4
- ADR 0004, ADR 0005, ADR 0006
