<?php

namespace App\Security\Voter;

use App\Entity\Department;
use App\Entity\Users;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DepartmentVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === 'MANAGE' && $subject instanceof Department;
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token
    ): bool {
        $user = $token->getUser();

        if (!$user instanceof Users) {
            return false;
        }

        /** @var Department $department */
        $department = $subject;

        return $user->isManager()
            && $department->getManager() === $user;
    }
}
