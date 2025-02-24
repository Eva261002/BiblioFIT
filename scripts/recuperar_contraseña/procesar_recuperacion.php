<?php
include('includes/db.php');

include('PROYECTO_BF/login.php'/'procesar_recuperacion.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Verificar si el correo está registrado
    $query = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generar un token único
        $token = bin2hex(random_bytes(50));
        $query = "UPDATE usuarios SET token_recuperacion = ?, token_expiracion = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $token, $email);
        $stmt->execute();

        // Enviar correo con el enlace
        $enlace = "http://tu-sitio.com/restablecer_contraseña.php?token=$token";
        $mensaje = "Haz clic en el siguiente enlace para restablecer tu contraseña: $enlace";
        mail($email, "Recuperación de Contraseña", $mensaje);

        echo "Se han enviado las instrucciones a tu correo.";
    } else {
        echo "El correo electrónico no está registrado.";
    }
}
?>
