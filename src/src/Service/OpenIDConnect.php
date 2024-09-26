<?php

namespace App\Service;

use Jumbojett\OpenIDConnectClient;
use Jumbojett\OpenIDConnectClientException;
use App\Service\IDP\IdentityProviderInterface;

class OpenIDConnect
{
    private array $scope = ['openid', 'profile', 'offline_access'];

    public function __construct(
        private readonly OpenIDConnectClient $client,
        private readonly IdentityProviderInterface $identityProvider,
    ) {
        $this->setClientId($this->identityProvider->getClientId());
        $this->setClientSecret($this->identityProvider->getClientSecret());
        $this->setRedirectURL($this->identityProvider->getRedirectUri());
    }

    public function setClientId(string $clientId): static
    {
        $this->client->setClientId($clientId);

        return $this;
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

    public function getScope(): array
    {
        return $this->scope;
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

    public function getVerifiedClaims(): mixed
    {
        return $this->client->getVerifiedClaims();
    }

    /**
     * @throws OpenIDConnectClientException
     */
    public function requestUserInfo(string $attribute = null): mixed
    {
        return $this->client->requestUserInfo($attribute);
    }

    /**
     * @throws OpenIDConnectClientException
     */
    public function verifyJWTSignature(string $jwt): bool
    {
        return $this->client->verifyJWTSignature($jwt);
    }
}
