<?php
/**
 * login.php - Página de inicio de sesión de Eduflow
 * 
 * Permite a los usuarios autenticarse con diferentes perfiles.
 * 
 * USUARIOS DE PRUEBA:
 * - coordinador / 1234 → Perfil: coordinacion (acceso a REST)
 * - profesor / 1234    → Perfil: profesor (acceso a SOAP)
 * 
 * @package Eduflow
 * @author Ricard Penin Honrubia
 */

session_start();

// Si ya hay sesión activa, redirigir a index
if (isset($_SESSION['usuario'])) {
    header('Location: /eduflow/index.php');
    exit;
}

$error = null;

// Usuarios de prueba (en producción usar base de datos)
$usuariosValidos = [
    'coordinador' => ['password' => '1234', 'perfil' => 'coordinacion', 'nombre' => 'Coordinador Académico'],
    'profesor' => ['password' => '1234', 'perfil' => 'profesor', 'nombre' => 'Prof. García'],
    'admin' => ['password' => 'admin', 'perfil' => 'coordinacion', 'nombre' => 'Administrador']
];

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($usuario) || empty($password)) {
        $error = "Por favor, complete todos los campos";
    } elseif (isset($usuariosValidos[$usuario]) && $usuariosValidos[$usuario]['password'] === $password) {
        // Login exitoso
        $_SESSION['usuario'] = $usuariosValidos[$usuario]['nombre'];
        $_SESSION['perfil'] = $usuariosValidos[$usuario]['perfil'];
        $_SESSION['ultimo_acceso'] = time();

        header('Location: /eduflow/index.php');
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}

// Verificar si hay mensaje de sesión expirada
if (isset($_GET['error']) && $_GET['error'] === 'sesion_expirada') {
    $error = "Su sesión ha expirado por inactividad. Por favor, inicie sesión de nuevo.";
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Eduflow</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    <link rel="stylesheet" href="/eduflow/css/styles.css">
    <style>
        body {
            background-color: var(--color-info);
            padding: 2vh;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <h1>📚 Eduflow</h1>
            <p>Sistema de Agenda Académica</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="usuario">Usuario</label>
                <input type="text" name="usuario" id="usuario" placeholder="Introduce tu usuario" required
                    value="<?= isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" placeholder="Introduce tu contraseña" required>
            </div>

            <button type="submit" class="btn-login">
                🔐 Iniciar Sesión
            </button>
        </form>

        <div class="demo-users">
            <h4>Usuarios de prueba:</h4>
            <ul>
                <li>👔 <code>coordinador</code> / <code>1234</code> → Acceso REST</li>
                <li>👨‍🏫 <code>profesor</code> / <code>1234</code> → Acceso SOAP</li>
            </ul>
        </div>
    </div>
</body>

</html>