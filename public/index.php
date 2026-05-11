<?php
/**
 * Página principal del proyecto EduConsulta Valencia.
 *
 * Esta vista contiene el formulario de búsqueda y el contenedor
 * donde JavaScript mostrará los centros educativos obtenidos
 * desde nuestra API interna en PHP.
 */
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>EduConsulta Valencia</title>

    <link rel="stylesheet" href="css/estilos.css">
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    >
</head>
<body>

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

        <section class="panel-busqueda">
            <h2>Busca centros educativos</h2>

            <p class="texto-ayuda">
                Introduce una localidad y consulta información educativa útil
                para familias, estudiantes o personas que buscan orientación.
            </p>

            <form id="formulario-busqueda" class="formulario-busqueda">

                <div class="campo">
                    <label for="provincia">Provincia</label>
                    <select id="provincia" name="provincia" required>
                        <option value="">Selecciona una provincia</option>
                    </select>
                </div>

                <div class="campo">
                    <label for="localidad">Localidad</label>
                    <select id="localidad" name="localidad" required disabled>
                        <option value="">Selecciona primero una provincia</option>
                    </select>
                </div>

                <div class="campo">
                    <label for="regimen">Régimen</label>
                    <select id="regimen" name="regimen">
                        <option value="">Todos</option>
                        <option value="Público">Público</option>
                        <option value="Privado concertado">Privado concertado</option>
                        <option value="Privado">Privado</option>
                    </select>
                </div>

                <div class="campo">
                    <label for="tipo-centro">Tipología / enseñanzas</label>
                    <select id="tipo-centro" name="tipo_centro">
                        <option value="">Todos</option>
                        <option value="infantil">Infantil</option>
                        <option value="primaria">Primaria</option>
                        <option value="secundaria">Secundaria</option>
                        <option value="bachillerato">Bachillerato</option>
                        <option value="fp">Formación Profesional</option>
                        <option value="bach_fp">Bachillerato y FP / centros mixtos</option>
                        <option value="universidad">Universidad</option>
                        <option value="adultos">Adultos</option>
                        <option value="especial">Educación especial</option>
                        <option value="idiomas">Idiomas</option>
                        <option value="musica">Música y danza</option>
                    </select>
                </div>

                <button type="submit" class="boton-principal">
                    Buscar centros
                </button>

            </form>
        </section>

        <section class="seccion-resultados">
            <div class="cabecera-resultados">
                <div>
                    <h2>Resultados</h2>
                    <p class="texto-ayuda texto-ayuda-corto">
                        Consulta la lista, explora el mapa o trabaja con ambas vistas a la vez.
                    </p>
                </div>
                <div class="panel-resumen-resultados">
                    <span id="contador-resultados">Sin búsquedas todavía</span>
                    <span id="contador-mapa" class="contador-secundario">El mapa se activará con una búsqueda</span>
                </div>
            </div>

            <div id="mensaje-estado" class="mensaje-estado">
                Realiza una búsqueda para ver centros educativos.
            </div>

            <div id="mensaje-filtros" class="mensaje-filtros" hidden></div>

            <div id="barra-vistas" class="barra-vistas oculto">
                <div class="grupo-vistas" role="tablist" aria-label="Modo de visualización de resultados">
                    <button type="button" class="boton-vista activo" data-view="mixta">Vista mixta</button>
                    <button type="button" class="boton-vista" data-view="lista">Solo lista</button>
                    <button type="button" class="boton-vista" data-view="mapa">Solo mapa</button>
                </div>
            </div>

            <div id="panel-resultados" class="panel-resultados vista-mixta oculto">
                <div class="columna-lista">
                    <div id="contenedor-resultados" class="grid-resultados">
                        <!-- Aquí JavaScript insertará las tarjetas de centros -->
                    </div>
                </div>

                <aside class="columna-mapa">
                    <div class="tarjeta-mapa">
                        <div class="cabecera-mapa">
                            <h3>Mapa de centros</h3>
                            <p id="mensaje-mapa">Selecciona una búsqueda para ubicar los centros disponibles.</p>
                        </div>
                        <div id="mapa-resultados" class="mapa-resultados" aria-label="Mapa de resultados"></div>
                    </div>
                </aside>
            </div>
        </section>

    </main>

    <footer class="pie">
        <p>
            Proyecto académico desarrollado con PHP, JavaScript y una API pública externa.
        </p>
    </footer>

    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""
    ></script>
    <script src="js/app.js"></script>
</body>
</html>
