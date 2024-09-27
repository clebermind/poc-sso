<?php

namespace App\Service\IDP;

use GuzzleHttp\Exception\GuzzleException;

interface IdentityProviderInterface
{
    public function getClientId(): string;
    public function setClientId(string $clientId): static;
    public function getClientSecret(): string;
    public function setClientSecret(string $clientSecret): static;
    public function getTenant(): string;
    public function setTenant(string $tenant): static;
    public function getRedirectUri(): string;
    public function setRedirectUri(string $uri): static;
    public function getScope(): array;
    public function addScope(string|array $scope): static;
    public function removeScope(string $scope): static;
    public function getProviderUrl(): string;
    /**
     * @throws GuzzleException
     */
    public function getConfiguration(): array;
}
