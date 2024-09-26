<?php


namespace App\Service\IDP;


abstract class IdentityProviderExtraFieldsAbstract implements IdentityProviderExtraFieldsInterface
{
    protected array $extraFields = [];

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
