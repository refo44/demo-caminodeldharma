# Camino del Dharma — Orden de implementación

Secuencia acordada para llevar el sitio a la web. **No saltar etapas.**

---

## Fase 1: Documentación y diseño

1. **Completar identidad:** Extraer paleta y tipografía del PDF `Identidad CAMINO DEL DHARMA- (1).pdf` → actualizar `02-identidad-corporativa.md`
2. **Wireframes (opcional):** Bocetos de pantallas según `04-mapa-pantallas`
3. **Validar documentación:** Revisar que todos los docs estén alineados
4. **Consultar tendencias UX/UI:** `15-tendencias-ux-ui-sistema-editorial` como filtro para decisiones de diseño

---

## Fase 2: Maqueta estática

1. **Maqueta responsiva** con:
   - HTML5 semántico
   - CSS3 (tokens de identidad, roles semánticos)
   - JS mínimo con `defer` (navegación, formularios, accesibilidad)
2. Contenido según: `04-mapa-pantallas`, `05-arquitectura-informacion-navegacion`, `08-ui-copy-sheet`, `02-identidad-corporativa`
3. Assets desde `content-source/` copiados a `public/assets/`
4. **Validar contra checklist** de `15-tendencias-ux-ui-sistema-editorial` (sección 8) antes de dar por cerrada la fase

---

## Fase 3: WordPress

1. **Convertir** la maqueta en theme de WordPress
2. Ajustar a `03-wordpress-content-model` y `10-arbol-urls-final`
3. Implementar CPT `event` si se requieren eventos dinámicos
4. **Subir** al servidor y configurar contenido/hosting

---

## Prioridad de páginas

1. **Inicio** — Hero, meditación semanal, caminos de participación
2. **Contacto** — Formulario + WhatsApp
3. **La comunidad** — Quiénes somos, fundador
4. **Práctica** — Meditación, talleres, retiros
5. **El linaje** — Tradición, Chan, Tierra Pura
6. **Eventos** — Condicional; implementar cuando haya eventos vigentes

---

## Regla

No escribir código de theme WordPress ni subir a servidor final hasta que la maqueta estática esté validada.

---

## Checklist pre-lanzamiento

- [ ] Identidad (paleta, tipografía) definida
- [ ] Todas las páginas maquetadas
- [ ] Formulario de contacto funcional
- [ ] Botón WhatsApp operativo
- [ ] Enlaces externos (Pausa Profunda, redes) verificados
- [ ] Datos bancarios correctos en footer
- [ ] Accesibilidad básica (contraste, alt en imágenes)

---

**Versión:** 1.0
