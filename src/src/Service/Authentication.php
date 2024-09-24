<?php

namespace App\Service;

use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

class Authentication
{
    private SessionInterface $session;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly JwtDecoder $jwtDecoder,
        RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
    }

    public function authenticateWithCredentials(string $username, string $password): void
    {
        $user = $this->userRepository->findOneBy(['username' => $username]);

        if (!$user || md5($password) !== $user->getPassword()) {
            throw new AuthenticationException('Invalid credentials.');
        }

        $this->authorizeByCredentials($user);
    }

    public function authenticateSsoTokens(string $accessToken, string $idToken, ?string $refreshToken = null): void
    {
        $accessTokenPayload = $this->jwtDecoder->getPayload($accessToken);

        $emailAddress = $accessTokenPayload->email ?? null;
        if (is_null($emailAddress)) {
            throw new AuthenticationException('Email address not identified.');
        }

        $user = $this->userRepository->findOneBy(['username' => $emailAddress]);
        if (!$user) {
            throw new AuthenticationException('User not registered.');
        }

        $this->authorizeBySso($user, $accessToken, $idToken, $refreshToken);
    }

    protected function authorizeByCredentials(UserInterface $user): void
    {
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());

        $this->authorize($token);
    }

    protected function authorizeBySso(
        UserInterface $user,
        string $accessToken,
        string $idToken,
        ?string $refreshToken = null,
    ): void {
        $token = new PreAuthenticatedToken($user, 'main', $user->getRoles());
        $token->setAttributes([
            'accessToken' => $accessToken,
            'idToken' => $idToken,
            'refreshToken' => $refreshToken,
        ]);

        $this->authorize($token);
    }

    private function authorize(TokenInterface $token): void
    {
        $this->tokenStorage->setToken($token);
        $this->session->set('_security_main', serialize($token));
    }


    public function logout(): void
    {
        $this->tokenStorage->setToken(null);
        $this->session->remove('_security_main');
    }
}
