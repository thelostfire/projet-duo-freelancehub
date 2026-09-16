<?php

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

#[AsEventListener(event: LoginSuccessEvent::class)]
class JwtCookieListener
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager
    ) {}

    // méthode 'magique' php, convention utilisée par asEventListener quand on a pas quelque chose de plus explicite à mettre
    public function __invoke(LoginSuccessEvent $event): void
    {
        
        // comme loginSuccessEvent se déclanche pour 2 firewalls (ici main et login), on utilise ce check pour s'assurer que ça ne s'effectue que pour le login twig,
        // pour éviter une génération de jwt redondante quand on passe par api/login_check
        // -> à voir si avec le removal de certains trucs de test ce check sera toujours requis. Trop fatigué atm.
        if($event->getFirewallName() !== 'main') {
            return;
        }

        $response = $event->getResponse();

        // check pour éviter un crash dans le cas où getResponse() renvoie null
        if (!$response) {
            return;
        }

        /** @var User $user */
        $user = $event->getUser();
        $token = $this->jwtManager->create($user);

        // génération du token en cookie
        $response->headers->setCookie(
            Cookie::create('jwt_token')
                ->withValue($token) // récupère les données du jwt généré avant et les mets dans le cookie
                ->withHttpOnly(true) // empêche l'accès au cookie via javascript, pour éviter les failles XSS
                ->withSecure(false) // false en dev, à passer en true en prod une fois qu'on a le certif (http'S' -> secure !)
                ->withSameSite('lax') // protection CSRF basique, à voir si 'strict' est pas mieux
                ->withPath('/')
        );
    }
}