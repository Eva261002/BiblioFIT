<?php
include('includes/db.php'); // Conexión a la base de datos
 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Verificar que las contraseñas coincidan
    if ($password !== $confirm_password) {
        echo "Las contraseñas no coinciden.";
        exit;
    }

    // Verificar si el correo ya existe
    $query = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "El correo ya está registrado. Intenta con otro.";
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Correo electrónico no válido.";
        exit;
    }
    
    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[!@#$%^&*]/', $password)) {
        echo "La contraseña debe tener al menos 8 caracteres, una letra mayúscula, un número y un carácter especial.";
        exit;
    }
    
    // Encriptar la contraseña
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insertar el usuario en la base de datos
    $insert_query = "INSERT INTO usuarios (email, contraseña, rol) VALUES (?, ?, 'usuario')";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ss", $email, $hashed_password);

    if ($stmt->execute()) {
        $message = "Cuenta creada con éxito. Puedes <a href='login.php'>iniciar sesión</a> ahora.";
    } else {
        $message = "Error al crear la cuenta. Por favor, intenta nuevamente.";
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
    <script src="scripts/login.js"></script>
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
            <form action="registro_login.php" method="POST" onsubmit="return validarRegistro()">
    <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
        <input type="email" id="email" name="email" required class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div class="mb-4">
        <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
        <input type="password" id="password" name="password" required class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div class="mb-4">
        <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
        <input type="password" id="confirm_password" name="confirm_password" required class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Crear Cuenta</button>
</form>

            <div class="mt-4 text-center">
                <p class="text-sm text-gray-600">¿Ya tienes cuenta? <a href="login.php" class="text-blue-600 hover:text-blue-700">Iniciar sesión</a></p>
            </div>
        </div>
        <?php if (!empty($message)): ?>
                <div class="mb-4 p-4 text-center text-white 
                    <?php echo (strpos($message, 'éxito') !== false) ? 'bg-green-500' : 'bg-red-500'; ?> 
                    rounded-md">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?> 
            
    </section>

    <footer class="bg-gray-800 text-white py-6">
        <div class="container mx-auto text-center">
            &copy; 2024 Sistema de Biblioteca - FIT-UABJB. Todos los derechos reservados.
        </div>
    </footer>
</body>
</html>
