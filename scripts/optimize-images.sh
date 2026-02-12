#!/usr/bin/env bash
# Optimize images in assets/images for web.
# Uses ImageMagick (magick or convert). Run from repo root.
# - JPEG: max 1600px longest side, quality 85, strip metadata.
# - PNG: strip metadata; resize if longer than 1600px (preserves logo etc).

set -e
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
IMAGES_DIR="${ROOT}/assets/images"
MAX_PX=1600
JPEG_QUALITY=85

# Prefer ImageMagick 7 (magick); fallback to convert (IMv6)
if command -v magick &>/dev/null; then
  IM_CMD="magick"
elif command -v convert &>/dev/null; then
  IM_CMD="convert"
else
  echo "ImageMagick (magick or convert) is required. Install with: brew install imagemagick"
  exit 1
fi

count=0
while IFS= read -r -d '' f; do
  tmp="${f}.opt"
  if "$IM_CMD" "$f" -resize "${MAX_PX}x${MAX_PX}>" -quality "$JPEG_QUALITY" -strip "$tmp" 2>/dev/null; then
    mv "$tmp" "$f"
    count=$((count + 1))
    echo "  $f"
  else
    rm -f "$tmp"
  fi
done < <(find "$IMAGES_DIR" -type f \( -iname "*.jpg" -o -iname "*.jpeg" \) -print0 2>/dev/null)

while IFS= read -r -d '' f; do
  tmp="${f}.opt"
  if "$IM_CMD" "$f" -resize "${MAX_PX}x${MAX_PX}>" -strip "$tmp" 2>/dev/null; then
    mv "$tmp" "$f"
    count=$((count + 1))
    echo "  $f"
  else
    rm -f "$tmp"
  fi
done < <(find "$IMAGES_DIR" -type f -iname "*.png" -print0 2>/dev/null)

echo "Optimized $count images."
