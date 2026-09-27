<?php
// tests/Functional/AuthTest.php
// A noter: on utilise le DAMADoctrineTestBundle pour reset la bdd de test après chaque, euh, test. 
// Il faut qu'elle soit sur InnoDB, avec MyISAM ça marche pas.
// J'ai pas du tout perdu une demi-heure avant de trouver le problème.

namespace App\Tests\Functional;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthTest extends WebTestCase
{
    public function testRegisterValid(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/register');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Register')->form([
            'registration_form[email]' => 'newuser@test.com',
            'registration_form[plainPassword]' => 'password123',
            'registration_form[agreeTerms]' => true,
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/login');
    }

        // test avec mail invalide
        public function testRegisterWithInvalidEmail(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/register');

        $form = $crawler->selectButton('Register')->form([
            'registration_form[email]' => 'not-an-email',
            'registration_form[plainPassword]' => 'password123',
            'registration_form[agreeTerms]' => true,
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(422);

        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'not-an-email']);

        $this->assertNull($user);
    }

    // test sasn accepter les termes & conditions
    public function testRegisterWithoutAcceptingTerms(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/register');

        $form = $crawler->selectButton('Register')->form([
            'registration_form[email]' => 'another@test.com',
            'registration_form[plainPassword]' => 'password123',
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(422);

        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'another@test.com']);

        $this->assertNull($user);
    }

    // test avec mauvais mot de passe
    public function testRegisterWithInvalidPassword(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/register');

        $form = $crawler->selectButton('Register')->form([
            'registration_form[email]' => 'another@user.com',
            'registration_form[plainPassword]' => '123',
            'registration_form[agreeTerms]' => true,
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(422);

        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'another@user.com']);

        $this->assertNull($user);
    }
}