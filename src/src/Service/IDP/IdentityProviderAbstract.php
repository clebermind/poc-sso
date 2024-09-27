<?php


namespace App\Service\IDP;

use GuzzleHttp\ClientInterface;

abstract class IdentityProviderAbstract implements IdentityProviderInterface
{
    protected string $redirectUrl;
    protected array $scope = ['openid'];
    protected string $tenant;
    protected string $clientId;
    protected string $clientSecret;
    protected array $extraFields = [];

    public function __construct(protected ClientInterface $httpClient)
    {
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function setClientId(string $clientId): static
    {
        $this->clientId = $clientId;
        
        return $this;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function setClientSecret(string $clientSecret): static
    {
        $this->clientSecret = $clientSecret;
        
        return $this;
    }

    public function getTenant(): string
    {
        return $this->tenant;
    }

    public function setTenant(string $tenant): static
    {
        $this->tenant = $tenant;
        
        return $this;
    }

    public function getRedirectUri(): string
    {
        return $this->redirectUrl;
    }

    public function setRedirectUri(string $uri): static
    {
        $this->redirectUrl = $uri;

        return $this;
    }

    public function getScope(): array
    {
        return $this->scope;
    }

    public function addScope(array|string $scope): static
    {
        if (is_array($scope)) {
            $this->scope = array_merge($this->scope, $scope);
        } else {
            $this->scope[] = $scope;
        }

        return $this;
    }

    public function removeScope(string $scope): static
    {
        unset($this->scope[$scope]);

        return $this;
    }

    public function addExtraField(string $name, mixed $value): static
    {
        $this->extraFields[$name] = $value;

        return $this;
    }

    public function addExtraFields(array $extraFields): static
    {
        $this->extraFields = array_merge($this->extraFields, $extraFields);

        return $this;
    }

    public function deleteExtraField(string $fieldName): static
    {
        unset($this->extraFields[$fieldName]);

        return $this;
    }

    public function getExtraFields(): array
    {
        return $this->extraFields;
    }
}
