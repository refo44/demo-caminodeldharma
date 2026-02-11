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
| Eventos especiales | La ruta `/eventos/` **existe siempre**. Si no hay eventos vigentes → mensaje amable; si hay eventos → listado. El ítem en menú se muestra u oculta según haya evento vigente. |

---

## Vistas de contenido (single)

| Vista | Uso |
|-------|-----|
| Evento individual | Detalle de evento (si se implementa single-event) |

---

## Estados

| Estado | Descripción |
|--------|-------------|
| Sin eventos vigentes | En `/eventos/`: mensaje amable (la página existe; el contenido varía) |
| 404 | Página no existe |

---

## Elementos globales (componentes, no pantallas)

**Header:** logo + navegación (menú principal). Presente en todas las páginas.

**Footer:** presente en todas las páginas. Contenido: Comunidad Buddhista Camino del Dharma, Personería Jurídica Especial – Ministerio del Interior de Colombia, Contacto | Redes sociales, Pausa Profunda (proyecto vinculado), información de donaciones, WhatsApp, correo.

---

## Conjunto total de pantallas a construir

**Páginas principales (estáticas):** 1. Inicio · 2. La comunidad · 3. El linaje · 4. Práctica y actividades · 5. Contacto

**Página condicional:** 6. Eventos especiales — la ruta `/eventos/` existe siempre; contenido: listado si hay eventos vigentes, mensaje amable si no.

**Vistas de contenido:** 7. Evento individual — solo si se habilita vista detalle por evento.

**Estados:** Sin eventos vigentes (en `/eventos/`, mensaje amable) · 404

**Elementos globales (componentes):** Header (logo + navegación), Footer (identidad, contacto, redes, donaciones, WhatsApp, Pausa Profunda). No son pantallas; se repiten en todas las vistas.

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
| Header global | `parts/header.php` (incluye navigation) |
| Footer global | `parts/footer.php` |

---

## Lo que el sitio NO tiene

- No blog
- No buscador
- No área privada
- No registro de usuarios
- No sistema de cursos

Ayuda a acotar el alcance y evitar scope creep.

---

## Regla clave del sistema

La única lógica dinámica del sitio:

- **Ruta `/eventos/`:** existe siempre. Si hay al menos un evento vigente → listado (y ítem en menú). Si no hay → mensaje amable en la misma ruta (ítem en menú oculto o neutro).
- Todo lo demás es contenido estable. No hay jerarquías profundas ni flujos de marketing.

---

## Siguiente paso (orden sugerido)

1. Definir estructura del menú principal (`05-arquitectura-informacion-navegacion`).
2. Bajar a estructura real del theme (plantillas + naming en `12-theme-file-structure`).
3. Implementar lógica de visibilidad de «Eventos especiales» en WordPress (query por `event_status = vigente`).

---

**Versión:** 2.0
