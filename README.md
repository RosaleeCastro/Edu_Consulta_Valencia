# EduConsulta Valencia

EduConsulta Valencia es una aplicación web orientada a la consulta de centros educativos de la Comunitat Valenciana a partir de datos abiertos oficiales de la Generalitat Valenciana. El proyecto transforma un dataset público en una experiencia de búsqueda más clara, visual y útil para familias, estudiantes, orientadores o cualquier persona que necesite localizar y comparar centros.

La aplicación combina filtrado guiado, resultados enriquecidos, mapa interactivo y ficha detallada por centro, apoyándose en una API interna en PHP que normaliza y organiza la información consumida desde el portal de datos abiertos.

## Qué ofrece

- Búsqueda guiada por `provincia` y `localidad` para evitar errores de escritura.
- Filtros por `régimen` y `tipología / enseñanzas`.
- Resultados en tarjetas con información ampliada.
- Vista mixta con `lista + mapa` usando Leaflet y OpenStreetMap.
- Ficha interna de cada centro con datos institucionales, contacto, código postal y geolocalización.
- Enlace de contraste con la fuente oficial del centro.

## Fuente de datos

La aplicación consume datos abiertos oficiales de la Generalitat Valenciana:

- API base: `https://dadesobertes.gva.es/api/3/action/datastore_search`
- Recurso utilizado: `1aa53c3a-4639-41aa-ac85-d58254c428c0`
- Dataset: Centros docentes de la Comunitat Valenciana

Decisión técnica relevante:

- El proyecto usa `datastore_search` y filtra en PHP.
- Se descartó `datastore_search_sql` porque en este entorno devolvía errores de conexión intermitentes.
- La aplicación incorpora fallback entre `cURL` y `file_get_contents` para robustecer el consumo remoto.

## Funcionalidades principales

### 1. Búsqueda guiada

El usuario selecciona:

- Provincia
- Localidad
- Régimen
- Tipología / enseñanzas

Las localidades se cargan dinámicamente a partir de los datos reales del dataset.

### 2. Resultados enriquecidos

Cada centro puede mostrar:

- Nombre
- Código del centro
- Tipo de centro
- Régimen
- Dirección
- Localidad y provincia
- Código postal
- Teléfono
- Titular
- Comarca

### 3. Vista geográfica

La portada incluye:

- Vista mixta
- Solo lista
- Solo mapa

El mapa está implementado con Leaflet y permite:

- visualizar los centros geolocalizados
- abrir un popup por centro
- navegar a la ficha interna
- resaltar visualmente el marcador cuando el usuario interactúa con la tarjeta correspondiente

### 4. Ficha interna del centro

Cada centro dispone de una página de detalle propia con:

- información general
- datos institucionales
- latitud y longitud
- mapa embebido en la misma página
- enlace para contrastar con la fuente oficial del centro

## Arquitectura del proyecto

```text
Edu_consultas/
├── api/
│   ├── buscar-centros.php
│   ├── detalle-centro.php
│   └── opciones-ubicacion.php
├── app/
│   ├── controllers/
│   │   └── CentroController.php
│   ├── helpers/
│   │   └── respuesta.php
│   └── services/
│       └── CentroEduactivoApiService.php
├── config/
│   └── config.php
├── public/
│   ├── centro.php
│   ├── diagnostico-api.php
│   ├── index.php
│   ├── css/
│   │   └── estilos.css
│   └── js/
│       ├── app.js
│       └── centro.js
└── README.md
```

## Flujo de aplicación

### Búsqueda de centros

1. El frontend envía la búsqueda a `api/buscar-centros.php`.
2. El controlador valida la entrada.
3. El servicio consulta el dataset oficial.
4. El servicio normaliza, filtra y mapea la información.
5. El frontend pinta tarjetas y mapa sincronizados.

### Carga de provincias y localidades

1. `public/js/app.js` consulta `api/opciones-ubicacion.php`.
2. El backend genera un catálogo de provincias y localidades reales.
3. El frontend rellena los selects dependientes.

### Ficha de detalle

1. El usuario entra en `public/centro.php?codigo=...`
2. `public/js/centro.js` solicita `api/detalle-centro.php`
3. El backend busca el centro por código
4. La vista renderiza la ficha completa y el mapa embebido

## Stack técnico

- PHP
- JavaScript vanilla
- HTML5
- CSS3
- Leaflet
- OpenStreetMap
- XAMPP para entorno local

## Requisitos

- XAMPP o entorno compatible con PHP
- Apache en ejecución
- Acceso saliente a internet para consultar la API pública

## Puesta en marcha

### 1. Copiar el proyecto en `htdocs`

Ubicación esperada:

```text
C:\xampp\htdocs\Edu_consultas
```

### 2. Iniciar Apache en XAMPP

### 3. Abrir la aplicación

```text
http://localhost/Edu_consultas/public/
```

## Configuración básica

Archivo:

- [config/config.php](/mnt/c/xampp/htdocs/Edu_consultas/config/config.php:1)

Constantes principales:

- `NOMBRE_PROYECTO`
- `API_BASE_URL`
- `RESOURCE_ID_CENTROS`
- `LIMITE_RESULTADOS`

## Endpoints internos

### `POST /api/buscar-centros.php`

Recibe:

```json
{
  "provincia": "VALENCIA/VALÈNCIA",
  "localidad": "VALENCIA",
  "regimen": "Público",
  "tipo_centro": "secundaria"
}
```

Devuelve:

```json
{
  "ok": true,
  "mensaje": "Consulta realizada correctamente.",
  "total": 12,
  "centros": []
}
```

### `GET /api/opciones-ubicacion.php`

Devuelve el catálogo de provincias y localidades disponible en el dataset.

### `GET /api/detalle-centro.php?codigo=...`

Devuelve la ficha completa de un centro concreto.

## Consideraciones de diseño y UX

- La búsqueda evita texto libre en provincia y localidad para reducir errores.
- La ficha interna tiene prioridad frente a la salida inmediata a la web oficial.
- El mapa se usa como apoyo visual a la exploración, no como reemplazo del listado.
- La fuente oficial sigue disponible como referencia externa, pero no compite con la navegación principal.

## Diagnóstico técnico

El proyecto incluye una utilidad de diagnóstico para revisar conectividad con la API pública:

```text
http://localhost/Edu_consultas/public/diagnostico-api.php
```

Sirve para verificar:

- disponibilidad de `cURL`
- disponibilidad de `allow_url_fopen`
- respuesta de los endpoints remotos
- conectividad HTTPS desde el entorno local

## Mejoras futuras sugeridas

- Clustering de marcadores en el mapa
- Ordenación por cercanía si el usuario comparte ubicación
- Comparador de centros
- Exportación de resultados
- Caché local de respuestas o catálogo de ubicaciones
- Paginación o carga progresiva de resultados

## Estado del proyecto

Proyecto funcional y preparado para seguir evolucionando como base de una herramienta de orientación educativa con datos abiertos.

## Autoría

Proyecto académico desarrollado como práctica de Desarrollo de Aplicaciones Web, evolucionado hacia una experiencia de consulta más cercana a un producto real.
