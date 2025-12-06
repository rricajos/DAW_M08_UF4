<?php
/**
 * rest_server.php - Servidor REST para Eduflow
 * 
 * Servicio REST que permite a coordinación consultar horarios y buscar asignaturas.
 * 
 * ENDPOINTS DISPONIBLES:
 * - GET /horarios          → Lista todos los horarios
 * - GET /horarios/{dia}    → Horarios de un día específico
 * - GET /asignaturas       → Lista todas las asignaturas
 * - GET /asignaturas?profesor=X → Busca asignaturas por profesor
 * 
 * RESPUESTAS:
 * - Formato JSON
 * - Códigos HTTP estándar (200, 400, 404, 405)
 * 
 * @package Eduflow
 * @subpackage REST
 * @author Ricard Penin Honrubia
 */

// ============================================================================
// CONFIGURACIÓN DE CABECERAS HTTP
// ============================================================================

// Tipo de contenido JSON con charset UTF-8
header("Content-Type: application/json; charset=UTF-8");

// Permitir acceso desde otros orígenes (CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Manejar preflight request de CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ============================================================================
// CONFIGURACIÓN
// ============================================================================

// Ruta al archivo XML de datos
$xmlFile = __DIR__ . "/../data/horarios.xml";

// ============================================================================
// FUNCIONES DE UTILIDAD
// ============================================================================

/**
 * Envía una respuesta JSON con código HTTP
 * 
 * @param array $data Datos a enviar
 * @param int $code Código HTTP (default 200)
 * @return void
 */
function enviarRespuesta($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Envía una respuesta de error
 * 
 * @param string $mensaje Mensaje de error
 * @param int $code Código HTTP de error
 * @return void
 */
function enviarError($mensaje, $code = 400)
{
    enviarRespuesta([
        "status" => "error",
        "code" => $code,
        "message" => $mensaje
    ], $code);
}

/**
 * Carga y valida el archivo XML
 * 
 * @return SimpleXMLElement Objeto XML parseado
 */
function cargarXML()
{
    global $xmlFile;

    if (!file_exists($xmlFile)) {
        enviarError("Archivo de datos no encontrado", 500);
    }

    $xml = simplexml_load_file($xmlFile);

    if ($xml === false) {
        enviarError("Error al parsear el archivo XML", 500);
    }

    return $xml;
}

// ============================================================================
// FUNCIONES DE NEGOCIO
// ============================================================================

/**
 * Obtiene todos los horarios o filtra por día de la semana
 * 
 * @param string|null $dia Día de la semana para filtrar (opcional)
 *                         Valores válidos: lunes, martes, miercoles, jueves, viernes
 * @return array Respuesta con los horarios encontrados
 * 
 * Ejemplo de uso:
 * - getHorarios()           → Todos los horarios
 * - getHorarios('lunes')    → Solo horarios del lunes
 */
function getHorarios($dia = null)
{
    $xml = cargarXML();
    $result = [];

    // Normalizar día a minúsculas para comparación
    $diaFiltro = $dia ? strtolower(trim($dia)) : null;

    // Iterar sobre todas las clases
    foreach ($xml->clase as $clase) {
        $diaClase = strtolower((string) $clase->dia);

        // Si se especificó día, filtrar
        if ($diaFiltro !== null && $diaClase !== $diaFiltro) {
            continue;
        }

        // Añadir clase al resultado
        $result[] = [
            'id' => (string) $clase->id,
            'asignatura' => (string) $clase->asignatura,
            'profesor' => (string) $clase->profesor,
            'dia' => (string) $clase->dia,
            'hora_inicio' => (string) $clase->hora_inicio,
            'hora_fin' => (string) $clase->hora_fin,
            'aula' => (string) $clase->aula
        ];
    }

    // Verificar si se encontraron resultados cuando se filtró por día
    if ($diaFiltro !== null && empty($result)) {
        enviarError("No hay clases programadas para el día: {$dia}", 404);
    }

    return [
        "status" => "success",
        "filtro" => $diaFiltro ? "dia={$diaFiltro}" : "todos",
        "count" => count($result),
        "data" => $result
    ];
}

/**
 * Obtiene todas las asignaturas únicas del sistema
 * 
 * @return array Lista de asignaturas sin duplicados
 */
function getAsignaturas()
{
    $xml = cargarXML();
    $asignaturas = [];
    $vistas = []; // Para evitar duplicados

    foreach ($xml->clase as $clase) {
        $nombre = (string) $clase->asignatura;

        // Evitar duplicados
        if (!in_array($nombre, $vistas)) {
            $vistas[] = $nombre;
            $asignaturas[] = [
                'nombre' => $nombre,
                'profesor' => (string) $clase->profesor
            ];
        }
    }

    return [
        "status" => "success",
        "count" => count($asignaturas),
        "asignaturas" => $asignaturas
    ];
}

/**
 * Busca asignaturas impartidas por un profesor específico
 * 
 * @param string $profesor Nombre (o parte del nombre) del profesor
 * @return array Asignaturas encontradas para ese profesor
 * 
 * La búsqueda es:
 * - Case-insensitive (no distingue mayúsculas/minúsculas)
 * - Parcial (busca si el texto está contenido en el nombre)
 */
function getAsignaturasPorProfesor($profesor)
{
    $xml = cargarXML();
    $result = [];

    // Normalizar búsqueda
    $busqueda = strtolower(trim($profesor));

    if (empty($busqueda)) {
        enviarError("Debe especificar un nombre de profesor", 400);
    }

    foreach ($xml->clase as $clase) {
        $nombreProfesor = strtolower((string) $clase->profesor);

        // Búsqueda parcial case-insensitive
        if (strpos($nombreProfesor, $busqueda) !== false) {
            $result[] = [
                'id' => (string) $clase->id,
                'nombre' => (string) $clase->asignatura,
                'profesor' => (string) $clase->profesor,
                'horario' => (string) $clase->dia . ' ' .
                    (string) $clase->hora_inicio . '-' .
                    (string) $clase->hora_fin,
                'aula' => (string) $clase->aula
            ];
        }
    }

    // Verificar si se encontraron resultados
    if (empty($result)) {
        enviarError("No se encontraron asignaturas para el profesor: {$profesor}", 404);
    }

    return [
        "status" => "success",
        "busqueda" => $profesor,
        "count" => count($result),
        "asignaturas" => $result
    ];
}

// ============================================================================
// ROUTER - PROCESAMIENTO DE PETICIONES
// ============================================================================

// Obtener método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Solo permitir método GET
if ($method !== 'GET') {
    enviarError("Método no permitido. Este servicio solo acepta GET.", 405);
}

// Obtener ruta de la petición
$pathInfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
$path = array_filter(explode('/', trim($pathInfo, '/')));
$path = array_values($path); // Reindexar

// Determinar recurso solicitado
$recurso = isset($path[0]) ? strtolower($path[0]) : '';
$parametro = isset($path[1]) ? $path[1] : null;

// Enrutar según el recurso
switch ($recurso) {

    case 'horarios':
        // GET /horarios o GET /horarios/{dia}
        enviarRespuesta(getHorarios($parametro));
        break;

    case 'asignaturas':
        // GET /asignaturas o GET /asignaturas?profesor=X
        if (isset($_GET['profesor']) && !empty($_GET['profesor'])) {
            enviarRespuesta(getAsignaturasPorProfesor($_GET['profesor']));
        } else {
            enviarRespuesta(getAsignaturas());
        }
        break;

    case '':
        // Raíz del servicio - mostrar documentación
        enviarRespuesta([
            "status" => "success",
            "servicio" => "Eduflow REST API",
            "version" => "1.0",
            "endpoints" => [
                [
                    "metodo" => "GET",
                    "ruta" => "/horarios",
                    "descripcion" => "Obtiene todos los horarios"
                ],
                [
                    "metodo" => "GET",
                    "ruta" => "/horarios/{dia}",
                    "descripcion" => "Horarios de un día específico (lunes, martes, etc.)"
                ],
                [
                    "metodo" => "GET",
                    "ruta" => "/asignaturas",
                    "descripcion" => "Lista todas las asignaturas"
                ],
                [
                    "metodo" => "GET",
                    "ruta" => "/asignaturas?profesor={nombre}",
                    "descripcion" => "Busca asignaturas por nombre de profesor"
                ]
            ]
        ]);
        break;

    default:
        // Recurso no reconocido
        enviarError(
            "Recurso no válido: '{$recurso}'. Use /horarios o /asignaturas",
            400
        );
}
?>