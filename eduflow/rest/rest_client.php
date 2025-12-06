<?php
/**
 * rest_client.php - Cliente REST con interfaz web para Eduflow
 * 
 */

require_once __DIR__ . '/../includes/header.php';
verificarSesion();

$baseUrl = "http://localhost/eduflow/rest/rest_server.php";
$resultado = null;
$error = null;
$tipoConsulta = null;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['accion'])) {
    $accion = $_GET['accion'];
    $tipoConsulta = $accion;

    try {
        switch ($accion) {
            case 'horarios':
                $dia = isset($_GET['dia']) && !empty($_GET['dia']) ? '/' . urlencode($_GET['dia']) : '';
                $url = $baseUrl . "/horarios" . $dia;
                break;
            case 'profesor':
                $profesor = isset($_GET['profesor']) ? trim($_GET['profesor']) : '';
                if (empty($profesor))
                    throw new Exception("Debe introducir el nombre del profesor");
                $url = $baseUrl . "/asignaturas?profesor=" . urlencode($profesor);
                break;
            default:
                throw new Exception("Acción no válida");
        }

        $response = @file_get_contents($url);
        if ($response === false)
            throw new Exception("No se pudo conectar con el servicio REST.");

        $resultado = json_decode($response, true);
        if (isset($resultado['status']) && $resultado['status'] === 'error') {
            $error = $resultado['message'];
            $resultado = null;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

mostrarCabecera('Consulta de Horarios');
?>

<h1>📅 Consulta de Horarios y Asignaturas</h1>

<section class="search-section">
    <h2>🗓️ Ver Horarios</h2>
    <form method="GET" action="">
        <input type="hidden" name="accion" value="horarios">
        <div class="form-group">
            <label for="dia">Día:</label>
            <select name="dia" id="dia">
                <option value="">Todos los días</option>
                <option value="lunes">Lunes</option>
                <option value="martes">Martes</option>
                <option value="miercoles">Miércoles</option>
                <option value="jueves">Jueves</option>
                <option value="viernes">Viernes</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Consultar</button>
    </form>
</section>

<section class="search-section">
    <h2>👨‍🏫 Buscar por Profesor</h2>
    <form method="GET" action="">
        <input type="hidden" name="accion" value="profesor">
        <div class="form-group">
            <label for="profesor">Nombre del profesor:</label>
            <input type="text" name="profesor" id="profesor" placeholder="Ej: María García" required>
        </div>
        <button type="submit" class="btn btn-primary">Buscar</button>
    </form>
</section>

<section class="results-section">
    <?php if ($error): ?>
        <div class="alert alert-error"><strong>Error:</strong> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($resultado && isset($resultado['data'])): ?>
        <h2>Resultados (<?= $resultado['count'] ?> encontrados)</h2>
        <table class="results-table">
            <thead>
                <tr>
                    <th>Asignatura</th>
                    <th>Profesor</th>
                    <th>Día</th>
                    <th>Horario</th>
                    <th>Aula</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultado['data'] as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['asignatura']) ?></td>
                        <td><?= htmlspecialchars($item['profesor']) ?></td>
                        <td><?= htmlspecialchars($item['dia']) ?></td>
                        <td><?= $item['hora_inicio'] ?> - <?= $item['hora_fin'] ?></td>
                        <td><?= htmlspecialchars($item['aula']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($resultado && isset($resultado['asignaturas'])): ?>
        <h2>Asignaturas de <?= htmlspecialchars($resultado['busqueda']) ?> (<?= $resultado['count'] ?>)</h2>
        <table class="results-table">
            <thead>
                <tr>
                    <th>Asignatura</th>
                    <th>Horario</th>
                    <th>Aula</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultado['asignaturas'] as $asig): ?>
                    <tr>
                        <td><?= htmlspecialchars($asig['nombre']) ?></td>
                        <td><?= htmlspecialchars($asig['horario']) ?></td>
                        <td><?= htmlspecialchars($asig['aula']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php mostrarPie(); ?>