<?php

namespace App\Service;

use App\Entity\Candidacy;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class CandidacyMailer
{
    public function __construct(private MailerInterface $mailer) {}

    // Envoi direct sans Twig
    private function sendEmailDirect(Candidacy $candidacy, string $subject, string $htmlContent): bool
    {
        try {
            $email = (new Email())
                ->from('nextworkapp@gmail.com')
                ->to($candidacy->getUser()->getEmail())
                ->subject($subject)
                ->html($htmlContent);

            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            dump($e->getMessage()); // Affiche l'erreur réelle si échec
            return false;
        }
    }

    public function sendInterviewEmail(Candidacy $candidacy): bool
    {
        $html = "<p>Bonjour ".$candidacy->getUser()->getFirstName().",</p>
                 <p>Vous êtes invité à un entretien pour l'offre : <strong>".$candidacy->getJobOffer()->getTitle()."</strong>.</p>
                 <p>Merci de vous connecter à votre compte pour plus de détails.</p>";

        return $this->sendEmailDirect($candidacy, 'Invitation entretien', $html);
    }

    public function sendAcceptedEmail(Candidacy $candidacy): bool
    {
        $html = "<p>Félicitations ".$candidacy->getUser()->getFirstName()." !</p>
                 <p>Votre candidature pour l'offre <strong>".$candidacy->getJobOffer()->getTitle()."</strong> a été acceptée.</p>";

        return $this->sendEmailDirect($candidacy, 'Candidature acceptée', $html);
    }
}
