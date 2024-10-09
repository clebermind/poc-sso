<?php

namespace App\Service;

use GuzzleHttp\Exception\GuzzleException;
use Jumbojett\OpenIDConnectClient;
use Jumbojett\OpenIDConnectClientException;
use App\Service\IDP\IdentityProviderInterface;
use Exception;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OpenIDConnect
{
    private readonly string $codeVerifierStr;
    private ?string $identityProviderName = null;

    public function __construct(
        private readonly OpenIDConnectClient $client,
        private readonly IdentityProviderInterface $identityProvider,
        private readonly SessionInterface $session,
    ) {
        $this->setClientId($this->identityProvider->getClientId());
        $this->setClientSecret($this->identityProvider->getClientSecret());
        $this->setRedirectURL($this->identityProvider->getRedirectUri());
        $this->codeVerifierStr = '__sso_code_verifier';
    }

    public function setIdentityProviderName(string $identityProviderName): static
    {
        $this->identityProviderName = $identityProviderName;

        return $this;
    }

    public function getIdentityProviderName(): string
    {
        return $this->identityProviderName;
    }

    /**
     * @throws GuzzleException
     */
    public function getIdentityProviderConfiguration(): array
    {
        return $this->identityProvider->getConfiguration();
    }

    public function setClientId(string $clientId): static
    {
        $this->client->setClientId($clientId);

        return $this;
    }

    public function refreshToken(string $refreshToken): bool
    {
        try {
            $this->client->refreshToken($refreshToken);
        } catch (OpenIDConnectClientException|Exception $exception) {
            error_log($exception->getMessage());
            return false;
        }

        return true;
    }

    public function getClientId(): string
    {
        return $this->client->getClientId();
    }

    public function setClientSecret(string $clientSecret): static
    {
        $this->client->setClientSecret($clientSecret);

        return $this;
    }

    public function setRedirectURL(string $redirectUri): static
    {
        $this->client->setRedirectURL($redirectUri);

        return $this;
    }

    /**
     * @throws OpenIDConnectClientException
     */
    public function authenticate(): void
    {
        $this->client->authenticate();
    }

    public function getAccessToken(): ?string
    {
        return $this->client->getAccessToken();
    }

    public function getIdToken(): ?string
    {
        return $this->client->getIdToken();
    }

    public function getRefreshToken(): ?string
    {
        return $this->client->getRefreshToken();
    }

    /**
     * @throws OpenIDConnectClientException
     */
    public function requestUserInfo(string $attribute = null): mixed
    {
        return $this->client->requestUserInfo($attribute);
    }

    public function useCodeChallenge(): bool
    {
        try {
            $codeChallenge = $this->generateCodeChallenge();
        } catch (RandomException $exception) {
            error_log($exception->getMessage());
            return false;
        }

        $this->client->setCodeChallengeMethod($codeChallenge);

        return true;
    }

    public function eraseCodeChallenge(): void
    {
        $this->session->remove($this->codeVerifierStr);
    }

    /**
     * @throws RandomException
     */
    private function generateCodeChallenge(): string
    {
        if ($this->session->has($this->codeVerifierStr)) {
            $codeVerifier = $this->session->get($this->codeVerifierStr);
        } else {
            $codeVerifier = bin2hex(random_bytes(32));
            $this->session->set($this->codeVerifierStr, $codeVerifier);
        }

        return strtr(rtrim(base64_encode(hash('sha256', $codeVerifier, true)), '='), '+/', '-_');
    }
}
