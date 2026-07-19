# ADR 0012: WordPress como motor de contenido

## Estado

Aceptada

## Fecha

2026-07-19

## Contexto

Camino del Dharma dispone de una implementación estática funcional y optimizada en producción: URLs limpias, SEO técnico, accesibilidad, rendimiento, CI/CD y documentación exhaustiva.

Sin embargo, el sitio será **administrado por terceros** que necesitan crear, editar y publicar contenido **sin modificar archivos HTML**, **sin usar Git** ni **acceder directamente al servidor**.

El proyecto requiere una interfaz de administración accesible para gestionar entradas del blog, eventos, imágenes y otros contenidos editoriales, con roles y permisos diferenciados.

La necesidad principal no es técnica sino **operativa**: permitir mantenimiento editorial autónomo por personas no desarrolladoras.

## Decisión

Se utilizará **WordPress como sistema de gestión de contenidos (CMS)**.

La maqueta estática validada (ADR 0001) será la **referencia visual, estructural y funcional** para desarrollar el theme. La migración a WordPress **no implicará un rediseño** (ADR 0002).

### WordPress aportará

- Administración de contenido desde interfaz gráfica.
- Gestión de usuarios, roles y permisos.
- Publicación de entradas de blog y eventos (CPT).
- Biblioteca de medios para imágenes subidas por administradores.
- Flujos editoriales sin intervención técnica cotidiana.

### El theme mantendrá (invariantes)

- Estructura de bloques y jerarquía visual.
- Arquitectura CSS y tokens de identidad (ADR 0009).
- Rutas definidas (ADR 0008).
- Sistema editorial según `23-sistema-editorial`.
- Criterios de accesibilidad (`19-accesibilidad-estandares`).

### Restricciones

- Los administradores **no** editarán archivos del theme.
- La presentación visual **no** dependerá de constructores de páginas genéricos.
- Los campos editables estarán definidos por el modelo de contenido (`03-wordpress-content-model`).
- Los cambios de **código** se realizan en Git y mediante pipeline de despliegue (ADR 0004, ADR 0005).
- Los cambios **editoriales** se realizan desde WordPress.

## Alternativas consideradas

### Mantener el sitio completamente estático

**Descartada.** Obligaría a los administradores a editar HTML, trabajar con Git o depender de una persona técnica para cada publicación.

### CMS desacoplado o basado en Git (Decap CMS, Markdown en repo)

**Descartada** para este proyecto. Exige flujo editorial más técnico, servicios adicionales o curva de aprendizaje incompatible con el perfil de los administradores previstos.

### Headless WordPress

**Descartada.** Complejidad innecesaria para el alcance; dos sistemas que mantener.

### Constructor visual genérico (Elementor, etc.)

**Descartada.** Compromete consistencia visual, accesibilidad, rendimiento y la arquitectura definida en la maqueta.

## Consecuencias

### Positivas

- Administradores actualizan contenido desde interfaz familiar.
- Roles y permisos configurables.
- Contenido separado de la presentación.
- Blog y eventos gestionables dinámicamente.
- La administración cotidiana no depende de Git ni de conocimientos técnicos.

### Negativas

- Mayor superficie de mantenimiento y seguridad (WordPress, plugins, PHP).
- Actualizaciones periódicas de core y dependencias.
- Copias de seguridad de archivos **y** base de datos.
- Despliegue del theme y gestión del contenido son **procesos separados** (ADR 0013).
- Limitar capacidad de editores para evitar alteraciones del diseño.

### Trabajo futuro

- Iniciar Fase 3 según `17-orden-implementacion` y ADR 0011 / ADR 0014 (estructura `static/` + `wordpress/`).
- Staging WordPress en Hostinger antes del corte de producción.
- Definir roles editoriales mínimos y capacitación breve para administradores.

## Referencias

- `docs/01-plataforma-comunidad-plan`
- `docs/03-wordpress-content-model`
- `docs/17-orden-implementacion` Fase 3
- ADR 0001, ADR 0002, ADR 0011, ADR 0013, ADR 0014
