<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends MainController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($tokenStorage);
    }

    #[Route('/users', name: 'user_list')]
    #[IsGranted('ROLE_USER')]
    public function list(): Response
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();

        return $this->render('User/list.html.twig', [
            'users' => $users,
        ]);
    }
}
