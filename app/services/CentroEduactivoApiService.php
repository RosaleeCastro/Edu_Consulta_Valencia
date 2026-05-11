<?php

require_once __DIR__ . '/../../config/config.php';

class CentroEducativoApiService
{
    public function buscarCentros(string $localidad, string $provincia = '', string $regimen = '', string $tipoCentro = ''): array
    {
        $registros = $this->obtenerRegistros();

        return $this->filtrarYMapearCentros($registros, $localidad, $provincia, $regimen, $tipoCentro);
    }

    public function obtenerUbicaciones(): array
    {
        $registros = $this->obtenerRegistros();
        $ubicaciones = [];

        foreach ($registros as $registro) {
            $provincia = trim($registro['provincia'] ?? '');
            $localidad = trim($registro['localidad'] ?? '');

            if ($provincia === '' || $localidad === '') {
                continue;
            }

            if (!isset($ubicaciones[$provincia])) {
                $ubicaciones[$provincia] = [];
            }

            $ubicaciones[$provincia][$localidad] = true;
        }

        ksort($ubicaciones, SORT_NATURAL | SORT_FLAG_CASE);

        $resultado = [];

        foreach ($ubicaciones as $provincia => $localidades) {
            $listaLocalidades = array_keys($localidades);
            sort($listaLocalidades, SORT_NATURAL | SORT_FLAG_CASE);
            $resultado[] = [
                'provincia' => $provincia,
                'localidades' => $listaLocalidades
            ];
        }

        return $resultado;
    }

    public function obtenerCentroPorCodigo(string $codigo): ?array
    {
        $codigoBuscado = trim($codigo);
        $registros = $this->obtenerRegistros();

        foreach ($registros as $registro) {
            if (($registro['codigo'] ?? '') !== $codigoBuscado) {
                continue;
            }

            return $this->mapearCentro($registro);
        }

        return null;
    }

    private function obtenerRegistros(): array
    {
        $url = API_BASE_URL . '?' . http_build_query([
            'resource_id' => RESOURCE_ID_CENTROS,
            'limit' => 5000
        ]);

        $respuestaApi = $this->hacerPeticion($url);

        if (!$respuestaApi['success']) {
            return [];
        }

        return $respuestaApi['result']['records'] ?? [];
    }

    private function hacerPeticion(string $url): array
    {
        $respuesta = false;
        $ultimoError = '';

        if (function_exists('curl_init')) {
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTPGET => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_ENCODING => '',
                CURLOPT_USERAGENT => 'EduConsultaValencia/1.0',
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);

            $respuesta = curl_exec($curl);
            $ultimoError = curl_error($curl);

            curl_close($curl);
        }

        if ($respuesta === false) {
            $contexto = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 15,
                    'header' => "User-Agent: EduConsultaValencia/1.0\r\n"
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);

            $respuesta = @file_get_contents($url, false, $contexto);

            if ($respuesta === false) {
                $mensaje = 'No se pudo consultar la API externa.';

                if ($ultimoError !== '') {
                    $mensaje .= ' cURL devolvió: ' . $ultimoError . '.';
                }

                $mensaje .= ' Revisa si la API remota está disponible o si allow_url_fopen está habilitado en PHP.';

                throw new Exception($mensaje);
            }
        }

        $datos = json_decode($respuesta, true);

        if (!is_array($datos)) {
            throw new Exception('La API externa no devolvió un JSON válido.');
        }

        return $datos;
    }

    private function filtrarYMapearCentros(array $registros, string $localidad, string $provincia, string $regimen, string $tipoCentro): array
    {
        $centros = [];
        $localidadBuscada = $this->normalizarTexto($localidad);
        $provinciaBuscada = $this->normalizarTexto($provincia);
        $regimenBuscado = $this->normalizarRegimen($regimen);
        $tipoCentroBuscado = $this->normalizarTexto($tipoCentro);

        foreach ($registros as $registro) {
            $localidadApi = $registro['localidad'] ?? '';
            $provinciaApi = $registro['provincia'] ?? '';
            $regimenApi = $registro['regimen'] ?? '';
            $tipoCentroApi = trim(($registro['denominacion_generica_es'] ?? '') . ' ' . ($registro['denominacion'] ?? ''));

            if (!$this->contieneTextoNormalizado($localidadApi, $localidadBuscada)) {
                continue;
            }

            if ($provinciaBuscada !== '' && !$this->contieneTextoNormalizado($provinciaApi, $provinciaBuscada)) {
                continue;
            }

            if ($regimenBuscado !== '' && !$this->coincideRegimen($regimenApi, $regimenBuscado)) {
                continue;
            }

            if ($tipoCentroBuscado !== '' && !$this->coincideTipoCentro($tipoCentroApi, $tipoCentroBuscado)) {
                continue;
            }

            $centros[] = $this->mapearCentro($registro);
        }

        return $centros;
    }

    private function mapearCentro(array $registro): array
    {
        $tipoVia = trim($registro['tipo_via'] ?? '');
        $direccion = trim($registro['direccion'] ?? '');
        $numero = trim($registro['numero'] ?? '');
        $direccionCompleta = trim($tipoVia . ' ' . $direccion . ' ' . $numero);
        $latitud = $registro['latitud'] ?? null;
        $longitud = $registro['longitud'] ?? null;
        $codigo = trim($registro['codigo'] ?? '');
        $urlOficial = trim($registro['url_es'] ?? '');

        if ($urlOficial === '' && $codigo !== '') {
            $urlOficial = 'https://www.ceice.gva.es/web/centros-docentes/ficha-centro?codi=' . rawurlencode($codigo);
        }

        return [
            'codigo' => $codigo,
            'nombre' => $registro['denominacion'] ?? 'Centro sin nombre',
            'tipo' => $registro['denominacion_generica_es'] ?? 'Tipo no disponible',
            'tipo_valenciano' => $registro['denominacion_generica_val'] ?? '',
            'regimen' => ($registro['regimen'] ?? '') ?: 'No indicado',
            'direccion' => $direccionCompleta !== '' ? $direccionCompleta : 'No disponible',
            'tipo_via' => $tipoVia,
            'numero' => $numero,
            'codigo_postal' => ($registro['codigo_postal'] ?? '') ?: 'No disponible',
            'localidad' => ($registro['localidad'] ?? '') ?: 'No indicada',
            'provincia' => ($registro['provincia'] ?? '') ?: 'No indicada',
            'telefono' => ($registro['telefono'] ?? '') ?: 'No disponible',
            'fax' => ($registro['fax'] ?? '') ?: 'No disponible',
            'titular' => ($registro['titular'] ?? '') ?: 'No indicado',
            'cif' => ($registro['cif'] ?? '') ?: 'No indicado',
            'comarca' => ($registro['comarca'] ?? '') ?: 'No indicada',
            'latitud' => $latitud,
            'longitud' => $longitud,
            'url' => $urlOficial !== '' ? $urlOficial : '#',
            'url_valenciano' => ($registro['url_va'] ?? '') ?: '#',
            'fecha_constitucion' => ($registro['fe_constitucion'] ?? '') ?: 'No disponible',
            'coordenadas_disponibles' => $latitud !== null && $latitud !== '' && $longitud !== null && $longitud !== '',
        ];
    }

    private function contieneTextoNormalizado(string $texto, string $busqueda): bool
    {
        if ($busqueda === '') {
            return true;
        }

        return str_contains($this->normalizarTexto($texto), $busqueda);
    }

    private function coincideRegimen(string $regimenApi, string $regimenBuscado): bool
    {
        $regimenApiNormalizado = $this->normalizarTexto($regimenApi);

        return match ($regimenBuscado) {
            'publico' => str_contains($regimenApiNormalizado, 'pub'),
            'privado concertado' => str_contains($regimenApiNormalizado, 'conc'),
            'privado' => str_contains($regimenApiNormalizado, 'priv'),
            default => str_contains($regimenApiNormalizado, $regimenBuscado),
        };
    }

    private function normalizarRegimen(string $regimen): string
    {
        return $this->normalizarTexto($regimen);
    }

    private function coincideTipoCentro(string $tipoCentroApi, string $tipoCentroBuscado): bool
    {
        $tipoApiNormalizado = $this->normalizarTexto($tipoCentroApi);
        $mapaTipos = [
            'infantil' => ['infantil', 'primer ciclo de educacion infantil', 'segundo ciclo de educacion infantil'],
            'primaria' => ['primaria', 'educacion primaria', 'colegio de educacion infantil y primaria', 'ceip'],
            'secundaria' => ['secundaria', 'educacion secundaria', 'ies', 'instituto de educacion secundaria'],
            'bachillerato' => ['bachillerato', 'bach', 'ies', 'instituto de educacion secundaria'],
            'fp' => ['formacion profesional', 'fp', 'ciclos formativos', 'integrado de formacion profesional', 'ies', 'instituto de educacion secundaria'],
            'bach_fp' => ['bachillerato', 'bach', 'formacion profesional', 'fp', 'ciclos formativos', 'integrado de formacion profesional', 'ies', 'instituto de educacion secundaria'],
            'universidad' => ['universidad', 'universitari'],
            'adultos' => ['adultos', 'personas adultas', 'fpa'],
            'especial' => ['especial', 'educacion especial'],
            'idiomas' => ['idiomas', 'escuela oficial de idiomas', 'eoi'],
            'musica' => ['musica', 'danza', 'conservatorio'],
        ];

        $patrones = $mapaTipos[$tipoCentroBuscado] ?? [$tipoCentroBuscado];

        foreach ($patrones as $patron) {
            if (str_contains($tipoApiNormalizado, $patron)) {
                return true;
            }
        }

        return false;
    }

    private function normalizarTexto(string $texto): string
    {
        $texto = trim(mb_strtolower($texto, 'UTF-8'));
        $reemplazos = [
            'á' => 'a',
            'à' => 'a',
            'ä' => 'a',
            'â' => 'a',
            'é' => 'e',
            'è' => 'e',
            'ë' => 'e',
            'ê' => 'e',
            'í' => 'i',
            'ì' => 'i',
            'ï' => 'i',
            'î' => 'i',
            'ó' => 'o',
            'ò' => 'o',
            'ö' => 'o',
            'ô' => 'o',
            'ú' => 'u',
            'ù' => 'u',
            'ü' => 'u',
            'û' => 'u',
            'ñ' => 'n',
            'ç' => 'c',
        ];

        return strtr($texto, $reemplazos);
    }
}
