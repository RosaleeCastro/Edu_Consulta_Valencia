<?php

// Helper comun para enviar respuestas JSON.
require_once __DIR__ . '/../app/helpers/respuesta.php';

// Controlador que contiene la logica de validacion y busqueda.
require_once __DIR__ . '/../app/controllers/CentroController.php';

// Este endpoint solo acepta POST porque recibe filtros en el cuerpo JSON.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON([
        'ok' => false,
        'mensaje' => 'Metodo no permitido. Usa POST.'
    ], 405);
}

try {
    // Leemos el cuerpo de la peticion y lo convertimos de JSON a array PHP.
    $datos = json_decode(file_get_contents('php://input'), true);

    // Si el JSON no es valido, no se puede continuar con la busqueda.
    if (!is_array($datos)) {
        responderJSON([
            'ok' => false,
            'mensaje' => 'No se recibieron datos validos.'
        ], 400);
    }

    // El controlador valida filtros y llama al servicio que consulta la API.
    $controlador = new CentroController();
    $respuesta = $controlador->buscar($datos);

    // Devolvemos al frontend el resultado final ya preparado.
    responderJSON($respuesta);

} catch (Throwable $error) {
    // Cualquier error inesperado se devuelve como error interno.
    responderJSON([
        'ok' => false,
        'mensaje' => 'Error interno: ' . $error->getMessage()
    ], 500);
}
