<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Principal - Sistema de Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">
    <!-- Encabezado Mejorado -->
    <header class="bg-gradient-to-r from-blue-500 to-blue-700 shadow-lg">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <a href="#" class="text-white text-2xl font-bold hover:text-gray-200 transition">Sistema de Biblioteca</a>
            </div>

            <!-- Menú de navegación mejorado con cierre de sesión directo -->
            <div class="flex items-center space-x-4">
                <div class="hidden md:flex space-x-4">
                    <a href="reportes.php" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-600 transition flex items-center">
                        <i class="fas fa-chart-bar mr-2"></i> Reportes
                    </a>
                    <a href="listar_estudiantes.php" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-600 transition flex items-center">
                        <i class="fas fa-users mr-2"></i> Estudiantes
                    </a>
                </div>
                <a href="logout.php" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-600 transition flex items-center">
                    <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                </a>
            </div>
        </nav>
    </header>

    <!-- Sección de Bienvenida Mejorada -->
    <section class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white relative overflow-hidden">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="container mx-auto px-6 py-24 text-center relative z-10">
            <h1 class="text-4xl md:text-5xl font-bold mb-6">Bienvenido al Sistema de Biblioteca</h1>
            <p class="text-xl mb-8 max-w-2xl mx-auto">Gestiona y administra tus recursos bibliográficos de manera eficiente y profesional.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="catalogo_libros.php" class="bg-white text-blue-600 px-8 py-3 rounded-full font-semibold hover:bg-gray-100 transition transform hover:scale-105 shadow-lg">
                    <i class="fas fa-book-open mr-2"></i> Explorar Catálogo
                </a>
                <a href="registro_entrada_salida.php" class="bg-blue-800 text-white px-8 py-3 rounded-full font-semibold hover:bg-blue-900 transition transform hover:scale-105 shadow-lg">
                    <i class="fas fa-door-open mr-2"></i> Registrar Entrada
                </a>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-gray-50 to-transparent"></div>
    </section>

    <!-- Sección de Opciones Mejorada -->
    <main class="container mx-auto px-6 py-16 flex-grow">
        <h2 class="text-3xl font-bold text-gray-800 text-center mb-12">¿Qué deseas hacer hoy?</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Registrar Entrada y Salida -->
            <a href="registro_entrada_salida.php" class="bg-white rounded-xl shadow-md p-6 flex flex-col items-center hover:-translate-y-1 transition-transform duration-300 border border-gray-100 hover:shadow-lg">
                <div class="bg-blue-100 p-4 rounded-full mb-4">
                    <i class="fas fa-door-open text-blue-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2 text-center">Registro de Acceso</h3>
                <p class="text-center text-gray-600 text-sm">Controla el ingreso y salida de usuarios de la biblioteca.</p>
                <span class="mt-4 text-blue-600 text-sm font-medium flex items-center">
                    Acceder <i class="fas fa-arrow-right ml-2"></i>
                </span>
            </a>
            
            <!-- Administrar Estudiantes -->
            <a href="listar_estudiantes.php" class="bg-white rounded-xl shadow-md p-6 flex flex-col items-center hover:-translate-y-1 transition-transform duration-300 border border-gray-100 hover:shadow-lg">
                <div class="bg-green-100 p-4 rounded-full mb-4">
                    <i class="fas fa-users text-green-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2 text-center">Gestión de Estudiantes</h3>
                <p class="text-center text-gray-600 text-sm">Administra el registro de estudiantes y sus datos.</p>
                <span class="mt-4 text-green-600 text-sm font-medium flex items-center">
                    Administrar <i class="fas fa-arrow-right ml-2"></i>
                </span>
            </a>
            
            <!-- Préstamo de Libros -->
            <a href="catalogo_libros.php" class="bg-white rounded-xl shadow-md p-6 flex flex-col items-center hover:-translate-y-1 transition-transform duration-300 border border-gray-100 hover:shadow-lg">
                <div class="bg-yellow-100 p-4 rounded-full mb-4">
                    <i class="fas fa-book text-yellow-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2 text-center">Préstamos</h3>
                <p class="text-center text-gray-600 text-sm">Gestiona el préstamo y devolución de recursos.</p>
                <span class="mt-4 text-yellow-600 text-sm font-medium flex items-center">
                    Gestionar <i class="fas fa-arrow-right ml-2"></i>
                </span>
            </a>
            
            <!-- Crear Reportes -->
            <a href="reportes.php" class="bg-white rounded-xl shadow-md p-6 flex flex-col items-center hover:-translate-y-1 transition-transform duration-300 border border-gray-100 hover:shadow-lg">
                <div class="bg-purple-100 p-4 rounded-full mb-4">
                    <i class="fas fa-chart-pie text-purple-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2 text-center">Reportes</h3>
                <p class="text-center text-gray-600 text-sm">Genera informes estadísticos y reportes detallados.</p>
                <span class="mt-4 text-purple-600 text-sm font-medium flex items-center">
                    Generar <i class="fas fa-arrow-right ml-2"></i>
                </span>
            </a>
        </div>
        
        <!-- Sección de estadísticas rápidas -->
        <div class="mt-16 bg-white rounded-xl shadow-md p-6">
            <h3 class="text-xl font-semibold mb-6 text-gray-800">Resumen Rápido</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-gray-600">Estudiantes registrados</p>
                            <p class="text-2xl font-bold text-gray-800">1,248</p>
                        </div>
                        <i class="fas fa-user-graduate text-blue-500 text-xl"></i>
                    </div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-gray-600">Préstamos activos</p>
                            <p class="text-2xl font-bold text-gray-800">87</p>
                        </div>
                        <i class="fas fa-book-open text-green-500 text-xl"></i>
                    </div>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg border-l-4 border-purple-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-gray-600">Visitas hoy</p>
                            <p class="text-2xl font-bold text-gray-800">156</p>
                        </div>
                        <i class="fas fa-door-open text-purple-500 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Pie de Página Mejorado -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center mb-4 md:mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span class="text-xl font-semibold">Sistema de Biblioteca</span>
                </div>
                <div class="flex space-x-6">
                    <a href="#" class="hover:text-blue-300 transition">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="hover:text-blue-400 transition">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="hover:text-pink-500 transition">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-6 pt-6 text-center md:text-left">
                <p>&copy; 2024 Sistema de Biblioteca - FIT-UABJB. Todos los derechos reservados.</p>
                <p class="text-gray-400 text-sm mt-2">Versión 2.0.0</p>
            </div>
        </div>
    </footer>
</body>
</html>