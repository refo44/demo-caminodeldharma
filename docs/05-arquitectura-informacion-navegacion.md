# Camino del Dharma — Arquitectura de información y navegación

**Mapa de navegación y enlaces vivos**  
**Versión 1.0**

Define qué enlaces salen de cada pantalla, a dónde van y cuáles no deben existir.

**Referencia:** `04-mapa-pantallas`, `08-ui-copy-sheet`, `09-user-journey`

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
| Contacto | Contacto |

**Alternativa:** Eventos como ítem condicional; solo visible cuando hay evento vigente.

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

## 7. Eventos (si visible)

| Enlace | Destino | Tipo |
|--------|---------|------|
| "Inscribirme" | URL de inscripción | Primario |
| Práctica | Práctica | Secundario |
| Contacto | Contacto | Secundario |

---

## 8. Contacto

| Enlace | Destino | Tipo |
|--------|---------|------|
| "Enviar" | Confirmación en página | Primario |
| "Volver" | Inicio o Práctica | Secundario |

---

## 9. Estados

### Sin eventos vigentes

- Menú: ocultar "Eventos" o mostrar página con mensaje amable.
- Ejemplo: "No hay eventos programados en este momento."

### 404

| Enlace | Destino |
|--------|---------|
| "Volver al inicio" | Inicio |
| "Explorar la comunidad" | Comunidad |

**Nunca** dejar una pantalla sin salida.

---

## 10. Regla final

Si un enlace no empuja hacia:

- La práctica
- El contacto
- La participación
- La comprensión de la comunidad

**no existe.**

---

**Versión:** 1.0
