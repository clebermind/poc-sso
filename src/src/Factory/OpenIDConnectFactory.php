<?php

namespace App\Factory;

use App\Service\IDP\IdentityProviderInterface;
use App\Service\OpenIDConnect;
use App\Service\SingleSignOnSettings;
use GuzzleHttp\Client;
use Jumbojett\OpenIDConnectClient;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class OpenIDConnectFactory
{
    public function __construct(
        private readonly ParameterBagInterface $params,
        private readonly Client $httpClient,
        private readonly RequestStack $requestStack,
        private readonly SingleSignOnSettings $singleSignOnSettings,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function create(): OpenIDConnect
    {
        $identityProviderSetting = $this->singleSignOnSettings->getIdentityProviderSetting();

        $ssoSettingValue = $this->singleSignOnSettings->getSsoSetting();
        $identityProvider = $this->getIdentityProviderObject($ssoSettingValue);

        $defaultRedirectUri = $identityProviderSetting->getRedirectUrl() ?? $this->params->get('default_redirect_uri');

        $identityProvider->setClientId($identityProviderSetting->getClientId())
            ->setClientSecret($identityProviderSetting->getClientSecret())
            ->addExtraFields($identityProviderSetting->getExtraFields())
            ->addScope($identityProviderSetting->getScope())
            ->setRedirectUri($defaultRedirectUri);

        $openIDConnectClient = new OpenIDConnectClient($identityProvider->getProviderUrl());
        $openIDConnectClient->addScope($identityProvider->getScope());

        $openIdConnect = new OpenIDConnect($openIDConnectClient, $identityProvider,  $this->requestStack->getSession());
        $openIdConnect->setIdentityProviderName($identityProviderSetting->getName());

        return $openIdConnect;
    }

    /**
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    private function getIdentityProviderObject(string $className): IdentityProviderInterface
    {
        $identityProviderClassName = sprintf("\App\Service\IDP\%s", $className);
        if (!class_exists($identityProviderClassName)) {
            throw new InvalidArgumentException("Identity Provider class {$identityProviderClassName} does not exist.");
        }

        if (!in_array(IdentityProviderInterface::class, class_implements($identityProviderClassName))) {
            throw new LogicException(
                "IDP class {$identityProviderClassName} must implement " . IdentityProviderInterface::class
            );
        }

        return new $identityProviderClassName($this->httpClient);
    }
}
