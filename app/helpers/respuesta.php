<?php

  function responderJSON(array $datos, int $codigoHttp = 200):void
  {
    http_response_code($codigoHttp);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
  }

?>
