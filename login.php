 
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema de Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="scripts/login.js"></script>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
<!-- Agregar al inicio del body -->
<?php if (isset($_GET['error'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <?php
        switch($_GET['error']) {
            case 'campos_vacios':
                echo 'Por favor complete todos los campos';
                break;
            case 'credenciales_invalidas':
                echo 'Correo electrónico o contraseña incorrectos';
                break;
            default:
                echo 'Error al iniciar sesión';
                 
        }
        ?>
    </div>
<?php endif; ?>

    <!-- Encabezado -->
    <header class="bg-blue-600 shadow">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0H7a1 1 0 01-1-1V5a1 1 0 011-1h3m6 0h3a1 1 0 011 1v10a1 1 0 01-1 1h-3" />
                </svg>
                <a href="#" class="text-white text-2xl font-bold">Sistema de Biblioteca</a>
            </div>
        </nav>
    </header>

    <!-- Formulario de Login -->
    <section class="flex-grow flex justify-center items-center bg-gradient-to-r from-blue-500 to-indigo-600">
        <div class="w-full max-w-sm bg-white p-8 rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold text-center mb-4">Iniciar Sesión</h2>
            <form action="login_logica.php" method="POST" onsubmit="return validarLogin()">
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
    <p class="text-sm text-gray-600">¿No tienes cuenta? <a href="registro_login.php" class="text-blue-600 hover:text-blue-700">Crear cuenta</a></p>
</div>

<div class="mt-4 text-center">
    <p class="text-sm text-gray-600">
        ¿Olvidaste tu contraseña? 
        <a href="recuperar_contraseña/recuperar_contraseña.php" class="text-blue-600 hover:text-blue-700">Recuperar contraseña</a>
    </p>
</div>

    </section>

<?php if (isset($_GET['logout']) && $_GET['logout'] == 1): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
        Has cerrado sesión correctamente.
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
</body>
</html>
