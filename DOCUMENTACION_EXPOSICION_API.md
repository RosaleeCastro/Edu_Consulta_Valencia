# Documentación Para La Exposición Del Proyecto Con API Pública

## Datos básicos de la presentación

- Proyecto: `EduConsulta Valencia`
- Tipo de exposición: proyecto con integración de API pública externa
- Duración objetivo: `8-10 minutos`
- API utilizada: portal de datos abiertos de la Generalitat Valenciana
- Recurso principal consumido: centros docentes de la Comunitat Valenciana

---

## Objetivo de este documento

Este documento está pensado para ayudarte a preparar la exposición en clase y transformarlo fácilmente en una presentación visual. Incluye:

- estructura recomendada por diapositivas
- guion oral resumido
- qué capturas debes incluir
- qué fragmentos de código conviene enseñar
- dónde colocar imágenes y demos

Puedes usar este contenido para montar la presentación en PowerPoint, Google Slides o Canva.

---

# Estructura recomendada de la presentación

## Diapositiva 1. Portada

### Título sugerido

`EduConsulta Valencia: explotación de datos abiertos educativos mediante una API pública`

### Subtítulo sugerido

`Buscador de centros educativos de la Comunitat Valenciana con valor añadido sobre datos oficiales`

### Qué poner en la diapositiva

- Nombre del proyecto
- Tu nombre
- Curso / módulo
- Fecha

### Captura recomendada

`[INSERTAR IMAGEN 01: captura general de la portada de la aplicación]`

### Qué decir

Presento una aplicación web llamada EduConsulta Valencia. El proyecto consume una API pública externa con información oficial de centros educativos de la Comunitat Valenciana y no se limita a mostrar los datos, sino que los reorganiza y los convierte en una herramienta de consulta más útil.

---

## Diapositiva 2. Contexto del servicio

### Título sugerido

`Contexto del servicio y proveedor`

### Qué poner en la diapositiva

- Servicio ofrecido por: `Generalitat Valenciana`
- Canal: `portal de datos abiertos`
- Tipo de información: `centros docentes oficiales`
- Formato de acceso: `API CKAN + recurso público`

### Contenido sugerido

La información procede del portal de datos abiertos de la Generalitat Valenciana. Este servicio publica datasets públicos reutilizables y, en este caso, nos permite consultar centros docentes con datos como nombre, tipo, régimen, dirección, localidad, provincia, teléfono, coordenadas, comarca o enlace oficial.

### Captura recomendada

`[INSERTAR IMAGEN 02: captura del dataset o de la página oficial del recurso en dadesobertes.gva.es]`

### Qué decir

El servicio no lo ofrece una empresa privada ni una API comercial, sino una administración pública. Eso es importante porque convierte el proyecto en un caso real de reutilización de datos abiertos con utilidad social.

---

## Diapositiva 3. ¿Por qué este servicio?

### Título sugerido

`¿Por qué elegí esta API?`

### Qué poner en la diapositiva

- Fuente oficial y fiable
- Datos con utilidad real
- Información geográfica y educativa
- Posibilidad de añadir valor sobre la API original
- Encaje con una futura plataforma educativa o TFG

### Qué decir

Elegí este servicio por tres motivos. Primero, porque ofrece datos oficiales y actualizados. Segundo, porque la información es útil para familias, alumnado y orientación educativa. Y tercero, porque permitía crear valor añadido real: filtros guiados, geolocalización, una ficha interna del centro y una experiencia de búsqueda mucho más usable que la consulta cruda del dataset.

---

## Diapositiva 4. Qué problema resuelve el proyecto

### Título sugerido

`Problema detectado y propuesta de valor`

### Qué poner en la diapositiva

#### Problema

- La información oficial existe, pero no siempre es cómoda de consultar
- Puede haber errores si el usuario introduce localidades manualmente
- El dataset original no está orientado a una experiencia de usuario final

#### Solución

- Búsqueda guiada por provincia y localidad
- Tipología de centro
- Vista lista + mapa
- Ficha interna más clara
- Enlace de contraste con la fuente oficial

### Captura recomendada

`[INSERTAR IMAGEN 03: captura de la búsqueda guiada con provincia, localidad y filtros]`

### Qué decir

Mi proyecto no se limita a consumir la API. La transforma. El valor añadido está en cómo organizo los datos, cómo reduzco errores de búsqueda y cómo convierto un dataset técnico en una herramienta usable.

---

## Diapositiva 5. Especificación de la API

### Título sugerido

`Especificación esencial de la API`

### Qué poner en la diapositiva

- Base de acceso utilizada:
  - `https://dadesobertes.gva.es/api/3/action/datastore_search`
- Recurso utilizado:
  - `resource_id = 1aa53c3a-4639-41aa-ac85-d58254c428c0`
- Tipo de respuesta:
  - `JSON`
- Método:
  - `GET`

### Campos más relevantes que utiliza el proyecto

- `codigo`
- `denominacion`
- `denominacion_generica_es`
- `regimen`
- `direccion`
- `codigo_postal`
- `localidad`
- `provincia`
- `telefono`
- `latitud`
- `longitud`
- `titular`
- `comarca`
- `url_es`

### Captura recomendada

`[INSERTAR IMAGEN 04: captura de la documentación o ejemplo JSON del endpoint datastore_search]`

### Qué decir

La API trabaja sobre CKAN y devuelve JSON estructurado. Yo utilizo el endpoint `datastore_search` con un `resource_id` concreto. A partir de ahí recupero el conjunto de registros y filtro la información desde mi backend.

---

## Diapositiva 6. Qué partes de la API utilizo y cuáles no

### Título sugerido

`Qué consumo y qué ofrece adicionalmente`

### Qué pongo en la diapositiva

#### Lo que utilizo

- Consulta del recurso de centros docentes
- Extracción de registros
- Campos descriptivos, institucionales y geográficos

#### Lo que ofrece la API pero no uso directamente

- Otros datasets del portal
- Posibles descargas en otros formatos
- Otros recursos del ecosistema CKAN
- Consulta avanzada SQL del datastore

### Nota técnica importante

En este proyecto se descartó `datastore_search_sql` porque en este entorno local daba errores de conexión, mientras que `datastore_search` funcionaba de forma estable.

### Qué decir

Esto demuestra que la integración no es solo “leer la documentación y pegar una URL”, sino tomar decisiones técnicas reales para que el proyecto sea estable.

---

## Diapositiva 7. Arquitectura de integración

### Título sugerido

`Cómo incorporo la API al proyecto`

### Qué poner en la diapositiva

```text
Frontend (HTML/CSS/JS)
        ↓
API interna en PHP
        ↓
Servicio de integración
        ↓
API pública de la Generalitat Valenciana
```

### Explicación

- El frontend nunca consume directamente la API pública
- PHP actúa como capa intermedia
- El servicio normaliza, filtra y reorganiza los datos
- La app añade una capa propia de experiencia y estructura

### Captura recomendada

`[INSERTAR IMAGEN 05: esquema visual del flujo frontend -> PHP -> API pública]`

### Qué decir

No conecto el navegador directamente contra la API externa. Uso una API interna en PHP porque así puedo controlar validaciones, filtrar, transformar datos y construir una lógica propia.

---

## Diapositiva 8. Código clave: punto de entrada

### Título sugerido

`Código clave 1: endpoint interno`

### Archivo recomendado para enseñar

- `api/buscar-centros.php`

### Qué captura debes poner

`[INSERTAR IMAGEN 06: captura de buscar-centros.php mostrando recepción de POST, llamada al controlador y respuesta JSON]`

### Qué explicar

- Recibe la petición desde el frontend
- Valida el método
- Llama al controlador
- Devuelve JSON al cliente

### Código relevante a mostrar

```php
$controlador = new CentroController();
$respuesta = $controlador->buscar($datos);
responderJSON($respuesta);
```

---

## Diapositiva 9. Código clave: capa de lógica

### Título sugerido

`Código clave 2: controlador y servicio`

### Archivos recomendados

- `app/controllers/CentroController.php`
- `app/services/CentroEduactivoApiService.php`

### Qué captura debes poner

`[INSERTAR IMAGEN 07: captura del controlador recogiendo filtros]`

`[INSERTAR IMAGEN 08: captura del servicio consultando la API y filtrando resultados]`

### Qué explicar

- El controlador valida la entrada
- El servicio llama a la API pública
- El servicio transforma la respuesta y añade valor

### Fragmentos recomendados

```php
$centros = $this->servicioApi->buscarCentros($localidad, $provincia, $regimen, $tipoCentro);
```

```php
return $this->filtrarYMapearCentros($registros, $localidad, $provincia, $regimen, $tipoCentro);
```

### Qué decir

Esta es una de las partes más importantes del proyecto. El valor añadido no está en mostrar un JSON, sino en filtrar, normalizar, deduplicar ubicaciones, construir fichas internas y preparar la información para el mapa y las tarjetas.

---

## Diapositiva 10. Valor añadido real sobre la API

### Título sugerido

`Qué valor añade mi proyecto frente a la API original`

### Qué poner en la diapositiva

- Select dependiente de provincia y localidad
- Filtros por régimen y tipología
- Mapa de resultados con Leaflet
- Ficha interna por centro
- Datos institucionales y geográficos reorganizados
- Enlace de contraste con fuente oficial

### Captura recomendada

`[INSERTAR IMAGEN 09: captura de los resultados con tarjetas y mapa simultáneo]`

### Qué decir

Si solo mostrara la API tal cual, el proyecto no cumpliría el objetivo. Aquí el valor está en transformar la información y convertirla en una experiencia de consulta útil y más clara que el consumo original del dataset.

---

## Diapositiva 11. Demo en vivo

### Título sugerido

`Demo del proyecto`

### Flujo de demo recomendado

1. Abrir la portada
2. Seleccionar provincia
3. Seleccionar localidad
4. Aplicar uno o dos filtros
5. Mostrar los resultados
6. Señalar la vista mapa
7. Abrir un centro concreto
8. Enseñar la ficha interna
9. Mostrar el mapa embebido
10. Mostrar el enlace de contraste con la fuente oficial

### Capturas recomendadas si no haces demo en directo

`[INSERTAR IMAGEN 10: portada con filtros listos]`

`[INSERTAR IMAGEN 11: resultados con lista + mapa]`

`[INSERTAR IMAGEN 12: ficha detallada de un centro]`

### Qué decir

En la demo voy a enseñar una búsqueda real para que se vea el flujo completo desde la selección guiada hasta la ficha final del centro.

---

## Diapositiva 12. Dificultades técnicas y decisiones tomadas

### Título sugerido

`Problemas encontrados y cómo se resolvieron`

### Qué poner en la diapositiva

- Problemas iniciales de conexión y carga de servicio
- Ajustes en la respuesta JSON del backend
- Fallo del endpoint `datastore_search_sql`
- Uso de `datastore_search` como estrategia estable
- Fallback de conexión entre `cURL` y `file_get_contents`
- Ajustes de Leaflet para una visualización correcta

### Qué decir

Este apartado es importante porque demuestra trabajo técnico real. No todo fue lineal: hubo que depurar la conexión, revisar endpoints, instrumentar respuestas y tomar decisiones para garantizar estabilidad y usabilidad.

---

## Diapositiva 13. Conclusión

### Título sugerido

`Conclusiones`

### Qué poner en la diapositiva

- Se ha integrado una API pública real
- El proyecto aporta valor añadido sobre la fuente original
- La información se transforma en una herramienta usable
- El resultado encaja como base de evolución para un TFG o proyecto mayor

### Qué decir

La conclusión es que este proyecto no solo consume una API, sino que reutiliza datos públicos de forma útil, los integra en una arquitectura propia y construye una experiencia más clara y más rica para el usuario final.

---

## Diapositiva 14. Posibles mejoras futuras

### Título sugerido

`Líneas de evolución`

### Qué poner en la diapositiva

- clustering de marcadores
- geolocalización del usuario
- ordenación por cercanía
- comparador de centros
- caché local
- exportación de resultados

### Qué decir

El proyecto ya es funcional, pero tiene recorrido. Las mejoras futuras irían orientadas a análisis geográfico, personalización y rendimiento.

---

# Material que debes preparar

## 1. Capturas obligatorias recomendadas

Prepara estas capturas antes de montar la presentación:

1. `Captura de la portada del proyecto`
2. `Captura del dataset o documentación oficial de la API`
3. `Captura de los filtros guiados`
4. `Captura de un JSON de respuesta de la API`
5. `Captura del endpoint buscar-centros.php`
6. `Captura del controlador`
7. `Captura del servicio que consume la API`
8. `Captura de resultados con mapa`
9. `Captura de la ficha interna del centro`
10. `Captura del mapa embebido o de la ficha oficial`

---

## 2. Código que conviene enseñar

No muestres demasiado código a la vez. Enseña solo fragmentos cortos y coméntalos:

### Fragmento A

Recepción de la petición y respuesta JSON

- Archivo: `api/buscar-centros.php`

### Fragmento B

Recogida de filtros y llamada al servicio

- Archivo: `app/controllers/CentroController.php`

### Fragmento C

Consumo de la API y filtrado/mapeo

- Archivo: `app/services/CentroEduactivoApiService.php`

### Fragmento D

Render de resultados y sincronización con mapa

- Archivo: `public/js/app.js`

### Fragmento E

Ficha interna del centro

- Archivos:
  - `api/detalle-centro.php`
  - `public/centro.php`
  - `public/js/centro.js`

---

## 3. Orden recomendado de exposición

Para ajustarte a 8-10 minutos:

### Minuto 1

Portada + contexto del servicio

### Minuto 2

Por qué esta API y qué problema resuelve

### Minuto 3

Explicación breve de la API

### Minutos 4-5

Arquitectura + código clave

### Minutos 6-8

Demo en vivo

### Minutos 9-10

Valor añadido + conclusiones + mejoras futuras

---

# Guion breve para defender el proyecto

Puedes usar esta versión resumida como base oral:

> He desarrollado una aplicación llamada EduConsulta Valencia. El proyecto consume una API pública del portal de datos abiertos de la Generalitat Valenciana para consultar centros docentes oficiales.  
>  
> La API no se usa simplemente para mostrar datos sin más. El valor añadido del proyecto está en que la información se filtra, se reorganiza y se transforma en una experiencia de búsqueda más útil.  
>  
> La aplicación permite seleccionar provincia y localidad de forma guiada, aplicar filtros por régimen y tipología, ver resultados en lista y mapa, y acceder a una ficha interna del centro con información más clara y geolocalización.  
>  
> A nivel técnico, la integración se realiza mediante una API interna en PHP que consume el recurso público, procesa la respuesta JSON y la devuelve preparada al frontend. También se resolvieron incidencias reales como problemas de conexión con ciertos endpoints del portal, lo que obligó a adaptar la estrategia de consumo.  
>  
> En definitiva, el proyecto reutiliza datos abiertos oficiales y les añade valor funcional, técnico y visual, convirtiéndolos en una herramienta más útil para el usuario final.

---

# Entregables que debes presentar

Según la solicitud del ejercicio, deberías entregar:

- la presentación final
- las capturas de pantalla del código clave
- las capturas de la demo

## Recomendación práctica

Crea una carpeta para organizarte:

```text
material_exposicion/
├── presentacion_final.pptx
├── capturas_codigo/
├── capturas_demo/
└── notas_orales/
```

---

# Recomendación final

Si vas justo de tiempo, no intentes enseñar demasiados archivos ni demasiadas líneas de código. En este tipo de exposición puntúa más:

- explicar bien el contexto
- dejar claro el valor añadido
- mostrar una demo limpia
- defender las decisiones técnicas con seguridad

Si quieres, en el siguiente paso te puedo generar también una `versión diapositiva a diapositiva` todavía más visual, con texto corto listo para copiar directamente en PowerPoint.  
