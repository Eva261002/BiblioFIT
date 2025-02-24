<?php
session_start();
include('../includes/db.php');

// Asegúrate de que el email del usuario está almacenado en la sesión
if (!isset($_SESSION['email'])) {
    die('Acceso no autorizado.');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_SESSION['email'];
    $nueva_password = $_POST['nueva_password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';

    if (empty($nueva_password) || empty($confirmar_password)) {
        $mensaje_error = 'Todos los campos son obligatorios.';
    } elseif ($nueva_password !== $confirmar_password) {
        $mensaje_error = 'Las contraseñas no coinciden.';
    } else {
        // Actualizar contraseña
        $hashed_password = password_hash($nueva_password, PASSWORD_BCRYPT);
        $query = "UPDATE usuarios SET contraseña = ? WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $hashed_password, $email);

        if ($stmt->execute()) {
            $mensaje_exito = 'Contraseña actualizada exitosamente. Redirigiendo al login...';
            session_destroy(); // Elimina sesión después del cambio
            header("refresh:3;url=login.php"); // Redirigir al login
        } else {
            $mensaje_error = 'Error al actualizar la contraseña. Inténtalo de nuevo.';
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex justify-center items-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-semibold text-center mb-4">Cambiar Contraseña</h2>

        <?php if (isset($mensaje_exito)): ?>
            <p class="bg-green-100 text-green-700 p-2 rounded mb-4 text-center"><?php echo htmlspecialchars($mensaje_exito); ?></p>
        <?php endif; ?>

        <?php if (isset($mensaje_error)): ?>
            <p class="bg-red-100 text-red-700 p-2 rounded mb-4 text-center"><?php echo htmlspecialchars($mensaje_error); ?></p>
        <?php endif; ?>

        <form action="cambiar_contraseña.php" method="POST">
            <div class="mb-4">
                <label for="nueva_password" class="block text-sm font-medium text-gray-700">Nueva Contraseña</label>
                <input type="password" id="nueva_password" name="nueva_password" class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="mb-4">
                <label for="confirmar_password" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                <input type="password" id="confirmar_password" name="confirmar_password" class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Cambiar Contraseña</button>
        </form>
    </div>
</body>
</html>
