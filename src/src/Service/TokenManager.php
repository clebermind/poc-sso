<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;

class TokenManager
{
    private SessionInterface $session;

    public function __construct(
        private readonly CacheClient $cacheClient,
        private readonly Encryptor $encryptor,
        private RequestStack $requestStack
    ) {
        $this->session = $requestStack->getSession();
    }

    public function storeTokens(string $accessToken, ?string $refreshToken = null): void
    {
        $identifier = $this->getIdentifier();
        $this->cacheClient->set("{$identifier}_at", $this->encryptor->encrypt($accessToken));
        $this->cacheClient->set("{$identifier}_rt", $this->encryptor->encrypt($refreshToken));
    }

    /**
     * @throws TokenNotFoundException
     */
    public function getAccessToken(): ?string
    {
        $identifier = $this->getIdentifier();
        return $this->encryptor->decrypt($this->cacheClient->get("{$identifier}_at"));
    }

    /**
     * @throws TokenNotFoundException
     */
    public function getRefreshTokenToken(): ?string
    {
        $identifier = $this->getIdentifier();
        return $this->encryptor->decrypt($this->cacheClient->get("{$identifier}_rt"));
    }

    /**
     * @throws TokenNotFoundException
     */
    private function getIdentifier(): string
    {
        $token = unserialize($this->session->get(AuthManager::_SECURITY_MAIN));
        if (!($token instanceof TokenInterface)) {
            throw new TokenNotFoundException('Could not find a token for the session');
        }

        return md5($token->getUser()->getUserIdentifier());
    }
}
