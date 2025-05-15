<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}


header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Principal - Sistema de Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Heroicons -->
    <script src="https://unpkg.com/heroicons@1.0.6/dist/heroicons.min.js"></script>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
    <!-- Encabezado -->
    <header class="bg-blue-600 shadow">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center">
                <!-- Icono de Biblioteca   -->
                 <!-- <img src="images/fit.png" alt="Logo del Sistema" class="h-16 mr-4">-->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0H7a1 1 0 01-1-1V5a1 1 0 011-1h3m6 0h3a1 1 0 011 1v10a1 1 0 01-1 1h-3" />
                </svg>
                <a href="#" class="text-white text-2xl font-bold">Sistema de Biblioteca</a>
            </div>

            <!-- Menú de navegación  -->
            <div>
                <a href="#" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition">Inicio</a>
                
                <a href="reportes.php" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition">Reportes</a>
                <a href="listar_estudiantes.php" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition">Estudiantes</a>
                <a href="logout.php" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition">Cerrar Sesión</a>
            </div>
        </nav>
    </header>

    <!-- Sección de Bienvenida -->
    <section class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white">
        <div class="container mx-auto px-6 py-20 text-center">
            
            <h2 class="text-4xl font-bold mb-4">Bienvenido al Sistema de Biblioteca</h2>
            <p class="text-lg mb-8">Gestiona y administra tus préstamos de manera eficiente y sencilla.</p>
            <a href="catalogo_libros.php" class="bg-white text-blue-600 px-6 py-3 rounded-full font-semibold hover:bg-gray-200 transition transform hover:scale-105">Explorar Catálogo</a>
        </div>
    </section>

    <!-- Sección de Opciones -->
    <main class="container mx-auto px-6 py-12 flex-grow">
        <h3 class="text-3xl font-bold text-gray-800 text-center mb-8">Panel Principal</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Registrar Entrada y Salida -->
            <a href="registro_entrada_salida.php" class="bg-white rounded-lg shadow-lg p-6 flex flex-col items-center hover:shadow-2xl transition transform hover:-translate-y-2">
                <!-- Icono -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16h4v-4m0 0h-4m4 4v4m-9-4H3v-4h5m0 0H3m5 4v4m0-4h5v-4H8z" />
                </svg>
                <h4 class="text-xl font-semibold mb-2">Registrar Entrada y Salida</h4>
                <p class="text-center text-gray-600">Gestiona el registro de entrada y salida de los usuarios.</p>
            </a>
            <!-- Administrar Estudiantes -->
            <a href="listar_estudiantes.php" class="bg-white rounded-lg shadow-lg p-6 flex flex-col items-center hover:shadow-2xl transition transform hover:-translate-y-2">
                <!-- Icono -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-green-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A4.992 4.992 0 015 12V7a2 2 0 012-2h2.5a4 4 0 014 4v1.585a2 2 0 01.586 1.414l-.707 2.828a2 2 0 01-1.414.586H8a2 2 0 01-2-2v-2" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <h4 class="text-xl font-semibold mb-2">Administrar Estudiantes</h4>
                <p class="text-center text-gray-600">Agrega, edita o elimina estudiantes en el sistema.</p>
            </a>
            <!-- Préstamo de Libros -->
            <a href="catalogo_libros.php" class="bg-white rounded-lg shadow-lg p-6 flex flex-col items-center hover:shadow-2xl transition transform hover:-translate-y-2">
                <!-- Icono -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-yellow-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8H11c-4.418 0-8-3.582-8-8V8c0-4.418 3.582-8 8-8h2c4.418 0 8 3.582 8 8v4z" />
                </svg>
                <h4 class="text-xl font-semibold mb-2">Préstamo de Libros</h4>
                <p class="text-center text-gray-600">Gestiona el préstamo y devolución de libros.</p>
            </a>
            <!-- Crear Reportes -->
            <a href="reportes.php" class="bg-white rounded-lg shadow-lg p-6 flex flex-col items-center hover:shadow-2xl transition transform hover:-translate-y-2">
                <!-- Icono -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-purple-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h4a2 2 0 012 2v6m-6 0h6" />
                </svg>
                <h4 class="text-xl font-semibold mb-2">Crear Reportes</h4>
                <p class="text-center text-gray-600">Genera reportes detallados sobre la asistencia y préstamos.</p>
            </a>
        </div>
    </main>

    <!-- Pie de Página -->
    <footer class="bg-gray-800 text-white py-6">
        <div class="container mx-auto text-center">
            &copy; 2024 Sistema de Biblioteca - FIT-UABJB. Todos los derechos reservados.
        </div>
    </footer>

    
</body>
</html>
