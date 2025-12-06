<?php
/**
 * header.php - Cabecera común y gestión de sesión para Eduflow
 * 
 * CRITERIOS CUBIERTOS:
 * - Cabecera con nombre y colores de la aplicación (include en cada página)
 * - Sesión con caducidad de 30 minutos
 * - Vistas según perfil de usuario (coordinación/profesor)
 * 
 * @package Eduflow
 * @author Ricard Penin Honrubia
 */

// Tiempo de sesión en segundos (30 minutos = 1800 segundos)
define('SESSION_TIMEOUT', 1800);

/**
 * Verifica la sesión del usuario y controla la caducidad
 * Redirige al login si no hay sesión activa o ha expirado
 * 
 * @return void
 */
function verificarSesion()
{
    // Iniciar sesión si no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Verificar si existe sesión de usuario
    if (!isset($_SESSION['usuario'])) {
        header('Location: /eduflow/login.php');
        exit;
    }

    // Verificar caducidad de sesión (30 minutos)
    if (isset($_SESSION['ultimo_acceso'])) {
        $tiempoInactivo = time() - $_SESSION['ultimo_acceso'];

        if ($tiempoInactivo > SESSION_TIMEOUT) {
            // Sesión expirada - limpiar y redirigir
            session_unset();
            session_destroy();
            header('Location: /eduflow/login.php?error=sesion_expirada');
            exit;
        }
    }

    // Actualizar tiempo de último acceso
    $_SESSION['ultimo_acceso'] = time();
}

/**
 * Verifica si el usuario tiene un perfil específico
 * 
 * @param string $perfil Perfil requerido ('coordinacion' o 'profesor')
 * @return bool True si el usuario tiene el perfil indicado
 */
function tienePermiso($perfil)
{
    return isset($_SESSION['perfil']) && $_SESSION['perfil'] === $perfil;
}

/**
 * Obtiene el nombre del usuario actual
 * 
 * @return string Nombre del usuario o 'Invitado'
 */
function obtenerUsuario()
{
    return $_SESSION['usuario'] ?? 'Invitado';
}

/**
 * Obtiene el perfil del usuario actual
 * 
 * @return string Perfil del usuario o cadena vacía
 */
function obtenerPerfil()
{
    return $_SESSION['perfil'] ?? '';
}

/**
 * Muestra la cabecera HTML común para todas las páginas
 * Incluye navegación dinámica según el perfil del usuario
 * 
 * @param string $titulo Título de la página (opcional)
 * @return void
 */
function mostrarCabecera($titulo = 'Eduflow')
{
    $nombreUsuario = obtenerUsuario();
    $perfil = obtenerPerfil();
    ?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($titulo) ?> - Eduflow</title>
        <link rel="stylesheet" href="/eduflow/css/styles.css">
        <link rel="icon" type="image/x-icon" href="/favicon.ico">

    </head>

    <body>
        <header class="main-header">
            <div class="header-container">
                <!-- Logo y nombre de la aplicación -->
                <div class="logo">
                    <h1>📚 Eduflow</h1>
                    <span class="tagline">Sistema de Agenda Académica</span>
                </div>

                <!-- Navegación principal - cambia según perfil -->
                <nav class="main-nav">
                    <ul>
                        <li><a href="/eduflow/index.php">🏠 Inicio</a></li>

                        <?php if (tienePermiso('coordinacion')): ?>
                            <!-- Menú para Coordinación (REST) -->
                            <li><a href="/eduflow/rest/rest_client.php">📅 Horarios</a></li>
                        <?php endif; ?>

                        <?php if (tienePermiso('profesor')): ?>
                            <!-- Menú para Profesor (SOAP) -->
                            <li><a href="/eduflow/soap/soap_client.php">📝 Tareas</a></li>
                        <?php endif; ?>

                        <li><a href="/eduflow/logout.php">⭕ Cerrar Session</a></li>
                    </ul>
                </nav>

                <!-- Información del usuario -->
                <div class="user-info">
                    <span class="user-name">👤 <?= htmlspecialchars($nombreUsuario) ?></span>
                    <span class="perfil-badge"><?= htmlspecialchars(ucfirst($perfil)) ?></span>
                </div>
            </div>
        </header>
        <main class="container">
            <?php
}

/**
 * Cierra las etiquetas HTML abiertas por mostrarCabecera()
 * 
 * @return void
 */
function mostrarPie()
{
    ?>
        </main>
        <footer class="main-footer">
            <div class="footer-container">
                <p>&copy; <?= date('Y') ?> Eduflow - Sistema de Agenda Académica</p>
                <p>Actividad UF4 - Desarrollo Web Entorno Servidor</p>
            </div>
        </footer>
    </body>

    </html>
    <?php
}

/**
 * Muestra un mensaje de alerta
 * 
 * @param string $mensaje Texto del mensaje
 * @param string $tipo Tipo de alerta: 'success', 'error', 'warning', 'info'
 * @return void
 */
function mostrarAlerta($mensaje, $tipo = 'info')
{
    $clases = [
        'success' => 'alert-success',
        'error' => 'alert-error',
        'warning' => 'alert-warning',
        'info' => 'alert-info'
    ];
    $clase = $clases[$tipo] ?? 'alert-info';
    echo "<div class='alert {$clase}'>" . htmlspecialchars($mensaje) . "</div>";
}
?>