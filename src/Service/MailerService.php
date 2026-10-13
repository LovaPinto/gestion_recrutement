<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    public function send(string $to, string $subject, string $html): void
    {
        $email = (new Email())
            ->from('nextworkapp@gmail.com')
        ->to($to)
            ->subject($subject)
            ->html($html);

        $this->mailer->send($email);
    }
}
