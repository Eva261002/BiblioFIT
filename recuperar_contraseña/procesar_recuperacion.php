<?php

require '../vendor/autoload.php'; // Autocarga de dependencias, incluyendo PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        die("Correo no válido.");
    }
    

    // Verificar si el correo existe
    $query = "SELECT id FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generar código de verificación
        $codigo = rand(100000, 999999); // Código de 6 dígitos
        $codigo_expiracion = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        // Actualizar en la base de datos
        $query = "UPDATE usuarios SET codigo_verificacion = ?, codigo_expiracion = ? WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iss", $codigo, $codigo_expiracion, $email);
        $stmt->execute();

        // Enviar correo con PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'eviitamariavargas@gmail.com';  // Tu correo de Gmail
            $mail->Password = 'owys hito vpta ydlg';  // Tu contraseña de Gmail
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('eviitamariavargas@gmail.com', 'Sistema de Biblioteca');
            $mail->addAddress($email, 'Usuario');

            $mail->isHTML(true);
            $mail->Subject = 'Pin para cambiar clave';
            $mail->Body = "Tu código de verificación es: <b>$codigo</b>. El código expira en 10 minutos.";

            
            $mail->send();
            // Redirigir a la página de verificación con mensaje
            header("Location: verificar_codigo.php?email=" . urlencode($email) . "&message=Codigo enviado");
            exit;
        } catch (Exception $e) {
            echo "No se pudo enviar el mensaje. Error de Mailer: {$mail->ErrorInfo}";
        }
    } else {
        echo "El correo ingresado no está registrado.";
    }
}
?>
