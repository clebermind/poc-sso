<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;

abstract class MainController extends AbstractController
{
    protected ?TokenInterface $securityToken;
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->securityToken = $tokenStorage->getToken();
    }

    public function isSsoAuthenticated(): bool
    {
        return $this->securityToken  instanceof PreAuthenticatedToken;
    }
}