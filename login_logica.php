<?php
include('includes/db.php'); // Conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
 
    // Validación básica
    if (empty($email) || empty($password)) {
        header("Location: login.php?error=campos_vacios");
        exit;
    }

    // Buscar al usuario en la base de datos
    $query = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
  
    if ($result->num_rows === 0) {
        header("Location: login.php?error=credenciales_invalidas");
        exit;
    }

    $user = $result->fetch_assoc();

    // Verificar la contraseña
    if (!password_verify($password, $user['contraseña'])) {
        header("Location: login.php?error=credenciales_invalidas");
        exit;
    }

    // Iniciar sesión
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['rol'] = $user['rol'];
    
    // Redirigir según el rol
    header("Location: index.php");
    exit;
}
?>