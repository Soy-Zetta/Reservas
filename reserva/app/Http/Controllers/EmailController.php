<?php
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;

class EmailController
{
    public function sendTestEmail(MailerInterface $mailer)
    {
        $email = (new Email())
            ->from('jeison0603k@gmail.com')
            ->to('usuario@ejemplo.com')
            ->subject('Prueba de reserva')
            ->text('¡Su reserva fue aprobada!')
            ->html('<p>Ver detalles: <a href="#">aquí</a></p>');

        $mailer->send($email);
        return new Response('¡Correo enviado!');
    }
}

