#!/usr/bin/env bash
# Regenerate the subsetted MarloweEscapade woff2.
# Uses pyftsubset (fonttools + brotli). Run from repo root.
#
# MarloweEscapade solo dibuja .site-name y .site-title, y ambos dicen
# "Camino del Dharma". Subsetar a esos caracteres baja la fuente de 52,1 KB
# a ~3,4 KB y la saca de la ruta crítica.
#
# IMPORTANTE: si el texto de .site-name / .site-title cambia, actualizar
# SUBSET_TEXT y volver a ejecutar este script, o faltarán glifos.

set -e
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
FONT_DIR="${ROOT}/assets/fonts/marlowe-escapade"
SOURCE="${FONT_DIR}/marlowe-escapade.woff2"
OUTPUT="${FONT_DIR}/marlowe-escapade-subset.woff2"
SUBSET_TEXT="Camino del Dharma"

if ! command -v pyftsubset &>/dev/null; then
  echo "pyftsubset es necesario. Instalar con: pip install 'fonttools[woff]' brotli"
  exit 1
fi

if [ ! -f "${SOURCE}" ]; then
  echo "No se encuentra la fuente completa: ${SOURCE}"
  exit 1
fi

pyftsubset "${SOURCE}" \
  --text="${SUBSET_TEXT}" \
  --flavor=woff2 \
  --layout-features='*' \
  --output-file="${OUTPUT}"

echo "OK  ${OUTPUT}"
ls -l "${SOURCE}" "${OUTPUT}" | awk '{printf "  %7.1f KB  %s\n", $5/1024, $9}'
echo
echo "Recordatorio: ejecutar 'npm run lint:css' si se tocó main.css."
