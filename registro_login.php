<?php
include('includes/db.php');
session_start();

$errors = [];
$email = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validación del correo
    if (empty($email)) {
        $errors['email'] = "El correo electrónico es requerido";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Correo electrónico no válido";
    } else {
        // Verificar si el correo ya existe
        $query = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errors['email'] = "El correo ya está registrado";
        }
    }

    // Validación de contraseña
    if (empty($password)) {
        $errors['password'] = "La contraseña es requerida";
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Mínimo 8 caracteres";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors['password'] = "Debe contener al menos una mayúscula";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors['password'] = "Debe contener al menos un número";
    } elseif (!preg_match('/[!@#$%^&*]/', $password)) {
        $errors['password'] = "Debe contener un carácter especial (!@#$%^&*)";
    }

    // Confirmación de contraseña
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Las contraseñas no coinciden";
    }

    // Si no hay errores, registrar al usuario
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert_query = "INSERT INTO usuarios (email, contraseña, rol) VALUES (?, ?, 'usuario')";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ss", $email, $hashed_password);

        if ($stmt->execute()) {
            $success_message = "Cuenta creada con éxito. Puedes <a href='login.php' class='underline'>iniciar sesión</a> ahora.";
            // Limpiar campos después de registro exitoso
            $email = '';
        } else {
            $errors['general'] = "Error al crear la cuenta. Por favor, intenta nuevamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - Sistema de Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .error-message {
            transition: all 0.3s ease;
            max-height: 0;
            overflow: hidden;
        }
        .error-message.show {
            max-height: 50px;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
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
    
    <section class="flex-grow flex justify-center items-center bg-gradient-to-r from-blue-500 to-indigo-600">
        <div class="w-full max-w-sm bg-white p-8 rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold text-center mb-4">Crear Cuenta</h2>
            
            <?php if (!empty($success_message)): ?>
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    <?= $success_message ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($errors['general'])): ?>
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <?= $errors['general'] ?>
                </div>
            <?php endif; ?>
            
            <form action="registro_login.php" method="POST" id="registroForm">
                <!-- Campo Email -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required
                           class="w-full px-4 py-2 mt-1 border <?= isset($errors['email']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div class="error-message text-sm text-red-600 <?= isset($errors['email']) ? 'show' : '' ?>" id="emailError">
                        <?= $errors['email'] ?? '' ?>
                    </div>
                </div>
                
                <!-- Campo Contraseña -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-2 mt-1 border <?= isset($errors['password']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div class="error-message text-sm text-red-600 <?= isset($errors['password']) ? 'show' : '' ?>" id="passwordError">
                        <?= $errors['password'] ?? '' ?>
                    </div>
                    <div class="mt-2 text-xs text-gray-500">
                        La contraseña debe tener:
                        <ul class="list-disc list-inside">
                            <li id="length" class="text-gray-400">Mínimo 8 caracteres</li>
                            <li id="uppercase" class="text-gray-400">Al menos una mayúscula</li>
                            <li id="number" class="text-gray-400">Al menos un número</li>
                            <li id="special" class="text-gray-400">Al menos un carácter especial (!@#$%^&*)</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Campo Confirmar Contraseña -->
                <div class="mb-4">
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           class="w-full px-4 py-2 mt-1 border <?= isset($errors['confirm_password']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div class="error-message text-sm text-red-600 <?= isset($errors['confirm_password']) ? 'show' : '' ?>" id="confirmPasswordError">
                        <?= $errors['confirm_password'] ?? '' ?>
                    </div>
                </div>
                
                <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    Crear Cuenta
                </button>
            </form>
            
            <div class="mt-4 text-center">
                <p class="text-sm text-gray-600">¿Ya tienes cuenta? <a href="login.php" class="text-blue-600 hover:text-blue-700">Iniciar sesión</a></p>
            </div>
        </div>
    </section>

    <footer class="bg-gray-800 text-white py-6">
        <div class="container mx-auto text-center">
            &copy; 2024 Sistema de Biblioteca - FIT-UABJB. Todos los derechos reservados.
        </div>
    </footer>

    <script>
        // Validación en tiempo real
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registroForm');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            // Validar contraseña en tiempo real
            password.addEventListener('input', function() {
                const value = this.value;
                const lengthValid = value.length >= 8;
                const upperValid = /[A-Z]/.test(value);
                const numberValid = /[0-9]/.test(value);
                const specialValid = /[!@#$%^&*]/.test(value);
                
                // Actualizar indicadores visuales
                document.getElementById('length').className = lengthValid ? 'text-green-500' : 'text-gray-400';
                document.getElementById('uppercase').className = upperValid ? 'text-green-500' : 'text-gray-400';
                document.getElementById('number').className = numberValid ? 'text-green-500' : 'text-gray-400';
                document.getElementById('special').className = specialValid ? 'text-green-500' : 'text-gray-400';
                
                // Validar confirmación de contraseña cuando cambia la contraseña
                if (confirmPassword.value) {
                    validatePasswordMatch();
                }
            });
            
            // Validar coincidencia de contraseñas en tiempo real
            confirmPassword.addEventListener('input', validatePasswordMatch);
            
            function validatePasswordMatch() {
                const errorElement = document.getElementById('confirmPasswordError');
                if (password.value !== confirmPassword.value) {
                    errorElement.textContent = "Las contraseñas no coinciden";
                    errorElement.classList.add('show');
                    confirmPassword.classList.add('border-red-500');
                } else {
                    errorElement.textContent = "";
                    errorElement.classList.remove('show');
                    confirmPassword.classList.remove('border-red-500');
                }
            }
            
            // Validación antes de enviar el formulario
            form.addEventListener('submit', function(event) {
                let isValid = true;
                
                // Validar email
                const email = document.getElementById('email').value;
                const emailError = document.getElementById('emailError');
                if (!email) {
                    emailError.textContent = "El correo electrónico es requerido";
                    emailError.classList.add('show');
                    isValid = false;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    emailError.textContent = "Correo electrónico no válido";
                    emailError.classList.add('show');
                    isValid = false;
                }
                
                // Validar contraseña
                const passValue = password.value;
                const passError = document.getElementById('passwordError');
                if (!passValue) {
                    passError.textContent = "La contraseña es requerida";
                    passError.classList.add('show');
                    isValid = false;
                } else {
                    let errorMsg = [];
                    if (passValue.length < 8) errorMsg.push("Mínimo 8 caracteres");
                    if (!/[A-Z]/.test(passValue)) errorMsg.push("Debe contener al menos una mayúscula");
                    if (!/[0-9]/.test(passValue)) errorMsg.push("Debe contener al menos un número");
                    if (!/[!@#$%^&*]/.test(passValue)) errorMsg.push("Debe contener un carácter especial (!@#$%^&*)");
                    
                    if (errorMsg.length > 0) {
                        passError.textContent = errorMsg.join(", ");
                        passError.classList.add('show');
                        isValid = false;
                    }
                }
                
                // Validar confirmación
                if (password.value !== confirmPassword.value) {
                    document.getElementById('confirmPasswordError').textContent = "Las contraseñas no coinciden";
                    document.getElementById('confirmPasswordError').classList.add('show');
                    confirmPassword.classList.add('border-red-500');
                    isValid = false;
                }
                
                if (!isValid) {
                    event.preventDefault();
                }
            });
        });
    </script>
</body>
</html>