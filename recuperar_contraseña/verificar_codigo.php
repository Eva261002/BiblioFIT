<?php
// en verificar codigo si el codigo es incorrecto o ya expiro, abajo del boton podrias poner algo como
//no has recibido el codigo? volver a enviar y que se envie de nuevo otro codigo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $codigo = $_POST['codigo'];

    if (!$email || !$codigo) {
        $response = ["status" => "error", "message" => "Por favor, ingresa todos los datos."];
    } else {
        include('../includes/db.php');

        // Verificar el código ingresado
        $query = "SELECT codigo_verificacion, codigo_expiracion FROM usuarios WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $codigo_correcto = $row['codigo_verificacion'];
            $codigo_expiracion = $row['codigo_expiracion'];

            // Verificar si el código es correcto y no ha expirado
            if ($codigo == $codigo_correcto && strtotime($codigo_expiracion) > time()) {
                // Código correcto y no expirado
                header("Location: cambiar_contraseña.php?email=" . urlencode($email));
                exit;
            } else {
                $response = ["status" => "error", "message" => "El código es incorrecto o ha expirado. Intenta de nuevo."];
            }
        } else {
            $response = ["status" => "error", "message" => "El correo ingresado no está registrado."];
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Código - Sistema de Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
    <section class="flex-grow flex justify-center items-center bg-gradient-to-r from-blue-500 to-indigo-600">
        <div class="w-full max-w-sm bg-white p-8 rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold text-center mb-4">Verificar Código</h2>
            <?php
            if (isset($response)) {
                echo '<p class="text-red-600 text-center mb-4">' . htmlspecialchars($response["message"]) . '</p>';
            }
            ?>
            <form action="verificar_codigo.php" method="POST">
                <div class="mb-4">
                    <label for="codigo" class="block text-sm font-medium text-gray-700">Código de Verificación</label>
                    <input type="text" id="codigo" name="codigo" required class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
                <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Verificar Código</button>
            </form>
        </div>
    </section>
</body>
</html>
