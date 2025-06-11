<?php
session_start();
include('../includes/db.php');

// Manejar reenvío de código
if (isset($_GET['reenviar'])) {  // ← Aquí estaba el error
    $email = filter_var($_GET['email'], FILTER_VALIDATE_EMAIL);
    if ($email) {
        // Incluir lógica de reenvío (similar a procesar_recuperacion.php)
        $codigo = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $codigo_expiracion = date("Y-m-d H:i:s", strtotime("+10 minutes"));
        
        $query = "UPDATE usuarios SET codigo_verificacion = ?, codigo_expiracion = ? WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $codigo, $codigo_expiracion, $email);
        $stmt->execute();
        
        // Redirigir con mensaje de éxito
        header("Location: verificar_codigo.php?email=".urlencode($email)."&success=Se ha reenviado un nuevo código a $email");
        exit;
    }
}

// Procesar verificación del código
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $codigo = trim($_POST['codigo']); // Eliminar espacios en blanco

    if (!$email || !$codigo) {
        $error = "Por favor, ingresa todos los datos.";
    } else {
        $query = "SELECT codigo_verificacion, codigo_expiracion FROM usuarios WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Normalizar códigos como cadenas
            $codigo_ingresado = (string) $codigo;
            $codigo_almacenado = (string) $row['codigo_verificacion'];
            // Depuración
            error_log("Código ingresado: '$codigo_ingresado'");
            error_log("Código en BD: '$codigo_almacenado'");
            error_log("Expiración: '{$row['codigo_expiracion']}', Tiempo actual: " . date('Y-m-d H:i:s', time()));
            error_log("Expiración en segundos: " . strtotime($row['codigo_expiracion']) . ", Tiempo actual: " . time());

            // Separar condiciones para mejor diagnóstico
            if ($codigo_ingresado === $codigo_almacenado) {
                if (strtotime($row['codigo_expiracion']) > time()) {
                    $_SESSION['email_verificado'] = $email;
                    header("Location: cambiar_contraseña.php");
                    exit;
                } else {
                    $error = "El código ha expirado";
                }
            } else {
                $error = "Código incorrecto";
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
    <title>Verificar Código</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
    <section class="flex-grow flex justify-center items-center bg-gradient-to-r from-blue-500 to-indigo-600">
        <div class="w-full max-w-sm bg-white p-8 rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold text-center mb-4">Verificar Código</h2>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-4">
                    <label for="codigo" class="block text-sm font-medium text-gray-700">Código de 6 dígitos</label>
                    <input type="text" id="codigo" name="codigo" required maxlength="6" pattern="\d{6}"
                           class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="123456">
                </div>
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
                
                <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 mb-4">
                    Verificar Código
                </button>
                
                <div class="text-center">
                    <a href="verificar_codigo.php?reenviar=1&email=<?php echo urlencode($_GET['email'] ?? ''); ?>" 
                       class="text-blue-600 hover:text-blue-800 text-sm">
                        ¿No recibiste el código? Haz clic aquí para reenviar
                    </a>
                </div>
                
                <div class="mt-4 text-center text-sm text-gray-600">
                    El código fue enviado a: <span class="font-medium"><?php echo htmlspecialchars($_GET['email'] ?? ''); ?></span>
                </div>
            </form>
        </div>
    </section>
</body>
</html>