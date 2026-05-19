<?php
/**
 * Pagina principal del proyecto.
 *
 * Esta vista contiene la interfaz inicial:
 * - formulario de busqueda
 * - contenedor de resultados
 * - barra para cambiar entre lista, mapa o vista mixta
 * - contenedor donde Leaflet dibuja el mapa
 */
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>EduConsulta Valencia</title>

    <!-- Estilos propios de la aplicacion. -->
    <link rel="stylesheet" href="css/estilos.css">

    <!-- Estilos de Leaflet, la libreria usada para el mapa interactivo. -->
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    >
</head>
<body>

    <!-- Cabecera informativa de la aplicacion. -->
    <header class="cabecera">
        <div class="contenedor">
            <span class="etiqueta-proyecto">Datos abiertos educativos</span>

            <h1>EduConsulta Valencia</h1>

            <p>
                Buscador de centros educativos de la Comunitat Valenciana
                utilizando datos abiertos oficiales de la Generalitat Valenciana.
            </p>
        </div>
    </header>

    <main class="contenedor">

        <!-- Panel con los filtros que el usuario utiliza para buscar. -->
        <section class="panel-busqueda">
            <h2>Busca centros educativos</h2>

            <p class="texto-ayuda">
                Introduce una localidad y consulta informacion educativa util
                para familias, estudiantes o personas que buscan orientacion.
            </p>

            <!-- JavaScript intercepta este formulario y lo envia por fetch. -->
            <form id="formulario-busqueda" class="formulario-busqueda">

                <!-- Provincia: se rellena dinamicamente desde api/opciones-ubicacion.php. -->
                <div class="campo">
                    <label for="provincia">Provincia</label>
                    <select id="provincia" name="provincia" required>
                        <option value="">Selecciona una provincia</option>
                    </select>
                </div>

                <!-- Localidad: se habilita cuando el usuario escoge una provincia. -->
                <div class="campo">
                    <label for="localidad">Localidad</label>
                    <select id="localidad" name="localidad" required disabled>
                        <option value="">Selecciona primero una provincia</option>
                    </select>
                </div>

                <!-- Filtro opcional por regimen del centro. -->
                <div class="campo">
                    <label for="regimen">Regimen</label>
                    <select id="regimen" name="regimen">
                        <option value="">Todos</option>
                        <option value="Publico">Publico</option>
                        <option value="Privado concertado">Privado concertado</option>
                        <option value="Privado">Privado</option>
                    </select>
                </div>

                <!-- Filtro opcional por tipo de ensenanza o centro. -->
                <div class="campo">
                    <label for="tipo-centro">Tipologia / ensenanzas</label>
                    <select id="tipo-centro" name="tipo_centro">
                        <option value="">Todos</option>
                        <option value="infantil">Infantil</option>
                        <option value="primaria">Primaria</option>
                        <option value="secundaria">Secundaria</option>
                        <option value="bachillerato">Bachillerato</option>
                        <option value="fp">Formacion Profesional</option>
                        <option value="bach_fp">Bachillerato y FP / centros mixtos</option>
                        <option value="universidad">Universidad</option>
                        <option value="adultos">Adultos</option>
                        <option value="especial">Educacion especial</option>
                        <option value="idiomas">Idiomas</option>
                        <option value="musica">Musica y danza</option>
                    </select>
                </div>

                <button type="submit" class="boton-principal">
                    Buscar centros
                </button>

            </form>
        </section>

        <!-- Seccion donde se muestran mensajes, tarjetas y mapa. -->
        <section class="seccion-resultados">
            <div class="cabecera-resultados">
                <div>
                    <h2>Resultados</h2>
                    <p class="texto-ayuda texto-ayuda-corto">
                        Consulta la lista, explora el mapa o trabaja con ambas vistas a la vez.
                    </p>
                </div>

                <!-- Contadores actualizados desde JavaScript. -->
                <div class="panel-resumen-resultados">
                    <span id="contador-resultados">Sin busquedas todavia</span>
                    <span id="contador-mapa" class="contador-secundario">El mapa se activara con una busqueda</span>
                </div>
            </div>

            <!-- Mensaje de estado: buscando, error, sin resultados, etc. -->
            <div id="mensaje-estado" class="mensaje-estado">
                Realiza una busqueda para ver centros educativos.
            </div>

            <!-- Resumen textual de los filtros usados. -->
            <div id="mensaje-filtros" class="mensaje-filtros" hidden></div>

            <!-- Botones para cambiar entre vista mixta, lista o mapa. -->
            <div id="barra-vistas" class="barra-vistas oculto">
                <div class="grupo-vistas" role="tablist" aria-label="Modo de visualizacion de resultados">
                    <button type="button" class="boton-vista activo" data-view="mixta">Vista mixta</button>
                    <button type="button" class="boton-vista" data-view="lista">Solo lista</button>
                    <button type="button" class="boton-vista" data-view="mapa">Solo mapa</button>
                </div>
            </div>

            <!-- Panel completo de resultados. JavaScript lo muestra tras buscar. -->
            <div id="panel-resultados" class="panel-resultados vista-mixta oculto">
                <div class="columna-lista">
                    <div id="contenedor-resultados" class="grid-resultados">
                        <!-- Aqui JavaScript inserta las tarjetas de centros. -->
                    </div>
                </div>

                <aside class="columna-mapa">
                    <div class="tarjeta-mapa">
                        <div class="cabecera-mapa">
                            <h3>Mapa de centros</h3>
                            <p id="mensaje-mapa">Selecciona una busqueda para ubicar los centros disponibles.</p>
                        </div>

                        <!-- Leaflet usa este div como contenedor del mapa. -->
                        <div id="mapa-resultados" class="mapa-resultados" aria-label="Mapa de resultados"></div>
                    </div>
                </aside>
            </div>
        </section>

    </main>

    <footer class="pie">
        <p>
            Proyecto academico desarrollado con PHP, JavaScript y una API publica externa.
        </p>
    </footer>

    <!-- Libreria Leaflet para mapas. Debe cargarse antes de app.js. -->
    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""
    ></script>

    <!-- Logica del buscador, resultados y mapa. -->
    <script src="js/app.js"></script>
</body>
</html>
