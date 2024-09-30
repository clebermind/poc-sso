<?php

namespace App\Service;

use GuzzleHttp\Exception\GuzzleException;
use Jumbojett\OpenIDConnectClient;
use Jumbojett\OpenIDConnectClientException;
use App\Service\IDP\IdentityProviderInterface;

class OpenIDConnect
{
    private ?string $identityProviderName = null;

    public function __construct(
        private readonly OpenIDConnectClient $client,
        private readonly IdentityProviderInterface $identityProvider,
    ) {
        $this->setClientId($this->identityProvider->getClientId());
        $this->setClientSecret($this->identityProvider->getClientSecret());
        $this->setRedirectURL($this->identityProvider->getRedirectUri());
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
}
