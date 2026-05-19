<?php

// Helper para enviar respuestas JSON uniformes.
require_once __DIR__ . '/../app/helpers/respuesta.php';

// Controlador que sabe buscar un centro concreto por codigo.
require_once __DIR__ . '/../app/controllers/CentroController.php';

// La ficha de detalle se consulta con GET: detalle-centro.php?codigo=...
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responderJSON([
        'ok' => false,
        'mensaje' => 'Metodo no permitido. Usa GET.'
    ], 405);
}

try {
    // Codigo oficial del centro recibido desde la URL.
    $codigo = trim($_GET['codigo'] ?? '');

    // El controlador valida el codigo y pide el detalle al servicio.
    $controlador = new CentroController();
    $respuesta = $controlador->obtenerDetalleCentro($codigo);

    // Si el centro existe respondemos 200; si no existe, 404.
    responderJSON($respuesta, $respuesta['ok'] ? 200 : 404);
} catch (Throwable $error) {
    responderJSON([
        'ok' => false,
        'mensaje' => 'Error interno: ' . $error->getMessage()
    ], 500);
}
