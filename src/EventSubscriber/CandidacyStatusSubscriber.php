<?php
namespace App\EventSubscriber;

use App\Event\CandidacyStatusChangedEvent;
use App\Service\CandidacyMailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CandidacyStatusSubscriber implements EventSubscriberInterface
{
    public function __construct(private CandidacyMailer $mailer) {}

    public static function getSubscribedEvents(): array
    {
        return [
            CandidacyStatusChangedEvent::NAME => 'onStatusChanged'
        ];
    }

    public function onStatusChanged(CandidacyStatusChangedEvent $event): void
    {
        $candidacy = $event->getCandidacy();
        $status = strtolower($candidacy->getStatus());

        switch ($status) {
            case 'invité à un entretien':
                $this->mailer->sendInterviewEmail($candidacy);
                break;
            case 'acceptée':
                $this->mailer->sendAcceptedEmail($candidacy);
                break;
            default:
                // refusée → pas d'email
                break;
        }
    }
}
