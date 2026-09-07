<?php

namespace App\Security\Voter;

use App\Entity\Client;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ClientVoter extends Voter
{
    public const ACCESS = 'CLIENT_ACCESS';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::ACCESS && $subject instanceof Client;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
{
    $user = $token->getUser();

    if (!$user instanceof User) {
        $vote?->addReason("This user cannot access other user's clients data");

        return false;
    }

    if (!$subject instanceof Client) {
        return false;
    }

    //Les instanceof vérifient bien qu'on a un User/Client pour éviter des erreurs (à voir peut-être avec des tests plus tard?)

    return $subject->getOwner() === $user;
}
}
