<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema de Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .animate-fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        }
    </style>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
    <!-- Encabezado -->
    <header class="bg-blue-600 shadow-lg">
        <nav class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <a href="#" class="text-white text-3xl font-bold">Biblioteca de la FIT-UABJB</a>
            </div>
        </nav>
    </header>

    <!-- Mensajes de alerta -->
    <?php if (isset($_GET['error'])): ?>
        <div class="animate-fade-in bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 mx-auto w-full max-w-md mt-4" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <p class="font-medium">
                    <?php
                    switch($_GET['error']) {
                        case 'campos_vacios':
                            echo 'Por favor complete todos los campos';
                            break;
                        case 'credenciales_invalidas':
                            echo 'Correo electrónico o contraseña incorrectos';
                            break;
                        case 'cuenta_no_verificada':
                            echo 'Por favor verifica tu cuenta primero';
                            break;
                        case 'sesion_expirada':
                            echo 'Tu sesión ha expirado, por favor ingresa nuevamente';
                            break;
                        default:
                            echo 'Error al iniciar sesión';
                    }
                    ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['logout']) && $_GET['logout'] == 1): ?>
        <div class="animate-fade-in bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 mx-auto w-full max-w-md mt-4" role="alert">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <p class="font-medium">Has cerrado sesión correctamente</p>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
        <div class="animate-fade-in bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 mx-auto w-full max-w-md mt-4" role="alert">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <p class="font-medium"><?= htmlspecialchars($_GET['success']) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Formulario de Login -->
    <section class="flex-grow flex flex-col justify-center items-center bg-gradient-to-br from-blue-500 to-indigo-700 py-12 px-4">
        <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-2xl transform transition-all hover:scale-[1.01]">
            <div class="text-center mb-8">
                <i class="fas fa-user-circle text-5xl text-blue-500 mb-4"></i>
                <h2 class="text-3xl font-bold text-gray-800">Iniciar Sesión</h2>
                <p class="text-gray-600 mt-2">Ingresa tus credenciales para acceder</p>
            </div>
            
            <form action="login_logica.php" method="POST" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input type="email" id="email" name="email" required
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent input-focus"
                               placeholder="tu@email.com">
                    </div>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" id="password" name="password" required
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent input-focus"
                               placeholder="••••••••">
                        <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" id="togglePassword">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember-me" class="ml-2 block text-sm text-gray-700">Recordar sesión</label>
                    </div>
                    
                    <div class="text-sm">
                        <a href="recuperar_contraseña/recuperar_contraseña.php" class="font-medium text-blue-600 hover:text-blue-500">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                </div>
                
                <button type="submit" class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-md transition duration-300 flex items-center justify-center">
                    <i class="fas fa-sign-in-alt mr-2"></i> Iniciar Sesión
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    ¿No tienes cuenta? 
                    <a href="registro_login.php" class="font-medium text-blue-600 hover:text-blue-500">
                        Regístrate aquí
                    </a>
                </p>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mostrar/ocultar contraseña
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="far fa-eye"></i>' : '<i class="far fa-eye-slash"></i>';
            });
            
            // Validación del formulario
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const email = document.getElementById('email').value.trim();
                const password = document.getElementById('password').value.trim();
                
                if (!email || !password) {
                    e.preventDefault();
                    alert('Por favor complete todos los campos');
                }
            });
            
            // Efecto hover para el card del formulario
            const formCard = document.querySelector('.bg-white');
            formCard.addEventListener('mouseenter', function() {
                this.classList.add('shadow-lg');
            });
            formCard.addEventListener('mouseleave', function() {
                this.classList.remove('shadow-lg');
            });
        });
    </script>
</body>
</html>