<?php

namespace App\Service;

use App\Repository\UserRepository;
use Jumbojett\OpenIDConnectClientException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthManager
{
    public const _SECURITY_MAIN = '_security_main';
    public const _LOGIN_METHOD = '_login_method';

    private SessionInterface $session;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly TokenValidator $tokenValidator,
        RequestStack $requestStack,
    ) {
        $this->session = $requestStack->getSession();
    }

    public function authenticateCredentials(string $username, string $password): void
    {
        $user = $this->userRepository->findOneBy(['username' => $username]);

        if (!$user || md5($password) !== $user->getPassword()) {
            throw new AuthenticationException('Invalid credentials.');
        }

        $this->authorizeByCredentials($user);

        $this->session->set('login_method', 'Credentials');
    }

    /**
     * @throws AuthenticationException
     * @throws OpenIDConnectClientException
     */
    public function authenticateSso(OpenIDConnect $openIdConnect): void
    {
        $idToken = $openIdConnect->getIdToken();
        $this->tokenValidator->validate($idToken);

        $userInfo = $openIdConnect->requestUserInfo();
        if (empty($userInfo->email)) {
            throw new AuthenticationException('Email address not identified.');
        }

        $user = $this->userRepository->findOneBy(['username' => $userInfo->email]);
        if (!$user) {
            throw new AuthenticationException('User not registered.');
        }

        $this->authorizeBySso($user);

        $this->session->set(self::_LOGIN_METHOD, $openIdConnect->getIdentityProviderName());
    }

    protected function authorizeByCredentials(UserInterface $user): void
    {
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->authorize($token);
    }

    protected function authorizeBySso(UserInterface $user): void
    {
        $token = new PreAuthenticatedToken($user, 'main', $user->getRoles());
        $this->authorize($token);
    }

    private function authorize(TokenInterface $token): void
    {
        $this->tokenStorage->setToken($token);
        $this->session->set(self::_SECURITY_MAIN, serialize($token));
    }

    public function logout(): void
    {
        $this->tokenStorage->setToken(null);
        $this->session->remove(self::_SECURITY_MAIN);
        $this->session->remove(self::_LOGIN_METHOD);
    }

    public function isSsoLoggedIn(): bool
    {
        return $this->tokenStorage->getToken() instanceof PreAuthenticatedToken;
    }
}
