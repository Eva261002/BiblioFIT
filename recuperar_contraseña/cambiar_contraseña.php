<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['email_verificado'])) {
    header("Location: ../login.php");
    exit;
}

$email = $_SESSION['email_verificado'];
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nueva_password = $_POST['nueva_password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';

    // Validación de contraseña
    if (empty($nueva_password)) {
        $errors['nueva_password'] = "La contraseña es requerida";
    } elseif (strlen($nueva_password) < 8) {
        $errors['nueva_password'] = "Mínimo 8 caracteres";
    } elseif (!preg_match('/[A-Z]/', $nueva_password)) {
        $errors['nueva_password'] = "Debe contener al menos una mayúscula";
    } elseif (!preg_match('/[0-9]/', $nueva_password)) {
        $errors['nueva_password'] = "Debe contener al menos un número";
    } elseif (!preg_match('/[!@#$%^&*]/', $nueva_password)) {
        $errors['nueva_password'] = "Debe contener un carácter especial (!@#$%^&*)";
    }

    // Validación de confirmación
    if ($nueva_password !== $confirmar_password) {
        $errors['confirmar_password'] = "Las contraseñas no coinciden";
    }

    if (empty($errors)) {
        $hashed_password = password_hash($nueva_password, PASSWORD_BCRYPT);
        $query = "UPDATE usuarios SET contraseña = ?, codigo_verificacion = NULL, codigo_expiracion = NULL WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $hashed_password, $email);

        if ($stmt->execute()) {
            session_destroy();
            header("Location: ../login.php?success=Contraseña actualizada. Inicia sesión.");
            exit;
        } else {
            $errors['general'] = "Error al actualizar la contraseña";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .requirement {
            transition: all 0.3s ease;
        }
        .requirement.valid {
            color: #10B981;
        }
        .requirement.invalid {
            color: #6B7280;
        }
        .error-message {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        .error-message.show {
            max-height: 50px;
        }
    </style>
</head>
<body class="bg-gray-100 flex justify-center items-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md mx-4">
        <div class="text-center mb-6">
            <i class="fas fa-lock text-4xl text-blue-500 mb-3"></i>
            <h2 class="text-2xl font-semibold">Nueva Contraseña</h2>
            <p class="text-gray-600 mt-1">Crea una nueva contraseña segura</p>
        </div>
        
        <?php if (isset($errors['general'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($errors['general']) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="passwordForm">
            <div class="mb-4">
                <label for="nueva_password" class="block text-sm font-medium text-gray-700 mb-1">Nueva Contraseña</label>
                <div class="relative">
                    <input type="password" id="nueva_password" name="nueva_password" required
                           class="w-full px-4 py-2 border <?= isset($errors['nueva_password']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10">
                    <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" id="togglePassword">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
                <div class="error-message text-sm text-red-600 <?= isset($errors['nueva_password']) ? 'show' : '' ?>" id="passwordError">
                    <?= $errors['nueva_password'] ?? '' ?>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    <p class="font-medium mb-1">La contraseña debe contener:</p>
                    <ul class="space-y-1">
                        <li class="requirement invalid" id="lengthReq"><i class="fas fa-check-circle mr-1"></i> Mínimo 8 caracteres</li>
                        <li class="requirement invalid" id="upperReq"><i class="fas fa-check-circle mr-1"></i> Al menos una mayúscula</li>
                        <li class="requirement invalid" id="numberReq"><i class="fas fa-check-circle mr-1"></i> Al menos un número</li>
                        <li class="requirement invalid" id="specialReq"><i class="fas fa-check-circle mr-1"></i> Al menos un carácter especial (!@#$%^&*)</li>
                    </ul>
                </div>
            </div>
            
            <div class="mb-6">
                <label for="confirmar_password" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Contraseña</label>
                <div class="relative">
                    <input type="password" id="confirmar_password" name="confirmar_password" required
                           class="w-full px-4 py-2 border <?= isset($errors['confirmar_password']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10">
                    <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" id="toggleConfirmPassword">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
                <div class="error-message text-sm text-red-600 <?= isset($errors['confirmar_password']) ? 'show' : '' ?>" id="confirmPasswordError">
                    <?= $errors['confirmar_password'] ?? '' ?>
                </div>
            </div>
            
            <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <i class="fas fa-sync-alt mr-2"></i> Cambiar Contraseña
            </button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('nueva_password');
            const confirmInput = document.getElementById('confirmar_password');
            const togglePassword = document.getElementById('togglePassword');
            const toggleConfirm = document.getElementById('toggleConfirmPassword');
            
            // Mostrar/ocultar contraseña
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="far fa-eye"></i>' : '<i class="far fa-eye-slash"></i>';
            });
            
            toggleConfirm.addEventListener('click', function() {
                const type = confirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmInput.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="far fa-eye"></i>' : '<i class="far fa-eye-slash"></i>';
            });
            
            // Validación en tiempo real
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                const lengthValid = password.length >= 8;
                const upperValid = /[A-Z]/.test(password);
                const numberValid = /[0-9]/.test(password);
                const specialValid = /[!@#$%^&*]/.test(password);
                
                // Actualizar indicadores visuales
                document.getElementById('lengthReq').className = lengthValid ? 'requirement valid' : 'requirement invalid';
                document.getElementById('upperReq').className = upperValid ? 'requirement valid' : 'requirement invalid';
                document.getElementById('numberReq').className = numberValid ? 'requirement valid' : 'requirement invalid';
                document.getElementById('specialReq').className = specialValid ? 'requirement valid' : 'requirement invalid';
                
                // Validar confirmación si hay valor
                if (confirmInput.value) {
                    validatePasswordMatch();
                }
            });
            
            // Validar coincidencia de contraseñas
            confirmInput.addEventListener('input', validatePasswordMatch);
            
            function validatePasswordMatch() {
                const errorElement = document.getElementById('confirmPasswordError');
                if (passwordInput.value !== confirmInput.value) {
                    errorElement.textContent = "Las contraseñas no coinciden";
                    errorElement.classList.add('show');
                    confirmInput.classList.add('border-red-500');
                } else {
                    errorElement.textContent = "";
                    errorElement.classList.remove('show');
                    confirmInput.classList.remove('border-red-500');
                }
            }
            
            // Validación antes de enviar
            document.getElementById('passwordForm').addEventListener('submit', function(e) {
                let isValid = true;
                
                // Validar contraseña
                const password = passwordInput.value;
                const passwordError = document.getElementById('passwordError');
                
                if (!password) {
                    passwordError.textContent = "La contraseña es requerida";
                    passwordError.classList.add('show');
                    isValid = false;
                } else {
                    let errorMsg = [];
                    if (password.length < 8) errorMsg.push("Mínimo 8 caracteres");
                    if (!/[A-Z]/.test(password)) errorMsg.push("Debe contener al menos una mayúscula");
                    if (!/[0-9]/.test(password)) errorMsg.push("Debe contener al menos un número");
                    if (!/[!@#$%^&*]/.test(password)) errorMsg.push("Debe contener un carácter especial (!@#$%^&*)");
                    
                    if (errorMsg.length > 0) {
                        passwordError.textContent = errorMsg.join(", ");
                        passwordError.classList.add('show');
                        isValid = false;
                    }
                }
                
                // Validar confirmación
                if (passwordInput.value !== confirmInput.value) {
                    document.getElementById('confirmPasswordError').textContent = "Las contraseñas no coinciden";
                    document.getElementById('confirmPasswordError').classList.add('show');
                    confirmInput.classList.add('border-red-500');
                    isValid = false;
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>