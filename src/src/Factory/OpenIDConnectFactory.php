<?php

namespace App\Factory;

use App\Service\IDP\IdentityProviderInterface;
use App\Service\OpenIDConnect;
use Jumbojett\OpenIDConnectClient;
use InvalidArgumentException;
use LogicException;

final class OpenIDConnectFactory
{
    /**
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function create(): OpenIDConnect
    {
        $identityProvider = $this->getIdentityProviderObject();

        $tenant = '{---tenant---}';
        $clientId = '{---client-id---}';
        $clientSecret = '{---client---}';
        $redirectUri = 'http://localhost:8080/login/sso/callback';

        $extraFields = [];

        $identityProvider->setTenant($tenant)
            ->setClientId($clientId)
            ->setClientSecret($clientSecret)
            ->addExtraFields($extraFields)
            ->setRedirectUri($redirectUri);

        $openIDConnectClient = new OpenIDConnectClient($identityProvider->getProviderUrl());

        return new OpenIDConnect($openIDConnectClient, $identityProvider);
    }

    /**
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    private function getIdentityProviderObject(): IdentityProviderInterface
    {
        $identityProviderClassName = sprintf("\App\Service\IDP\%s", 'MicrosoftEntraId');
        if (!class_exists($identityProviderClassName)) {
            throw new InvalidArgumentException("Identity Provider class {$identityProviderClassName} does not exist.");
        }

        if (!in_array(IdentityProviderInterface::class, class_implements($identityProviderClassName))) {
            throw new LogicException(
                "IDP class {$identityProviderClassName} must implement " . IdentityProviderInterface::class
            );
        }

        return new $identityProviderClassName();
    }
}
