<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Eliminar cookies de sesión si existen
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

// 2. Destruir completamente la sesión
$_SESSION = array(); // Vaciar el array de sesión
session_destroy();   // Destruir la sesión del servidor

// 3. Cabeceras de seguridad para evitar caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT"); // Fecha en el pasado

// 4. Redirección con parámetro de logout exitoso
header("Location: login.php?logout=1");
exit();
?>