<?php

/**
 * Configuracion general del proyecto.
 *
 * Este archivo centraliza los valores que se usan en varias partes de la
 * aplicacion. Asi, si cambia la URL de la API o el recurso de datos, solo hay
 * que modificarlo aqui.
 */

// Nombre visible del proyecto.
define('NOMBRE_PROYECTO', 'EduConsulta Valencia');

// Endpoint CKAN de la Generalitat Valenciana usado para consultar recursos.
define('API_BASE_URL', 'https://dadesobertes.gva.es/api/3/action/datastore_search');

// Identificador del dataset/recurso de centros docentes.
define('RESOURCE_ID_CENTROS', '1aa53c3a-4639-41aa-ac85-d58254c428c0');

// Limite interno previsto para resultados si se necesita en futuras mejoras.
define('LIMITE_RESULTADOS', 40);

?>
