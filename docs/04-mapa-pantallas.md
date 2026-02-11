# Camino del Dharma — Mapa de pantallas

Lista de qué pantallas existen. No describe diseño; solo qué vistas hay que construir. Fuente única para «qué vistas construir»; el contenido de cada una está en 03; la estructura del theme en 12.

**Depende de / referencia:** `01-plataforma-comunidad-plan`, `03-wordpress-content-model`, `05-arquitectura-informacion-navegacion`. **Estructura de bloques por pantalla:** `06-wireframes`

---

## Páginas fijas

| Página | Función |
|--------|---------|
| Inicio | Hero, comunidad, cómo practicamos, meditación semanal, caminos de participación |
| La comunidad | Quiénes somos, fundador, experiencia y propósito |
| El linaje | Tradición viva, Mahāyāna, Chan y Tierra Pura |
| Práctica y actividades | Meditación semanal, talleres, retiros, vida comunitaria |
| Contacto | Formulario Nombre, Correo, Mensaje |

---

## Página condicional

| Página | Condición |
|--------|------------|
| Eventos especiales | **Visible solo cuando hay evento vigente** |

---

## Vistas de contenido (single)

| Vista | Uso |
|-------|-----|
| Evento individual | Detalle de evento (si se implementa single-event) |

---

## Estados

| Estado | Descripción |
|--------|-------------|
| Sin eventos vigentes | La página de Eventos oculta o muestra mensaje amable |
| Sin resultados | Búsqueda o filtros (si aplica) |
| 404 | Página no existe |

---

## Footer (en todas las páginas)

- Comunidad Buddhista Camino del Dharma
- Personería Jurídica Especial – Ministerio del Interior de Colombia
- Contacto | Redes sociales
- Pausa Profunda (proyecto vinculado)
- Información de donaciones
- WhatsApp, correo

---

## Conjunto total de pantallas a construir

**Páginas principales (estáticas):** 1. Inicio · 2. La comunidad · 3. El linaje · 4. Práctica y actividades · 5. Contacto

**Página condicional:** 6. Eventos especiales — solo se muestra cuando existe al menos un evento vigente.

**Vistas de contenido:** 7. Evento individual — solo si se habilita vista detalle por evento.

**Estados:** Sin eventos vigentes (la página de Eventos oculta o mensaje amable) · Sin resultados (búsqueda/filtros si se agregan) · 404

**Elemento global:** Footer en todas las páginas (identidad, contacto, redes, donaciones, WhatsApp, Pausa Profunda).

---

## Traducción a WordPress (plantillas)

| Vista | Plantilla |
|-------|-----------|
| Inicio | `front-page.php` |
| La comunidad | `page-comunidad.php` |
| El linaje | `page-linaje.php` |
| Práctica y actividades | `page-practica.php` |
| Contacto | `page-contacto.php` |
| Eventos especiales | `archive-event.php` o `page-eventos.php` (condicional) |
| Evento individual | `single-event.php` (si se activa) |
| 404 | `404.php` |
| Footer global | `parts/footer.php` o `footer.php` |

---

## Regla clave del sistema

La única lógica dinámica del sitio:

- **Si existe al menos un evento con estado vigente** → se muestra la página Eventos especiales (y el ítem en menú).
- **Si no existe** → se oculta del menú o se reemplaza por un mensaje neutro en la ruta de eventos.

Todo lo demás es contenido estable. No hay jerarquías profundas ni flujos de marketing.

---

## Siguiente paso (orden sugerido)

1. Definir estructura del menú principal (`05-arquitectura-informacion-navegacion`).
2. Bajar a estructura real del theme (plantillas + naming en `12-theme-file-structure`).
3. Implementar lógica de visibilidad de «Eventos especiales» en WordPress (query por `event_status = vigente`).

---

**Versión:** 2.0
