<?php

namespace App\Service;

use App\Entity\IdentityProvider;
use App\Repository\IdentityProviderRepository;
use App\Repository\SettingRepository;
use LogicException;

class SingleSignOnSettings
{
    const SSO_SETTING_KEY_NAME = 'sso_setting';
    const IDENTITY_PROVIDER_SETTINGS_KEY_NAME = 'identity_provider_settings';

    public function __construct(
        private readonly IdentityProviderRepository $identityProviderRepository,
        private readonly SettingRepository $settingRepository,
        private readonly CacheClient $cacheClient,
    ) {
    }

    public function clearSsoSettingCache(): bool
    {
        return $this->cacheClient->delete(self::SSO_SETTING_KEY_NAME) > 0;
    }

    public function clearIdentityProviderSettingsCache(): bool
    {
        return $this->cacheClient->delete(self::IDENTITY_PROVIDER_SETTINGS_KEY_NAME) > 0;
    }

    /**
     * @throws LogicException
     */
    public function getSsoSetting(): ?string
    {
        $ssoSettingValue = $this->cacheClient->get(self::SSO_SETTING_KEY_NAME);
        if (is_null($ssoSettingValue)) {
            $ssoSetting = $this->settingRepository->findOneByName('sso');
            if (is_null($ssoSetting)) {
                throw new LogicException('SSO settings not found');
            }

            $ssoSettingValue = $ssoSetting->getValue();
            $this->cacheClient->set(self::SSO_SETTING_KEY_NAME, $ssoSettingValue);
        }

        return $ssoSettingValue;
    }

    /**
     * @throws LogicException
     */
    public function getIdentityProviderSetting(): IdentityProvider
    {
        $identityProviderSettingValue = $this->cacheClient->get(self::IDENTITY_PROVIDER_SETTINGS_KEY_NAME);
        if (is_null($identityProviderSettingValue)) {
            $ssoSettingValue = $this->getSsoSetting();
            $identityProviderSetting = $this->identityProviderRepository->findOneByClassName($ssoSettingValue);
            if (is_null($identityProviderSetting)) {
                throw new LogicException('Identity Provider settings not found');
            }

            $identityProviderSettingValue = json_encode([
                'client_id' => $identityProviderSetting->getClientId(),
                'client_secret' => $identityProviderSetting->getClientSecret(),
                'extra_fields' => $identityProviderSetting->getExtraFields(),
                'scope' => $identityProviderSetting->getScope(),
                'name' => $identityProviderSetting->getName(),
                'redirect_url' => $identityProviderSetting->getRedirectUrl(),
            ]);

            $this->cacheClient->set(self::IDENTITY_PROVIDER_SETTINGS_KEY_NAME, $identityProviderSettingValue);
        }

        $identityProviderSettingValue = json_decode($identityProviderSettingValue, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new LogicException("Not possible to retrieve Identity Provider settings");
        }

        $identityProvider = new IdentityProvider();
        $identityProvider->setClientId($identityProviderSettingValue['client_id'])
            ->setClientSecret($identityProviderSettingValue['client_secret'])
            ->setExtraFields($identityProviderSettingValue['extra_fields'])
            ->setScope($identityProviderSettingValue['scope'])
            ->setName($identityProviderSettingValue['name'])
            ->setRedirectUrl($identityProviderSettingValue['redirect_url']);

        return $identityProvider;
    }
}