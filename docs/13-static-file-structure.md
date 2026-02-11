# Camino del Dharma — Estructura de archivos estáticos

**Geografía del proyecto: repositorio y archivos estáticos**

Define dónde viven los archivos del proyecto que no son generados dinámicamente por WordPress: documentación, contenido fuente, assets del theme y, si aplica, salida de build o maqueta estática.

**Depende de:** `12-theme-file-structure`, `15-assets-strategy`. **Referencia:** `16-content-source-inventario`

---

## 1. Estructura del repositorio (nivel raíz)

```
demo-caminodeldharma/          (o nombre del repo)
├── docs/                      Documentación del proyecto (01–19)
├── content-source/            Fuentes de contenido; no se despliega (16)
│   └── Pagina web Camino del Dharma/
│       ├── Contenido_Web_Camino_del_Dharma.docx | .md
│       ├── Lluvia de ideas… .docx | .md
│       ├── Link-videos-youtube.md | Link videos subidos en Youtube.docx
│       ├── Identidad CAMINO DEL DHARMA- (1).pdf
│       └── FOTOS PAGINA WEB/   Imágenes y videos por pestaña
└── theme-camino-del-dharma/   Theme WordPress (12)
    ├── style.css
    ├── theme.json
    ├── functions.php
    ├── (plantillas PHP)
    └── assets/                Archivos estáticos del theme (13)
        ├── css/
        ├── js/
        ├── fonts/
        ├── icons/
        ├── images/
        └── favicon/
```
El nombre del repo (`demo-caminodeldharma`) no forma parte de la arquitectura; es solo referencia.

---

## 2. Reglas

| Ubicación | Regla |
|-----------|--------|
| **docs/** | Solo Markdown (y recursos referenciados). Orden por prefijo numérico (00–19). No se despliega al sitio. |
| **content-source/** | Solo en local. No se enlaza desde el código. Assets para el sitio se copian al theme o a `public/` según 13 y 15. Puede ignorarse en producción (deploy), pero debe mantenerse versionado en el repo para trazabilidad editorial. |
| **theme-camino-del-dharma/** | Theme WordPress. Los archivos estáticos (CSS, JS, imágenes, fuentes) viven en `assets/` dentro del theme. WordPress los sirve desde la URL del theme. Solo los assets dentro de `theme-camino-del-dharma/assets/` son accesibles públicamente desde el sitio. |
| **Maqueta estática (si existe)** | Si se usa carpeta `public/` o `dist/` para HTML/CSS previo al theme, no sustituye la estructura del theme; se alinea con 12 y 13. Rutas estáticas: `/`, `/comunidad/`, `/linaje/`, `/practica/`, `/eventos/`, `/galeria/`, `/contacto/`, `404`. Es opcional y no obligatoria para este proyecto. |

**Versionado:** `content-source/` se versiona (trazabilidad editorial). Archivos temporales de build (`public/`, `dist/` si son salida de build) no deben versionarse.

---

## 3. Flujo de assets estáticos

1. **Contenido y referencia:** `content-source/` (inventario en 16).
2. **Decisión de uso:** 15-assets-strategy (qué existe, formatos, tamaños).
3. **Estructura técnica:** 12-theme-file-structure (dónde va cada tipo de archivo en el theme).
4. **Copia/migración:** Assets necesarios se copian al theme (p. ej. `assets/images/`, `assets/fonts/`) o se referencian por URL (YouTube). Nunca enlazar a `content-source/` desde el sitio. Los assets copiados al theme deben estar optimizados (peso, formato web).

---

## 4. Qué no es “archivo estático” aquí

- Plantillas PHP (son parte del theme; 12).
- Contenido generado por WordPress (páginas, posts, CPT) desde la base de datos.
- Plugins (fuera del alcance de este documento).

Este documento se limita a la **geografía de archivos estáticos** del proyecto (docs, content-source, assets del theme).

---

## Cierre

Este documento define la **geografía oficial** del repositorio: dónde viven docs, content-source y assets del theme. No define plantillas PHP ni contenido dinámico (12); tampoco formatos o tamaños de assets (15). Alineado con 12 (theme), 15 (estrategia de assets) y 16 (inventario de contenido fuente).

---

**Versión:** 1.2
