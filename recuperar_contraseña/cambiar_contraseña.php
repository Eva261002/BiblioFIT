<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['email_verificado'])) {
    header("Location: ../login.php");
    exit;
}

$email = $_SESSION['email_verificado'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nueva_password = $_POST['nueva_password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';

    // Validación de contraseña
    if (strlen($nueva_password) < 8) {
        $error = "La contraseña debe tener al menos 8 caracteres";
    } elseif ($nueva_password !== $confirmar_password) {
        $error = "Las contraseñas no coinciden";
    } else {
        $hashed_password = password_hash($nueva_password, PASSWORD_BCRYPT);
        $query = "UPDATE usuarios SET contraseña = ?, codigo_verificacion = NULL, codigo_expiracion = NULL WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $hashed_password, $email);

        if ($stmt->execute()) {
            session_destroy();
            header("Location: ../login.php?success=Contraseña actualizada. Inicia sesión.");
            exit;
        } else {
            $error = "Error al actualizar la contraseña";
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
</head>
<body class="bg-gray-100 flex justify-center items-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-semibold text-center mb-4">Nueva Contraseña</h2>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-4">
                <label for="nueva_password" class="block text-sm font-medium text-gray-700">Nueva Contraseña</label>
                <input type="password" id="nueva_password" name="nueva_password" required
                       class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       minlength="8">
                <p class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres</p>
            </div>
            
            <div class="mb-4">
                <label for="confirmar_password" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                <input type="password" id="confirmar_password" name="confirmar_password" required
                       class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Cambiar Contraseña
            </button>
        </form>
    </div>
</body>
</html>