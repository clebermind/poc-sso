<?php

namespace App\Controller;

use App\Entity\IdentityProvider;
use App\Entity\Setting;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Exception;

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
        $settings = $this->entityManager->getRepository(Setting::class)->findOneBy(['name' => 'sso']);
        $identityProviders = $this->entityManager->getRepository(IdentityProvider::class)->findAll();

        return $this->render('Dashboard/identity-providers.html.twig', [
            'identityProviders' => $identityProviders,
            'enableIdp' => $settings->getValue(),
        ]);
    }

    #[Route('/dashboard/identity-providers/enable', name: 'enable_identity_provider')]
    #[IsGranted('ROLE_ADMIN')]
    public function enableIdentityProvider(Request $request): Response
    {
        $identityProviderId = $request->request->get('idp_id');
        if (empty($identityProviderId)) {
            return $this->redirectToRoute(
                'available_identity_providers',
                ['message' => 'Identity provider is mandatory for this action']
            );
        }

        $identityProvider = $this->entityManager->getRepository(IdentityProvider::class)->find($identityProviderId);
        if (empty($identityProvider)) {
            return $this->redirectToRoute(
                'available_identity_providers',
                ['message' => 'Identity provider not found']
            );
        }

        $setting = $this->entityManager->getRepository(Setting::class)->findOneBy(['name' => 'sso']);
        if (empty($setting)) {
            return $this->redirectToRoute(
                'available_identity_providers',
                ['message' => 'SSO setting not set up']
            );
        }

        try {
            $setting->setValue($identityProvider->getClassName());
            $this->entityManager->persist($setting);
            $this->entityManager->flush();
        } catch (Exception $e) {
            return $this->redirectToRoute(
                'available_identity_providers',
                ['message' => 'Error while saving: ' . $e->getMessage()]
            );
        }

        return $this->redirectToRoute('available_identity_providers', ['message' => 'Updated!']);
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
