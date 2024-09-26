<?php

namespace App\Service\IDP;

interface IdentityProviderExtraFieldsInterface
{
    public function addExtraField(string $name, mixed $value): static;
    public function addExtraFields(array $extraFields): static;
    public function getExtraFields(): array;
    public function deleteExtraField(string $fieldName): static;
}
