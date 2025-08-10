<?php
session_start();
include('../includes/db.php');

// Manejar reenvío de código
if (isset($_GET['reenviar'])) {
    $email = filter_var($_GET['email'], FILTER_VALIDATE_EMAIL);
    if ($email) {
        // Generar nuevo código
        $codigo = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $codigo_expiracion = date("Y-m-d H:i:s", strtotime("+10 minutes"));
        
        // Actualizar en la base de datos
        $query = "UPDATE usuarios SET codigo_verificacion = ?, codigo_expiracion = ? WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $codigo, $codigo_expiracion, $email);
        
        if ($stmt->execute()) {
            // Redirigir con mensaje de éxito
            header("Location: verificar_codigo.php?email=".urlencode($email)."&success=Se+ha+reenviado+un+nuevo+código");
            exit;
        } else {
            $error = "Error al reenviar el código";
        }
    }
}

// Procesar verificación del código
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $codigo = trim($_POST['codigo']);

    if (!$email || !$codigo) {
        $error = "Por favor, ingresa todos los datos";
    } else {
        $query = "SELECT codigo_verificacion, codigo_expiracion FROM usuarios WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $codigo_ingresado = (string) $codigo;
            $codigo_almacenado = (string) $row['codigo_verificacion'];
            
            if ($codigo_ingresado === $codigo_almacenado) {
                if (strtotime($row['codigo_expiracion']) > time()) {
                    $_SESSION['email_verificado'] = $email;
                    header("Location: cambiar_contraseña.php");
                    exit;
                } else {
                    $error = "El código ha expirado. Por favor solicita uno nuevo";
                }
            } else {
                $error = "Código incorrecto. Inténtalo de nuevo";
            }
        } else {
            $error = "Correo no encontrado";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Código | Sistema de Biblioteca</title>
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
        .input-code {
            letter-spacing: 0.5em;
            font-size: 1.5rem;
            text-align: center;
        }
        .timer {
            font-family: monospace;
        }
    </style>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
    <section class="flex-grow flex justify-center items-center bg-gradient-to-br from-blue-600 to-indigo-700 py-12 px-4">
        <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-lg transform transition-all hover:scale-[1.01]">
            <!-- Encabezado -->
            <div class="text-center mb-8">
                <div class="mx-auto bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-shield-alt text-blue-600 text-2xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Verificación en Dos Pasos</h1>
                <p class="text-gray-600 mt-2">Ingresa el código de 6 dígitos que enviamos a:</p>
                <p class="font-medium text-gray-800"><?= htmlspecialchars($_GET['email'] ?? '') ?></p>
            </div>

            <!-- Mensajes de estado -->
            <?php if (isset($_GET['success'])): ?>
                <div class="animate-fade-in bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span><?= htmlspecialchars($_GET['success']) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="animate-fade-in bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Formulario -->
            <form method="POST" class="space-y-6">
                <div>
                    <label for="codigo" class="block text-sm font-medium text-gray-700 mb-2">Código de Verificación</label>
                    <input 
                        type="text" 
                        id="codigo" 
                        name="codigo" 
                        required 
                        maxlength="6" 
                        pattern="\d{6}"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 input-code"
                        placeholder="••••••"
                        autofocus
                    >
                    <p class="mt-1 text-xs text-gray-500">Ingresa el código de 6 dígitos que recibiste por correo</p>
                </div>
                <input type="hidden" name="email" value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
                
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600 timer" id="timer">
                        El código expira en: <span id="countdown">10:00</span>
                    </div>
                    <a 
                        href="verificar_codigo.php?reenviar=1&email=<?= urlencode($_GET['email'] ?? '') ?>" 
                        class="text-sm font-medium text-blue-600 hover:text-blue-500"
                        id="resendLink"
                        style="display: none;"
                    >
                        <i class="fas fa-redo mr-1"></i> Reenviar código
                    </a>
                </div>
                
                <button 
                    type="submit" 
                    class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow transition duration-300 flex items-center justify-center"
                >
                    <i class="fas fa-check-circle mr-2"></i> Verificar Código
                </button>
            </form>
            
            <div class="mt-6 text-center text-sm text-gray-600">
                <a href="../login.php" class="font-medium text-blue-600 hover:text-blue-500">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al inicio de sesión
                </a>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-avance entre dígitos del código
            const codeInput = document.getElementById('codigo');
            codeInput.addEventListener('input', function() {
                if (this.value.length === 6) {
                    this.form.submit();
                }
            });
            
            // Temporizador de expiración
            let minutes = 9;
            let seconds = 59;
            const countdownElement = document.getElementById('countdown');
            const resendLink = document.getElementById('resendLink');
            
            const countdown = setInterval(function() {
                countdownElement.textContent = 
                    (minutes < 10 ? '0' + minutes : minutes) + ':' + 
                    (seconds < 10 ? '0' + seconds : seconds);
                
                if (minutes === 0 && seconds === 0) {
                    clearInterval(countdown);
                    countdownElement.textContent = 'Expirado';
                    countdownElement.classList.add('text-red-500');
                    resendLink.style.display = 'inline';
                } else {
                    if (seconds === 0) {
                        minutes--;
                        seconds = 59;
                    } else {
                        seconds--;
                    }
                }
            }, 1000);
            
            // Validación del formulario
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const code = codeInput.value.trim();
                
                if (code.length !== 6 || !/^\d+$/.test(code)) {
                    e.preventDefault();
                    alert('Por favor ingresa un código válido de 6 dígitos');
                    codeInput.focus();
                }
            });
        });
    </script>
</body>
</html>