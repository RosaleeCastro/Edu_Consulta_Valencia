<?php

// Carga las constantes API_BASE_URL y RESOURCE_ID_CENTROS.
require_once __DIR__ . '/../../config/config.php';

class CentroEducativoApiService
{
    /**
     * Busca centros aplicando los filtros que llegan desde el formulario.
     */
    public function buscarCentros(string $localidad, string $provincia = '', string $regimen = '', string $tipoCentro = ''): array
    {
        // Primero se obtiene el dataset completo desde la API publica.
        $registros = $this->obtenerRegistros();

        // Despues se filtra y se transforma a la estructura que usa el frontend.
        return $this->filtrarYMapearCentros($registros, $localidad, $provincia, $regimen, $tipoCentro);
    }

    /**
     * Genera el catalogo de provincias y localidades reales del dataset.
     */
    public function obtenerUbicaciones(): array
    {
        $registros = $this->obtenerRegistros();
        $ubicaciones = [];

        foreach ($registros as $registro) {
            $provincia = trim($registro['provincia'] ?? '');
            $localidad = trim($registro['localidad'] ?? '');

            // Si falta provincia o localidad, el registro no sirve para los selects.
            if ($provincia === '' || $localidad === '') {
                continue;
            }

            // Se usa un array asociativo para evitar localidades duplicadas.
            if (!isset($ubicaciones[$provincia])) {
                $ubicaciones[$provincia] = [];
            }

            $ubicaciones[$provincia][$localidad] = true;
        }

        // Orden alfabetico de provincias.
        ksort($ubicaciones, SORT_NATURAL | SORT_FLAG_CASE);

        $resultado = [];

        foreach ($ubicaciones as $provincia => $localidades) {
            $listaLocalidades = array_keys($localidades);

            // Orden alfabetico de localidades dentro de cada provincia.
            sort($listaLocalidades, SORT_NATURAL | SORT_FLAG_CASE);

            $resultado[] = [
                'provincia' => $provincia,
                'localidades' => $listaLocalidades
            ];
        }

        return $resultado;
    }

    /**
     * Busca un centro concreto por su codigo oficial.
     */
    public function obtenerCentroPorCodigo(string $codigo): ?array
    {
        $codigoBuscado = trim($codigo);
        $registros = $this->obtenerRegistros();

        foreach ($registros as $registro) {
            // La ficha debe coincidir exactamente con el codigo recibido.
            if (($registro['codigo'] ?? '') !== $codigoBuscado) {
                continue;
            }

            return $this->mapearCentro($registro);
        }

        return null;
    }

    /**
     * Consulta la API externa y devuelve los registros del recurso.
     */
    private function obtenerRegistros(): array
    {
        // Se construye la URL de CKAN con el recurso de centros y un limite amplio.
        $url = API_BASE_URL . '?' . http_build_query([
            'resource_id' => RESOURCE_ID_CENTROS,
            'limit' => 5000
        ]);

        $respuestaApi = $this->hacerPeticion($url);

        // CKAN devuelve success=false si la consulta no ha funcionado.
        if (!$respuestaApi['success']) {
            return [];
        }

        return $respuestaApi['result']['records'] ?? [];
    }

    /**
     * Hace la peticion HTTP a la API externa.
     *
     * Primero intenta usar cURL. Si cURL no esta disponible o falla, usa
     * file_get_contents como segunda opcion.
     */
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

                // En XAMPP local a veces hay problemas con certificados.
                // Para un proyecto en produccion deberia validarse SSL.
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);

            $respuesta = curl_exec($curl);
            $ultimoError = curl_error($curl);

            curl_close($curl);
        }

        // Fallback si cURL no existe o no pudo obtener respuesta.
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
                    $mensaje .= ' cURL devolvio: ' . $ultimoError . '.';
                }

                $mensaje .= ' Revisa si la API remota esta disponible o si allow_url_fopen esta habilitado en PHP.';

                throw new Exception($mensaje);
            }
        }

        // Convierte el JSON recibido en un array asociativo de PHP.
        $datos = json_decode($respuesta, true);

        if (!is_array($datos)) {
            throw new Exception('La API externa no devolvio un JSON valido.');
        }

        return $datos;
    }

    /**
     * Aplica filtros sobre los registros originales y mapea los resultados.
     */
    private function filtrarYMapearCentros(array $registros, string $localidad, string $provincia, string $regimen, string $tipoCentro): array
    {
        $centros = [];

        // Se normalizan las busquedas para comparar sin depender de mayusculas.
        $localidadBuscada = $this->normalizarTexto($localidad);
        $provinciaBuscada = $this->normalizarTexto($provincia);
        $regimenBuscado = $this->normalizarRegimen($regimen);
        $tipoCentroBuscado = $this->normalizarTexto($tipoCentro);

        foreach ($registros as $registro) {
            $localidadApi = $registro['localidad'] ?? '';
            $provinciaApi = $registro['provincia'] ?? '';
            $regimenApi = $registro['regimen'] ?? '';

            // Para filtrar por tipo se combinan la denominacion generica y el nombre.
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

    /**
     * Convierte un registro original de la API en el formato propio del proyecto.
     */
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

        // Si el dataset no trae URL oficial, se construye una ficha oficial por codigo.
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

            // El frontend usa esta bandera para decidir si pinta marcador.
            'coordenadas_disponibles' => $latitud !== null && $latitud !== '' && $longitud !== null && $longitud !== '',
        ];
    }

    /**
     * Comprueba si un texto de la API contiene una busqueda ya normalizada.
     */
    private function contieneTextoNormalizado(string $texto, string $busqueda): bool
    {
        if ($busqueda === '') {
            return true;
        }

        return str_contains($this->normalizarTexto($texto), $busqueda);
    }

    /**
     * Traduce los valores del filtro de regimen a patrones del dataset.
     */
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

    /**
     * Relaciona filtros simples del formulario con textos posibles del dataset.
     */
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

    /**
     * Normaliza textos para comparar de forma flexible.
     *
     * Convierte a minusculas y elimina acentos cuando PHP puede transliterar.
     */
    private function normalizarTexto(string $texto): string
    {
        $texto = trim(mb_strtolower($texto, 'UTF-8'));
        $textoSinAcentos = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);

        if ($textoSinAcentos !== false) {
            $texto = $textoSinAcentos;
        }

        return $texto;
    }
}
