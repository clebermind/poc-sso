<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;

class TokenManager
{
    private ?SessionInterface $session = null;

    public function __construct(
        private readonly CacheClient $cacheClient,
        private readonly Encryptor $encryptor,
        private RequestStack $requestStack
    ) {
        if ($this->requestStack->getCurrentRequest()->hasSession()) {
            $this->session = $requestStack->getSession();
        }
    }

    public function setSession(SessionInterface $session): static
    {
        $this->session = $session;

        return $this;
    }

    public function storeTokens(string $accessToken, ?string $refreshToken = null): bool
    {
        if (is_null($this->session)) {
            return false;
        }

        $identifier = $this->getIdentifier();
        $this->cacheClient->set("{$identifier}_at", $this->encryptor->encrypt($accessToken));

        if (!empty($refreshToken)) {
            $this->cacheClient->set("{$identifier}_rt", $this->encryptor->encrypt($refreshToken));
        }

        return true;
    }

    /**
     * @throws TokenNotFoundException
     */
    public function getAccessToken(): ?string
    {
        if (is_null($this->session)) {
            return null;
        }

        $identifier = $this->getIdentifier();
        return $this->encryptor->decrypt($this->cacheClient->get("{$identifier}_at"));
    }

    /**
     * @throws TokenNotFoundException
     */
    public function getRefreshTokenToken(): ?string
    {
        if (is_null($this->session)) {
            return null;
        }

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
