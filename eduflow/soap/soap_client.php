<?php
/**
 * soap_client.php - Cliente SOAP con interfaz web para Eduflow
 * 
 * Interfaz web que permite al profesor gestionar tareas consumiendo el servicio SOAP.
 * 
 * @package Eduflow
 * @subpackage SOAP
 * @author Ricard Penin Honrubia
 */

require_once __DIR__ . '/../includes/header.php';
verificarSesion();

// Variables para mensajes
$mensaje = null;
$tipoMensaje = 'info';
$tareas = [];

// Configuración del cliente SOAP
$soapOptions = [
    'location' => 'http://localhost/eduflow/soap/soap_server.php',
    'uri' => 'http://localhost/eduflow/soap/',
    'trace' => true,
    'exceptions' => true
];

try {
    // Crear cliente SOAP
    $client = new SoapClient(null, $soapOptions);

    // ========================================================================
    // PROCESAR ACCIONES POST
    // ========================================================================

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
        $accion = $_POST['accion'];

        switch ($accion) {
            case 'agregar':
                // Obtener datos del formulario
                $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
                $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
                $fecha_entrega = isset($_POST['fecha_entrega']) ? $_POST['fecha_entrega'] : '';
                $asignatura = isset($_POST['asignatura']) ? trim($_POST['asignatura']) : '';
                $urgencia = isset($_POST['urgencia']) ? $_POST['urgencia'] : 'media';

                // Validar campos obligatorios
                if (empty($titulo) || empty($asignatura)) {
                    $mensaje = "Error: Título y asignatura son obligatorios";
                    $tipoMensaje = 'error';
                } else {
                    // Llamar al servicio SOAP
                    $resultado = $client->agregarTarea(
                        $titulo,
                        $descripcion,
                        $fecha_entrega,
                        $asignatura,
                        $urgencia
                    );
                    $mensaje = $resultado;
                    $tipoMensaje = (strpos($resultado, 'Error') === false) ? 'success' : 'error';
                }
                break;

            case 'eliminar':
                $asignatura = isset($_POST['asignatura_eliminar']) ? trim($_POST['asignatura_eliminar']) : '';

                if (empty($asignatura)) {
                    $mensaje = "Error: Debe especificar una asignatura";
                    $tipoMensaje = 'error';
                } else {
                    $resultado = $client->eliminarTarea($asignatura);
                    $mensaje = $resultado;
                    $tipoMensaje = (strpos($resultado, 'Error') === false && strpos($resultado, 'No se encontraron') === false)
                        ? 'success' : 'warning';
                }
                break;
        }
    }

    // ========================================================================
    // OBTENER LISTA DE TAREAS
    // ========================================================================

    $tareas = $client->listarTareas();

    // Verificar si es un error
    if (is_array($tareas) && isset($tareas['error'])) {
        $mensaje = $tareas['error'];
        $tipoMensaje = 'error';
        $tareas = [];
    }

} catch (SoapFault $e) {
    $mensaje = "Error SOAP: " . $e->getMessage();
    $tipoMensaje = 'error';
} catch (Exception $e) {
    $mensaje = "Error: " . $e->getMessage();
    $tipoMensaje = 'error';
}

// Mostrar cabecera
mostrarCabecera('Gestión de Tareas');
?>

<h1>📝 Gestión de Tareas</h1>
<p class="page-description">Panel del profesor para gestionar tareas académicas mediante servicio SOAP.</p>

<?php if ($mensaje): ?>
    <div class="alert alert-<?= $tipoMensaje ?>">
        <?= htmlspecialchars($mensaje) ?>
    </div>
<?php endif; ?>

<!-- ======================================================================
     SECCIÓN: LISTA DE TAREAS
     ====================================================================== -->
<section class="search-section">
    <h2>📋 Tareas Actuales</h2>

    <?php if (!empty($tareas) && is_array($tareas)): ?>
        <table class="results-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Asignatura</th>
                    <th>Fecha Entrega</th>
                    <th>Urgencia</th>
                    <th>Fichero</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tareas as $tarea): ?>
                    <tr class="urgencia-<?= htmlspecialchars($tarea['urgencia']) ?>">
                        <td><?= htmlspecialchars($tarea['id']) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($tarea['titulo']) ?></strong>
                            <?php if (!empty($tarea['descripcion'])): ?>
                                <br><small><?= htmlspecialchars(substr($tarea['descripcion'], 0, 50)) ?>...</small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($tarea['asignatura']) ?></td>
                        <td><?= htmlspecialchars($tarea['fecha_entrega']) ?></td>
                        <td>
                            <span class="badge badge-<?= $tarea['urgencia'] ?>">
                                <?= ucfirst(htmlspecialchars($tarea['urgencia'])) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($tarea['fichero_adjunto']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-results">No hay tareas registradas.</p>
    <?php endif; ?>
</section>

<!-- ======================================================================
     SECCIÓN: AGREGAR NUEVA TAREA
     ====================================================================== -->
<section class="search-section">
    <h2>➕ Agregar Nueva Tarea</h2>
    <p class="section-note">
        <em>Nota: Todas las tareas se asocian automáticamente con el fichero portada_1.png</em>
    </p>

    <form method="POST" action="" class="form-vertical">
        <input type="hidden" name="accion" value="agregar">

        <div class="form-row">
            <div class="form-group">
                <label for="titulo">Título: *</label>
                <input type="text" name="titulo" id="titulo" required placeholder="Ej: Práctica PHP">
            </div>

            <div class="form-group">
                <label for="asignatura">Asignatura: *</label>
                <input type="text" name="asignatura" id="asignatura" required
                    placeholder="Ej: Desarrollo Web Entorno Servidor">
            </div>
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción:</label>
            <textarea name="descripcion" id="descripcion" rows="3"
                placeholder="Descripción detallada de la tarea..."></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="fecha_entrega">Fecha de entrega:</label>
                <input type="date" name="fecha_entrega" id="fecha_entrega">
            </div>

            <div class="form-group">
                <label for="urgencia">Urgencia:</label>
                <select name="urgencia" id="urgencia">
                    <option value="baja">🟢 Baja</option>
                    <option value="media" selected>🟡 Media</option>
                    <option value="alta">🔴 Alta</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            ➕ Agregar Tarea
        </button>
    </form>
</section>

<!-- ======================================================================
     SECCIÓN: ELIMINAR TAREAS
     ====================================================================== -->
<section class="search-section">
    <h2>🗑️ Eliminar Tareas por Asignatura</h2>
    <p class="section-note">
        <em>Advertencia: Se eliminarán TODAS las tareas de la asignatura indicada.</em>
    </p>

    <form method="POST" action="" class="form-inline">
        <input type="hidden" name="accion" value="eliminar">

        <div class="form-group">
            <label for="asignatura_eliminar">Asignatura:</label>
            <input type="text" name="asignatura_eliminar" id="asignatura_eliminar" required
                placeholder="Nombre exacto de la asignatura">
        </div>

        <button type="submit" class="btn btn-danger"
            onclick="return confirm('¿Está seguro de eliminar todas las tareas de esta asignatura?')">
            🗑️ Eliminar Tareas
        </button>
    </form>
</section>

<?php mostrarPie(); ?>