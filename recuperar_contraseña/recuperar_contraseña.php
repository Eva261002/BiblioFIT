<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña | Sistema de Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .animate-fade-in {
            animation: fadeIn 0.3s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
    <section class="flex-grow flex justify-center items-center bg-gradient-to-br from-blue-600 to-indigo-700 py-12 px-4">
        <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-lg card-hover">
            <!-- Logo y título -->
            <div class="text-center mb-8">
                <div class="mx-auto bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-key text-blue-600 text-2xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Recuperar Contraseña</h1>
                <p class="text-gray-600 mt-2">Ingresa tu correo electrónico para recibir instrucciones</p>
            </div>

            <!-- Mensajes de estado -->
            <?php if (isset($_GET['error'])): ?>
                <div class="animate-fade-in bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span><?= htmlspecialchars($_GET['error']) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <div class="animate-fade-in bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span><?= htmlspecialchars($_GET['success']) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Formulario -->
            <form method="POST" action="procesar_recuperacion.php" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="tu@email.com"
                            value="<?= isset($_SESSION['recovery_email']) ? htmlspecialchars($_SESSION['recovery_email']) : '' ?>"
                        >
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Te enviaremos un código de verificación a este correo</p>
                </div>
                
                <button 
                    type="submit" 
                    class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow transition duration-300 flex items-center justify-center"
                >
                    <i class="fas fa-paper-plane mr-2"></i> Enviar Código
                </button>
                
                <div class="text-center pt-4 border-t border-gray-200">
                    <a href="../login.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium inline-flex items-center">
                        <i class="fas fa-arrow-left mr-1"></i> Volver al inicio de sesión
                    </a>
                </div>
            </form>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Validación básica del formulario
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const email = document.getElementById('email').value.trim();
                if (!email) {
                    e.preventDefault();
                    alert('Por favor ingresa tu correo electrónico');
                    return false;
                }
                
                // Validación simple de formato de email
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    e.preventDefault();
                    alert('Por favor ingresa un correo electrónico válido');
                    return false;
                }
                
                // Muestra indicador de carga
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Enviando...';
                submitBtn.disabled = true;
            });
        });
    </script>

<?php include('includes/footer.php'); ?>
</body>
</html>