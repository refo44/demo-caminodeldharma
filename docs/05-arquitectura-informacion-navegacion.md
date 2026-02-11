# Camino del Dharma — Arquitectura de información y navegación

**Mapa de navegación y enlaces vivos**  
**Versión 1.0**

Define qué enlaces salen de cada pantalla, a dónde van y cuáles no deben existir.

**Referencia:** `04-mapa-pantallas`, `06-wireframes`, `09-ui-copy-sheet`, `10-user-journey`

---

## 1. Principios estructurales

| Tipo de enlace | Función |
|----------------|---------|
| **Primario** | Continuar hacia la práctica o el contacto. Un solo foco claro por contexto. |
| **Secundario** | Contexto o salida ordenada. |
| **Prohibido** | CTAs agresivos, ruido comercial. |

**Regla central:** Cada pantalla debe ofrecer un siguiente paso claro o una salida amable. Nunca un laberinto.

---

## 2. Navegación global

**Regla de cantidad:** Menú de 4 a 6 ítems. Navegación simple e intuitiva.

### Cabecera

| Enlace | Destino |
|--------|---------|
| Inicio | Home |
| La comunidad | Comunidad |
| El linaje | Linaje |
| Práctica | Práctica |
| Eventos | Eventos (si hay evento vigente) o se oculta |
| Galería | Galería |
| Contacto | Contacto |

**Alternativa:** Eventos como ítem condicional; solo visible cuando hay evento vigente.

**Móvil (hamburguesa):** En pantallas pequeñas la navegación global puede colapsarse en un único botón de menú accesible (icon button). Este botón muestra u oculta los mismos enlaces definidos arriba; no se añaden rutas nuevas. Se implementa siguiendo el patrón de `19-accesibilidad-estandares` (icono con `aria-hidden="true"` y texto accesible visible o con `.visually-hidden`, más `aria-expanded`/`aria-controls` para el panel de navegación).

### Pie

| Enlace | Destino |
|--------|---------|
| Contacto | Contacto |
| Pausa Profunda | URL externa (nueva pestaña) |
| Facebook | URL externa |
| Instagram | URL externa |
| Sostener la comunidad / Donar | Sección donaciones o modal |

---

## 3. Inicio

| Enlace | Destino | Tipo |
|--------|---------|------|
| "Practica con nosotros" | Contacto o WhatsApp | Primario |
| "Participar" (meditación) | WhatsApp | Primario |
| "Ver galería completa" | Galería | Secundario |
| La comunidad | Comunidad | Secundario |
| El linaje | Linaje | Secundario |
| Práctica | Práctica | Secundario |

**Nunca:** carruseles, "lo más visto", bloques de marketing.

---

## 4. La comunidad

| Enlace | Destino | Tipo |
|--------|---------|------|
| "Visitar Pausa Profunda" | URL externa (nueva pestaña) | Primario |
| "Practica con nosotros" | Contacto | Secundario |
| Práctica | Práctica | Secundario |

**Nota:** Biografía del fundador con divisor suave; enlace Pausa Profunda con tipografía menor.

---

## 5. El linaje

| Enlace | Destino | Tipo |
|--------|---------|------|
| Práctica | Práctica | Secundario |
| La comunidad | Comunidad | Secundario |

---

## 6. Práctica

| Enlace | Destino | Tipo |
|--------|---------|------|
| "Participar" (meditación) | WhatsApp | Primario |
| Eventos (si hay vigentes) | Eventos | Secundario |
| Contacto | Contacto | Secundario |

---

## 7. Galería

| Enlace | Destino | Tipo |
|--------|---------|------|
| "Volver al inicio" | Inicio | Secundario |
| Menú global | Inicio, Comunidad, Linaje, Práctica, Eventos, Contacto | Secundario |

---

## 8. Eventos (si visible)

| Enlace | Destino | Tipo |
|--------|---------|------|
| "Inscribirme" | URL de inscripción | Primario |
| Práctica | Práctica | Secundario |
| Contacto | Contacto | Secundario |

---

## 9. Contacto

| Enlace | Destino | Tipo |
|--------|---------|------|
| "Enviar" | Confirmación en página | Primario |
| "Volver" | Inicio o Práctica | Secundario |

---

## 10. Estados

### Sin eventos vigentes

- Menú: ocultar "Eventos" o mostrar página con mensaje amable.
- Ejemplo: "No hay eventos programados en este momento."

### 404

| Enlace | Destino |
|--------|---------|
| "Volver al inicio" | Inicio |
| "Explorar la comunidad" | Comunidad |
| "Practicar con nosotros" | Contacto o Práctica |

**Nunca** dejar una pantalla sin salida (alineado con 06-wireframes §8).

---

## 11. Regla final

Si un enlace no empuja hacia:

- La práctica
- El contacto
- La participación
- La comprensión de la comunidad

**no existe.**

---

## Cierre y aplicación

Este documento es la **guía estructural definitiva** de navegación y arquitectura de información del sitio. Se aplica tal como está definido, sin reinterpretaciones.

**Tres capas del sistema:** modelo de contenido (03) → mapa de pantallas (04) → red de enlaces vivos (este documento). Quedan coherentes entre sí.

**Siguiente paso en WordPress:** crear el menú con los ítems definidos; implementar lógica condicional para mostrar/ocultar Eventos según existencia de evento vigente; asignar en cada plantilla los enlaces primarios y secundarios indicados en las secciones 3–9.

---

**Versión:** 1.2
