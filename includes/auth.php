<?php
require_once 'includes/db.php';

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

// En includes/auth.php
function checkRole($requiredRole) {
    checkAuthentication();
    
    // Si el usuario es admin, permitir acceso a todo
    if ($_SESSION['rol'] === 'admin') {
        return true;
    }
    
    // Verificar si el rol del usuario coincide con el requerido
    if ($_SESSION['rol'] !== $requiredRole) {
        header("Location: unauthorized.php");
        exit;
    }
}
function getAvailableModules($usuario_id) {
    global $conn;
    
    // Consulta diferente para admin vs usuarios normales
    if ($_SESSION['rol'] === 'admin') {
        $stmt = $conn->prepare("SELECT id, nombre, url, icono FROM modulos");
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("SELECT m.id, m.nombre, m.url, m.icono 
                              FROM modulos m
                              JOIN usuario_modulos um ON m.id = um.modulo_id
                              WHERE um.usuario_id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
    }
    
    $result = $stmt->get_result();
    
    $modules = [];
    while ($row = $result->fetch_assoc()) {
        $modules[] = [
            'name' => $row['nombre'],
            'url' => $row['url'],
            'icon' => $row['icono']
        ];
    }
    
    // Todos los usuarios tienen acceso al dashboard
    array_unshift($modules, [
        'name' => 'Dashboard',
        'url' => 'index.php',
        'icon' => 'fas fa-tachometer-alt'
    ]);
    
    return $modules;
}

function canAccessModule($usuario_id, $modulo_url) {
    if ($modulo_url === 'index.php') return true;
    
    global $conn;
    
    $stmt = $conn->prepare("SELECT COUNT(*) 
                          FROM usuario_modulos um
                          JOIN modulos m ON um.modulo_id = m.id
                          WHERE um.usuario_id = ? AND m.url = ?");
    $stmt->bind_param("is", $usuario_id, $modulo_url);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    
    return $count > 0;
}

// Función mejorada para verificar acceso a módulo
function checkModuleAccess($module_url) {
    initializeSession();
    
    // Permitir acceso al dashboard siempre
    if ($module_url === 'index.php') {
        return true;
    }
    
    // Admin tiene acceso a todo
    if ($_SESSION['rol'] === 'admin') {
        return true;
    }
    
    // Verificar acceso para usuarios normales
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) 
                          FROM usuario_modulos um
                          JOIN modulos m ON um.modulo_id = m.id
                          WHERE um.usuario_id = ? AND m.url = ?");
    $stmt->bind_param("is", $_SESSION['user_id'], $module_url);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    
    return $count > 0;
}

// Función para redirigir si no tiene acceso
function verifyModuleAccess($module_url) {
    if (!checkModuleAccess($module_url)) {
        header("Location: unauthorized.php");
        exit;
    }
}

?>