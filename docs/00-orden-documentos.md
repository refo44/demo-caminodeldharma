# Orden de los documentos (prefijo numérico)

Los documentos en `docs/` llevan prefijo de dos dígitos (`01-`, `02-`, …) para mantener un orden fijo. El orden sigue la **jerarquía de dependencias**: del "por qué" al "cómo", y de la estrategia a la implementación.

---

## Justificación del orden

| Rango | Criterio | Motivo |
|-------|----------|--------|
| **01–02** | Visión e identidad | La identidad corporativa es la base. Paleta, tipografía y manual de marca definen el criterio visual. |
| **03–05** | Qué existe | Modelo WordPress, mapa de pantallas y arquitectura de información. Definen *qué* contenido y *qué* pantallas existen. |
| **06–08** | Voz y copy | Guía de voz, diccionario de términos, UI copy sheet. El copy depende de identidad; la navegación los usa después. |
| **09** | Experiencia del visitante | User journey. Cómo se recorre el sitio y cómo la persona llega a la práctica. |
| **10–11** | Navegación y URLs | Arquitectura de información/navegación y árbol de URLs. Dependen de pantallas, copy y recorridos. |
| **12–15** | Implementación técnica | Theme, assets, inventario de contenido, orden de implementación. Convierten la arquitectura en código. |

---

## Regla de dependencias

Ningún documento debe depender de uno con número mayor. Las referencias cruzadas apuntan siempre a documentos con prefijo menor o igual.

---

## Lista ordenada (documentos para Camino del Dharma)

1. `02-identidad-corporativa`
2. `03-wordpress-content-model`
4. `04-mapa-pantallas`
5. `05-arquitectura-informacion-navegacion`
6. `06-guia-voz-microcopy-ux`
7. `07-voice-dictionary`
8. `08-ui-copy-sheet`
9. `09-user-journey`
10. `10-arbol-urls-final`
11. `11-theme-file-structure`
12. `12-assets-strategy`
13. `13-content-source-inventario`
14. `14-orden-implementacion`

---

## Fuentes de contenido

Todo el contenido proviene de:

- `content-source/Pagina web Camino del Dharma/Contenido_Web_Camino_del_Dharma.docx`
- `content-source/Pagina web Camino del Dharma/Lluvia de ideas para la página web de la comunidad.docx`
- `content-source/Pagina web Camino del Dharma/Identidad CAMINO DEL DHARMA- (1).pdf` (manual de marca)
- `content-source/Pagina web Camino del Dharma/FOTOS PAGINA WEB/` (imágenes y videos por pestaña)
