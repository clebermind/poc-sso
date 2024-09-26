<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends MainController
{
    #[Route('/admin', name: 'admin_only')]
    public function admin(): Response
    {
        return $this->renderUserDetails('Admin');
    }

    #[Route('/user', name: 'user_access')]
    public function user(Request $request): Response
    {
        return $this->renderUserDetails('User');
    }

    private function renderUserDetails(string $userType): Response
    {
        $user = $this->getUser();

        return $this->render('User/index.html.twig', [
            'userType' => $userType,
            'isSsoAuthenticated' => $this->isSsoAuthenticated(),
            'user' => $user,
        ]);
    }
}
