<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User as UserEntity;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserController extends MainController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;

        parent::__construct($tokenStorage);
    }

    #[Route('/users', name: 'user_list')]
    public function list(): Response
    {
        $users = $this->entityManager->getRepository(UserEntity::class)->findAll();

        return $this->render('User/list.html.twig', [
            'users' => $users,
        ]);
    }
}
