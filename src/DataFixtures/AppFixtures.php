<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $regularUser = new User();
        $regularUser
            ->setEmail('freelance@test.local')
            ->setPassword($this->hasher->hashPassword($regularUser, 'test'));

        $manager->persist($regularUser);

        $adminUser = new User();
        $adminUser
            ->setEmail('admin@freelancehub.local')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->hasher->hashPassword($adminUser, 'test'));

        $manager->persist($adminUser);

        $manager->flush();
    }
}