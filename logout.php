<?php
session_start();

// Elimina todas las variables de sesión
$_SESSION = array();

// Destruye la sesión
session_destroy();

// Deshabilita la caché para evitar que el usuario pueda volver con el botón "Atrás"
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirige al usuario a la página de inicio de sesión
header("Location: login.php");
exit;
?>
