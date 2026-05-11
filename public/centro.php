<?php
$codigo = trim($_GET['codigo'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha del centro</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <header class="cabecera cabecera-detalle">
        <div class="contenedor">
            <a href="index.php" class="enlace-retorno">Volver al buscador</a>
            <span class="etiqueta-proyecto">Ficha del centro</span>
            <h1 id="detalle-nombre">Cargando información del centro...</h1>
            <p id="detalle-subtitulo">Estamos consultando los datos oficiales del centro seleccionado.</p>
        </div>
    </header>

    <main class="contenedor detalle-centro">
        <section id="detalle-estado" class="mensaje-estado">
            Cargando detalle del centro...
        </section>

        <section id="detalle-contenido" class="detalle-layout oculto">
            <article class="detalle-principal">
                <div class="detalle-bloque">
                    <div class="detalle-badges detalle-badges-bloque">
                        <span id="detalle-regimen" class="etiqueta-regimen"></span>
                        <span id="detalle-codigo" class="etiqueta-secundaria"></span>
                    </div>
                    <h2>Información general</h2>
                    <div class="detalle-grid">
                        <div><strong>Tipo</strong><span id="detalle-tipo"></span></div>
                        <div><strong>Localidad</strong><span id="detalle-localidad"></span></div>
                        <div><strong>Provincia</strong><span id="detalle-provincia"></span></div>
                        <div><strong>Código postal</strong><span id="detalle-cp"></span></div>
                        <div><strong>Teléfono</strong><span id="detalle-telefono"></span></div>
                        <div><strong>Fax</strong><span id="detalle-fax"></span></div>
                        <div class="detalle-doble"><strong>Dirección</strong><span id="detalle-direccion"></span></div>
                    </div>
                </div>

                <div class="detalle-bloque">
                    <h2>Datos institucionales</h2>
                    <div class="detalle-grid">
                        <div><strong>Titular</strong><span id="detalle-titular"></span></div>
                        <div><strong>CIF</strong><span id="detalle-cif"></span></div>
                        <div><strong>Comarca</strong><span id="detalle-comarca"></span></div>
                        <div><strong>Constitución</strong><span id="detalle-constitucion"></span></div>
                    </div>
                </div>
            </article>

            <aside class="detalle-lateral">
                <div class="detalle-bloque">
                    <h2>Ubicación</h2>
                    <div class="detalle-grid detalle-grid-simple">
                        <div><strong>Latitud</strong><span id="detalle-latitud"></span></div>
                        <div><strong>Longitud</strong><span id="detalle-longitud"></span></div>
                    </div>
                    <div id="detalle-mapa-embebido" class="mapa-embebido oculto">
                        <iframe
                            id="detalle-mapa-iframe"
                            title="Mapa del centro"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                        ></iframe>
                    </div>
                    <div class="acciones-detalle">
                        <a id="detalle-mapa" class="enlace-centro enlace-centro-secundario" href="#" target="_blank" rel="noopener noreferrer">Abrir mapa</a>
                        <a id="detalle-ficha-oficial" class="enlace-centro" href="#" target="_blank" rel="noopener noreferrer">Ficha oficial</a>
                    </div>
                </div>
            </aside>
        </section>
    </main>

    <script>
      window.CENTRO_CODIGO = <?= json_encode($codigo, JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <script src="js/centro.js"></script>
</body>
</html>
