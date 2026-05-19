<?php

  /**
   * Devuelve una respuesta JSON al navegador.
   *
   * Todos los endpoints internos usan esta funcion para mantener el mismo
   * formato de salida y la misma cabecera HTTP.
   */
  function responderJSON(array $datos, int $codigoHttp = 200): void
  {
    // Codigo HTTP de la respuesta: 200, 400, 404, 500, etc.
    http_response_code($codigoHttp);

    // Cabecera para que el cliente interprete la respuesta como JSON UTF-8.
    header('Content-Type: application/json; charset=utf-8');

    // JSON_UNESCAPED_UNICODE evita escapar caracteres como tildes o enes.
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);

    // Se corta la ejecucion para que no se imprima nada despues del JSON.
    exit;
  }

?>
