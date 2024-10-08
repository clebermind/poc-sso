<?php

namespace App\Service\IDP;

use GuzzleHttp\Exception\GuzzleException;
use LogicException;

interface IdentityProviderInterface
{
    public function getClientId(): string;
    public function setClientId(string $clientId): static;
    public function getClientSecret(): string;
    public function setClientSecret(string $clientSecret): static;
    public function getRedirectUri(): string;
    public function setRedirectUri(string $uri): static;
    public function getScope(): array;
    public function addScope(string|array $scope): static;
    public function removeScope(string $scope): static;
    public function addExtraField(string $name, mixed $value): static;
    public function addExtraFields(array $extraFields): static;
    public function getExtraFields(): array;
    public function deleteExtraField(string $fieldName): static;

    /**
     * @throws LogicException
     */
    public function getProviderUrl(): string;

    /**
     * @throws GuzzleException
     */
    public function getConfiguration(): array;
}
