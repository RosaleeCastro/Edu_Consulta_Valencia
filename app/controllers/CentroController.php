<?php

require_once __DIR__ . '/../services/CentroEducativoApiService.php';

class CentroController
{
    private CentroEducativoApiService $servicioApi;

    public function __construct()
    {
        $this->servicioApi = new CentroEducativoApiService();
    }

    public function buscar(array $datos): array
    {
        $localidad = trim($datos['localidad'] ?? '');
        $provincia = trim($datos['provincia'] ?? '');
        $regimen = trim($datos['regimen'] ?? '');

        if ($localidad === '') {
            return [
                'ok' => false,
                'mensaje' => 'La localidad es obligatoria.',
                'centros' => []
            ];
        }

        $centros = $this->servicioApi->buscarCentros($localidad, $provincia, $regimen);

        return [
            'ok' => true,
            'mensaje' => 'Consulta realizada correctamente.',
            'total' => count($centros),
            'centros' => $centros
        ];
    }
}