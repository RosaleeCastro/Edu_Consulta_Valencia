<?php

// Servicio encargado de consultar la API externa y transformar los datos.
require_once __DIR__ . '/../services/CentroEduactivoApiService.php';

class CentroController
{
    private CentroEducativoApiService $servicioApi;

    public function __construct()
    {
        // El controlador usa el servicio como capa de acceso a datos.
        $this->servicioApi = new CentroEducativoApiService();
    }

    /**
     * Procesa una busqueda enviada desde el formulario principal.
     */
    public function buscar(array $datos): array
    {
        // Se limpian los filtros recibidos para evitar espacios accidentales.
        $localidad = trim($datos['localidad'] ?? '');
        $provincia = trim($datos['provincia'] ?? '');
        $regimen = trim($datos['regimen'] ?? '');
        $tipoCentro = trim($datos['tipo_centro'] ?? '');

        // La provincia es obligatoria porque el select de localidades depende de ella.
        if ($provincia === '') {
            return [
                'ok' => false,
                'mensaje' => 'La provincia es obligatoria.',
                'centros' => []
            ];
        }

        // La localidad tambien es obligatoria para acotar la busqueda.
        if ($localidad === '') {
            return [
                'ok' => false,
                'mensaje' => 'La localidad es obligatoria.',
                'centros' => []
            ];
        }

        // El servicio consulta la API externa, filtra y mapea los centros.
        $centros = $this->servicioApi->buscarCentros($localidad, $provincia, $regimen, $tipoCentro);

        return [
            'ok' => true,
            'mensaje' => 'Consulta realizada correctamente.',
            'total' => count($centros),
            'centros' => $centros
        ];
    }

    /**
     * Devuelve el catalogo de provincias y localidades disponible en el dataset.
     */
    public function obtenerUbicaciones(): array
    {
        return [
            'ok' => true,
            'mensaje' => 'Ubicaciones cargadas correctamente.',
            'ubicaciones' => $this->servicioApi->obtenerUbicaciones()
        ];
    }

    /**
     * Devuelve la ficha de un centro concreto usando su codigo oficial.
     */
    public function obtenerDetalleCentro(string $codigo): array
    {
        $codigo = trim($codigo);

        if ($codigo === '') {
            return [
                'ok' => false,
                'mensaje' => 'El codigo del centro es obligatorio.'
            ];
        }

        // El servicio recorre los registros y busca el codigo exacto.
        $centro = $this->servicioApi->obtenerCentroPorCodigo($codigo);

        if ($centro === null) {
            return [
                'ok' => false,
                'mensaje' => 'No se encontro el centro solicitado.'
            ];
        }

        return [
            'ok' => true,
            'mensaje' => 'Detalle del centro cargado correctamente.',
            'centro' => $centro
        ];
    }
}
