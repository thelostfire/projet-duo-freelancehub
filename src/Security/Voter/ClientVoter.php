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

        // if the user is anonymous, do not grant access
        // Ici faire un check du client qui appartient bien à l'owner?
        if (!$user instanceof User) {
            $vote?->addReason("This user cannot access other user's clients data");

            return false;
        }

        $client = $subject;

        return $client->getOwner();
    }
}
