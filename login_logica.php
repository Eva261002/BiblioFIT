<?php
include('includes/db.php'); // Conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Buscar al usuario en la base de datos
    $query = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verificar la contraseña
        if (password_verify($password, $user['contraseña'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['rol'] = $user['rol'];
            echo "Inicio de sesión exitoso. Redirigiendo...";
            header("Location: index.php");
        } else {
            echo "Contraseña incorrecta.";
        }
    } else {
        echo "Usuario no encontrado.";
    }
}
?>
