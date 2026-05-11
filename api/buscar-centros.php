<?php

require_once __DIR__ . '/../app/helpers/respuesta.php';
require_once __DIR__ . '/../app/controllers/CentroController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON([
        'ok' => false,
        'mensaje' => 'Método no permitido. Usa POST.'
    ], 405);
}

try {
    $datos = json_decode(file_get_contents('php://input'), true);

    if (!is_array($datos)) {
        responderJSON([
            'ok' => false,
            'mensaje' => 'No se recibieron datos válidos.'
        ], 400);
    }

    $controlador = new CentroController();
    $respuesta = $controlador->buscar($datos);

    responderJSON($respuesta);

} catch (Exception $error) {
    responderJSON([
        'ok' => false,
        'mensaje' => 'Error interno: ' . $error->getMessage()
    ], 500);
}