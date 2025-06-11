<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
    <section class="flex-grow flex justify-center items-center bg-gradient-to-r from-blue-500 to-indigo-600">
        <div class="w-full max-w-sm bg-white p-8 rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold text-center mb-4">Recuperar Contraseña</h2>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="procesar_recuperacion.php">
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required
                           class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="correo@ejemplo.com">
                </div>
                
                <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Solicitar Código
                </button>
                
                <div class="mt-4 text-center">
                    <a href="../login.php" class="text-blue-600 hover:text-blue-800 text-sm">
                        Volver al login
                    </a>
                </div>
            </form>
        </div>
    </section>
</body>
</html>