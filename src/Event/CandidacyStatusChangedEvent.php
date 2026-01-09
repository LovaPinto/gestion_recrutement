<?php
namespace App\Event;

use App\Entity\Candidacy;
use Symfony\Contracts\EventDispatcher\Event;

class CandidacyStatusChangedEvent extends Event
{
    public const NAME = 'candidacy.status_changed';

    public function __construct(private Candidacy $candidacy) {}

    public function getCandidacy(): Candidacy
    {
        return $this->candidacy;
    }
}
