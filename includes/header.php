<?php
require_once 'db.php';
require_once 'auth.php';
checkAuthentication(); 


// Obtener módulos asignados al usuario
$modules = getAvailableModules($_SESSION['user_id']);

// Filtrar módulos basados en permisos (para usuarios no admin)
if ($_SESSION['rol'] !== 'admin') {
    $modules = array_filter($modules, function($module) {
        return checkModuleAccess($module['url']);
    });
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
<header class="bg-blue-600 shadow-lg">
    <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <a href="index.php" class="text-white text-xl font-bold hover:text-gray-200 transition">Biblioteca</a>
        </div>

        <div class="flex items-center space-x-4">
            <?php foreach($modules as $module): ?>
                <?php if($module['name'] != 'Dashboard'): ?>
                    <a href="<?= $module['url'] ?>" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition flex items-center">
                        <i class="<?= $module['icon'] ?> mr-2"></i> <?= $module['name'] ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
            
            <a href="logout.php" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition flex items-center">
                <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
            </a>
        </div>
    </nav>
</header>
<main class="container mx-auto px-4 py-8">