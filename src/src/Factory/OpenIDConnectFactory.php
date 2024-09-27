<?php

namespace App\Factory;

use App\Repository\IdentityProviderRepository;
use App\Repository\SettingRepository;
use App\Service\IDP\IdentityProviderInterface;
use App\Service\OpenIDConnect;
use Jumbojett\OpenIDConnectClient;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class OpenIDConnectFactory
{
    public function __construct(
        private readonly IdentityProviderRepository $identityProviderRepository,
        private readonly SettingRepository $settingRepository,
        private readonly ParameterBagInterface $params
    ) {
    }

    /**
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function create(): OpenIDConnect
    {
        $ssoSetting = $this->settingRepository->findOneByName('sso');
        if (is_null($ssoSetting)) {
            throw new LogicException('SSO settings not found');
        }

        $identityProviderSetting = $this->identityProviderRepository->findOneByClassName($ssoSetting->getValue());
        if (is_null($identityProviderSetting)) {
            throw new LogicException('Identity Provider settings not found');
        }

        $defaultRedirectUri = $identityProviderSetting->getRedirectUrl() ?? $this->params->get('default_redirect_uri');

        $identityProvider = $this->getIdentityProviderObject($ssoSetting->getValue());

        $identityProvider->setTenant($identityProviderSetting->getTenant())
            ->setClientId($identityProviderSetting->getClientId())
            ->setClientSecret($identityProviderSetting->getClientSecret())
            ->addExtraFields($identityProviderSetting->getExtraFields())
            ->setRedirectUri($defaultRedirectUri);

        $openIDConnectClient = new OpenIDConnectClient($identityProvider->getProviderUrl());

        return new OpenIDConnect($openIDConnectClient, $identityProvider);
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

        return new $identityProviderClassName();
    }
}
