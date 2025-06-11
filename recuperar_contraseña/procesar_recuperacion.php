<?php
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
date_default_timezone_set('Bolivia/GMT-4'); // zona horaria
include('../includes/db.php');

// Configuración segura (mover a archivo de configuración)
define('SMTP_USER', 'eviitamariavargas@gmail.com');
define('SMTP_PASS', 'owys hito vpta ydlg');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar el correo
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        header("Location: recuperar_contraseña.php?error=Correo no válido");
        exit;
    }

    // Verificar si el correo existe
    $query = "SELECT id FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header("Location: recuperar_contraseña.php?error=El correo no está registrado");
        exit;
    }

    // Generar código numérico de 6 dígitos
    $codigo = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $codigo_expiracion = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    // Actualizar en la base de datos
    $query = "UPDATE usuarios SET codigo_verificacion = ?, codigo_expiracion = ? WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $codigo, $codigo_expiracion, $email);
    
    if (!$stmt->execute()) {
        header("Location: recuperar_contraseña.php?error=Error al generar código");
        exit;
    }

    // Enviar correo
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        $mail->setFrom(SMTP_USER, 'Sistema de Biblioteca');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Código de recuperación';
        $mail->Body = "Tu código de verificación es: <b>$codigo</b>. Válido por 10 minutos.";
        
        $mail->send();
        header("Location: verificar_codigo.php?email=" . urlencode($email) . "&success=Código enviado");
        exit;
    } catch (Exception $e) {
        error_log("Error al enviar email: " . $e->getMessage());
        header("Location: recuperar_contraseña.php?error=Error al enviar el código");
        exit;
    }
} else {
    // Si no es un POST, redirigir al formulario
    header("Location: recuperar_contraseña.php");
    exit;
}
?>