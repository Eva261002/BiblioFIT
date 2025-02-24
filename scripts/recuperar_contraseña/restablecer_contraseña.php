<?php
include('includes/db.php');
include('PROYECTO_BF/login.php');

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $query = "SELECT * FROM usuarios WHERE token_recuperacion = ? AND token_expiracion > NOW()";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        die("Token inválido o expirado.");
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nueva_contraseña = password_hash($_POST['nueva_contraseña'], PASSWORD_DEFAULT);

    $query = "UPDATE usuarios SET contraseña = ?, token_recuperacion = NULL, token_expiracion = NULL WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $nueva_contraseña, $user['id']);
    $stmt->execute();

    echo "Contraseña restablecida con éxito.";
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>
    <section class="flex justify-center items-center h-screen bg-gray-100">
        <div class="w-full max-w-sm bg-white p-8 rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold text-center mb-4">Restablecer Contraseña</h2>
            <form action="" method="POST">
                <div class="mb-4">
                    <label for="nueva_contraseña" class="block text-sm font-medium text-gray-700">Nueva Contraseña</label>
                    <input type="password" id="nueva_contraseña" name="nueva_contraseña" required class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Restablecer Contraseña</button>
            </form>
        </div>
    </section>
</body>
</html>
