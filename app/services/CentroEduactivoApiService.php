<?php

require_once __DIR__ . '/../../config/config.php';

class CentroEducativoApiService
{
    public function buscarCentros(string $localidad, string $provincia = '', string $regimen = ''): array
    {
        $url = API_BASE_URL . '?' . http_build_query([
            'resource_id' => RESOURCE_ID_CENTROS,
            'limit' => LIMITE_RESULTADOS,
            'q' => $localidad
        ]);

        $respuestaApi = $this->hacerPeticion($url);

        if (!$respuestaApi['success']) {
            return [];
        }

        $registros = $respuestaApi['result']['records'] ?? [];

        return $this->limpiarYFiltrarDatos($registros, $localidad, $provincia, $regimen);
    }

    private function hacerPeticion(string $url): array
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $respuesta = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($respuesta === false) {
            throw new Exception('Error al consultar la API externa: ' . $error);
        }

        $datos = json_decode($respuesta, true);

        if (!is_array($datos)) {
            throw new Exception('La API externa no devolvió un JSON válido.');
        }

        return $datos;
    }

    private function limpiarYFiltrarDatos(array $registros, string $localidad, string $provincia, string $regimen): array
    {
        $centros = [];

        foreach ($registros as $registro) {
            $localidadApi = $registro['localidad'] ?? '';
            $provinciaApi = $registro['provincia'] ?? '';
            $regimenApi = $registro['regimen'] ?? '';

            if (!$this->contieneTexto($localidadApi, $localidad)) {
                continue;
            }

            if ($provincia !== '' && !$this->contieneTexto($provinciaApi, $provincia)) {
                continue;
            }

            if ($regimen !== '' && !$this->contieneTexto($regimenApi, $regimen)) {
                continue;
            }

            $centros[] = [
                'nombre' => $registro['denominacion'] ?? 'Centro sin nombre',
                'tipo' => $registro['denominacion_generica_es'] ?? 'Tipo no disponible',
                'regimen' => $regimenApi ?: 'No indicado',
                'direccion' => trim(($registro['tipo_via'] ?? '') . ' ' . ($registro['direccion'] ?? '') . ' ' . ($registro['numero'] ?? '')),
                'localidad' => $localidadApi ?: 'No indicada',
                'provincia' => $provinciaApi ?: 'No indicada',
                'telefono' => $registro['telefono'] ?? 'No disponible',
                'url' => $registro['url_es'] ?? '#',
                'latitud' => $registro['latitud'] ?? null,
                'longitud' => $registro['longitud'] ?? null
            ];
        }

        return $centros;
    }

    private function contieneTexto(string $texto, string $busqueda): bool
    {
        return mb_stripos($texto, $busqueda) !== false;
    }
}