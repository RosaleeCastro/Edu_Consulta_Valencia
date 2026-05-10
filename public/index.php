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
                    <label for="localidad">Localidad</label>
                    <input 
                        type="text" 
                        id="localidad" 
                        name="localidad" 
                        placeholder="Ejemplo: Valencia, Torrent, Paterna"
                        required
                    >
                </div>

                <div class="campo">
                    <label for="provincia">Provincia</label>
                    <select id="provincia" name="provincia">
                        <option value="">Todas</option>
                        <option value="Valencia">Valencia</option>
                        <option value="Castellón">Castellón</option>
                        <option value="Alicante">Alicante</option>
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

                <button type="submit" class="boton-principal">
                    Buscar centros
                </button>

            </form>
        </section>

        <section class="seccion-resultados">
            <div class="cabecera-resultados">
                <h2>Resultados</h2>
                <span id="contador-resultados">Sin búsquedas todavía</span>
            </div>

            <div id="mensaje-estado" class="mensaje-estado">
                Realiza una búsqueda para ver centros educativos.
            </div>

            <div id="contenedor-resultados" class="grid-resultados">
                <!-- Aquí JavaScript insertará las tarjetas de centros -->
            </div>
        </section>

    </main>

    <footer class="pie">
        <p>
            Proyecto académico desarrollado con PHP, JavaScript y una API pública externa.
        </p>
    </footer>

    <script src="js/app.js"></script>
</body>
</html>