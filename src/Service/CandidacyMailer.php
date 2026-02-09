<?php

namespace App\Service;

use App\Entity\Candidacy;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class CandidacyMailer
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    private function sendEmail(
        Candidacy $candidacy,
        string $subject,
        string $html
    ): bool {
        try {
            $email = (new Email())
                ->from('nextworkapp@gmail.com')
                ->to($candidacy->getUser()->getEmail())
                ->subject($subject)
                ->html($html);

            $this->mailer->send($email);
            return true;
        } catch (\Throwable $e) {
            dump($e->getMessage());
            return false;
        }
    }

    public function sendInterviewEmail(Candidacy $candidacy): bool
    {
        $html = "
            <p>Bonjour {$candidacy->getUser()->getFirstName()},</p>
            <p>Vous êtes invité à un entretien pour l'offre
            <strong>{$candidacy->getJobOffer()->getTitle()}</strong>.</p>
        ";

        return $this->sendEmail($candidacy, 'Invitation entretien', $html);
    }

    public function sendAcceptedEmail(Candidacy $candidacy): bool
    {
        $html = "
            <p>Félicitations {$candidacy->getUser()->getFirstName()} 🎉</p>
            <p>Votre candidature pour l'offre
            <strong>{$candidacy->getJobOffer()->getTitle()}</strong>
            a été acceptée.</p>
        ";

        return $this->sendEmail($candidacy, 'Candidature acceptée', $html);
    }
}
