# Camino del Dharma — Árbol de URLs

**Geografía oficial del sitio**

Define todas las rutas del sitio. Traducción directa de 03 (modelo de contenido), 04 (mapa de pantallas) y 05 (arquitectura de navegación). **Si una URL no está aquí, no existe.**

**Depende de:** `03-wordpress-content-model`, `04-mapa-pantallas`, `05-arquitectura-informacion-navegacion`. **Referencia:** `12-theme-file-structure`

---

## 1. Regla base

Idioma: español (Colombia). Sin prefijo de idioma por defecto. Si se añade multilingualismo más adelante, usar `/es/` como prefijo.

---

## 2. Páginas fijas

| Función | Slug |
|---------|------|
| Inicio | `/` |
| La comunidad | `/comunidad/` |
| El linaje | `/linaje/` |
| Práctica y actividades | `/practica/` |
| Eventos especiales | `/eventos/` *(la ruta existe siempre; contenido: listado o mensaje amable; ítem en menú condicional)* |
| Galería | `/galeria/` |
| Contribuir (donaciones) | `/donaciones/` |
| Contacto | `/contacto/` |
| Blog | `/blog/` |

---

## 3. Eventos (CPT)

| Tipo | URL |
|------|-----|
| Listado | `/eventos/` |
| Single | `/eventos/{slug}/` |

### 3.1. Sanghas (si se implementa CPT)

| Tipo | URL |
|------|-----|
| Listado | `/sanghas/` |
| Single | `/sanghas/{slug}/` |

---

## 4. Árbol completo

```
/
/comunidad/
/linaje/
/practica/
/practica/videos/          (secundaria: no en navbar; acceso desde Práctica «Ver más videos»)
/eventos/
/eventos/{slug}/
/galeria/
/donaciones/
/contacto/
/blog/
/blog/{slug}/
```
*(Si se implementa CPT sangha: `/sanghas/`, `/sanghas/{slug}/`.)*

---

## 5. Estados sin URL propia

| Estado | Dónde ocurre |
|--------|--------------|
| Sin eventos vigentes | En `/eventos/` se muestra mensaje amable; el ítem Eventos en el menú puede ocultarse. |
| 404 | Cualquier URL fuera del árbol. No existe ruta pública `/404/`; WordPress sirve la plantilla `404.php` para rutas no definidas aquí. Referencia interna de diseño: estado 404, no URL del árbol. |

---

## 6. URL → plantilla

| Ruta | Plantilla |
|------|-----------|
| `/` | front-page.php |
| `/comunidad/` | page-comunidad.php |
| `/linaje/` | page-linaje.php |
| `/practica/` | page-practica.php |
| `/eventos/` | page-eventos.php o archive-event.php |
| `/eventos/{slug}/` | single-event.php |
| `/galeria/` | page-galeria.php |
| `/contacto/` | page-contacto.php |

*(Si se implementa CPT sangha: `/sanghas/` → archive-sangha.php; `/sanghas/{slug}/` → single-sangha.php.)*

---

## Cierre

Este documento es la **geografía oficial de rutas** del sitio. Si una URL no está aquí, no existe. Alineado con 03 (modelo de contenido), 04 (mapa de pantallas), 05 (navegación) y 12 (plantillas). Referencia técnica estable para la estructura de URLs en WordPress.

---

**Versión:** 1.3
