<!-- login.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema de Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
    <!-- Encabezado -->
    <header class="bg-blue-600 shadow">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center">
                <a href="#" class="text-white text-2xl font-bold">Sistema de Biblioteca</a>
            </div>
        </nav>
    </header>

    <!-- Formulario de Login -->
    <section class="flex-grow flex justify-center items-center bg-gradient-to-r from-blue-500 to-indigo-600">
        <div class="w-full max-w-sm bg-white p-8 rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold text-center mb-4">Iniciar Sesión</h2>
            <form action="login_logica.php" method="POST">
    <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
        <input type="email" id="email" name="email" required class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div class="mb-4">
        <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
        <input type="password" id="password" name="password" required class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Iniciar Sesión</button>
</form>
<div class="mt-4 text-center">
    <p class="text-sm text-gray-600">¿No tienes cuenta? <a href="registro.php" class="text-blue-600 hover:text-blue-700">Crear cuenta</a></p>
</div>
    </section>

    <!-- Pie de Página -->
    <footer class="bg-gray-800 text-white py-6">
        <div class="container mx-auto text-center">
            &copy; 2024 Sistema de Biblioteca - FIT-UABJB. Todos los derechos reservados.
        </div>
    </footer>
</body>
</html>
