<?php
/**
 * logout.php - Cierre de sesión
 * 
 * Destruye la sesión del usuario y redirige al login.
 * 
 * @package Eduflow
 * @author Ricard Penin Honrubia
 */

session_start();

// Destruir todas las variables de sesión
$_SESSION = [];

// Destruir la cookie de sesión si existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Redirigir al login
header('Location: /eduflow/login.php');
exit;
?>