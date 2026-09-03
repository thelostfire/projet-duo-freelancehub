<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/*
Controller de test pour vérifier l'authentification par JWT, à supprimer à la fin du projet
*/
final class ToastController extends AbstractController
{
    #[Route('/api/test', name: 'app_toast')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Authentifié !',
            'user' => $this->getUser()?->getUserIdentifier()
        ]);
    }
}
