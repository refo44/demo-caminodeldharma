#!/usr/bin/env bash
# Rename all files in assets/images/galeria to galeria-NN.jpg (kebab-case, no spaces).
# Order: keep existing galeria-01..43; rename the rest to galeria-44, 45, ... (sorted by original name).
# Run from repo root.

set -e
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
GAL="${ROOT}/assets/images/galeria"
cd "$GAL"

# List files that are NOT already galeria-NN.(jpg|jpeg), sort by name
others=()
while IFS= read -r -d '' f; do
  base=$(basename "$f")
  if [[ ! "$base" =~ ^galeria-[0-9]+\.(jpg|jpeg)$ ]]; then
    others+=("$f")
  fi
done < <(find . -maxdepth 1 -type f \( -iname "*.jpg" -o -iname "*.jpeg" \) -print0 2>/dev/null)

# Sort by path (natural sort for consistent order)
sorted=()
while IFS= read -r line; do
  [[ -n "$line" ]] && sorted+=("$line")
done < <(for p in "${others[@]}"; do echo "$p"; done | sort -t/ -k2 -V)

# Phase 1: rename "others" to temp names
idx=44
for path in "${sorted[@]}"; do
  [[ ! -f "$path" ]] && continue
  tmp="__ren-${idx}.jpg"
  mv "$path" "$tmp"
  idx=$((idx + 1))
done

# Phase 2: normalize galeria-36..43 .jpeg to .jpg
for n in 36 37 38 39 40 41 42 43; do
  [[ -f "galeria-${n}.jpeg" ]] && mv "galeria-${n}.jpeg" "galeria-${n}.jpg"
done

# Phase 3: rename temp to galeria-NN.jpg
for f in __ren-*.jpg; do
  [[ -f "$f" ]] || continue
  num="${f#__ren-}"
  num="${num%.jpg}"
  mv "$f" "galeria-${num}.jpg"
done

total=$(ls -1 galeria-*.jpg 2>/dev/null | wc -l | tr -d ' ')
echo "Done. Gallery files: galeria-01.jpg … galeria-${total}.jpg"
