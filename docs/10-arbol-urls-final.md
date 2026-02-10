# Camino del Dharma — Árbol de URLs

**Geografía oficial del sitio**  
**Versión 1.0**

Define todas las rutas del sitio. Traducción directa de `03-wordpress-content-model`, `04-mapa-pantallas`, `05-arquitectura-informacion-navegacion`.

**Si una URL no está aquí, no existe.**

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
| Eventos especiales | `/eventos/` |
| Contacto | `/contacto/` |

---

## 3. Eventos (si se implementa CPT)

| Tipo | URL |
|------|-----|
| Listado | `/eventos/` |
| Single | `/eventos/{slug}/` |

---

## 4. Árbol completo

```
/
/comunidad/
/linaje/
/practica/
/eventos/
/eventos/{slug}/
/contacto/
```

---

## 5. Estados sin URL propia

| Estado | Dónde ocurre |
|--------|--------------|
| Sin eventos vigentes | En /eventos/ o menú oculto |
| 404 | Cualquier URL fuera del árbol |

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
| `/contacto/` | page-contacto.php |

---

## Cierre

Este documento es la **geografía oficial de rutas** del sitio. Si una URL no está aquí, no existe. Está alineado con mapa de pantallas, modelo de contenido y arquitectura de navegación. Puede considerarse estable como referencia técnica para la estructura de URLs en WordPress.

---

**Versión:** 1.1
