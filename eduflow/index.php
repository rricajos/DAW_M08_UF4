<?php
/**
 * index.php - Página principal de Eduflow
 * 
 * Punto de entrada de la aplicación. Redirige a los usuarios según su perfil
 * después de validar la sesión.
 * 
 * - Vistas según perfil de usuario
 * - Redirección post-login
 * 
 * @package Eduflow
 * @author Ricard Penin Honrubia
 */

require_once __DIR__ . '/includes/header.php';
verificarSesion();

// Mostrar cabecera
mostrarCabecera('Inicio');

$perfil = obtenerPerfil();
$usuario = obtenerUsuario();
?>

<div class="welcome-container">
    <h1>🎓 Bienvenido a Eduflow</h1>
    <p class="welcome-text">
        Hola, <strong><?= htmlspecialchars($usuario) ?></strong>.
        Has iniciado sesión como <strong><?= htmlspecialchars(ucfirst($perfil)) ?></strong>.
    </p>

    <?php if ($perfil === 'coordinacion'): ?>
        <!-- Panel para Coordinación -->
        <div class="dashboard-cards">
            <div class="card">
                <div class="card-icon">📅</div>
                <h3>Consultar Horarios</h3>
                <p>Ver el horario general de clases y filtrar por día de la semana.</p>
                <a href="/eduflow/rest/rest_client.php?accion=horarios" class="btn btn-primary">
                    Ver Horarios
                </a>
            </div>

            <div class="card">
                <div class="card-icon">👨‍🏫</div>
                <h3>Buscar por Profesor</h3>
                <p>Encontrar asignaturas impartidas por un profesor específico.</p>
                <a href="/eduflow/rest/rest_client.php" class="btn btn-secondary">
                    Buscar Profesor
                </a>
            </div>
        </div>

        <div class="info-box">
            <h4>📊 Servicios REST Disponibles</h4>
            <p>Como coordinador, tienes acceso al servicio REST que permite:</p>
            <ul>
                <li>Consultar el horario general de todas las clases</li>
                <li>Filtrar horarios por día de la semana</li>
                <li>Buscar asignaturas por nombre de profesor</li>
            </ul>
        </div>

    <?php elseif ($perfil === 'profesor'): ?>
        <!-- Panel para Profesor -->
        <div class="dashboard-cards">
            <div class="card">
                <div class="card-icon">📝</div>
                <h3>Mis Tareas</h3>
                <p>Ver, añadir y gestionar las tareas asignadas a los alumnos.</p>
                <a href="/eduflow/soap/soap_client.php" class="btn btn-primary">
                    Gestionar Tareas
                </a>
            </div>

            <div class="card">
                <div class="card-icon">➕</div>
                <h3>Nueva Tarea</h3>
                <p>Crear una nueva tarea con fecha de entrega y nivel de urgencia.</p>
                <a href="/eduflow/soap/soap_client.php#agregar" class="btn btn-secondary">
                    Añadir Tarea
                </a>
            </div>
        </div>

        <div class="info-box">
            <h4>📋 Servicio SOAP Disponible</h4>
            <p>Como profesor, tienes acceso al servicio SOAP que permite:</p>
            <ul>
                <li>Listar todas las tareas activas</li>
                <li>Añadir nuevas tareas (se asocia automáticamente portada_1.png)</li>
                <li>Eliminar tareas por asignatura</li>
            </ul>
        </div>

    <?php else: ?>
        <!-- Usuario sin perfil asignado -->
        <div class="alert alert-warning">
            <strong>⚠️ Atención:</strong> Tu cuenta no tiene un perfil asignado.
            Contacta con el administrador.
        </div>
    <?php endif; ?>

</div>


<?php mostrarPie(); ?>