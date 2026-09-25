<?php

namespace App\DataFixtures;

use App\Entity\Client;
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

        // clients de test, rattachés au user normal
        $client1 = new Client();
        $client1
            ->setName('Atelier Lumière')
            ->setCompany('Atelier Lumière SARL')
            ->setEmail('contact@atelierlumiere.fr')
            ->setPhone('0601020304')
            ->setOwner($regularUser);
        $manager->persist($client1);

        $client2 = new Client();
        $client2
            ->setName('Studio Nova')
            ->setCompany('Studio Nova')
            ->setEmail('contact@studionova.fr')
            ->setPhone('0605060708')
            ->setOwner($regularUser);
        $manager->persist($client2);

        // un client rattaché à l'admin, pour bien tester que le voter isole les deux
        $client3 = new Client();
        $client3
            ->setName('Café des Arts')
            ->setCompany('Café des Arts')
            ->setEmail('contact@cafedesarts.fr')
            ->setPhone('0611121314')
            ->setOwner($adminUser);
        $manager->persist($client3);

        $manager->flush();
    }
}