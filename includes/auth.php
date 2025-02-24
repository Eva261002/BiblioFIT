<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Verificar roles, si es necesario
function checkRole($requiredRole) {
    if ($_SESSION['rol'] !== $requiredRole) {
        echo "Acceso denegado. No tienes permisos para acceder a esta página.";
        exit;
    }
}
?>
