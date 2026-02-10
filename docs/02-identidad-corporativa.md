# Camino del Dharma — Identidad corporativa

**Versión 2.1**

Este documento define el sistema de identidad visual y tipográfica del sitio web de la Comunidad Buddhista Camino del Dharma. Los valores provienen estrictamente del manual de marca.

**Fuente canónica:** `content-source/Pagina web Camino del Dharma/Identidad CAMINO DEL DHARMA- (1).pdf`

---

## 1. Paleta cromática

Solo existen cuatro colores definidos en el manual de marca. Estos cuatro colores constituyen el núcleo cromático del sistema. No deben añadirse colores principales adicionales.

| Nombre | Hex | RGB |
|--------|-----|-----|
| Rojo oscuro | #8c2b3d | R: 140, G: 43, B: 41 |
| Rosa polvo | #b27474 | R: 178, G: 116, B: 116 |
| Mauve claro | #d1aeab | R: 209, G: 174, B: 171 |
| Gris pizarra | #3e424b | R: 62, G: 66, B: 75 |

### 1.1 Orden semántico (para implementación)

| Token | Hex | Uso sugerido |
|-------|-----|--------------|
| `--brand-1` | #8c2b3d | Acento principal, enlaces, CTAs |
| `--brand-2` | #b27474 | Acento secundario, hover |
| `--brand-3` | #d1aeab | Superficies, fondos suaves, bordes |
| `--brand-4` | #3e424b | Texto, cabecera, pie |

### 1.2 Roles semánticos (CSS)

```css
:root {
  --brand-1: #8c2b3d;   /* Rojo oscuro - acento principal */
  --brand-2: #b27474;   /* Rosa polvo - acento secundario */
  --brand-3: #d1aeab;   /* Mauve claro - superficies, bordes */
  --brand-4: #3e424b;   /* Gris pizarra - texto, header, footer */

  --bg: #ffffff;        /* Fondo base por defecto; puede alternar con --brand-3 en superficies suaves */
  --text: var(--brand-4);
  --text-muted: var(--brand-2);
  --surface: var(--brand-3);
  --border: var(--brand-3);
  --link: var(--brand-1);
  --link-hover: var(--brand-2);
  --primary: var(--brand-1);
  --primary-hover: var(--brand-2);
  --header-bg: var(--brand-4);
  --footer-bg: var(--brand-4);
}
```

**Nota:** Verificar contraste AA para accesibilidad. El gris pizarra (#3e424b) sobre blanco cumple AA. Verificar contraste AA en combinaciones críticas (texto sobre fondos brand-3 y brand-2).

---

## 2. Sistema tipográfico

Solo dos familias definidas en el manual de marca.

| Familia | Uso en manual |
|---------|---------------|
| **MarloweEscapade** | "dharma" (elemento de logo) |
| **Downtown DEMO Regular** | "dharma" (elemento de logo) |

### 2.1 Aplicación en web

- **MarloweEscapade:** Títulos, voz de marca, elementos destacados.
- **Downtown DEMO Regular:** Alternativa o complemento para títulos; según jerarquía del logo.

**Nota:** Estas son fuentes de display del logo. Para cuerpo de texto y navegación, el manual no especifica; usar una fuente web legible y sobria que armonice (p. ej. una sans-serif neutra para cuerpo, manteniendo MarloweEscapade o Downtown para títulos si están disponibles para web). Si las fuentes del logo no están disponibles para web, priorizar legibilidad y coherencia con la paleta.

### 2.2 Variables CSS

```css
:root {
  --font-display: "MarloweEscapade", serif;
  --font-heading: "Downtown DEMO Regular", "MarloweEscapade", serif;
  --font-body: /* Sans-serif neutra definida en implementación (p. ej. Inter, Source Sans, system-ui) */;
}
```

---

## 3. Logo

- **Archivo:** `content-source/Pagina web Camino del Dharma/FOTOS PAGINA WEB/logo 1.png`
- **Favicon:** Derivar del logo.
- **Uso:** Cabecera, favicon, materiales digitales.
- **Regla:** No alterar proporciones, color ni composición del logo.

---

## 4. Esencia de marca (Contenido_Web)

Camino del Dharma no es una página comercial ni informativa. Es un espacio de acogida.

| Rasgo | Descripción |
|-------|-------------|
| Voz | Sobria, acogedora, clara |
| Estética | Calma, amplios espacios en blanco |
| Ritmo | Pausado, respirado |
| Función | Orientar, inspirar confianza, facilitar primer contacto con la práctica buddhista |

El sitio debe transmitir **calma, claridad y coherencia**.

---

## 5. Ritmo editorial

### Texto corriente

- Medida: ~65ch (ancho humano)
- Line height: 1.5–1.6
- Espacio: amplios márgenes, respiración

### Navegación y UI

- Discretos, sin dominar el contenido
- Sin CTAs agresivos

---

## 6. Gramática de layout

| Regla | Significado |
|-------|-------------|
| Una columna / layout simple | Lectura vertical |
| Espacio | Respiración, calma |
| Sin grids densos | No revista ni e-commerce |
| Flujo claro | Guiar hacia la práctica |

---

## 7. Estado del sistema

| Capa | Estado |
|------|--------|
| Paleta | Definida (4 colores) |
| Tipografía | Definida (MarloweEscapade, Downtown DEMO Regular) |
| Logo | Disponible |
| Manual de marca | PDF canónico |

---

## 8. Regla final

Nada visual se decide fuera de este sistema. Los colores y fuentes del PDF son la fuente de verdad. Si hay conflicto con otro documento visual, prevalece el manual de marca (PDF).

---

**Versión del documento:** 2.1  
**Fuente:** Identidad CAMINO DEL DHARMA- (1).pdf
