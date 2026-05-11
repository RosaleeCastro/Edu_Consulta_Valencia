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
    $codigo = trim($_GET['codigo'] ?? '');
    $controlador = new CentroController();
    $respuesta = $controlador->obtenerDetalleCentro($codigo);

    responderJSON($respuesta, $respuesta['ok'] ? 200 : 404);
} catch (Throwable $error) {
    responderJSON([
        'ok' => false,
        'mensaje' => 'Error interno: ' . $error->getMessage()
    ], 500);
}
