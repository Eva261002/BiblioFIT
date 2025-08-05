<?php
function initializeSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isAuthenticated() {
    initializeSession();
    return isset($_SESSION['user_id']);
}

function redirectToLogin() {
    header("Location: login.php");
    exit;
}

function checkAuthentication() {
    if (!isAuthenticated()) {
        redirectToLogin();
    }
}

function checkRole($requiredRole) {
    checkAuthentication();
    
    if ($_SESSION['rol'] !== $requiredRole && $_SESSION['rol'] !== 'admin') {
        // Admin tiene acceso a todo
        header("Location: unauthorized.php");
        exit;
    }
}

function getAvailableModules($rol) {
    $modules = [
        'admin' => [
            ['name' => 'Dashboard', 'url' => 'index.php', 'icon' => 'fas fa-tachometer-alt'],
            ['name' => 'Asistencia', 'url' => 'registro_entrada_salida.php', 'icon' => 'fas fa-door-open'],
            ['name' => 'Estudiantes', 'url' => 'listar_estudiantes.php', 'icon' => 'fas fa-users'],
            ['name' => 'Préstamos', 'url' => 'catalogo_libros.php', 'icon' => 'fas fa-book'],
            ['name' => 'Reportes', 'url' => 'reportes.php', 'icon' => 'fas fa-chart-bar'],
            ['name' => 'Configuración', 'url' => 'configuracion.php', 'icon' => 'fas fa-cog']
        ],
        'usuario' => [
            ['name' => 'Dashboard', 'url' => 'index.php', 'icon' => 'fas fa-tachometer-alt'],
            ['name' => 'Asistencia', 'url' => 'registro_entrada_salida.php', 'icon' => 'fas fa-door-open']
        ]
    ];
    
    return $modules[$rol] ?? $modules['usuario']; // Default to usuario if role not found
}
?>