<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class TestMailerController extends AbstractController
{
    #[Route('/testmail', name: 'test_mail')]
    public function sendTestMail(MailerInterface $mailer): Response
    {
        try {
            $email = (new Email())
               ->from('Mirindra Test <mirindranavalonamamisoa@gmail.com>') // ton email Gmail
                ->to('mandaandrianavalona8@gmail.com')  // destinataire de test
                ->subject('Test Symfony Mailer')
                ->text("Salut ! Ceci est un test d'envoi d'email via Symfony Mailer 😎");

            $mailer->send($email);

            return new Response('✅ Mail envoyé ! Vérifie la boîte de réception de manjakaandrianavalona12@gmail.com.');
        } catch (\Exception $e) {
            return new Response('❌ Erreur lors de l’envoi du mail : '.$e->getMessage());
        }
    }
}
