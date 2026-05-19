<?php

// Helper comun para responder siempre en formato JSON.
require_once __DIR__ . '/../app/helpers/respuesta.php';

// Controlador principal de centros educativos.
require_once __DIR__ . '/../app/controllers/CentroController.php';

// Este endpoint solo carga datos, por eso trabaja con GET.
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responderJSON([
        'ok' => false,
        'mensaje' => 'Metodo no permitido. Usa GET.'
    ], 405);
}

try {
    // El controlador obtiene el catalogo de provincias y localidades.
    $controlador = new CentroController();
    $respuesta = $controlador->obtenerUbicaciones();

    // Se devuelve al frontend para rellenar los selects dependientes.
    responderJSON($respuesta);
} catch (Throwable $error) {
    responderJSON([
        'ok' => false,
        'mensaje' => 'Error interno: ' . $error->getMessage()
    ], 500);
}
