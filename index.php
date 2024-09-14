
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Principal - Sistema de Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f4f8;
            font-family: 'Roboto', sans-serif;
        }
        header {
            background-color: rgba(30, 41, 59, 0.9);
        }
        .nav-link {
            color: #ffffff;
        }
        .nav-link:hover {
            background-color: #3B82F6;
        }
        .main-button {
            background-color: #4F46E5;
            color: #ffffff;
            transition: background-color 0.3s, transform 0.3s;
        }
        .main-button:hover {
            background-color: #4338CA;
            transform: scale(1.05);
        }
        .card {
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <header class="py-4 shadow-md rounded">
        <nav class="container mx-auto flex justify-between items-center">
            <h1 class="text-white text-2xl font-bold">Sistema de Biblioteca - FIT - UABJB</h1>
        </nav>
    </header>
    <main class="container mx-auto mt-10 text-center p-8">
        <h2 class="text-3xl font-bold text-gray-800 mb-4">Panel Principal</h2>
        <p class="text-lg text-gray-700 mb-6">Selecciona una de las opciones para continuar</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <a href="registro_entrada_salida.php" class="card bg-white p-6 rounded-lg shadow-lg">
                <h3 class="text-xl font-bold text-gray-800 mb-2">Registrar Entrada y Salida</h3>
                <p class="text-gray-600">Gestiona el registro de entrada y salida de los usuarios.</p>
            </a>
            <a href="listar_estudiantes.php" class="card bg-white p-6 rounded-lg shadow-lg">
                <h3 class="text-xl font-bold text-gray-800 mb-2">Administrar Estudiantes</h3>
                <p class="text-gray-600">Agrega, edita o elimina estudiantes en el sistema.</p>
            </a>
            <a href="catalogo_libros.php" class="card bg-white p-6 rounded-lg shadow-lg">
                <h3 class="text-xl font-bold text-gray-800 mb-2">Préstamo de Libros</h3>
                <p class="text-gray-600">Gestiona el préstamo y devolución de libros.</p>
            </a>
            <a href="crear_reportes.php" class="card bg-white p-6 rounded-lg shadow-lg">
                <h3 class="text-xl font-bold text-gray-800 mb-2">Crear Reportes</h3>
                <p class="text-gray-600">Genera reportes detallados sobre la asistencia y préstamos.</p>
            </a>
        </div>
    </main>
    <footer class="py-4 bg-gray-800 text-white text-center mt-10">
        &copy; 2024 Sistema de Biblioteca - FIT-UABJB. Todos los derechos reservados.
    </footer>
</body>
</html>
