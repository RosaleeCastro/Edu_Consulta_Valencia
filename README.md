# EduConsulta Valencia

EduConsulta Valencia es una aplicacion web hecha con PHP, JavaScript, HTML y CSS que permite consultar centros educativos de la Comunitat Valenciana usando datos abiertos oficiales de la Generalitat Valenciana.

El proyecto no muestra directamente el JSON de la API externa. La aplicacion crea una capa propia en PHP que consulta la API publica, valida los filtros, transforma los datos y los devuelve preparados para que el frontend pueda pintar tarjetas, mapa y fichas detalladas.

---

## 1. Objetivo del proyecto

El objetivo principal es convertir un dataset publico de centros docentes en una herramienta mas clara y util para el usuario final.

La aplicacion permite:

- Seleccionar provincia y localidad desde listas guiadas.
- Filtrar centros por regimen.
- Filtrar por tipologia o ensenanzas.
- Ver los resultados en tarjetas.
- Ver los centros geolocalizados en un mapa.
- Abrir una ficha interna con informacion detallada de cada centro.
- Contrastar los datos con la fuente oficial.

---

## 2. Fuente de datos

La informacion procede del portal de datos abiertos de la Generalitat Valenciana.

API base utilizada:

```text
https://dadesobertes.gva.es/api/3/action/datastore_search
```

Recurso utilizado:

```text
1aa53c3a-4639-41aa-ac85-d58254c428c0
```

Este recurso corresponde al dataset de centros docentes de la Comunitat Valenciana.

La llamada que hace el proyecto tiene esta forma:

```text
https://dadesobertes.gva.es/api/3/action/datastore_search?resource_id=1aa53c3a-4639-41aa-ac85-d58254c428c0&limit=5000
```

La API devuelve una respuesta JSON. Dentro de esa respuesta, los registros reales estan en:

```php
$respuestaApi['result']['records']
```

Campos importantes que utiliza el proyecto:

- `codigo`
- `denominacion`
- `denominacion_generica_es`
- `denominacion_generica_val`
- `regimen`
- `tipo_via`
- `direccion`
- `numero`
- `codigo_postal`
- `localidad`
- `provincia`
- `telefono`
- `fax`
- `titular`
- `cif`
- `comarca`
- `latitud`
- `longitud`
- `url_es`
- `url_va`
- `fe_constitucion`

---

## 3. Estructura del proyecto

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
├── sql/
│   └── favoritos.sql
├── DOCUMENTACION_EXPOSICION_API.md
└── README.md
```

---

## 4. Explicacion de carpetas

### `config/`

Contiene la configuracion general del proyecto.

Archivo principal:

```text
config/config.php
```

Aqui se definen constantes como:

```php
define('NOMBRE_PROYECTO', 'EduConsulta Valencia');
define('API_BASE_URL', 'https://dadesobertes.gva.es/api/3/action/datastore_search');
define('RESOURCE_ID_CENTROS', '1aa53c3a-4639-41aa-ac85-d58254c428c0');
define('LIMITE_RESULTADOS', 40);
```

Estas constantes evitan repetir valores importantes en varios archivos.

### `api/`

Contiene los endpoints internos del proyecto. Son archivos PHP que reciben peticiones desde JavaScript y devuelven JSON.

Endpoints:

- `api/buscar-centros.php`: recibe los filtros de busqueda y devuelve centros.
- `api/opciones-ubicacion.php`: devuelve provincias y localidades.
- `api/detalle-centro.php`: devuelve la ficha completa de un centro por codigo.

### `app/controllers/`

Contiene el controlador principal:

```text
app/controllers/CentroController.php
```

El controlador valida los datos que llegan desde los endpoints y llama al servicio que trabaja con la API externa.

### `app/services/`

Contiene el servicio:

```text
app/services/CentroEduactivoApiService.php
```

Este archivo es la parte mas importante de la integracion con la API publica.

Se encarga de:

- Construir la URL de consulta.
- Hacer la peticion HTTP.
- Leer el JSON recibido.
- Extraer los registros.
- Filtrar por provincia, localidad, regimen y tipo de centro.
- Convertir cada registro en un formato mas facil de usar por el frontend.

### `app/helpers/`

Contiene funciones auxiliares.

Archivo:

```text
app/helpers/respuesta.php
```

Define `responderJSON()`, que establece la cabecera correcta y devuelve JSON al navegador.

### `public/`

Contiene la parte visible de la aplicacion.

Archivos principales:

- `public/index.php`: pagina principal con formulario, resultados y mapa.
- `public/centro.php`: pagina de detalle de un centro.
- `public/js/app.js`: logica principal del buscador y mapa.
- `public/js/centro.js`: logica de la ficha individual.
- `public/css/estilos.css`: estilos visuales.

---

## 5. Arquitectura general

La aplicacion esta organizada con una separacion sencilla por capas:

```text
Frontend
HTML + CSS + JavaScript
        ↓
API interna
Archivos PHP dentro de api/
        ↓
Controlador
CentroController.php
        ↓
Servicio
CentroEduactivoApiService.php
        ↓
API publica externa
Generalitat Valenciana
```

El navegador no consume directamente la API de la Generalitat. Primero pasa por la API interna del proyecto.

Esto permite:

- Validar los datos antes de buscar.
- Controlar errores.
- Normalizar textos.
- Filtrar resultados.
- Preparar una respuesta mas comoda para JavaScript.
- Evitar que el frontend dependa de la estructura exacta del JSON externo.

---

## 6. Flujo de carga inicial

Cuando el usuario abre:

```text
http://localhost/Edu_consultas/public/
```

se carga `public/index.php`.

Esta pagina contiene:

- El formulario de busqueda.
- El contenedor de resultados.
- El contenedor del mapa.
- La carga de Leaflet.
- La carga de `public/js/app.js`.

Al cargarse `app.js`, se ejecutan estas acciones:

```js
cargarUbicaciones();
inicializarMapaResultados();
```

Primero se cargan provincias y localidades.

Despues se inicializa el mapa vacio centrado en Valencia.

---

## 7. Flujo de provincias y localidades

Paso a paso:

1. `app.js` llama a:

```js
fetch("../api/opciones-ubicacion.php")
```

2. `api/opciones-ubicacion.php` crea un `CentroController`.

3. El controlador llama a:

```php
$this->servicioApi->obtenerUbicaciones()
```

4. El servicio consulta todos los registros de la API externa.

5. Recorre los registros y recoge combinaciones reales de `provincia` y `localidad`.

6. Ordena las provincias y localidades.

7. Devuelve una respuesta JSON con esta estructura:

```json
{
  "ok": true,
  "mensaje": "Ubicaciones cargadas correctamente.",
  "ubicaciones": [
    {
      "provincia": "VALENCIA/VALENCIA",
      "localidades": ["ALAQUAS", "ALBORAYA", "VALENCIA"]
    }
  ]
}
```

8. JavaScript rellena el select de provincias.

9. Cuando el usuario selecciona una provincia, JavaScript rellena el select de localidades correspondiente.

---

## 8. Flujo de busqueda de centros

Cuando el usuario pulsa "Buscar centros":

1. `public/js/app.js` detiene el envio normal del formulario:

```js
evento.preventDefault();
```

2. Recoge los filtros:

```js
const localidad = selectLocalidad.value.trim();
const provincia = selectProvincia.value;
const regimen = document.getElementById("regimen").value;
const tipoCentro = document.getElementById("tipo-centro").value;
```

3. Envia una peticion `POST` a:

```text
../api/buscar-centros.php
```

Con este cuerpo JSON:

```json
{
  "localidad": "VALENCIA",
  "provincia": "VALENCIA/VALENCIA",
  "regimen": "Publico",
  "tipo_centro": "secundaria"
}
```

4. `api/buscar-centros.php` comprueba que el metodo sea `POST`.

5. Lee el JSON recibido:

```php
$datos = json_decode(file_get_contents('php://input'), true);
```

6. Crea el controlador:

```php
$controlador = new CentroController();
```

7. Llama al metodo:

```php
$respuesta = $controlador->buscar($datos);
```

8. El controlador valida que `provincia` y `localidad` no esten vacias.

9. El controlador llama al servicio:

```php
$centros = $this->servicioApi->buscarCentros($localidad, $provincia, $regimen, $tipoCentro);
```

10. El servicio obtiene los registros desde la API externa.

11. Filtra los centros segun los filtros del usuario.

12. Mapea cada centro a una estructura propia.

13. PHP devuelve el resultado en JSON.

14. JavaScript recibe los centros y ejecuta:

```js
pintarResultados(centros);
actualizarContadorResultados(totalResultados);
```

---

## 9. Como se consume la API externa

La consulta se hace en `CentroEduactivoApiService.php`, dentro del metodo `obtenerRegistros()`.

Primero se construye la URL:

```php
$url = API_BASE_URL . '?' . http_build_query([
    'resource_id' => RESOURCE_ID_CENTROS,
    'limit' => 5000
]);
```

Luego se llama a:

```php
$respuestaApi = $this->hacerPeticion($url);
```

El metodo `hacerPeticion()` intenta dos estrategias:

1. Usar `cURL`, si esta disponible.
2. Usar `file_get_contents()`, si `cURL` falla o no existe.

Esto hace que el proyecto sea mas resistente en entornos locales como XAMPP.

Despues convierte el JSON en array PHP:

```php
$datos = json_decode($respuesta, true);
```

Si la respuesta no es JSON valido, lanza una excepcion.

---

## 10. Filtrado y mapeo de centros

El servicio no envia los registros originales tal cual.

Primero filtra con:

```php
filtrarYMapearCentros()
```

Este metodo compara:

- Localidad buscada con localidad del registro.
- Provincia buscada con provincia del registro.
- Regimen buscado con regimen del registro.
- Tipo de centro buscado con denominacion del centro.

Para evitar problemas con mayusculas, minusculas y acentos, usa:

```php
normalizarTexto()
```

Luego cada registro se transforma con:

```php
mapearCentro()
```

Este metodo crea un array mas limpio:

```php
[
  'codigo' => '46000000',
  'nombre' => 'Nombre del centro',
  'tipo' => 'Instituto de Educacion Secundaria',
  'regimen' => 'Publico',
  'direccion' => 'Calle ejemplo 1',
  'localidad' => 'VALENCIA',
  'provincia' => 'VALENCIA/VALENCIA',
  'latitud' => '39.4699',
  'longitud' => '-0.3763',
  'coordenadas_disponibles' => true
]
```

La clave `coordenadas_disponibles` es muy importante para el mapa.

---

## 11. Como se generan las tarjetas de resultados

Cuando el frontend recibe los centros, llama a:

```js
pintarResultados(centros);
```

Esa funcion:

1. Comprueba si hay resultados.
2. Muestra mensajes de estado.
3. Muestra la barra de vistas.
4. Crea una tarjeta HTML por cada centro.
5. Inserta nombre, tipo, direccion, telefono, titular y comarca.
6. Crea enlaces para:
   - Ver la ficha interna.
   - Abrir el mapa externo en OpenStreetMap.
7. Guarda cada tarjeta en un `Map` usando el codigo del centro.

Esto permite sincronizar tarjetas y marcadores.

---

## 12. Como se genera el mapa principal

El mapa principal esta en `public/index.php`.

Contenedor HTML:

```html
<div id="mapa-resultados" class="mapa-resultados" aria-label="Mapa de resultados"></div>
```

Leaflet se carga desde CDN:

```html
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
```

La inicializacion ocurre en `public/js/app.js`:

```js
inicializarMapaResultados();
```

Paso a paso:

1. Se crea el mapa:

```js
mapaResultados = L.map("mapa-resultados", {
  zoomControl: true,
  scrollWheelZoom: false,
}).setView([39.4699, -0.3763], 9);
```

2. Se centra inicialmente en Valencia:

```js
[39.4699, -0.3763]
```

3. Se anade la capa de OpenStreetMap:

```js
L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
  maxZoom: 19,
  attribution: '&copy; OpenStreetMap contributors',
}).addTo(mapaResultados);
```

4. Se crea una capa para marcadores:

```js
capaMarcadores = L.layerGroup().addTo(mapaResultados);
```

---

## 13. Como se pintan los marcadores

Cuando llegan los centros de una busqueda, se llama a:

```js
actualizarMapaResultados(centros);
```

Esta funcion:

1. Limpia los marcadores anteriores:

```js
capaMarcadores.clearLayers();
```

2. Recorre los centros.

3. Ignora los centros sin coordenadas:

```js
if (!centro.coordenadas_disponibles) {
  return;
}
```

4. Convierte latitud y longitud a numeros:

```js
const lat = Number(centro.latitud);
const lon = Number(centro.longitud);
```

5. Crea el marcador:

```js
const marcador = L.marker([lat, lon], {
  title: centro.nombre,
  icon: crearIconoCentro(false),
}).addTo(capaMarcadores);
```

6. Crea el popup:

```js
marcador.bindPopup(crearPopupMapa(centro));
```

7. Guarda el marcador por codigo:

```js
marcadoresPorCodigo.set(String(centro.codigo), marcador);
```

8. Ajusta el mapa para que se vean todos los centros:

```js
mapaResultados.fitBounds(bounds, {
  padding: [36, 36],
  maxZoom: 14,
});
```

---

## 14. Sincronizacion entre tarjetas y mapa

El proyecto relaciona cada tarjeta con su marcador usando el codigo del centro.

Para las tarjetas:

```js
tarjetasPorCodigo.set(String(centro.codigo), tarjeta);
```

Para los marcadores:

```js
marcadoresPorCodigo.set(String(centro.codigo), marcador);
```

Cuando el usuario pasa el raton sobre una tarjeta, se llama a:

```js
activarMarcadorCentro(centro.codigo);
```

Eso cambia el icono del marcador y abre su popup.

Cuando el usuario hace clic en un marcador, se llama a:

```js
destacarTarjetaCentro(centro.codigo);
```

Eso resalta la tarjeta y hace scroll hasta ella.

---

## 15. Ficha detallada de centro

Cada tarjeta incluye un enlace como:

```text
centro.php?codigo=CODIGO_DEL_CENTRO
```

Cuando se abre `public/centro.php`, PHP guarda el codigo recibido en una variable JavaScript:

```php
window.CENTRO_CODIGO = <?= json_encode($codigo, JSON_UNESCAPED_UNICODE) ?>;
```

Despues `public/js/centro.js` llama a:

```js
../api/detalle-centro.php?codigo=...
```

El endpoint busca el centro por codigo y devuelve sus datos.

La ficha muestra:

- Nombre.
- Tipo.
- Regimen.
- Codigo.
- Localidad.
- Provincia.
- Codigo postal.
- Telefono.
- Fax.
- Direccion.
- Titular.
- CIF.
- Comarca.
- Fecha de constitucion.
- Latitud.
- Longitud.
- Enlace oficial.
- Mapa embebido.

---

## 16. Mapa embebido en la ficha

La ficha individual no usa Leaflet.

Usa un `iframe` de OpenStreetMap:

```html
<iframe id="detalle-mapa-iframe"></iframe>
```

En `centro.js`, si el centro tiene coordenadas, se genera una URL de mapa embebido:

```js
construirUrlMapaEmbebido(centro.latitud, centro.longitud)
```

La URL tiene esta forma:

```text
https://www.openstreetmap.org/export/embed.html?bbox=...&layer=mapnik&marker=latitud,longitud
```

El parametro `bbox` define el area visible del mapa.

El parametro `marker` coloca el marcador en la ubicacion exacta del centro.

---

## 17. Endpoints internos

### `POST /api/buscar-centros.php`

Recibe:

```json
{
  "provincia": "VALENCIA/VALENCIA",
  "localidad": "VALENCIA",
  "regimen": "Publico",
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

Devuelve provincias y localidades disponibles.

### `GET /api/detalle-centro.php?codigo=...`

Devuelve la ficha completa de un centro concreto.

---

## 18. Puesta en marcha

1. Copiar el proyecto en:

```text
C:\xampp\htdocs\Edu_consultas
```

2. Iniciar Apache desde XAMPP.

3. Abrir:

```text
http://localhost/Edu_consultas/public/
```

4. Para diagnosticar la API externa:

```text
http://localhost/Edu_consultas/public/diagnostico-api.php
```

---

## 19. Resumen rapido para exposicion

EduConsulta Valencia consume una API publica de la Generalitat Valenciana con datos oficiales de centros docentes. El proyecto crea una API interna en PHP que consulta el dataset, filtra y transforma los registros. El frontend recibe datos ya preparados y los muestra como tarjetas, mapa interactivo con Leaflet y ficha individual por centro.

La parte mas importante no es solo consumir la API, sino convertir datos abiertos en una experiencia util: busqueda guiada, filtros, geolocalizacion, detalle interno y enlace a la fuente oficial.
