// Sistema visual Camino del Dharma para documentos Word.
// Fuente: docs/02-identidad-corporativa.md (paleta de 4 colores del manual de marca)
// Referencia de estilo y estructura: docs/21-manual-voz-copywriting-editorial.docx

const {
  AlignmentType, BorderStyle, HeadingLevel, LevelFormat,
} = require('docx');

// Paleta del manual de marca. No se añaden colores principales.
const C = {
  brand1: '8C2B3D', // rojo oscuro  — acento principal
  brand2: 'B27474', // rosa polvo   — acento secundario
  brand3: 'D1AEAB', // mauve claro  — superficies, bordes
  brand4: '3E424B', // gris pizarra — texto
  surface: 'F6F0EF', // superficie derivada (mauve al 25%), como en el manual de voz
  white: 'FFFFFF',
};

const FONT_HEAD = 'Georgia';
const FONT_BODY = 'Aptos';
const FONT_MONO = 'Consolas';

// A4, con los márgenes del documento de referencia.
const PAGE = {
  width: 11909,
  height: 16834,
  margin: { top: 1224, right: 1368, bottom: 1224, left: 1368 },
};
const CONTENT_WIDTH = PAGE.width - PAGE.margin.left - PAGE.margin.right; // 9173 dxa

const rule = (color, size = 6, space = 4) => ({
  bottom: { style: BorderStyle.SINGLE, size, space, color },
});

const styles = {
  default: {
    document: {
      run: { font: FONT_BODY, size: 22, color: C.brand4 },
      paragraph: { spacing: { after: 160, line: 300, lineRule: 'auto' } },
    },
    heading1: {
      run: { font: FONT_HEAD, bold: true, size: 34, color: C.brand1 },
      paragraph: {
        spacing: { before: 400, after: 160 },
        border: rule(C.brand3, 6, 4),
        keepNext: true,
        keepLines: true,
      },
    },
    heading2: {
      run: { font: FONT_HEAD, bold: true, size: 27, color: C.brand1 },
      paragraph: { spacing: { before: 280, after: 120 }, keepNext: true, keepLines: true },
    },
    heading3: {
      run: { font: FONT_BODY, bold: true, size: 23, color: C.brand2 },
      paragraph: { spacing: { before: 220, after: 80 }, keepNext: true, keepLines: true },
    },
    heading4: {
      run: { font: FONT_BODY, bold: true, italics: true, size: 22, color: C.brand4 },
      paragraph: { spacing: { before: 180, after: 60 }, keepNext: true },
    },
  },
  paragraphStyles: [
    {
      id: 'Lead',
      name: 'Lead',
      basedOn: 'Normal',
      quickFormat: true,
      run: { font: FONT_BODY, size: 23, color: C.brand4 },
      paragraph: { spacing: { after: 200, line: 324, lineRule: 'auto' } },
    },
    {
      id: 'SmallCapsMuted',
      name: 'SmallCapsMuted',
      basedOn: 'Normal',
      run: { font: FONT_BODY, size: 19, color: C.brand2 },
      paragraph: { spacing: { after: 120 } },
    },
    {
      id: 'Callout',
      name: 'Callout',
      basedOn: 'Normal',
      run: { font: FONT_BODY, size: 21, color: C.brand4 },
      paragraph: {
        spacing: { before: 160, after: 200, line: 288, lineRule: 'auto' },
        indent: { left: 340, right: 200 },
        border: {
          left: { style: BorderStyle.SINGLE, size: 18, space: 14, color: C.brand1 },
        },
        shading: { type: 'clear', fill: C.surface },
      },
    },
    {
      id: 'CodeBlock',
      name: 'CodeBlock',
      basedOn: 'Normal',
      run: { font: FONT_MONO, size: 18, color: C.brand4 },
      paragraph: {
        spacing: { after: 0, line: 260, lineRule: 'auto' },
        indent: { left: 200 },
        shading: { type: 'clear', fill: C.surface },
      },
    },
    {
      id: 'Closing',
      name: 'Closing',
      basedOn: 'Normal',
      run: { font: FONT_BODY, size: 20, italics: true, color: C.brand2 },
      paragraph: {
        spacing: { before: 320, after: 120, line: 288, lineRule: 'auto' },
        border: { top: { style: BorderStyle.SINGLE, size: 6, space: 10, color: C.brand3 } },
      },
    },
  ],
};

// Viñetas y listas numeradas: nunca se escribe el carácter "•" a mano.
const numbering = {
  config: [
    {
      reference: 'vinetas',
      levels: [
        {
          level: 0,
          format: LevelFormat.BULLET,
          text: '•',
          alignment: AlignmentType.LEFT,
          style: {
            paragraph: { indent: { left: 460, hanging: 240 }, spacing: { after: 60 } },
            run: { color: C.brand2 },
          },
        },
        {
          level: 1,
          format: LevelFormat.BULLET,
          text: '–',
          alignment: AlignmentType.LEFT,
          style: {
            paragraph: { indent: { left: 900, hanging: 240 }, spacing: { after: 60 } },
            run: { color: C.brand2 },
          },
        },
      ],
    },
    {
      reference: 'numeros',
      levels: [
        {
          level: 0,
          format: LevelFormat.DECIMAL,
          text: '%1.',
          alignment: AlignmentType.START,
          style: {
            paragraph: { indent: { left: 460, hanging: 240 }, spacing: { after: 60 } },
            run: { color: C.brand1, bold: true },
          },
        },
      ],
    },
  ],
};

module.exports = {
  C, FONT_HEAD, FONT_BODY, FONT_MONO, PAGE, CONTENT_WIDTH, styles, numbering, rule, HeadingLevel,
};
