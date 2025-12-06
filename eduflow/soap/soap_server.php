<?php
/**
 * soap_server.php - Servidor SOAP para gestión de tareas en Eduflow
 * 
 * Servicio SOAP que permite al profesor gestionar tareas académicas:
 * listar, añadir y eliminar tareas.
 * 
 * MÉTODOS DISPONIBLES:
 * - listarTareas()                                    → Lista todas las tareas
 * - agregarTarea($titulo, $desc, $fecha, $asig, $urg) → Añade nueva tarea
 * - eliminarTarea($asignatura)                        → Elimina tareas por asignatura
 * 
 * @package Eduflow
 * @subpackage SOAP
 * @author Ricard Penin Honrubia
 */

/**
 * Clase TareasService
 * 
 * Expone métodos SOAP para la gestión de tareas académicas.
 * Todas las operaciones se realizan sobre el archivo tareas.xml
 * 
 * REQUISITO ESPECIAL:
 * Todas las tareas nuevas se asocian automáticamente con portada_1.png
 */
class TareasService
{

    /**
     * @var string Ruta al archivo XML de tareas
     */
    private $xmlFile;

    /**
     * @var string Fichero que se asocia automáticamente a nuevas tareas
     */
    private $ficheroDefecto = "portada_1.png";

    /**
     * Constructor
     * Inicializa la ruta al archivo XML de tareas
     */
    public function __construct()
    {
        $this->xmlFile = __DIR__ . "/../data/tareas.xml";
    }

    /**
     * Lista todas las tareas del sistema
     * 
     * @return array Array asociativo con todas las tareas
     *               Cada tarea contiene: id, titulo, descripcion, fecha_entrega,
     *               asignatura, urgencia, fichero_adjunto
     * 
     * Ejemplo de uso desde cliente SOAP:
     * $tareas = $client->listarTareas();
     */
    public function listarTareas()
    {
        // Verificar que existe el archivo
        if (!file_exists($this->xmlFile)) {
            return ["error" => "Archivo de tareas no encontrado"];
        }

        $xml = simplexml_load_file($this->xmlFile);

        if ($xml === false) {
            return ["error" => "Error al leer el archivo de tareas"];
        }

        $result = [];

        foreach ($xml->tarea as $tarea) {
            $result[] = [
                'id' => (string) $tarea->id,
                'titulo' => (string) $tarea->titulo,
                'descripcion' => (string) $tarea->descripcion,
                'fecha_entrega' => (string) $tarea->fecha_entrega,
                'asignatura' => (string) $tarea->asignatura,
                'urgencia' => (string) $tarea->urgencia,
                'fichero_adjunto' => (string) $tarea->fichero_adjunto
            ];
        }

        return $result;
    }

    /**
     * Agrega una nueva tarea al sistema
     * 
     * IMPORTANTE: Automáticamente asocia el fichero portada_1.png a la tarea
     * según lo especificado en los requisitos de la actividad.
     * 
     * @param string $titulo        Título de la tarea (obligatorio)
     * @param string $descripcion   Descripción detallada de la tarea
     * @param string $fecha_entrega Fecha límite en formato YYYY-MM-DD
     * @param string $asignatura    Nombre de la asignatura (obligatorio)
     * @param string $urgencia      Nivel de urgencia: 'baja', 'media' o 'alta'
     * 
     * @return string Mensaje de confirmación o error
     * 
     * Ejemplo de uso desde cliente SOAP:
     * $resultado = $client->agregarTarea(
     *     "Práctica PHP",
     *     "Implementar servicio REST",
     *     "2024-12-15",
     *     "Desarrollo Web",
     *     "alta"
     * );
     */
    public function agregarTarea($titulo, $descripcion, $fecha_entrega, $asignatura, $urgencia)
    {
        // ================================================================
        // VALIDACIÓN DE PARÁMETROS
        // ================================================================

        // Campos obligatorios
        if (empty(trim($titulo))) {
            return "Error: El título es obligatorio";
        }

        if (empty(trim($asignatura))) {
            return "Error: La asignatura es obligatoria";
        }

        // Validar urgencia
        $urgenciasValidas = ['baja', 'media', 'alta'];
        $urgencia = strtolower(trim($urgencia));
        if (!in_array($urgencia, $urgenciasValidas)) {
            $urgencia = 'media'; // Valor por defecto
        }

        // Validar formato de fecha
        if (!empty($fecha_entrega) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_entrega)) {
            return "Error: Formato de fecha inválido. Use YYYY-MM-DD";
        }

        // ================================================================
        // CARGAR XML Y GENERAR NUEVO ID
        // ================================================================

        $xml = simplexml_load_file($this->xmlFile);

        if ($xml === false) {
            return "Error: No se pudo leer el archivo de tareas";
        }

        // Calcular nuevo ID (máximo actual + 1)
        $maxId = 0;
        foreach ($xml->tarea as $t) {
            $id = (int) $t->id;
            if ($id > $maxId) {
                $maxId = $id;
            }
        }
        $nuevoId = $maxId + 1;

        // ================================================================
        // CREAR NUEVA TAREA
        // ================================================================

        $nuevaTarea = $xml->addChild('tarea');
        $nuevaTarea->addChild('id', $nuevoId);
        $nuevaTarea->addChild('titulo', htmlspecialchars(trim($titulo)));
        $nuevaTarea->addChild('descripcion', htmlspecialchars(trim($descripcion)));
        $nuevaTarea->addChild('fecha_entrega', $fecha_entrega);
        $nuevaTarea->addChild('asignatura', htmlspecialchars(trim($asignatura)));
        $nuevaTarea->addChild('urgencia', $urgencia);

        // REQUISITO CRÍTICO: Asociar automáticamente portada_1.png
        $nuevaTarea->addChild('fichero_adjunto', $this->ficheroDefecto);

        // ================================================================
        // GUARDAR CAMBIOS
        // ================================================================

        $guardado = $xml->asXML($this->xmlFile);

        if ($guardado === false) {
            return "Error: No se pudo guardar la tarea";
        }

        return "Tarea '{$titulo}' añadida correctamente con ID: {$nuevoId}. " .
            "Fichero adjunto: {$this->ficheroDefecto}";
    }

    /**
     * Elimina todas las tareas de una asignatura específica
     * 
     * @param string $asignatura Nombre exacto de la asignatura
     * 
     * @return string Mensaje indicando el número de tareas eliminadas
     * 
     * Ejemplo de uso desde cliente SOAP:
     * $resultado = $client->eliminarTarea("Desarrollo Web");
     */
    public function eliminarTarea($asignatura)
    {
        // Validar parámetro
        if (empty(trim($asignatura))) {
            return "Error: Debe especificar una asignatura";
        }

        $xml = simplexml_load_file($this->xmlFile);

        if ($xml === false) {
            return "Error: No se pudo leer el archivo de tareas";
        }

        $asignaturaBusqueda = trim($asignatura);
        $eliminadas = 0;
        $indices = [];

        // ================================================================
        // BUSCAR TAREAS A ELIMINAR
        // ================================================================

        $i = 0;
        foreach ($xml->tarea as $tarea) {
            if ((string) $tarea->asignatura === $asignaturaBusqueda) {
                $indices[] = $i;
                $eliminadas++;
            }
            $i++;
        }

        // ================================================================
        // ELIMINAR TAREAS (en orden inverso para mantener índices válidos)
        // ================================================================

        if ($eliminadas > 0) {
            rsort($indices); // Ordenar de mayor a menor
            foreach ($indices as $index) {
                unset($xml->tarea[$index]);
            }

            // Guardar cambios
            $guardado = $xml->asXML($this->xmlFile);

            if ($guardado === false) {
                return "Error: No se pudieron guardar los cambios";
            }

            $plural = $eliminadas > 1 ? 's' : '';
            return "{$eliminadas} tarea{$plural} de '{$asignaturaBusqueda}' eliminada{$plural} correctamente";
        }

        return "No se encontraron tareas para la asignatura: {$asignaturaBusqueda}";
    }

    /**
     * Obtiene una tarea específica por su ID
     * 
     * @param int $id ID de la tarea
     * @return array|string Datos de la tarea o mensaje de error
     */
    public function obtenerTarea($id)
    {
        $xml = simplexml_load_file($this->xmlFile);

        foreach ($xml->tarea as $tarea) {
            if ((int) $tarea->id === (int) $id) {
                return [
                    'id' => (string) $tarea->id,
                    'titulo' => (string) $tarea->titulo,
                    'descripcion' => (string) $tarea->descripcion,
                    'fecha_entrega' => (string) $tarea->fecha_entrega,
                    'asignatura' => (string) $tarea->asignatura,
                    'urgencia' => (string) $tarea->urgencia,
                    'fichero_adjunto' => (string) $tarea->fichero_adjunto
                ];
            }
        }

        return "Tarea con ID {$id} no encontrada";
    }
}

// ============================================================================
// CONFIGURACIÓN DEL SERVIDOR SOAP
// ============================================================================

// Opciones del servidor SOAP (sin WSDL - modo simple)
$options = [
    'uri' => 'http://localhost/eduflow/soap/'
];

// Crear instancia del servidor SOAP
$server = new SoapServer(null, $options);

// Registrar la clase de servicio
$server->setClass('TareasService');

// Procesar peticiones SOAP entrantes
$server->handle();
?>