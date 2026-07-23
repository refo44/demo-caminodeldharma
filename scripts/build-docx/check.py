"""Comprobación del .docx generado: XML bien formado, cobertura de texto y estructura."""
import re
import sys
import pathlib
import zipfile
import xml.etree.ElementTree as ET

W = '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}'


def check(docx_path, md_path, start_heading):
    z = zipfile.ZipFile(docx_path)
    problems = []

    # 1. Todas las partes XML deben parsear.
    for name in z.namelist():
        if name.endswith(('.xml', '.rels')):
            try:
                ET.fromstring(z.read(name))
            except ET.ParseError as e:
                problems.append(f'XML mal formado en {name}: {e}')

    doc = ET.fromstring(z.read('word/document.xml'))

    # 2. Texto plano del documento.
    text = ''.join(t.text or '' for t in doc.iter(W + 't'))
    norm = lambda s: re.sub(r'\s+', ' ', s).replace(' ', ' ')
    dtext = norm(text)

    # 3. Cobertura: cada línea de texto del markdown debe aparecer.
    md = open(md_path, encoding='utf-8').read()
    md = md[md.index(start_heading):]
    missing = []
    in_code = False
    for line in md.split('\n'):
        if line.startswith('```'):
            in_code = not in_code
            continue
        if in_code:
            continue
        s = line.strip()
        if not s or set(s) <= set('-|: '):
            continue
        # Quitar marcado para comparar solo el texto visible.
        s = re.sub(r'^#{1,6}\s+', '', s)
        s = re.sub(r'^>\s?', '', s)
        s = re.sub(r'^\s*(?:[-*]|\d+\.)\s+', '', s)
        s = re.sub(r'\[([^\]]+)\]\([^)]+\)', r'\1', s)
        s = re.sub(r'<((?:https?://|mailto:)[^\s<>]+|[^\s<>@]+@[^\s<>]+)>', r'\1', s)
        s = s.replace('**', '').replace('`', '')
        if s.startswith('|'):
            cells = [c.strip().replace('\x00', '|') for c in s.replace('\\|', '\x00').strip('|').split('|')]
        else:
            cells = [s]
        for cell in cells:
            cell = re.sub(r'(?<!\*)\*([^*]+)\*', r'\1', cell)
            cell = norm(cell).strip()
            if len(cell) < 12:
                continue
            if cell not in dtext:
                missing.append(cell)

    # 4. Estructura.
    styles_used = [s.get(W + 'val') for s in doc.iter(W + 'pStyle')]
    tables = len(list(doc.iter(W + 'tbl')))
    images = len(list(doc.iter('{http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing}inline')))
    headings = sum(1 for s in styles_used if s and s.startswith('Heading'))
    hyperlinks = len(list(doc.iter(W + 'hyperlink')))

    # 5. Todas las relaciones de hyperlink deben existir.
    rels = ET.fromstring(z.read('word/_rels/document.xml.rels'))
    rel_ids = {r.get('Id') for r in rels}
    R = '{http://schemas.openxmlformats.org/officeDocument/2006/relationships}id'
    for h in doc.iter(W + 'hyperlink'):
        if h.get(R) and h.get(R) not in rel_ids:
            problems.append(f'hyperlink sin relación: {h.get(R)}')

    # 6. Anchos de tabla: gridCol debe sumar el ancho declarado.
    for i, tbl in enumerate(doc.iter(W + 'tbl')):
        grid = [int(g.get(W + 'w')) for g in tbl.iter(W + 'gridCol')]
        tw = tbl.find(f'{W}tblPr/{W}tblW')
        if tw is not None and tw.get(W + 'type') == 'dxa':
            declared = int(tw.get(W + 'w'))
            if sum(grid) != declared:
                problems.append(f'tabla {i}: gridCol suma {sum(grid)} != tblW {declared}')
        # Cada fila debe tener tantas celdas como columnas.
        for j, tr in enumerate(tbl.findall(W + 'tr')):
            n = len(tr.findall(W + 'tc'))
            if n != len(grid):
                problems.append(f'tabla {i} fila {j}: {n} celdas, {len(grid)} columnas')

    print(f'--- {docx_path.split("/")[-1]}')
    print(f'    encabezados={headings}  tablas={tables}  imágenes={images}  enlaces={hyperlinks}')
    print(f'    caracteres de texto={len(dtext)}')
    if problems:
        print('    PROBLEMAS:')
        for p in problems[:20]:
            print(f'      - {p}')
    if missing:
        print(f'    TEXTO FALTANTE ({len(missing)}):')
        for m in missing[:15]:
            print(f'      - {m[:110]}')
    if not problems and not missing:
        print('    OK — sin problemas de estructura ni texto faltante')
    return len(problems) + len(missing)


if __name__ == '__main__':
    # Raíz del repositorio, dos niveles por encima de scripts/build-docx/.
    R = str(pathlib.Path(__file__).resolve().parents[2]) + '/'
    total = 0
    total += check(R + 'docs/informes-seo/00-informe-auditoria-seo.docx',
                   R + 'docs/informes-seo/00-informe-auditoria-seo.md', '## 1. En una página')
    total += check(R + 'docs/informes-seo/02-auditoria-seo-tecnica.docx',
                   R + 'docs/informes-seo/02-auditoria-seo-tecnica.md', '## 1. Contexto y veredicto')
    total += check(R + 'docs/24-brief-editorial-blog-y-visibilidad.docx',
                   R + 'docs/24-brief-editorial-blog-y-visibilidad.md', '## 1. Resumen')
    total += check(R + 'docs/21-manual-voz-copywriting-editorial.docx',
                   R + 'docs/21-manual-voz-copywriting-editorial.md', '## 1. Propósito')
    sys.exit(1 if total else 0)
