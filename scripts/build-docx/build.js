// Genera los tres entregables en .docx con la identidad de Camino del Dharma.
//   docs/informes-seo/00-informe-auditoria-seo.docx
//   docs/informes-seo/02-auditoria-seo-tecnica.docx
//   docs/24-brief-editorial-blog-y-visibilidad.docx

const fs = require('fs');
const path = require('path');
const {
  Document, Packer, Paragraph, TextRun, ImageRun, Table, TableRow, TableCell,
  Header, Footer, PageBreak, PageNumber, AlignmentType, BorderStyle, ShadingType,
  WidthType, TableOfContents, VerticalAlign,
} = require('docx');

const {
  C, FONT_HEAD, FONT_BODY, PAGE, CONTENT_WIDTH, styles, numbering,
} = require('./brand');
const { parseBlocks, renderBlocks, inline } = require('./md');

// La raíz del repositorio, dos niveles por encima de scripts/build-docx/.
const REPO = path.resolve(__dirname, '..', '..');
// El logo de marca es un trazo BLANCO sobre transparencia: sobre página blanca
// resulta invisible. Como el manual prohíbe alterar su color, se usa la variante
// oficial sobre fondo de marca —el mismo criterio que la cabecera del sitio, que
// sirve el logo blanco sobre --header-bg (#8c2b3d)—. Original de 1000 px, no la
// versión de 240 px que sirve la web, porque esta es una pieza para impresión.
// (`logo-camino-del-dharma-fondo-blanco.png` no sirve: es el trazo blanco
// aplanado sobre blanco, es decir, un cuadro liso.)
const LOGO = fs.readFileSync(path.join(
  REPO,
  'content-source/Pagina web Camino del Dharma/FOTOS PAGINA WEB/logo-camino-del-dharma-fondo-marca-oscuro.png',
));

/* ---------------------------------------------------------------- portada */

const centered = (children, extra = {}) => new Paragraph({
  alignment: AlignmentType.CENTER, children, ...extra,
});

function cover({ kicker, title, subtitle, tagline, footNote }) {
  const out = [];

  out.push(new Paragraph({ spacing: { after: 420 }, children: [] }));

  out.push(centered([new ImageRun({
    data: LOGO, type: 'png',
    altText: { title: 'Camino del Dharma', description: 'Emblema de la Comunidad Buddhista Camino del Dharma', name: 'logo' },
    transformation: { width: 132, height: 132 },
  })]));

  out.push(centered([new TextRun({
    text: 'Camino del Dharma',
    font: FONT_HEAD, bold: true, size: 48, color: C.brand1,
  })], { spacing: { before: 200, after: 60 } }));

  out.push(centered([new TextRun({
    text: kicker,
    font: FONT_BODY, size: 19, color: C.brand2, allCaps: true, characterSpacing: 60,
  })], { spacing: { after: 320 } }));

  out.push(centered([new TextRun({
    text: title,
    font: FONT_HEAD, bold: true, size: 40, color: C.brand4,
  })], { spacing: { after: 80 } }));

  out.push(centered([new TextRun({
    text: subtitle,
    font: FONT_BODY, italics: true, size: 24, color: C.brand4,
  })], { spacing: { after: 200 } }));

  // Filete de separación, en rosa polvo.
  out.push(new Paragraph({
    alignment: AlignmentType.CENTER,
    spacing: { after: 240 },
    border: { bottom: { style: BorderStyle.SINGLE, size: 10, space: 8, color: C.brand2 } },
    indent: { left: 2400, right: 2400 },
    children: [new TextRun({ text: ' ' })],
  }));

  out.push(centered(inline(tagline), { style: 'Lead', spacing: { after: 200 } }));

  out.push(centered([new TextRun({
    text: footNote,
    font: FONT_BODY, size: 19, color: C.brand2,
  })], { spacing: { before: 1200 } }));

  out.push(new Paragraph({ children: [new PageBreak()] }));
  return out;
}

/* ------------------------------------------------- ficha de identificación */

function metaTable(rows) {
  const labelW = Math.round(CONTENT_WIDTH * 0.28);
  const valueW = CONTENT_WIDTH - labelW;
  const border = { style: BorderStyle.SINGLE, size: 4, color: C.brand3 };

  return new Table({
    columnWidths: [labelW, valueW],
    width: { size: CONTENT_WIDTH, type: WidthType.DXA },
    borders: {
      top: border, bottom: border, left: border, right: border,
      insideHorizontal: border, insideVertical: border,
    },
    rows: rows.map(([label, value], i) => new TableRow({
      children: [
        new TableCell({
          width: { size: labelW, type: WidthType.DXA },
          shading: { type: ShadingType.CLEAR, fill: C.surface },
          verticalAlign: VerticalAlign.CENTER,
          margins: { top: 90, bottom: 90, left: 130, right: 130 },
          children: [new Paragraph({
            spacing: { before: 0, after: 0, line: 264, lineRule: 'auto' },
            children: [new TextRun({ text: label, bold: true, size: 21, color: C.brand1 })],
          })],
        }),
        new TableCell({
          width: { size: valueW, type: WidthType.DXA },
          verticalAlign: VerticalAlign.CENTER,
          margins: { top: 90, bottom: 90, left: 130, right: 130 },
          children: [new Paragraph({
            spacing: { before: 0, after: 0, line: 264, lineRule: 'auto' },
            children: inline(value),
          })],
        }),
      ],
    })),
  });
}

function frontMatter({ title, meta, notes }) {
  const out = [];

  out.push(new Paragraph({
    spacing: { after: 200 },
    border: { bottom: { style: BorderStyle.SINGLE, size: 8, space: 6, color: C.brand3 } },
    children: [new TextRun({
      text: title, font: FONT_HEAD, bold: true, size: 34, color: C.brand1,
    })],
  }));

  out.push(metaTable(meta));
  out.push(new Paragraph({ spacing: { after: 200 }, children: [] }));

  for (const n of notes || []) {
    out.push(new Paragraph({ style: 'Callout', children: inline(n) }));
  }

  // Índice: campo TOC real de Word.
  out.push(new Paragraph({
    spacing: { before: 320, after: 160 },
    border: { bottom: { style: BorderStyle.SINGLE, size: 8, space: 6, color: C.brand3 } },
    children: [new TextRun({
      text: 'Contenido', font: FONT_HEAD, bold: true, size: 30, color: C.brand1,
    })],
  }));
  out.push(new Paragraph({
    style: 'SmallCapsMuted',
    children: [new TextRun({
      text: 'Pulse con el botón derecho sobre el índice y elija «Actualizar campos» para generarlo.',
    })],
  }));
  out.push(new TableOfContents('Contenido', { hyperlinks: true, headingStyleRange: '1-3' }));

  out.push(new Paragraph({ children: [new PageBreak()] }));
  return out;
}

/* --------------------------------------------------------------- cuerpo */

// Toma el markdown a partir del encabezado indicado (se descartan la portada
// original, la ficha y el índice manual, que aquí se reconstruyen).
function bodyFrom(mdPath, startHeading) {
  const md = fs.readFileSync(path.join(REPO, mdPath), 'utf8');
  const idx = md.indexOf(startHeading);
  if (idx === -1) throw new Error(`No se encontró "${startHeading}" en ${mdPath}`);
  return renderBlocks(parseBlocks(md.slice(idx)));
}

/* ------------------------------------------------------------ documento */

function buildDoc({ shortName, coverCfg, front, children }) {
  return new Document({
    creator: 'Rafael Figueredo Oropeza',
    title: `${coverCfg.title} — Camino del Dharma`,
    description: coverCfg.subtitle,
    styles,
    numbering,
    sections: [{
      properties: {
        page: {
          size: { width: PAGE.width, height: PAGE.height },
          margin: PAGE.margin,
        },
        titlePage: true,
      },
      headers: {
        first: new Header({ children: [] }),
        default: new Header({
          children: [new Paragraph({
            alignment: AlignmentType.RIGHT,
            spacing: { after: 0 },
            border: { bottom: { style: BorderStyle.SINGLE, size: 4, space: 6, color: C.brand3 } },
            children: [new TextRun({ text: shortName, font: FONT_BODY, size: 17, color: C.brand2 })],
          })],
        }),
      },
      footers: {
        first: new Footer({ children: [] }),
        default: new Footer({
          children: [new Paragraph({
            alignment: AlignmentType.CENTER,
            children: [
              new TextRun({ text: 'Camino del Dharma  |  ', font: FONT_BODY, size: 18, color: C.brand2 }),
              new TextRun({ children: [PageNumber.CURRENT], font: FONT_BODY, size: 18, color: C.brand2 }),
            ],
          })],
        }),
      },
      children: [...cover(coverCfg), ...frontMatter(front), ...children],
    }],
  });
}

/* --------------------------------------------------------------- datos */

const AUTOR = 'Rafael Figueredo Oropeza — [LinkedIn](https://www.linkedin.com/in/rafaelfigueredo/) · <refo44@gmail.com>';

const docs = [
  {
    out: 'docs/informes-seo/00-informe-auditoria-seo.docx',
    shortName: 'Informe de Auditoría SEO',
    coverCfg: {
      kicker: 'Comunidad Buddhista',
      title: 'Informe de Auditoría SEO',
      subtitle: 'caminodeldharma.org',
      tagline: 'El sitio está bien construido. Lo que falta no se corrige desde el código.',
      footNote: '20 de julio de 2026 · actualizado el 21 de julio',
    },
    front: {
      title: 'Informe de Auditoría SEO',
      meta: [
        ['Para', 'Liderazgo de la Comunidad Buddhista Camino del Dharma'],
        ['Autor', AUTOR],
        ['Sitio auditado', 'https://caminodeldharma.org'],
        ['Fecha del informe', '20 de julio de 2026 · **actualizado el 21 de julio**'],
        ['Periodo de auditoría', '19–20 de julio de 2026'],
        ['Mercado evaluado', 'Colombia'],
        ['Próxima medición', 'Entre el 17 de agosto y el 14 de septiembre de 2026'],
      ],
      notes: [],
    },
    source: ['docs/informes-seo/00-informe-auditoria-seo.md', '## 1. En una página'],
  },
  {
    out: 'docs/informes-seo/02-auditoria-seo-tecnica.docx',
    shortName: 'Auditoría SEO Técnica',
    coverCfg: {
      kicker: 'Comunidad Buddhista',
      title: 'Auditoría SEO Técnica',
      subtitle: 'Estado de salud técnica del sitio',
      tagline: 'La salud técnica del sitio es alta y está verificada por dos vías independientes.',
      footNote: '20 de julio de 2026 · actualizado el 21 de julio',
    },
    front: {
      title: 'Auditoría SEO Técnica',
      meta: [
        ['Cliente', 'Comunidad Buddhista Camino del Dharma'],
        ['Autor', AUTOR],
        ['Sitio auditado', 'https://caminodeldharma.org'],
        ['Versión en producción', 'v1.0.19 (`/galeria` pendiente de despliegue)'],
        ['Fecha del informe', '20 de julio de 2026 · **actualizado el 21 de julio**'],
        ['Periodo de auditoría', '19–20 de julio de 2026'],
        ['Destinatario', 'Equipo de publicación web'],
      ],
      notes: [
        '**Alcance:** indexación y rastreo, higiene del índice, rendimiento, datos estructurados, seguridad y protocolo de medición. Cada hallazgo lleva estado, esfuerzo estimado y criterio de aceptación.',
        '**Naturaleza del sitio.** El estático actual es **temporal**: será sustituido por WordPress. Ninguna recomendación de este informe compromete al sitio a plazos largos —por ejemplo, cabeceras de seguridad con vigencia de un año—.',
      ],
    },
    source: ['docs/informes-seo/02-auditoria-seo-tecnica.md', '## 1. Contexto y veredicto'],
  },
  {
    out: 'docs/24-brief-editorial-blog-y-visibilidad.docx',
    shortName: 'Brief — Blog y visibilidad',
    coverCfg: {
      kicker: 'Comunidad Buddhista',
      title: 'Brief editorial',
      subtitle: 'Blog y visibilidad en buscadores',
      tagline: 'La comunicación no vende. La comunicación invita a practicar.',
      footNote: '20 de julio de 2026',
    },
    front: {
      title: 'Brief — Blog y visibilidad en buscadores',
      meta: [
        ['Para', 'Persona encargada de la redacción y edición del blog de Camino del Dharma'],
        ['De', `${AUTOR} — auditoría SEO del sitio (julio 2026)`],
        ['Fecha', '20 de julio de 2026'],
      ],
      notes: [],
    },
    source: ['docs/24-brief-editorial-blog-y-visibilidad.md', '## 1. Resumen'],
  },
];

/* ------------------------------------------------------------- ejecución */

// Sin argumentos genera los tres; con uno, solo los documentos que lo contengan
// en su ruta (p. ej. `node build.js 24`).
const filtro = process.argv[2];

(async () => {
  for (const d of docs.filter((x) => !filtro || x.out.includes(filtro))) {
    const doc = buildDoc({
      shortName: d.shortName,
      coverCfg: d.coverCfg,
      front: d.front,
      children: bodyFrom(...d.source),
    });
    const buf = await Packer.toBuffer(doc);
    const dest = path.join(REPO, d.out);
    fs.writeFileSync(dest, buf);
    console.log(`✓ ${d.out}  (${(buf.length / 1024).toFixed(0)} KB)`);
  }
})();
