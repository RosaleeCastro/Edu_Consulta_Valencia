<?php

require_once __DIR__ . '/../services/CentroEduactivoApiService.php';

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
        $tipoCentro = trim($datos['tipo_centro'] ?? '');

        if ($provincia === '') {
            return [
                'ok' => false,
                'mensaje' => 'La provincia es obligatoria.',
                'centros' => []
            ];
        }

        if ($localidad === '') {
            return [
                'ok' => false,
                'mensaje' => 'La localidad es obligatoria.',
                'centros' => []
            ];
        }

        $centros = $this->servicioApi->buscarCentros($localidad, $provincia, $regimen, $tipoCentro);

        return [
            'ok' => true,
            'mensaje' => 'Consulta realizada correctamente.',
            'total' => count($centros),
            'centros' => $centros
        ];
    }

    public function obtenerUbicaciones(): array
    {
        return [
            'ok' => true,
            'mensaje' => 'Ubicaciones cargadas correctamente.',
            'ubicaciones' => $this->servicioApi->obtenerUbicaciones()
        ];
    }

    public function obtenerDetalleCentro(string $codigo): array
    {
        $codigo = trim($codigo);

        if ($codigo === '') {
            return [
                'ok' => false,
                'mensaje' => 'El código del centro es obligatorio.'
            ];
        }

        $centro = $this->servicioApi->obtenerCentroPorCodigo($codigo);

        if ($centro === null) {
            return [
                'ok' => false,
                'mensaje' => 'No se encontró el centro solicitado.'
            ];
        }

        return [
            'ok' => true,
            'mensaje' => 'Detalle del centro cargado correctamente.',
            'centro' => $centro
        ];
    }
}
