# Camino del Dharma — Theme File Structure

**Estructura de archivos del theme WordPress**  
**Versión 1.1**

Define la arquitectura de archivos del theme: qué plantillas existen y qué partes se reutilizan.

**Depende de:** `03-wordpress-content-model`, `04-mapa-pantallas`, `10-arbol-urls-final`, `05-arquitectura-informacion-navegacion`

---

## 1. Plantillas por función

| Función | Plantilla |
|---------|-----------|
| Home | `front-page.php` |
| La comunidad | `page-comunidad.php` |
| El linaje | `page-linaje.php` |
| Práctica | `page-practica.php` |
| Eventos | `page-eventos.php` o `archive-event.php` |
| Contacto | `page-contacto.php` |
| Fallback | `page.php` |

---

## 2. Event (si se implementa CPT)

| Vista | Plantilla |
|-------|-----------|
| Listado | `archive-event.php` |
| Single | `single-event.php` |

---

## 3. Estados

| Estado | Archivo |
|--------|---------|
| No existe | `404.php` |
| Sin eventos | Dentro de `page-eventos.php` |

---

## 4. Partes reutilizables

| Archivo | Función |
|---------|---------|
| `parts/header.php` | Cabecera, logo, navegación |
| `parts/footer.php` | Pie, contacto, redes, donaciones |
| `parts/navigation.php` | Menú principal |
| `parts/meditation-block.php` | Bloque meditación semanal (reutilizable en Inicio y Práctica) |

---

## 5. Árbol del theme

```
theme-camino-del-dharma/
├── style.css
├── functions.php
├── index.php
├── front-page.php
├── page-comunidad.php
├── page-linaje.php
├── page-practica.php
├── page-eventos.php
├── page-contacto.php
├── page.php
├── single-event.php      (si CPT event)
├── archive-event.php      (si CPT event)
├── 404.php
└── parts/
    ├── header.php
    ├── footer.php
    ├── navigation.php
    └── meditation-block.php
```

---

## 6. URL → plantilla

| Ruta | Archivo |
|------|---------|
| `/` | `front-page.php` |
| `/comunidad/` | `page-comunidad.php` |
| `/linaje/` | `page-linaje.php` |
| `/practica/` | `page-practica.php` |
| `/eventos/` | `page-eventos.php` o `archive-event.php` |
| `/eventos/{slug}/` | `single-event.php` (si CPT event) |
| `/contacto/` | `page-contacto.php` |
| Cualquier otra | `404.php` |

---

## 7. theme.json

- Paleta: solo colores de `02-identidad-corporativa`
- Tipografías: según manual de marca
- Sin bloques de animación en área de lectura

---

## Cierre

Este documento define la **estructura oficial de archivos del theme**: plantillas, partes reutilizables y mapeo URL → plantilla. Está alineado con el árbol de URLs (10), mapa de pantallas (04), modelo de contenido (03) y arquitectura de navegación (05).

---

**Versión:** 1.1
