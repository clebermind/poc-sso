<?php

namespace App\Entity;

use App\Repository\IdentityProviderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IdentityProviderRepository::class)]
class IdentityProvider
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $class_name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $redirect_url = null;

    #[ORM\Column]
    private array $scope = [];

    #[ORM\Column(length: 255)]
    private ?string $tenant = null;

    #[ORM\Column(length: 255)]
    private ?string $client_id = null;

    #[ORM\Column(length: 255)]
    private ?string $client_secret = null;

    #[ORM\Column]
    private array $extra_fields = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getClassName(): ?string
    {
        return $this->class_name;
    }

    public function setClassName(string $className): static
    {
        $this->class_name = $className;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirect_url;
    }

    public function setRedirectUrl(?string $redirect_url): static
    {
        $this->redirect_url = $redirect_url;

        return $this;
    }

    public function getScope(): array
    {
        return $this->scope;
    }

    public function setScope(array $scope): static
    {
        $this->scope = $scope;

        return $this;
    }

    public function getTenant(): ?string
    {
        return $this->tenant;
    }

    public function setTenant(string $tenant): static
    {
        $this->tenant = $tenant;

        return $this;
    }

    public function getClientId(): ?string
    {
        return $this->client_id;
    }

    public function setClientId(string $client_id): static
    {
        $this->client_id = $client_id;

        return $this;
    }

    public function getClientSecret(): ?string
    {
        return $this->client_secret;
    }

    public function setClientSecret(string $client_secret): static
    {
        $this->client_secret = $client_secret;

        return $this;
    }

    public function getExtraFields(): array
    {
        return $this->extra_fields;
    }

    public function setExtraFields(array $extra_fields): static
    {
        $this->extra_fields = $extra_fields;

        return $this;
    }
}
