<?php

namespace App\Controller;

use App\Entity\IdentityProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends MainController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($tokenStorage);
    }

    #[Route('/dashboard/admin', name: 'admin_only')]
    #[IsGranted('ROLE_ADMIN')]
    public function admin(): Response
    {
        return $this->renderUserDetails('Admin');
    }

    #[Route('/dashboard/user', name: 'user_access')]
    #[IsGranted('ROLE_USER')]
    public function user(Request $request): Response
    {
        return $this->renderUserDetails('User');
    }

    #[Route('/dashboard/identity-providers', name: 'available_identity_providers')]
    #[IsGranted('ROLE_ADMIN')]
    public function availableIdentityProviders(Request $request): Response
    {
        $identityProviders = $this->entityManager->getRepository(IdentityProvider::class)->findAll();
        return $this->render('Dashboard/identity-providers.html.twig', [
            'identityProviders' => $identityProviders
        ]);
    }

    private function renderUserDetails(string $userType): Response
    {
        $user = $this->getUser();

        return $this->render('Dashboard/index.html.twig', [
            'userType' => $userType,
            'isSsoAuthenticated' => $this->isSsoAuthenticated(),
            'user' => $user,
        ]);
    }
}
