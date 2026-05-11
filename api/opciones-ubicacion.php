<?php

require_once __DIR__ . '/../app/helpers/respuesta.php';
require_once __DIR__ . '/../app/controllers/CentroController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responderJSON([
        'ok' => false,
        'mensaje' => 'Método no permitido. Usa GET.'
    ], 405);
}

try {
    $controlador = new CentroController();
    $respuesta = $controlador->obtenerUbicaciones();

    responderJSON($respuesta);
} catch (Throwable $error) {
    responderJSON([
        'ok' => false,
        'mensaje' => 'Error interno: ' . $error->getMessage()
    ], 500);
}
