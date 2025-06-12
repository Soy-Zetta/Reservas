<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Carga las dependencias

// Configuración de Gmail
$gmail_user = 'jeison0603k@gmail.com';
$gmail_password = 'uiyp azht ahfm oamu'; // Aquí va la contraseña especial
$recipient = 'jeisonortega881@gmail.com'; // Cambia esto

// Crear instancia de PHPMailer
$mail = new PHPMailer(true);

try {
    // Configuración del servidor SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $gmail_user;
    $mail->Password = $gmail_password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Configurar remitente y destinatario
    $mail->setFrom($gmail_user, 'Sistema de Reservas');
    $mail->addAddress($recipient);

    // Contenido del correo
    $mail->isHTML(true);
    $mail->Subject = 'Prueba de correo desde tu sitio';
    $mail->Body    = '<h1>¡Funciona!</h1><p>Este es un correo de prueba enviado desde tu sistema de reservas.</p>';
    $mail->AltBody = '¡Funciona! Este es un correo de prueba enviado desde tu sistema de reservas.';

    // Enviar el correo
    $mail->send();
    echo 'El correo se ha enviado correctamente';
} catch (Exception $e) {
    echo "Error al enviar el correo: {$mail->ErrorInfo}";
}