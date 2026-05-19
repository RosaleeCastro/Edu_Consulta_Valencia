<?php

/**
 * Pagina de diagnostico de conectividad.
 *
 * Sirve para comprobar si el entorno local puede conectarse a la API externa
 * usando cURL y file_get_contents. Es util cuando XAMPP no consigue obtener
 * datos del portal de la Generalitat.
 */

require_once __DIR__ . '/../config/config.php';

// Endpoint base alternativo del portal de datos abiertos.
$endpointBase = 'https://dadesobertes.gva.es/va/api/3/action';

// Consulta SQL de prueba. Se mantiene para diagnosticar datastore_search_sql.
$sql = 'SELECT * FROM "' . RESOURCE_ID_CENTROS . '" LIMIT 1';

// URLs que se probaran desde PHP.
$pruebas = [
    'api_sql' => $endpointBase . '/datastore_search_sql?' . http_build_query([
        'sql' => $sql
    ]),
    'api_search' => $endpointBase . '/datastore_search?' . http_build_query([
        'resource_id' => RESOURCE_ID_CENTROS,
        'limit' => 1
    ]),
    'csv_directo' => 'https://dadesobertes.gva.es/dataset/68eb1d94-76d3-4305-8507-e1aab7717d0e/resource/' .
        RESOURCE_ID_CENTROS . '/download/centros-docentes-de-la-comunitat-valenciana.csv'
];

/**
 * Escapa valores para mostrarlos en HTML sin riesgo.
 */
function escaparHtml(mixed $valor): string
{
    if (is_bool($valor)) {
        $valor = $valor ? 'true' : 'false';
    } elseif ($valor === null) {
        $valor = 'null';
    } elseif (is_array($valor)) {
        $valor = json_encode($valor, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/**
 * Prueba una URL usando cURL.
 */
function probarConCurl(string $url): array
{
    if (!function_exists('curl_init')) {
        return [
            'disponible' => false,
            'ok' => false,
            'mensaje' => 'La extension cURL no esta habilitada.'
        ];
    }

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
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HEADER => true
    ]);

    $respuesta = curl_exec($curl);
    $error = curl_error($curl);
    $info = curl_getinfo($curl);
    $codigo = curl_errno($curl);

    curl_close($curl);

    if ($respuesta === false) {
        return [
            'disponible' => true,
            'ok' => false,
            'mensaje' => $error !== '' ? $error : 'Error desconocido con cURL.',
            'codigo_error' => $codigo,
            'info' => $info
        ];
    }

    // Cuando CURLOPT_HEADER esta activo, la respuesta trae cabeceras y cuerpo.
    $tamCabecera = $info['header_size'] ?? 0;
    $cabeceras = substr($respuesta, 0, $tamCabecera);
    $cuerpo = substr($respuesta, $tamCabecera);
    $json = json_decode($cuerpo, true);

    return [
        'disponible' => true,
        'ok' => true,
        'http_code' => $info['http_code'] ?? null,
        'cabeceras' => trim($cabeceras),
        'cuerpo_preview' => mb_substr($cuerpo, 0, 600),
        'json_valido' => is_array($json),
        'json_preview' => is_array($json) ? $json : null,
        'info' => $info
    ];
}

/**
 * Prueba una URL usando file_get_contents.
 */
function probarConFileGetContents(string $url): array
{
    $allowUrlFopen = filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN);

    if (!$allowUrlFopen) {
        return [
            'disponible' => false,
            'ok' => false,
            'mensaje' => 'allow_url_fopen esta deshabilitado en php.ini.'
        ];
    }

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
    $error = error_get_last();
    $cabeceras = $http_response_header ?? [];

    if ($respuesta === false) {
        return [
            'disponible' => true,
            'ok' => false,
            'mensaje' => $error['message'] ?? 'Error desconocido con file_get_contents.',
            'cabeceras' => $cabeceras
        ];
    }

    $json = json_decode($respuesta, true);

    return [
        'disponible' => true,
        'ok' => true,
        'cabeceras' => $cabeceras,
        'cuerpo_preview' => mb_substr($respuesta, 0, 600),
        'json_valido' => is_array($json),
        'json_preview' => is_array($json) ? $json : null
    ];
}

// Se agrupan los resultados para mostrarlos facilmente en la pagina.
$diagnostico = [
    'php_version' => PHP_VERSION,
    'curl_habilitado' => function_exists('curl_init'),
    'openssl_habilitado' => extension_loaded('openssl'),
    'mbstring_habilitado' => extension_loaded('mbstring'),
    'allow_url_fopen' => ini_get('allow_url_fopen'),
    'api_base_url' => API_BASE_URL,
    'urls_prueba' => $pruebas,
    'curl' => [
        'api_sql' => probarConCurl($pruebas['api_sql']),
        'api_search' => probarConCurl($pruebas['api_search']),
        'csv_directo' => probarConCurl($pruebas['csv_directo'])
    ],
    'file_get_contents' => [
        'api_sql' => probarConFileGetContents($pruebas['api_sql']),
        'api_search' => probarConFileGetContents($pruebas['api_search']),
        'csv_directo' => probarConFileGetContents($pruebas['csv_directo'])
    ]
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostico API</title>
    <style>
        /* Estilos simples solo para esta pagina de diagnostico. */
        body {
            margin: 0;
            padding: 32px;
            font-family: Arial, sans-serif;
            background: #f6f7fb;
            color: #1f2937;
        }

        .contenedor {
            max-width: 980px;
            margin: 0 auto;
        }

        .tarjeta {
            background: #ffffff;
            border: 1px solid #dbe2ea;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 18px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        .estado-ok {
            color: #0f766e;
            font-weight: 700;
        }

        .estado-error {
            color: #b91c1c;
            font-weight: 700;
        }

        code, pre {
            font-family: Consolas, monospace;
            font-size: 0.92rem;
        }

        pre {
            white-space: pre-wrap;
            word-break: break-word;
            background: #0f172a;
            color: #e2e8f0;
            padding: 14px;
            border-radius: 10px;
            overflow: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        td:first-child {
            width: 240px;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="contenedor">
        <div class="tarjeta">
            <h1>Diagnostico de conexion con la API externa</h1>
            <p>Esta pagina prueba la conectividad desde tu PHP/XAMPP hacia la API de la Generalitat.</p>
        </div>

        <div class="tarjeta">
            <h2>Resumen del entorno</h2>
            <table>
                <tr><td>Version de PHP</td><td><?= escaparHtml($diagnostico['php_version']) ?></td></tr>
                <tr><td>cURL habilitado</td><td><?= escaparHtml($diagnostico['curl_habilitado']) ?></td></tr>
                <tr><td>OpenSSL habilitado</td><td><?= escaparHtml($diagnostico['openssl_habilitado']) ?></td></tr>
                <tr><td>mbstring habilitado</td><td><?= escaparHtml($diagnostico['mbstring_habilitado']) ?></td></tr>
                <tr><td>allow_url_fopen</td><td><?= escaparHtml($diagnostico['allow_url_fopen']) ?></td></tr>
                <tr><td>API base</td><td><code><?= escaparHtml($diagnostico['api_base_url']) ?></code></td></tr>
                <tr><td>URLs de prueba</td><td><pre><?= escaparHtml($diagnostico['urls_prueba']) ?></pre></td></tr>
            </table>
        </div>

        <div class="tarjeta">
            <h2>Prueba con cURL</h2>
            <p>Se prueban tres rutas: SQL, busqueda simple y descarga directa del CSV.</p>
            <pre><?= escaparHtml($diagnostico['curl']) ?></pre>
        </div>

        <div class="tarjeta">
            <h2>Prueba con file_get_contents</h2>
            <p>Esto ayuda a distinguir si el problema esta en cURL o en toda la salida HTTPS desde PHP.</p>
            <pre><?= escaparHtml($diagnostico['file_get_contents']) ?></pre>
        </div>
    </div>
</body>
</html>
